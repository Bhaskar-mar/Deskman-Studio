<?php
/**
 * Integration tests for the deskman-templates plugin, run against a real
 * WordPress install with the plugin active.
 *
 *   WP_PATH=/path/to/wordpress php tests/php/run.php
 *
 * Creates and cleans up its own test data.
 *
 * @package DeskmanTemplates
 */

// phpcs:disable

$wp_path = getenv( 'WP_PATH' );
if ( ! $wp_path || ! is_file( rtrim( $wp_path, '/' ) . '/wp-load.php' ) ) {
	fwrite( STDERR, "Set WP_PATH to a WordPress install with the plugin active.\n" );
	exit( 2 );
}
$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REMOTE_ADDR'] = '203.0.113.' . mt_rand( 1, 250 ); // Fresh rate-limit bucket per run.

require rtrim( $wp_path, '/' ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/user.php';
do_action( 'rest_api_init' );

$passed = 0;
$failed = 0;
function check( $label, $cond, $detail = '' ) {
	global $passed, $failed;
	if ( $cond ) {
		$passed++;
		echo "  ✔ {$label}\n";
	} else {
		$failed++;
		echo "  ✘ {$label}" . ( $detail ? " — {$detail}" : '' ) . "\n";
	}
}
function section( $name ) {
	echo "\n{$name}\n";
}

$created = array();
add_filter( 'dmt_send_customer_acknowledgement', '__return_false' );
add_filter( 'pre_wp_mail', '__return_true' ); // Never send real email from tests.
add_filter( 'dmt_is_uploaded_file', '__return_true' ); // Allow fixture files as "uploads".

function old_token() {
	$time = (string) ( time() - 30 );
	return $time . '.' . hash_hmac( 'sha256', $time, wp_salt( 'nonce' ) . 'dmt-enquiry' );
}
function submit( array $params, array $files = array() ) {
	$req = new WP_REST_Request( 'POST', '/deskman-templates/v1/enquiries' );
	$req->set_body_params( $params );
	if ( $files ) {
		$req->set_file_params( $files );
	}
	return rest_do_request( $req );
}
function valid_params( $overrides = array() ) {
	return array_merge(
		array(
			'name'          => 'Test Person',
			'business'      => 'Test Dental',
			'email'         => 'test@example.com',
			'phone'         => '+1 (555) 010-1234',
			'requirements'  => 'Needs online booking.',
			'consent'       => '1',
			'website'       => '',
			'token'         => old_token(),
			'template'      => 'dental',
			'customisation' => wp_json_encode( array( 'clinic_name' => 'Test Dental', 'color_primary' => '#123ABC' ) ),
		),
		$overrides
	);
}
function fixture( $name, $contents ) {
	$path = wp_tempnam( $name );
	file_put_contents( $path, $contents );
	return array( 'name' => $name, 'type' => 'image/png', 'tmp_name' => $path, 'error' => UPLOAD_ERR_OK, 'size' => filesize( $path ) );
}

/* ------------------------------------------------------------------ */
section( 'Registry & routes' );

$industries = DMT_Registry::industries();
check( 'ten industries registered', 10 === count( $industries ) );
check( 'only Dental Clinics is available', array( 'dental-clinics' ) === array_keys( wp_list_filter( $industries, array( 'available' => true ) ) ) );
check( 'coming-soon industries have no template', '' === $industries['hospitals']['template'] );
check( 'every thumbnail file exists', array() === array_filter( $industries, function ( $i ) { return ! is_file( DMT_DIR . 'assets/img/' . $i['thumbnail'] ); } ) );
check( 'Templates page exists and is published', DMT_Routes::page_id() && 'publish' === get_post_status( DMT_Routes::page_id() ) );
check( 'industry URL is under the Templates page', str_ends_with( DMT_Routes::industry_url( 'dental-clinics' ), '/templates/dental-clinics/' ) );

// Filters allow adding industries without code changes elsewhere.
add_filter( 'dmt_industries', function ( $list ) { $list['vets'] = array( 'name' => 'Vets', 'template' => 'does-not-exist' ); return $list; } );
DMT_Registry::flush();
check( 'filtered industry with unknown template stays coming soon', false === DMT_Registry::industry( 'vets' )['available'] );
remove_all_filters( 'dmt_industries' );
DMT_Registry::flush();

/* ------------------------------------------------------------------ */
section( 'Field schema & sanitisation' );

$defaults = DMT_Fields::defaults( 'dental' );
foreach ( array( 'clinic_name', 'dentist_name', 'description', 'phone', 'email', 'address', 'hours', 'cta_text', 'color_primary', 'color_secondary', 'color_accent', 'hero_heading', 'hero_text', 'about_text', 'service_1_name', 'service_6_desc' ) as $key ) {
	check( "schema has {$key}", array_key_exists( $key, $defaults ) );
}
$clean = DMT_Fields::sanitize_customisation(
	'dental',
	array(
		'clinic_name'   => '<script>alert(1)</script>Smile <b>Co</b>',
		'color_primary' => 'red; background:url(x)',
		'color_accent'  => '#abc',
		'email'         => 'nope',
		'facebook_url'  => 'javascript:alert(1)',
		'instagram_url' => 'https://instagram.com/clinic',
		'phone'         => '+1 555<img> 0100',
		'hero_heading'  => str_repeat( 'x', 500 ),
		'unknown_key'   => 'ignored',
		'hours'         => "Mon: 9-5\nTue: 9-5",
	)
);
check( 'HTML stripped from text', 'Smile Co' === $clean['clinic_name'], $clean['clinic_name'] );
check( 'invalid colour falls back to default', $defaults['color_primary'] === $clean['color_primary'] );
check( 'short hex colours accepted', '#ABC' === $clean['color_accent'] );
check( 'invalid email falls back to default', $defaults['email'] === $clean['email'] );
check( 'javascript: URLs removed', '' === $clean['facebook_url'] );
check( 'https URLs kept', 'https://instagram.com/clinic' === $clean['instagram_url'] );
check( 'phone stripped to allowed characters', '+1 555 0100' === $clean['phone'], $clean['phone'] );
check( 'length limited to schema max', 90 === mb_strlen( $clean['hero_heading'] ) );
check( 'unknown keys discarded', ! isset( $clean['unknown_key'] ) );
check( 'multi-line text preserved', "Mon: 9-5\nTue: 9-5" === $clean['hours'] );
check( 'logo never accepted as a field value', ! isset( $clean['logo'] ) );

section( 'Colour helpers' );
check( 'white text on dark primary', '#FFFFFF' === DMT_Color::on( '#1E6FD9' ) );
check( 'dark text on light accent', '#0B1B2B' === DMT_Color::on( '#8FE3C8' ) );
check( 'ink darkens light colours for text', DMT_Color::luminance( DMT_Color::ink( '#FFE066' ) ) <= 0.18 );

/* ------------------------------------------------------------------ */
section( 'Enquiry REST endpoint' );

$res = submit( valid_params( array( 'token' => '' ) ) );
check( 'missing token rejected (400)', 400 === $res->get_status() );

$fresh = (string) time();
$res   = submit( valid_params( array( 'token' => $fresh . '.' . hash_hmac( 'sha256', $fresh, wp_salt( 'nonce' ) . 'dmt-enquiry' ) ) ) );
check( 'instant submission rejected (400)', 400 === $res->get_status() );

$res = submit( valid_params( array( 'website' => 'http://spam.example' ) ) );
check( 'honeypot rejected (400)', 400 === $res->get_status() );

$res    = submit( valid_params( array( 'email' => 'bad', 'phone' => '12', 'name' => 'A', 'consent' => '0', 'template' => 'hospitals' ) ) );
$fields = $res->get_data()['data']['fields'] ?? array();
check( 'invalid fields rejected (422)', 422 === $res->get_status() );
foreach ( array( 'email', 'phone', 'name', 'consent', 'template' ) as $f ) {
	check( "error for {$f}", isset( $fields[ $f ] ) );
}
$before = count( get_posts( array( 'post_type' => 'dmt_enquiry', 'post_status' => 'any', 'numberposts' => -1 ) ) );

$res = submit( valid_params(), array( 'logo' => fixture( 'logo.png', 'not really an image' ) ) );
check( 'non-image "PNG" logo rejected (422)', 422 === $res->get_status() && isset( $res->get_data()['data']['fields']['logo'] ) );

$big = fixture( 'logo.png', 'x' );
$big['size'] = DMT_Enquiries::LOGO_MAX_BYTES + 1;
$res = submit( valid_params(), array( 'logo' => $big ) );
check( 'oversized logo rejected (422)', 422 === $res->get_status() );

$after = count( get_posts( array( 'post_type' => 'dmt_enquiry', 'post_status' => 'any', 'numberposts' => -1 ) ) );
check( 'rejected submissions store nothing', $before === $after );

// Valid submission with a real PNG logo.
$img = imagecreatetruecolor( 120, 60 );
imagefill( $img, 0, 0, imagecolorallocate( $img, 30, 111, 217 ) );
ob_start();
imagepng( $img );
$png = ob_get_clean();
$mails = array();
remove_filter( 'pre_wp_mail', '__return_true' );
add_filter( 'pre_wp_mail', function ( $r, $atts ) use ( &$mails ) { $mails[] = $atts; return true; }, 10, 2 );

$res  = submit( valid_params( array( 'name' => "Eve\r\nBcc: x@evil.example" ) ), array( 'logo' => fixture( 'logo.png', $png ) ) );
$data = $res->get_data();
check( 'valid enquiry accepted (201)', 201 === $res->get_status(), wp_json_encode( $data ) );
$posts = get_posts( array( 'post_type' => 'dmt_enquiry', 'post_status' => 'any', 'meta_key' => '_dmt_reference', 'meta_value' => $data['reference'] ?? '-' ) );
$id    = $posts ? $posts[0]->ID : 0;
$created[] = $id;
check( 'enquiry stored', (bool) $id );
check( 'stored as private post', $id && 'private' === get_post_status( $id ) );
check( 'customisation stored sanitised', $id && '#123ABC' === get_post_meta( $id, '_dmt_customisation', true )['color_primary'] );
check( 'header injection stripped from name', $id && false === strpos( get_post_meta( $id, '_dmt_name', true ), "\n" ) );
$logo = $id ? DMT_Enquiries::logo_path( get_post_meta( $id, '_dmt_logo', true ) ) : '';
check( 'logo stored in private directory', $logo && false !== strpos( $logo, '/dmt-private/' ) );
check( 'stored logo is a valid PNG', $logo && 'image/png' === getimagesize( $logo )['mime'] );
check( 'logo not added to the media library', 0 === count( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'any', 's' => 'logo' ) ) ) );
check( 'private directory protected', is_file( dirname( $logo ) . '/.htaccess' ) && is_file( dirname( $logo ) . '/index.php' ) );
check( 'studio notification sent', 1 === count( $mails ) && false !== strpos( $mails[0]['message'], $data['reference'] ) );
check( 'notification headers have no injected lines', 1 === count( $mails ) && 1 === count( (array) $mails[0]['headers'] ) && false === strpos( implode( '', (array) $mails[0]['headers'] ), "\n" ) );

// Rate limiting.
add_filter( 'dmt_rate_limit', function () { return 1; } );
$res = submit( valid_params() );
check( 'rate limit enforced (429)', 429 === $res->get_status() );
remove_all_filters( 'dmt_rate_limit' );

/* ------------------------------------------------------------------ */
section( 'Permissions & privacy' );

$admin  = get_users( array( 'role' => 'administrator', 'number' => 1 ) )[0];
$editor = wp_insert_user( array( 'user_login' => 'dmt_test_editor_' . wp_generate_password( 6, false ), 'user_pass' => wp_generate_password(), 'role' => 'editor' ) );
wp_set_current_user( $editor );
check( 'editors cannot read enquiries', ! current_user_can( 'edit_post', $id ) );
check( 'editors cannot list enquiries', ! current_user_can( 'edit_dmt_enquiries' ) );
wp_set_current_user( 0 );
check( 'visitors cannot read enquiries', ! current_user_can( 'edit_post', $id ) );
check( 'enquiry post type not public', ! is_post_type_viewable( 'dmt_enquiry' ) );
check( 'enquiry post type not in REST', ! get_post_type_object( 'dmt_enquiry' )->show_in_rest );
wp_set_current_user( $admin->ID );
check( 'administrators can read enquiries', current_user_can( 'edit_post', $id ) );
check( 'nobody can create enquiries in admin', ! current_user_can( get_post_type_object( 'dmt_enquiry' )->cap->create_posts ) );
wp_set_current_user( 0 );
wp_delete_user( $editor );

$export = DMT_Privacy::export( 'test@example.com' );
check( 'personal data exporter finds enquiry', count( $export['data'] ) >= 1 );
$erase = DMT_Privacy::erase( 'test@example.com' );
check( 'personal data eraser removes enquiries', $erase['items_removed'] && 0 === count( get_posts( array( 'post_type' => 'dmt_enquiry', 'post_status' => 'any', 'meta_key' => '_dmt_email', 'meta_value' => 'test@example.com' ) ) ) );
check( 'erasing an enquiry deletes its logo', $logo && ! file_exists( $logo ) );

echo "\n{$passed} passed, {$failed} failed\n";
exit( $failed ? 1 : 0 );
