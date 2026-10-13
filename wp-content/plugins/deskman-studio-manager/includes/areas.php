<?php
/**
 * Deskman Studio Manager — Area pages (local SEO landing pages).
 *
 * The studio wants a page per city and service — "best digital marketing
 * agency in Visakhapatnam", "digital marketing agency in Warangal" — without
 * those pages joining the header or footer navigation. They exist to be found
 * in search for a place-plus-service query, and to be linked to from ads,
 * listings and each other.
 *
 * That shape is why this is a custom post type rather than more Pages:
 *
 *   - A CPT never appears in a WordPress nav menu unless somebody adds it by
 *     hand, so the pages stay out of the main navigation on their own, with no
 *     exclusion rules to maintain.
 *   - 'public' => true keeps them fully indexable and in the core sitemap,
 *     which is the entire point — hidden from the menu is not hidden from
 *     Google.
 *   - They carry their own fields (city, keyword, service, FAQs, localities)
 *     instead of one blob of page content, so the same template renders every
 *     area consistently and the JSON-LD can be built from real values.
 *
 * Content for a new area is written by the generator in areas-generator.php —
 * from the configured AI provider when a key is set, otherwise from a built-in
 * template. In
 * both cases the result lands in these fields as an ordinary draft that can be
 * edited before publishing.
 *
 * @package Deskman_Studio_Manager
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'DESKMAN_AREA_CPT', 'deskman_area' );
define( 'DESKMAN_AREA_OPTION', 'deskman_areas' );

/* ==========================================================================
   Settings for the module
   ========================================================================== */

/** Defaults for the Area pages settings (stored in one option). */
function deskman_area_defaults() {
	return array(
		'slug'         => 'locations',
		'service'      => 'Digital Marketing',
		'region'       => 'Andhra Pradesh',
		'country'      => 'India',
		'phone'        => '',
		'related'      => '1',
		'related_max'  => '6',
		/* Groq by default, matching the chatbot module, so one key works for
		   both. The old defaults here pointed at xAI (api.x.ai / grok-3),
		   which failed for anyone who saved a Groq key and left the endpoint
		   and model alone. Any endpoint and model already saved are untouched;
		   these only apply where nothing has been chosen. */
		'ai_endpoint'  => 'https://api.groq.com/openai/v1/chat/completions',
		'ai_key'       => '',
		'ai_model'     => 'openai/gpt-oss-120b',
		'ai_tone'      => 'Confident, specific and plain-spoken. No hype, no invented statistics, no fake client names or awards.',
		'ai_extra'     => '',
	);
}

/** Read one Area pages setting, falling back to its default. */
function deskman_area_opt( $key ) {
	$o = get_option( DESKMAN_AREA_OPTION );
	if ( ! is_array( $o ) ) { $o = array(); }
	$d = deskman_area_defaults();
	if ( array_key_exists( $key, $o ) && $o[ $key ] !== '' ) { return $o[ $key ]; }
	return isset( $d[ $key ] ) ? $d[ $key ] : '';
}

add_action( 'admin_init', function () {
	register_setting( 'deskman_areas_group', DESKMAN_AREA_OPTION, array(
		'type'              => 'array',
		'sanitize_callback' => 'deskman_area_sanitize_settings',
		'default'           => deskman_area_defaults(),
	) );
} );

function deskman_area_sanitize_settings( $input ) {
	if ( ! is_array( $input ) ) { $input = array(); }
	$old   = get_option( DESKMAN_AREA_OPTION );
	$old   = is_array( $old ) ? $old : array();
	$clean = array();

	/* An empty base is a real choice, not a mistake: it puts area pages at the
	   site root on their own slug (/best-digital-marketing-in-visakhapatnam/).
	   See includes/area-urls.php, which adds one exact rewrite rule per page so
	   the root stays safe for real pages. */
	$clean['slug']        = isset( $input['slug'] ) ? sanitize_title( $input['slug'] ) : 'locations';
	$clean['service']     = isset( $input['service'] ) ? sanitize_text_field( $input['service'] ) : '';
	$clean['region']      = isset( $input['region'] ) ? sanitize_text_field( $input['region'] ) : '';
	$clean['country']     = isset( $input['country'] ) ? sanitize_text_field( $input['country'] ) : '';
	$clean['phone']       = isset( $input['phone'] ) ? sanitize_text_field( $input['phone'] ) : '';
	$clean['related']     = ! empty( $input['related'] ) ? '1' : '0';
	$clean['related_max'] = isset( $input['related_max'] ) ? (string) max( 0, min( 12, (int) $input['related_max'] ) ) : '6';
	$clean['ai_endpoint'] = isset( $input['ai_endpoint'] ) ? esc_url_raw( trim( $input['ai_endpoint'] ) ) : '';
	$clean['ai_model']    = isset( $input['ai_model'] ) ? sanitize_text_field( $input['ai_model'] ) : '';
	$clean['ai_tone']     = isset( $input['ai_tone'] ) ? sanitize_textarea_field( $input['ai_tone'] ) : '';
	$clean['ai_extra']    = isset( $input['ai_extra'] ) ? sanitize_textarea_field( $input['ai_extra'] ) : '';

	/* The API key is write-only in the UI: submitting the field empty keeps
	   whatever is already stored, so saving any other setting on the screen
	   can never wipe the key by accident. */
	$submitted = isset( $input['ai_key'] ) ? trim( wp_strip_all_tags( (string) $input['ai_key'] ) ) : '';
	$clean['ai_key'] = $submitted !== '' ? $submitted : ( isset( $old['ai_key'] ) ? $old['ai_key'] : '' );

	// The URL base changed, so the rewrite rules have to be rebuilt once.
	if ( ! isset( $old['slug'] ) || $old['slug'] !== $clean['slug'] ) {
		update_option( 'deskman_area_flush', 1 );
	}

	add_settings_error( DESKMAN_AREA_OPTION, 'saved', __( 'Area page settings saved.', 'deskman-studio-manager' ), 'updated' );
	return $clean;
}

/* ==========================================================================
   The post type
   ========================================================================== */

add_action( 'init', 'deskman_area_register_cpt', 5 );
function deskman_area_register_cpt() {
	$slug = deskman_area_opt( 'slug' );

	/* Root mode: no base, so WordPress must not build any rewrite of its own.
	   Its rule for an empty slug would be the catch-all ^([^/]+)/?$, which
	   would swallow /contact/ and every other one-segment URL on the site.
	   includes/area-urls.php adds one literal rule per published page instead,
	   and there is no archive to put anywhere. */
	$root = function_exists( 'deskman_area_root_mode' ) && deskman_area_root_mode();

	register_post_type( DESKMAN_AREA_CPT, array(
		'labels' => array(
			'name'               => __( 'Area pages', 'deskman-studio-manager' ),
			'singular_name'      => __( 'Area page', 'deskman-studio-manager' ),
			'menu_name'          => __( 'Area pages', 'deskman-studio-manager' ),
			'all_items'          => __( 'Area pages', 'deskman-studio-manager' ),
			'add_new'            => __( 'Add area page', 'deskman-studio-manager' ),
			'add_new_item'       => __( 'Add area page', 'deskman-studio-manager' ),
			'edit_item'          => __( 'Edit area page', 'deskman-studio-manager' ),
			'new_item'           => __( 'New area page', 'deskman-studio-manager' ),
			'view_item'          => __( 'View area page', 'deskman-studio-manager' ),
			'search_items'       => __( 'Search area pages', 'deskman-studio-manager' ),
			'not_found'          => __( 'No area pages yet — generate your first one.', 'deskman-studio-manager' ),
			'not_found_in_trash' => __( 'No area pages in the trash', 'deskman-studio-manager' ),
		),
		/* Public and indexable on purpose. These pages are kept out of the
		   navigation, not out of search — a noindex here would defeat the
		   whole feature. */
		'public'          => true,
		'has_archive'     => $root ? false : $slug,
		'show_in_menu'    => 'deskman-studio',
		'show_in_rest'    => true,
		'show_in_nav_menus' => false,
		'menu_position'   => 7,
		'menu_icon'       => 'dashicons-location',
		'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
		'rewrite'         => $root ? false : array( 'slug' => $slug, 'with_front' => false ),
		'query_var'       => DESKMAN_AREA_CPT,
		'capability_type' => 'post',
		'map_meta_cap'    => true,
	) );
}

/** Rebuild permalinks once after the URL base changes. */
add_action( 'wp_loaded', function () {
	if ( get_option( 'deskman_area_flush' ) ) {
		delete_option( 'deskman_area_flush' );
		flush_rewrite_rules();
	}
} );

/** Give area pages the same per-page SEO panel Pages and Projects get, so the
 *  theme's existing SEO engine picks up their title and description with no
 *  theme change at all. */
add_filter( 'deskman_seo_meta_post_types', function ( $types ) {
	$types[] = DESKMAN_AREA_CPT;
	return $types;
} );

/* ==========================================================================
   Fields
   ========================================================================== */

/**
 * The field schema for one area page.
 *
 * 'pairs' fields hold a list, one item per line, with :: between the two
 * halves ("Local SEO :: Get found in map results"). It is deliberately a
 * plain textarea rather than a JavaScript repeater: the generator writes these
 * fields as text, an editor can fix a typo without fighting a widget, and
 * there is no serialised structure to migrate later.
 */
function deskman_area_fields() {
	return array(
		'city' => array(
			'label' => 'Area / city',
			'type'  => 'text',
			'group' => 'Targeting',
			'help'  => 'Just the place name, e.g. Visakhapatnam. Used in the schema, the breadcrumb and the related-area links.',
		),
		'region' => array( 'label' => 'State / region', 'type' => 'text', 'group' => 'Targeting' ),
		'service' => array(
			'label' => 'Service focus',
			'type'  => 'text',
			'group' => 'Targeting',
			'help'  => 'e.g. Digital Marketing, Web Design, SEO. One page should target one service in one place.',
		),
		'keyword' => array(
			'label' => 'Primary keyword',
			'type'  => 'text',
			'group' => 'Targeting',
			'help'  => 'The exact phrase this page is trying to rank for, e.g. best digital marketing agency in Visakhapatnam.',
		),
		'keywords' => array(
			'label' => 'Secondary keywords',
			'type'  => 'textarea',
			'group' => 'Targeting',
			'rows'  => 2,
			'help'  => 'Comma separated. Used as supporting phrases in the copy, not as a meta keywords tag (search engines ignore those).',
		),
		'localities' => array(
			'label' => 'Localities covered',
			'type'  => 'textarea',
			'group' => 'Targeting',
			'rows'  => 2,
			'help'  => 'Comma separated neighbourhoods, e.g. MVP Colony, Gajuwaka, Madhurawada. These appear on the page and in the areaServed structured data, which is what makes a local page read as genuinely local.',
		),

		'hero_eyebrow' => array( 'label' => 'Eyebrow', 'type' => 'text', 'group' => 'Hero' ),
		'hero_h' => array(
			'label' => 'Headline (H1)',
			'type'  => 'textarea',
			'group' => 'Hero',
			'rows'  => 2,
			'help'  => 'Should contain the primary keyword, read like a sentence a person would say, and stay under about 70 characters.',
		),
		'hero_sub' => array( 'label' => 'Sub-paragraph', 'type' => 'textarea', 'group' => 'Hero', 'rows' => 3 ),
		'cta_label' => array( 'label' => 'Hero button label', 'type' => 'text', 'group' => 'Hero' ),
		'stats' => array(
			'label' => 'Hero stats',
			'type'  => 'pairs',
			'group' => 'Hero',
			'rows'  => 4,
			'help'  => 'One per line: value :: label. Keep these true — an invented number is the fastest way to lose a lead.',
		),

		'intro_h' => array( 'label' => 'Heading', 'type' => 'text', 'group' => 'Intro' ),
		'intro_body' => array(
			'label' => 'Body copy',
			'type'  => 'textarea',
			'group' => 'Intro',
			'rows'  => 7,
			'help'  => 'Two or three paragraphs, separated by a blank line. This is the part search engines read hardest, so it should say something specific about working in this city.',
		),

		'services_h' => array( 'label' => 'Heading', 'type' => 'text', 'group' => 'Services offered' ),
		'services' => array(
			'label' => 'Services',
			'type'  => 'pairs',
			'group' => 'Services offered',
			'rows'  => 8,
			'help'  => 'One per line: title :: description.',
		),

		'why_h' => array( 'label' => 'Heading', 'type' => 'text', 'group' => 'Why us' ),
		'why' => array( 'label' => 'Reasons', 'type' => 'pairs', 'group' => 'Why us', 'rows' => 6, 'help' => 'One per line: title :: description.' ),

		'process_h' => array( 'label' => 'Heading', 'type' => 'text', 'group' => 'Process' ),
		'process' => array( 'label' => 'Steps', 'type' => 'pairs', 'group' => 'Process', 'rows' => 6, 'help' => 'One per line: step name :: description.' ),

		'faq_h' => array( 'label' => 'Heading', 'type' => 'text', 'group' => 'FAQ' ),
		'faq' => array(
			'label' => 'Questions',
			'type'  => 'pairs',
			'group' => 'FAQ',
			'rows'  => 8,
			'help'  => 'One per line: question :: answer. These are also published as FAQPage structured data, which is what can win the expandable questions under your result.',
		),

		'cta_h' => array( 'label' => 'Heading', 'type' => 'text', 'group' => 'Closing CTA' ),
		'cta_sub' => array( 'label' => 'Sub-line', 'type' => 'textarea', 'group' => 'Closing CTA', 'rows' => 2 ),
		'cta_btn' => array( 'label' => 'Button label', 'type' => 'text', 'group' => 'Closing CTA' ),
		'phone' => array( 'label' => 'Phone shown on this page', 'type' => 'text', 'group' => 'Closing CTA', 'help' => 'Leave blank to use the studio phone from Area page settings.' ),
		'map' => array(
			'label' => 'Map embed URL',
			'type'  => 'text',
			'group' => 'Closing CTA',
			'help'  => 'Optional. The src URL from a Google Maps "Embed a map" iframe. Leave blank for no map.',
		),
	);
}

/** Read one area field. */
function deskman_area_field( $post_id, $key, $default = '' ) {
	$v = get_post_meta( (int) $post_id, '_dsk_area_' . $key, true );
	return ( $v === '' || $v === null || $v === false ) ? $default : $v;
}

/** Split a "title :: description" block into a usable list. */
function deskman_area_pairs( $raw ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$line = trim( $line );
		if ( $line === '' ) { continue; }
		if ( strpos( $line, '::' ) !== false ) {
			list( $t, $d ) = array_map( 'trim', explode( '::', $line, 2 ) );
		} else {
			$t = $line; $d = '';
		}
		if ( $t === '' ) { continue; }
		$out[] = array( 't' => $t, 'd' => $d );
	}
	return $out;
}

/** Split a comma-separated field into a trimmed list. */
function deskman_area_list( $raw ) {
	$out = array();
	foreach ( explode( ',', (string) $raw ) as $bit ) {
		$bit = trim( $bit );
		if ( $bit !== '' ) { $out[] = $bit; }
	}
	return $out;
}

/* ==========================================================================
   The editor panel
   ========================================================================== */

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'deskman_area_content',
		__( 'Area page content', 'deskman-studio-manager' ),
		'deskman_area_meta_box',
		DESKMAN_AREA_CPT,
		'normal',
		'high'
	);
} );

function deskman_area_meta_box( $post ) {
	wp_nonce_field( 'deskman_area_save', 'deskman_area_nonce' );

	$groups = array();
	foreach ( deskman_area_fields() as $key => $f ) { $groups[ $f['group'] ][ $key ] = $f; }
	?>
	<style>
		.dsa-grp{border:1px solid #dcdcde;border-radius:8px;margin:0 0 16px;background:#fff}
		.dsa-grp>summary{cursor:pointer;padding:11px 14px;font-weight:600;list-style:none;display:flex;align-items:center;gap:8px}
		.dsa-grp>summary::-webkit-details-marker{display:none}
		.dsa-grp>summary::before{content:"▸";color:#6840FF;font-size:14px}
		.dsa-grp[open]>summary::before{content:"▾"}
		.dsa-grp[open]>summary{border-bottom:1px solid #f0f0f1}
		.dsa-body{padding:6px 14px 14px}
		.dsa-f{margin:14px 0 0}
		.dsa-f>label{display:block;font-weight:600;margin:0 0 4px}
		.dsa-f input[type=text],.dsa-f textarea{width:100%}
		.dsa-f textarea{font-family:Menlo,Consolas,monospace;font-size:12.5px;line-height:1.7}
		.dsa-hint{color:#777;margin:4px 0 0;font-size:12.5px}
	</style>
	<p style="color:#555;margin:0 0 14px;max-width:820px">
		<?php esc_html_e( 'Every section below is optional — leave a field blank and that block is skipped on the front end. The Generate screen fills all of these in for you; this is where you correct anything before publishing.', 'deskman-studio-manager' ); ?>
	</p>
	<?php $first = true; foreach ( $groups as $group => $fields ) : ?>
		<details class="dsa-grp" <?php echo $first ? 'open' : ''; ?>>
			<summary><?php echo esc_html( $group ); ?></summary>
			<div class="dsa-body">
			<?php foreach ( $fields as $key => $f ) :
				$val  = deskman_area_field( $post->ID, $key );
				$name = 'dsk_area[' . $key . ']';
				$id   = 'dsk_area_' . $key;
				$rows = isset( $f['rows'] ) ? (int) $f['rows'] : 3; ?>
				<div class="dsa-f">
					<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $f['label'] ); ?></label>
					<?php if ( $f['type'] === 'text' ) : ?>
						<input type="text" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $val ); ?>" />
					<?php else : ?>
						<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="<?php echo esc_attr( $rows ); ?>"><?php echo esc_textarea( $val ); ?></textarea>
					<?php endif; ?>
					<?php if ( ! empty( $f['help'] ) ) : ?>
						<p class="dsa-hint"><?php echo esc_html( $f['help'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			</div>
		</details>
	<?php $first = false; endforeach;
}

add_action( 'save_post_' . DESKMAN_AREA_CPT, function ( $post_id ) {
	if ( ! isset( $_POST['deskman_area_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['deskman_area_nonce'] ) ), 'deskman_area_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	$in = isset( $_POST['dsk_area'] ) && is_array( $_POST['dsk_area'] ) ? wp_unslash( $_POST['dsk_area'] ) : array();
	foreach ( deskman_area_fields() as $key => $f ) {
		$raw = isset( $in[ $key ] ) ? $in[ $key ] : '';
		$val = ( $f['type'] === 'text' ) ? sanitize_text_field( $raw ) : sanitize_textarea_field( $raw );
		if ( $val === '' ) { delete_post_meta( $post_id, '_dsk_area_' . $key ); }
		else { update_post_meta( $post_id, '_dsk_area_' . $key, $val ); }
	}
} );

/**
 * Keep the excerpt in step with the hero paragraph.
 *
 * An area page keeps its copy in meta fields, so post_content is usually
 * empty. Every description fallback in WordPress — the theme's SEO engine,
 * Yoast, Rank Math, the RSS feed — walks content and then excerpt, and would
 * find nothing on a page written by hand rather than by the generator. Copying
 * the hero paragraph into the excerpt gives all of them something true to fall
 * back to, and costs nothing when a real meta description is set.
 *
 * Only ever fills a blank excerpt, so anything written deliberately stands.
 */
add_action( 'save_post_' . DESKMAN_AREA_CPT, function ( $post_id, $post ) {
	static $running = false;
	if ( $running ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( wp_is_post_revision( $post_id ) ) { return; }
	if ( trim( (string) $post->post_excerpt ) !== '' ) { return; }

	$lead = trim( (string) deskman_area_field( $post_id, 'hero_sub' ) );
	if ( $lead === '' ) { return; }

	$running = true;
	wp_update_post( array(
		'ID'           => $post_id,
		'post_excerpt' => wp_trim_words( $lead, 40, '' ),
	) );
	$running = false;
}, 20, 2 );

/* ==========================================================================
   List table columns
   ========================================================================== */

add_filter( 'manage_' . DESKMAN_AREA_CPT . '_posts_columns', function ( $cols ) {
	$out = array();
	if ( isset( $cols['cb'] ) ) { $out['cb'] = $cols['cb']; }
	$out['title']       = __( 'Page', 'deskman-studio-manager' );
	$out['dsa_city']    = __( 'Area', 'deskman-studio-manager' );
	$out['dsa_service'] = __( 'Service', 'deskman-studio-manager' );
	$out['dsa_kw']      = __( 'Target keyword', 'deskman-studio-manager' );
	$out['dsa_health']  = __( 'SEO', 'deskman-studio-manager' );
	$out['date']        = isset( $cols['date'] ) ? $cols['date'] : __( 'Date' );
	return $out;
} );

add_action( 'manage_' . DESKMAN_AREA_CPT . '_posts_custom_column', function ( $col, $post_id ) {
	if ( $col === 'dsa_city' ) {
		echo esc_html( deskman_area_field( $post_id, 'city', '—' ) );
	} elseif ( $col === 'dsa_service' ) {
		echo esc_html( deskman_area_field( $post_id, 'service', '—' ) );
	} elseif ( $col === 'dsa_kw' ) {
		$kw = deskman_area_field( $post_id, 'keyword' );
		echo $kw ? '<code style="font-size:12px">' . esc_html( $kw ) . '</code>' : '<span style="color:#999">—</span>';
	} elseif ( $col === 'dsa_health' ) {
		$issues = deskman_area_health( $post_id );
		if ( ! $issues ) {
			echo '<span style="color:#1a7f37;font-weight:600">&#10003; Ready</span>';
		} else {
			echo '<span title="' . esc_attr( implode( ' · ', $issues ) ) . '" style="color:#b26200;font-weight:600">' . esc_html( count( $issues ) ) . ' to fix</span>';
		}
	}
}, 10, 2 );

/**
 * The handful of checks worth surfacing before a local page goes live.
 * Deliberately short: a long checklist gets ignored, and these four are the
 * ones that actually decide whether the page can rank at all.
 */
function deskman_area_health( $post_id ) {
	$out = array();
	if ( trim( (string) deskman_area_field( $post_id, 'keyword' ) ) === '' ) { $out[] = __( 'No target keyword set', 'deskman-studio-manager' ); }
	if ( trim( (string) get_post_meta( $post_id, '_deskman_seo_desc', true ) ) === '' ) { $out[] = __( 'No meta description', 'deskman-studio-manager' ); }
	$body = (string) deskman_area_field( $post_id, 'intro_body' );
	if ( str_word_count( wp_strip_all_tags( $body ) ) < 60 ) { $out[] = __( 'Intro copy is thin', 'deskman-studio-manager' ); }
	if ( ! deskman_area_pairs( deskman_area_field( $post_id, 'faq' ) ) ) { $out[] = __( 'No FAQ entries', 'deskman-studio-manager' ); }
	return $out;
}

/* ==========================================================================
   Front end: template routing and assets
   ========================================================================== */

/**
 * Serve the bundled templates, unless the active theme supplies its own.
 * The theme wins whenever it defines single-deskman_area.php or
 * archive-deskman_area.php, which keeps the usual WordPress override path open.
 */
add_filter( 'template_include', function ( $template ) {
	if ( is_singular( DESKMAN_AREA_CPT ) ) {
		$theme = locate_template( array( 'single-' . DESKMAN_AREA_CPT . '.php' ) );
		if ( $theme ) { return $theme; }
		/* No bundled fallback here any more: the single template now comes
		   from the design system in includes/area-designs.php, which routes at
		   priority 30 and resolves to area-designs/default/ when a page has no
		   city-specific design. */
	}
	if ( is_post_type_archive( DESKMAN_AREA_CPT ) ) {
		$theme = locate_template( array( 'archive-' . DESKMAN_AREA_CPT . '.php' ) );
		if ( $theme ) { return $theme; }
		$own = DESKMAN_DIR . 'templates/archive-deskman_area.php';
		if ( file_exists( $own ) ) { return $own; }
	}
	return $template;
}, 20 );

add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_singular( DESKMAN_AREA_CPT ) && ! is_post_type_archive( DESKMAN_AREA_CPT ) ) { return; }
	$path = DESKMAN_DIR . 'assets/areas.css';
	wp_enqueue_style(
		'deskman-areas',
		DESKMAN_URL . 'assets/areas.css',
		array(),
		file_exists( $path ) ? filemtime( $path ) : DESKMAN_VER
	);
} );

/* ==========================================================================
   Cross-linking
   ========================================================================== */

/**
 * Other published area pages, for the "we also work in…" block.
 *
 * Programmatic local pages are weak on their own — a stack of near-identical
 * pages with no links between them tends to be read as thin. Linking each one
 * to its siblings gives the set a real internal structure and gives crawlers a
 * path to every page without any of them being in the menu.
 */
function deskman_area_related( $post_id, $limit = 6 ) {
	$limit = (int) $limit;
	if ( $limit < 1 ) { return array(); }
	return get_posts( array(
		'post_type'        => DESKMAN_AREA_CPT,
		'post_status'      => 'publish',
		'posts_per_page'   => $limit,
		'post__not_in'     => array( (int) $post_id ),
		'orderby'          => 'rand',
		'suppress_filters' => false,
	) );
}

/** [deskman_areas] — a plain list of every area page, for dropping into a
 *  footer, a sitemap page or a services page. Attributes: limit, service. */
add_shortcode( 'deskman_areas', function ( $atts ) {
	$a = shortcode_atts( array( 'limit' => 50, 'service' => '', 'title' => '' ), $atts, 'deskman_areas' );

	$args = array(
		'post_type'      => DESKMAN_AREA_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => (int) $a['limit'],
		'orderby'        => 'title',
		'order'          => 'ASC',
	);
	if ( trim( (string) $a['service'] ) !== '' ) {
		$args['meta_query'] = array( array( 'key' => '_dsk_area_service', 'value' => sanitize_text_field( $a['service'] ), 'compare' => '=' ) );
	}
	$posts = get_posts( $args );
	if ( ! $posts ) { return ''; }

	$out = '<div class="dsa-index">';
	if ( trim( (string) $a['title'] ) !== '' ) {
		$out .= '<h2 class="dsa-index-h">' . esc_html( $a['title'] ) . '</h2>';
	}
	$out .= '<ul class="dsa-index-list">';
	foreach ( $posts as $p ) {
		$out .= '<li><a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a></li>';
	}
	$out .= '</ul></div>';
	return $out;
} );

/* ==========================================================================
   Structured data
   ========================================================================== */

/**
 * The JSON-LD graph for one area page.
 *
 * Three connected things are described, because a local landing page has to
 * answer three different questions:
 *   ProfessionalService  who you are and, via areaServed, where you work
 *   Service              what this page is actually selling, in this place
 *   FAQPage              the questions on the page, which is what can earn
 *                        the expandable answers under a search result
 * A BreadcrumbList is added so the result shows a readable path rather than a
 * bare URL, which matters when the page is not in the navigation.
 *
 * Everything is built from stored values — nothing is invented here, and any
 * field left blank is simply left out of the graph rather than guessed at.
 */
function deskman_area_schema( $post_id ) {
	$city    = trim( (string) deskman_area_field( $post_id, 'city' ) );
	if ( $city === '' ) { return ''; }

	$region  = trim( (string) deskman_area_field( $post_id, 'region', deskman_area_opt( 'region' ) ) );
	$country = trim( (string) deskman_area_opt( 'country' ) );
	$service = trim( (string) deskman_area_field( $post_id, 'service', deskman_area_opt( 'service' ) ) );
	$phone   = trim( (string) deskman_area_field( $post_id, 'phone', deskman_area_opt( 'phone' ) ) );
	$url     = get_permalink( $post_id );
	$brand   = function_exists( 'deskman_seo_site_name' ) ? deskman_seo_site_name() : get_bloginfo( 'name' );
	$desc    = trim( (string) get_post_meta( $post_id, '_deskman_seo_desc', true ) );
	if ( $desc === '' ) { $desc = wp_trim_words( wp_strip_all_tags( (string) deskman_area_field( $post_id, 'hero_sub' ) ), 32 ); }

	$served = array();
	foreach ( deskman_area_list( deskman_area_field( $post_id, 'localities' ) ) as $loc ) {
		$served[] = array( '@type' => 'Place', 'name' => $loc );
	}
	array_unshift( $served, array( '@type' => 'City', 'name' => $city ) );

	$business = array(
		'@type'       => 'ProfessionalService',
		'@id'         => $url . '#business',
		'name'        => $brand . ' — ' . $city,
		'url'         => $url,
		'description' => $desc,
		'areaServed'  => $served,
		'address'     => array_filter( array(
			'@type'           => 'PostalAddress',
			'addressLocality' => $city,
			'addressRegion'   => $region,
			'addressCountry'  => $country,
		) ),
	);
	if ( $phone !== '' ) { $business['telephone'] = $phone; }
	$logo = trim( (string) deskman_opt( 'seo_logo', '' ) );
	if ( $logo !== '' ) { $business['image'] = $logo; }

	$graph = array( $business );

	if ( $service !== '' ) {
		$offers = array();
		foreach ( deskman_area_pairs( deskman_area_field( $post_id, 'services' ) ) as $s ) {
			$offers[] = array(
				'@type'       => 'Offer',
				'itemOffered' => array_filter( array(
					'@type'       => 'Service',
					'name'        => $s['t'],
					'description' => $s['d'],
				) ),
			);
		}
		$svc = array(
			'@type'       => 'Service',
			'@id'         => $url . '#service',
			'name'        => $service . ' in ' . $city,
			'serviceType' => $service,
			'provider'    => array( '@id' => $url . '#business' ),
			'areaServed'  => array( '@type' => 'City', 'name' => $city ),
		);
		if ( $offers ) {
			$svc['hasOfferCatalog'] = array(
				'@type'           => 'OfferCatalog',
				'name'            => $service . ' — ' . $city,
				'itemListElement' => $offers,
			);
		}
		$graph[] = $svc;
	}

	$faqs = deskman_area_pairs( deskman_area_field( $post_id, 'faq' ) );
	if ( $faqs ) {
		$items = array();
		foreach ( $faqs as $f ) {
			if ( $f['d'] === '' ) { continue; }
			$items[] = array(
				'@type'          => 'Question',
				'name'           => $f['t'],
				'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f['d'] ),
			);
		}
		if ( $items ) {
			$graph[] = array(
				'@type'      => 'FAQPage',
				'@id'        => $url . '#faq',
				'mainEntity' => $items,
			);
		}
	}

	$archive = get_post_type_archive_link( DESKMAN_AREA_CPT );
	$crumbs  = array(
		array( '@type' => 'ListItem', 'position' => 1, 'name' => __( 'Home', 'deskman-studio-manager' ), 'item' => home_url( '/' ) ),
	);
	if ( $archive ) {
		$crumbs[] = array( '@type' => 'ListItem', 'position' => 2, 'name' => __( 'Locations', 'deskman-studio-manager' ), 'item' => $archive );
		$crumbs[] = array( '@type' => 'ListItem', 'position' => 3, 'name' => get_the_title( $post_id ), 'item' => $url );
	} else {
		$crumbs[] = array( '@type' => 'ListItem', 'position' => 2, 'name' => get_the_title( $post_id ), 'item' => $url );
	}
	$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $crumbs );

	return wp_json_encode(
		array( '@context' => 'https://schema.org', '@graph' => $graph ),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	);
}

add_action( 'wp_head', function () {
	if ( ! is_singular( DESKMAN_AREA_CPT ) ) { return; }
	$json = deskman_area_schema( get_queried_object_id() );
	if ( $json ) {
		echo "\n<script type=\"application/ld+json\">" . $json . "</script>\n";
	}
}, 20 );
