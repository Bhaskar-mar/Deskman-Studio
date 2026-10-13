<?php
/**
 * Integration tests for the site templates module of Deskman Studio Manager
 * (and its theme integration), run against a real WordPress install with the
 * Deskman Studio theme and the Manager plugin active.
 *
 *   WP_PATH=/path/to/wordpress php tests/php/run.php
 *
 * Creates and cleans up its own enquiries. Never sends email.
 * In environments without DNS, set the plugin's lead_check_mx option to '0'.
 */

// phpcs:disable

$wp_path = getenv( 'WP_PATH' );
if ( ! $wp_path || ! is_file( rtrim( $wp_path, '/' ) . '/wp-load.php' ) ) {
	fwrite( STDERR, "Set WP_PATH to a WordPress install with the plugin active.\n" );
	exit( 2 );
}
$_SERVER['HTTP_HOST']       = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REQUEST_URI']     = '/';
$_SERVER['REMOTE_ADDR']     = '203.0.113.' . mt_rand( 1, 250 ); // fresh rate-limit bucket
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (integration test)';

require rtrim( $wp_path, '/' ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/user.php';
do_action( 'rest_api_init' );

$passed = 0;
$failed = 0;
function check( $label, $cond, $detail = '' ) {
	global $passed, $failed;
	if ( $cond ) { $passed++; echo "  ✔ {$label}\n"; }
	else { $failed++; echo "  ✘ {$label}" . ( $detail !== '' ? " — {$detail}" : '' ) . "\n"; }
}
function section( $name ) { echo "\n{$name}\n"; }

$mails = array();
add_filter( 'pre_wp_mail', function ( $r, $atts ) use ( &$mails ) { $mails[] = $atts; return true; }, 10, 2 );
add_filter( 'deskman_tpl_is_uploaded_file', '__return_true' );

function token_secs_ago( $secs ) {
	$ts = time() - $secs;
	return $ts . '.' . substr( wp_hash( $ts . '|deskman_lead_form' ), 0, 20 );
}
function submit( array $params, array $files = array() ) {
	$req = new WP_REST_Request( 'POST', '/deskman/v1/template-enquiry' );
	$req->set_body_params( $params );
	if ( $files ) { $req->set_file_params( $files ); }
	return rest_do_request( $req );
}
function valid_params( $over = array() ) {
	return array_merge( array(
		'name'          => 'Test Person',
		'business'      => 'Test Dental ' . wp_generate_password( 4, false ),
		'email'         => 'test.person@example.com',
		'phone'         => '+44 20 7946 0' . mt_rand( 100, 999 ),
		'requirements'  => 'We would like online booking and a page for each treatment. ' . wp_generate_password( 6, false ),
		'consent'       => '1',
		'website'       => '',
		'token'         => token_secs_ago( 60 ),
		'deskman_nonce' => wp_create_nonce( 'deskman_contact' ),
		'template'      => 'dental',
		'customisation' => wp_json_encode( array( 'clinic_name' => 'Sunrise Dental', 'color_primary' => '#123ABC' ) ),
	), $over );
}
function fixture( $name, $contents ) {
	$path = wp_tempnam( $name );
	file_put_contents( $path, $contents );
	return array( 'name' => $name, 'type' => 'image/png', 'tmp_name' => $path, 'error' => UPLOAD_ERR_OK, 'size' => filesize( $path ) );
}
function enquiry_by_ref( $ref ) {
	$p = get_posts( array( 'post_type' => 'deskman_enquiry', 'post_status' => 'any', 'meta_key' => '_deskman_tpl_ref', 'meta_value' => $ref ) );
	return $p ? $p[0] : null;
}
function enquiry_count() {
	return count( get_posts( array( 'post_type' => 'deskman_enquiry', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) );
}

/* ------------------------------------------------------------------ */
section( 'Registry, page & routes' );

$ind = deskman_tpl_industries();
check( 'ten industries registered', 10 === count( $ind ) );
check( 'only Dental Clinics is live', array( 'dental-clinics' ) === array_keys( wp_list_filter( $ind, array( 'available' => true ) ) ) );
check( 'every thumbnail exists', array() === array_filter( $ind, function ( $i ) { return ! is_file( DESKMAN_DIR . 'assets/site-templates/img/' . $i['thumbnail'] ); } ) );
check( 'Templates page exists and is published', deskman_tpl_page_id() && 'publish' === get_post_status( deskman_tpl_page_id() ) );
check( 'customiser URL sits under the page', (bool) preg_match( '#/templates/dental-clinics/$#', deskman_tpl_industry_url( 'dental-clinics' ) ) );
check( 'setup wizard knows the page', isset( deskman_setup_pages()['templates'] ) );
check( 'gallery headline editable in Settings', isset( deskman_schema()['v5_tpl_h'], deskman_schema()['v5_tpl_lead'] ) );
check( 'theme links the nav to the gallery', function_exists( 'dm_link' ) && dm_link( 'templates' ) === deskman_tpl_gallery_url() );

/* ------------------------------------------------------------------ */
section( 'Field schema & sanitisation' );

$d = deskman_tpl_defaults( 'dental' );
foreach ( array( 'clinic_name', 'dentist_name', 'description', 'phone', 'whatsapp', 'email', 'address', 'hours', 'cta_text', 'color_primary', 'color_secondary', 'color_accent', 'font_style', 'corners', 'tone', 'hero_heading', 'hero_text', 'about_text', 'announcement', 'emergency', 'payments', 'service_1_name', 'service_6_desc', 'stat_1_value', 'faq_4_a' ) as $k ) {
	check( "schema has {$k}", array_key_exists( $k, $d ) );
}
$c = deskman_tpl_clean_customisation( 'dental', array(
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
	'font_style'    => 'comic-sans',
	'tone'          => 'warm',
	'announcement'  => '',
) );
check( 'HTML stripped from text', 'Smile Co' === $c['clinic_name'], $c['clinic_name'] );
check( 'invalid colour falls back to demo', $d['color_primary'] === $c['color_primary'] );
check( 'short hex colours accepted', '#ABC' === $c['color_accent'] );
check( 'invalid email falls back to demo', $d['email'] === $c['email'] );
check( 'javascript: URLs removed', '' === $c['facebook_url'] );
check( 'https URLs kept', 'https://instagram.com/clinic' === $c['instagram_url'] );
check( 'phone stripped to allowed characters', '+1 555 0100' === $c['phone'], $c['phone'] );
check( 'length limited to schema max', 90 === mb_strlen( $c['hero_heading'] ) );
check( 'unknown keys discarded', ! isset( $c['unknown_key'] ) );
check( 'multi-line text kept', "Mon: 9-5\nTue: 9-5" === $c['hours'] );
check( 'unknown style option falls back to default', 'modern' === $c['font_style'] );
check( 'known style option kept', 'warm' === $c['tone'] );
check( 'optional field may be emptied', '' === $c['announcement'] );
check( 'white text on dark brand colour', '#FFFFFF' === deskman_tpl_on( '#1E6FD9' ) );
check( 'dark text on light brand colour', '#0B1B2B' === deskman_tpl_on( '#8FE3C8' ) );
check( 'ink colour readable on white', deskman_tpl_luminance( deskman_tpl_ink( '#FFE066' ) ) <= 0.18 );

/* ------------------------------------------------------------------ */
section( 'Start My Project → Deskman → Forms' );

$before = enquiry_count();
$r = submit( valid_params( array( 'website' => 'http://spam.example' ) ) );
check( 'honeypot rejected (400)', 400 === $r->get_status() );
$r = submit( valid_params( array( 'deskman_nonce' => 'bad' ) ) );
check( 'bad nonce rejected (403)', 403 === $r->get_status() );
$r = submit( valid_params( array( 'email' => 'bad', 'phone' => '12', 'name' => 'A', 'consent' => '0', 'template' => 'hospitals', 'business' => '' ) ) );
$f = $r->get_data()['data']['fields'] ?? array();
check( 'invalid fields rejected (422)', 422 === $r->get_status() );
foreach ( array( 'email', 'phone', 'name', 'consent', 'template', 'business' ) as $k ) { check( "error for {$k}", isset( $f[ $k ] ) ); }
$r = submit( valid_params( array( 'email' => 'someone@gmial.com' ) ) );
check( 'email typo caught by the lead rules ("did you mean")', 422 === $r->get_status() && false !== strpos( $r->get_data()['data']['fields']['email'] ?? '', 'gmail.com' ) );
$r = submit( valid_params( array( 'email' => 'x@mailinator.com' ) ) );
check( 'throwaway inbox rejected by the lead rules', 422 === $r->get_status() );
$r = submit( valid_params( array( 'phone' => '0000000000' ) ) );
check( 'placeholder phone rejected by the lead rules', 422 === $r->get_status() );
$r = submit( valid_params(), array( 'logo' => fixture( 'logo.png', 'not an image' ) ) );
check( 'non-image "PNG" logo rejected', 422 === $r->get_status() && isset( $r->get_data()['data']['fields']['logo'] ) );
$big = fixture( 'logo.png', 'x' ); $big['size'] = DESKMAN_TPL_LOGO_MAX + 1;
$r = submit( valid_params(), array( 'logo' => $big ) );
check( 'oversized logo rejected', 422 === $r->get_status() );
check( 'rejected submissions store nothing', enquiry_count() === $before );

$img = imagecreatetruecolor( 120, 60 );
imagefill( $img, 0, 0, imagecolorallocate( $img, 30, 111, 217 ) );
ob_start(); imagepng( $img ); $png = ob_get_clean();
$mails = array();
$r    = submit( valid_params( array( 'name' => "Eve Example\r\nBcc: x@evil.example" ) ), array( 'logo' => fixture( 'logo.png', $png ) ) );
$data = $r->get_data();
check( 'valid enquiry accepted (201)', 201 === $r->get_status(), wp_json_encode( $data ) );
check( 'reference returned', (bool) preg_match( '/^DMS-\d{6}-[A-Z0-9]{5}$/', $data['reference'] ?? '' ) );
$e = enquiry_by_ref( $data['reference'] ?? '-' );
check( 'stored in the Forms inbox (deskman_enquiry)', (bool) $e );
check( 'published (not quarantined)', $e && 'publish' === $e->post_status, $e ? (string) get_post_meta( $e->ID, '_deskman_flags', true ) : '' );
check( 'project type is "Website template"', $e && 'Website template' === get_post_meta( $e->ID, '_deskman_ptype', true ) );
check( 'business stored as company', $e && 0 === strpos( get_post_meta( $e->ID, '_deskman_company', true ), 'Test Dental' ) );
check( 'phone normalised like other leads', $e && 0 === strpos( get_post_meta( $e->ID, '_deskman_phone', true ), '+442079460' ) );
check( 'personalisation stored, sanitised', $e && '#123ABC' === get_post_meta( $e->ID, '_deskman_tpl_custom', true )['color_primary'] );
check( 'name stored without injected lines', $e && false === strpos( get_post_meta( $e->ID, '_deskman_tpl_name', true ), "\n" ) );
$logo = $e ? deskman_tpl_logo_path( get_post_meta( $e->ID, '_deskman_tpl_logo', true ) ) : '';
check( 'logo stored in the private folder', $logo && false !== strpos( $logo, '/deskman-private/templates/' ) );
check( 'stored logo re-encoded as a valid PNG', $logo && 'image/png' === getimagesize( $logo )['mime'] );
check( 'private folder protected', $logo && is_file( dirname( $logo ) . '/.htaccess' ) && is_file( dirname( $logo ) . '/index.php' ) );
check( 'logo not in the media library', 0 === count( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'any', 's' => 'logo' ) ) ) );
check( 'studio emailed once, to the Settings contact email', 1 === count( $mails ) && $mails[0]['to'] === deskman_opt( 'contact_email', get_option( 'admin_email' ) ) );
check( 'email lists the personalisation', 1 === count( $mails ) && false !== strpos( $mails[0]['message'], 'Clinic or business name: Sunrise Dental' ) );
check( 'email headers carry no injected lines', 1 === count( $mails ) && false === strpos( implode( '', (array) $mails[0]['headers'] ), "\n" ) );

/* Spam scoring and quarantine apply exactly as for the contact form. */
$mails = array();
$r = submit( valid_params( array( 'requirements' => 'Buy backlinks guest post seo services http://a.example http://b.example http://c.example', 'token' => '' ) ) );
$s = enquiry_by_ref( $r->get_data()['reference'] ?? '-' );
check( 'spammy enquiry still stored, as a quarantined draft', 201 === $r->get_status() && $s && 'draft' === $s->post_status && '1' === get_post_meta( $s->ID, '_deskman_spam', true ) );
check( 'quarantined enquiry not emailed', 0 === count( $mails ) );

update_option( 'deskman_settings', array_merge( (array) get_option( 'deskman_settings' ), array( 'lead_rate_limit' => '1' ) ) );
$r = submit( valid_params() );
check( 'shared rate limit enforced (429)', 429 === $r->get_status() );
$o = (array) get_option( 'deskman_settings' ); unset( $o['lead_rate_limit'] ); update_option( 'deskman_settings', $o );

/* ------------------------------------------------------------------ */
section( 'Admin' );

$admin = get_users( array( 'role' => 'administrator', 'number' => 1 ) )[0];
wp_set_current_user( $admin->ID );
ob_start(); if ( $e ) { deskman_tpl_meta_box( $e ); } $box = ob_get_clean();
check( 'enquiry screen shows the reference', $e && false !== strpos( $box, $data['reference'] ) );
check( 'enquiry screen shows the logo via the protected endpoint', false !== strpos( $box, 'action=deskman_tpl_logo' ) && false !== strpos( $box, '_wpnonce=' ) );
check( 'enquiry screen marks demo values', false !== strpos( $box, '(demo)' ) );
wp_set_current_user( 0 );
check( 'visitors cannot read enquiries', $e && ! current_user_can( 'edit_post', $e->ID ) );
check( 'enquiries are not public', ! is_post_type_viewable( 'deskman_enquiry' ) );

/* clean up */
foreach ( array_filter( array( $e, $s ) ) as $p ) { wp_delete_post( $p->ID, true ); }
check( 'deleting the enquiry deletes its logo', $logo && ! file_exists( $logo ) );

echo "\n{$passed} passed, {$failed} failed\n";
exit( $failed ? 1 : 0 );
