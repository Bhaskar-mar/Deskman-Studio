<?php
/**
 * Site templates — industry website templates visitors can personalise.
 *
 *   /templates/                     the gallery of industry categories
 *   /templates/<industry>/          the live customiser for a ready template
 *   /templates/<industry>/preview/  the template itself: a complete,
 *                                   responsive page shown in the customiser's
 *                                   device frames (and openable on its own)
 *
 * What lives where:
 *
 *   site-templates/industries.php   the category cards (add industries here)
 *   site-templates/templates.php    the ready templates
 *   site-templates/<id>/fields.php  a template's editable fields + demo copy
 *   site-templates/<id>/preview.php a template's markup
 *   site-templates/views/           gallery + customiser (call get_header())
 *   assets/site-templates/          CSS, JS, thumbnails, the template font
 *
 * Views can be taken over by the theme, the same way area designs can:
 * {child theme}/site-templates/views/<file>, then {theme}/…, then this plugin.
 *
 * A visitor's details never touch the server while they personalise: they
 * live in that browser tab (sessionStorage) and reach the preview frame by
 * same-origin postMessage. Only "Start My Project" sends anything, and that
 * goes through the same lead rules as every other form (includes/leads.php)
 * into Deskman → Forms, so a template enquiry is triaged exactly like any
 * other one. Nothing is ever published.
 *
 * @package Deskman_Studio_Manager
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'DESKMAN_TPL_DIR', DESKMAN_DIR . 'site-templates/' );
define( 'DESKMAN_TPL_URL', DESKMAN_URL . 'assets/site-templates/' );
define( 'DESKMAN_TPL_PAGE', 'deskman_tpl_page' );          // option: Templates page id
define( 'DESKMAN_TPL_LOGO_MAX', 2097152 );                 // 2 MB
define( 'DESKMAN_TPL_UPLOADS', 'deskman-private/templates' );

/* ==========================================================================
   Registry
   ========================================================================== */

/** Ready templates, keyed by id. Filter: deskman_site_templates. */
function deskman_tpl_templates() {
	static $cache = null;
	if ( $cache !== null ) { return $cache; }
	$cache = array();
	foreach ( (array) apply_filters( 'deskman_site_templates', require DESKMAN_TPL_DIR . 'templates.php' ) as $id => $t ) {
		$id = sanitize_key( $id );
		if ( $id === '' || empty( $t['dir'] ) || ! is_readable( trailingslashit( $t['dir'] ) . 'preview.php' ) ) { continue; }
		$t['id']     = $id;
		$t['dir']    = trailingslashit( $t['dir'] );
		$cache[ $id ] = $t;
	}
	return $cache;
}

function deskman_tpl_template( $id ) {
	$all = deskman_tpl_templates();
	return isset( $all[ $id ] ) ? $all[ $id ] : null;
}

/**
 * Industry cards, keyed by slug, each with a resolved 'available' flag.
 * An industry is only "available" when its template is actually registered,
 * so a typo or a half-built template can never open a broken page.
 * Filter: deskman_site_template_industries.
 */
function deskman_tpl_industries() {
	static $cache = null;
	if ( $cache !== null ) { return $cache; }
	$cache = array();
	foreach ( (array) apply_filters( 'deskman_site_template_industries', require DESKMAN_TPL_DIR . 'industries.php' ) as $slug => $i ) {
		$slug = sanitize_title( $slug );
		if ( $slug === '' || empty( $i['name'] ) ) { continue; }
		$t = ! empty( $i['template'] ) ? deskman_tpl_template( $i['template'] ) : null;
		$cache[ $slug ] = array(
			'slug'      => $slug,
			'name'      => $i['name'],
			'summary'   => isset( $i['summary'] ) ? $i['summary'] : '',
			'thumbnail' => isset( $i['thumbnail'] ) ? $i['thumbnail'] : '',
			'template'  => $t ? $t['id'] : '',
			'available' => (bool) $t,
		);
	}
	return $cache;
}

function deskman_tpl_industry( $slug ) {
	$all = deskman_tpl_industries();
	return isset( $all[ $slug ] ) ? $all[ $slug ] : null;
}

function deskman_tpl_industry_for_template( $template_id ) {
	foreach ( deskman_tpl_industries() as $i ) {
		if ( $i['available'] && $i['template'] === $template_id ) { return $i; }
	}
	return null;
}

/** URL of a bundled image under assets/site-templates/img/. */
function deskman_tpl_img( $path ) {
	return $path ? DESKMAN_TPL_URL . 'img/' . ltrim( $path, '/' ) : '';
}

/** Bundled stroke icon (assets/site-templates/img/icons/<name>.svg). */
function deskman_tpl_icon( $name ) {
	static $cache = array();
	$name = sanitize_key( $name );
	if ( ! isset( $cache[ $name ] ) ) {
		$file = DESKMAN_DIR . 'assets/site-templates/img/icons/' . $name . '.svg';
		$cache[ $name ] = is_readable( $file ) ? trim( (string) file_get_contents( $file ) ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}
	return $cache[ $name ];
}

/* ==========================================================================
   Field schema — drives the customiser form, the preview and validation
   ========================================================================== */

/** A template's fields, grouped, with every key filled in. */
function deskman_tpl_groups( $template_id ) {
	static $cache = array();
	if ( isset( $cache[ $template_id ] ) ) { return $cache[ $template_id ]; }
	$t      = deskman_tpl_template( $template_id );
	$groups = ( $t && is_readable( $t['dir'] . 'fields.php' ) ) ? require $t['dir'] . 'fields.php' : array();
	$groups = (array) apply_filters( 'deskman_site_template_fields', $groups, $template_id );
	$types  = array( 'text', 'textarea', 'tel', 'email', 'url', 'color', 'logo', 'select' );
	foreach ( $groups as $gid => $g ) {
		foreach ( (array) $g['fields'] as $key => $f ) {
			$f = wp_parse_args( $f, array( 'type' => 'text', 'label' => $key, 'default' => '', 'max' => 200, 'help' => '', 'quick' => false, 'required' => false, 'rows' => 3, 'options' => array() ) );
			if ( ! in_array( $f['type'], $types, true ) ) { $f['type'] = 'text'; }
			$groups[ $gid ]['fields'][ $key ] = $f;
		}
	}
	return $cache[ $template_id ] = $groups;
}

function deskman_tpl_fields( $template_id ) {
	$out = array();
	foreach ( deskman_tpl_groups( $template_id ) as $g ) {
		foreach ( $g['fields'] as $key => $f ) { $out[ $key ] = $f; }
	}
	return $out;
}

function deskman_tpl_defaults( $template_id ) {
	return wp_list_pluck( deskman_tpl_fields( $template_id ), 'default' );
}

/** One value, cleaned for its field type. Returns '' when invalid. */
function deskman_tpl_clean_value( array $f, $value ) {
	if ( ! is_scalar( $value ) ) { return ''; }
	$value = (string) $value;
	switch ( $f['type'] ) {
		case 'textarea': $value = sanitize_textarea_field( $value ); break;
		case 'email':    $value = sanitize_email( $value ); $value = is_email( $value ) ? $value : ''; break;
		case 'tel':      $value = trim( preg_replace( '/\s+/', ' ', preg_replace( '/[^0-9+()\-.\s]/', '', $value ) ) ); break;
		case 'url':      $value = esc_url_raw( trim( $value ), array( 'http', 'https' ) ); break;
		case 'color':    $value = sanitize_hex_color( trim( $value ) ); $value = $value ? strtoupper( $value ) : ''; break;
		case 'logo':     return ''; // logos arrive as files, never as values
		case 'select':   return array_key_exists( $value, (array) $f['options'] ) ? $value : '';
		default:         $value = sanitize_text_field( $value );
	}
	$max = max( 1, (int) $f['max'] );
	return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $max ) : substr( $value, 0, $max );
}

/**
 * A whole personalisation payload, cleaned against the schema. Unknown keys
 * are dropped; empty or invalid required values fall back to the demo copy,
 * which is exactly what the visitor saw in the preview.
 */
function deskman_tpl_clean_customisation( $template_id, $raw ) {
	$raw = is_array( $raw ) ? $raw : array();
	$out = array();
	foreach ( deskman_tpl_fields( $template_id ) as $key => $f ) {
		if ( 'logo' === $f['type'] ) { continue; }
		$v = isset( $raw[ $key ] ) ? deskman_tpl_clean_value( $f, $raw[ $key ] ) : '';
		if ( $v === '' && $f['required'] ) { $v = (string) $f['default']; }
		$out[ $key ] = $v;
	}
	return $out;
}

/** The schema as the customiser and preview scripts need it. */
function deskman_tpl_fields_js( $template_id ) {
	$out = array();
	foreach ( deskman_tpl_fields( $template_id ) as $key => $f ) {
		$out[ $key ] = array( 'type' => $f['type'], 'label' => $f['label'], 'default' => $f['default'], 'max' => (int) $f['max'], 'required' => (bool) $f['required'] );
		if ( 'select' === $f['type'] ) { $out[ $key ]['options'] = array_keys( (array) $f['options'] ); }
	}
	return $out;
}

/* ==========================================================================
   Brand colours — mirrored in template-runtime.js so server defaults and live
   updates come out identical
   ========================================================================== */

function deskman_tpl_rgb( $hex ) {
	$hex = ltrim( (string) sanitize_hex_color( $hex ), '#' );
	if ( strlen( $hex ) === 3 ) { $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2]; }
	return strlen( $hex ) === 6 ? array_map( 'hexdec', str_split( $hex, 2 ) ) : array( 0, 0, 0 );
}

/** WCAG relative luminance. */
function deskman_tpl_luminance( $hex ) {
	$c = array_map( function ( $v ) {
		$v /= 255;
		return $v <= 0.03928 ? $v / 12.92 : pow( ( $v + 0.055 ) / 1.055, 2.4 );
	}, deskman_tpl_rgb( $hex ) );
	return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
}

/** White or deep-ink text, whichever reads better on the colour. */
function deskman_tpl_on( $hex ) {
	$l = deskman_tpl_luminance( $hex );
	return ( 1.05 / ( $l + 0.05 ) ) >= ( ( $l + 0.05 ) / 0.061 ) ? '#FFFFFF' : '#0B1B2B';
}

/** The colour, darkened until it is readable as text on white. */
function deskman_tpl_ink( $hex ) {
	$rgb = deskman_tpl_rgb( $hex );
	for ( $i = 0; $i < 12 && deskman_tpl_luminance( vsprintf( '#%02X%02X%02X', $rgb ) ) > 0.18; $i++ ) {
		$rgb = array_map( function ( $v ) { return (int) round( $v * 0.85 ); }, $rgb );
	}
	return vsprintf( '#%02X%02X%02X', $rgb );
}

/** CSS custom properties for a template's brand palette. */
function deskman_tpl_css_vars( $primary, $secondary, $accent ) {
	$vars = array(
		'--c-primary'       => $primary,
		'--c-secondary'     => $secondary,
		'--c-accent'        => $accent,
		'--c-on-primary'    => deskman_tpl_on( $primary ),
		'--c-on-secondary'  => deskman_tpl_on( $secondary ),
		'--c-on-accent'     => deskman_tpl_on( $accent ),
		'--c-primary-ink'   => deskman_tpl_ink( $primary ),
		'--c-secondary-ink' => deskman_tpl_ink( $secondary ),
	);
	$out = '';
	foreach ( $vars as $k => $v ) { $out .= $k . ':' . $v . ';'; }
	return $out;
}

/* ==========================================================================
   The Templates page and its routes
   ========================================================================== */

/** The Templates page id (0 if missing or trashed). */
function deskman_tpl_page_id() {
	$id = (int) get_option( DESKMAN_TPL_PAGE );
	return ( $id && 'page' === get_post_type( $id ) && 'trash' !== get_post_status( $id ) ) ? $id : 0;
}

/** Create (or adopt) the Templates page. Idempotent. */
function deskman_tpl_ensure_page() {
	$id = deskman_tpl_page_id();
	if ( ! $id ) {
		$p = get_page_by_path( 'templates' );
		if ( $p && 'trash' !== $p->post_status ) {
			$id = (int) $p->ID;
		} else {
			$id = (int) wp_insert_post( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Templates',
				'post_name'    => 'templates',
				'post_content' => '',
				'post_excerpt' => 'Industry website templates you can personalise and preview live on every device, then launch with Deskman Studio.',
			) );
		}
		if ( $id ) { update_option( DESKMAN_TPL_PAGE, $id ); }
	}
	return $id;
}

/* Activation creates the page; sites updating from 7.0 get it once on the next
   admin load, the same way the v7 migration works. */
register_activation_hook( DESKMAN_DIR . 'deskman-studio-manager.php', function () {
	deskman_tpl_ensure_page();
	deskman_tpl_rewrites();
	flush_rewrite_rules();
} );
add_action( 'admin_init', function () {
	if ( get_option( 'deskman_tpl_v1' ) || ! current_user_can( 'manage_options' ) ) { return; }
	deskman_tpl_ensure_page();
	deskman_tpl_protect_uploads();
	update_option( 'deskman_tpl_v1', 1 );
	update_option( 'deskman_tpl_flush', 1 );
} );

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'dsk_tpl';
	$vars[] = 'dsk_tpl_preview';
	return $vars;
} );

/** Sub-routes follow the page's own slug, so renaming the page keeps them working. */
function deskman_tpl_rewrites() {
	$id = deskman_tpl_page_id();
	if ( ! $id ) { return; }
	$base = preg_quote( trim( get_page_uri( $id ), '/' ), '#' );
	if ( $base === '' ) { return; }
	add_rewrite_rule( '^' . $base . '/([a-z0-9-]+)/preview/?$', 'index.php?dsk_tpl_preview=$matches[1]', 'top' );
	add_rewrite_rule( '^' . $base . '/([a-z0-9-]+)/?$', 'index.php?page_id=' . $id . '&dsk_tpl=$matches[1]', 'top' );
}
add_action( 'init', function () {
	deskman_tpl_rewrites();
	if ( get_option( 'deskman_tpl_flush' ) ) {
		delete_option( 'deskman_tpl_flush' );
		flush_rewrite_rules( false );
	}
}, 20 );
add_action( 'save_post_page', function ( $post_id ) {
	if ( (int) $post_id === deskman_tpl_page_id() ) { update_option( 'deskman_tpl_flush', 1 ); }
} );

function deskman_tpl_gallery_url() {
	$id = deskman_tpl_page_id();
	return $id ? get_permalink( $id ) : home_url( '/templates/' );
}
function deskman_tpl_industry_url( $slug ) {
	return get_option( 'permalink_structure' )
		? trailingslashit( deskman_tpl_gallery_url() ) . rawurlencode( $slug ) . '/'
		: add_query_arg( 'dsk_tpl', rawurlencode( $slug ), deskman_tpl_gallery_url() );
}
function deskman_tpl_preview_url( $slug ) {
	return get_option( 'permalink_structure' )
		? trailingslashit( deskman_tpl_gallery_url() ) . rawurlencode( $slug ) . '/preview/'
		: add_query_arg( 'dsk_tpl_preview', rawurlencode( $slug ), home_url( '/' ) );
}

/** sessionStorage key for a template's personalisation (browser only). */
function deskman_tpl_storage_key( $template_id ) {
	return 'dmt:state:' . $template_id . ':v1';
}

/* ==========================================================================
   Front end
   ========================================================================== */

/** What the current request is: '' | 'gallery' | 'customiser'. */
function deskman_tpl_view( $set = null, $industry = null ) {
	static $view = '', $current = null;
	if ( $set !== null ) { $view = $set; $current = $industry; }
	return array( $view, $current );
}

add_action( 'template_redirect', function () {
	$preview = get_query_var( 'dsk_tpl_preview' );
	if ( $preview ) {
		deskman_tpl_render_preview( sanitize_title( $preview ) );
		exit;
	}
	$page = deskman_tpl_page_id();
	if ( ! $page || ! is_page( $page ) ) { return; }

	$slug = sanitize_title( (string) get_query_var( 'dsk_tpl' ) );
	if ( $slug === '' ) { deskman_tpl_view( 'gallery' ); return; }

	$industry = deskman_tpl_industry( $slug );
	if ( ! $industry ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
		return;
	}
	if ( ! $industry['available'] ) {
		/* Coming soon: back to the gallery with a short explanation. Never a
		   template page that does not exist. */
		wp_safe_redirect( add_query_arg( 'coming-soon', $slug, deskman_tpl_gallery_url() ) . '#industries' );
		exit;
	}
	deskman_tpl_view( 'customiser', $industry );
}, 5 );

/* Keep WordPress from "correcting" the sub-routes back to the page URL. */
add_filter( 'redirect_canonical', function ( $redirect ) {
	return ( get_query_var( 'dsk_tpl' ) || get_query_var( 'dsk_tpl_preview' ) ) ? false : $redirect;
} );

/** First match: child theme, theme, plugin. */
function deskman_tpl_locate( $rel ) {
	foreach ( array( get_stylesheet_directory(), get_template_directory() ) as $root ) {
		$f = $root . '/site-templates/' . $rel;
		if ( is_file( $f ) ) { return $f; }
	}
	return DESKMAN_TPL_DIR . $rel;
}

add_filter( 'template_include', function ( $template ) {
	list( $view ) = deskman_tpl_view();
	return $view ? deskman_tpl_locate( 'views/' . $view . '.php' ) : $template;
}, 99 );

add_filter( 'document_title_parts', function ( $parts ) {
	list( $view, $industry ) = deskman_tpl_view();
	if ( 'customiser' === $view && $industry ) {
		/* translators: %s: industry name. */
		$parts['title'] = sprintf( __( '%s Website Template — Customise & Preview', 'deskman-studio-manager' ), $industry['name'] );
	}
	return $parts;
} );

add_filter( 'body_class', function ( $classes ) {
	list( $view ) = deskman_tpl_view();
	if ( $view ) { $classes[] = 'dmt-view dmt-view--' . $view; }
	return $classes;
} );

/** Asset version: file modified time, like the theme's dm_ver(). */
function deskman_tpl_ver( $rel ) {
	$p = DESKMAN_DIR . 'assets/site-templates/' . $rel;
	return file_exists( $p ) ? (string) filemtime( $p ) : DESKMAN_VER;
}

add_action( 'wp_enqueue_scripts', function () {
	list( $view, $industry ) = deskman_tpl_view();
	if ( ! $view ) { return; }

	wp_enqueue_style( 'deskman-site-templates', DESKMAN_TPL_URL . 'site-templates.css', array(), deskman_tpl_ver( 'site-templates.css' ) );
	if ( 'gallery' === $view ) {
		wp_enqueue_script( 'deskman-site-templates-gallery', DESKMAN_TPL_URL . 'gallery.js', array(), deskman_tpl_ver( 'gallery.js' ), true );
		return;
	}

	$t = deskman_tpl_template( $industry['template'] );
	wp_enqueue_script( 'deskman-site-templates-customiser', DESKMAN_TPL_URL . 'customiser.js', array(), deskman_tpl_ver( 'customiser.js' ), true );
	wp_add_inline_script( 'deskman-site-templates-customiser', 'window.DMT_CUSTOMISER = ' . wp_json_encode( array(
		'template'     => $t['id'],
		'templateName' => $t['name'],
		'industry'     => $industry['slug'],
		'storageKey'   => deskman_tpl_storage_key( $t['id'] ),
		'schema'       => deskman_tpl_fields_js( $t['id'] ),
		'endpoint'     => esc_url_raw( rest_url( 'deskman/v1/template-enquiry' ) ),
		'logo'         => array( 'maxBytes' => DESKMAN_TPL_LOGO_MAX, 'types' => array( 'image/png', 'image/jpeg', 'image/webp' ) ),
		'i18n'         => deskman_tpl_strings(),
	) ) . ';', 'before' );
}, 30 );

/** Strings used by customiser.js. */
function deskman_tpl_strings() {
	$d = 'deskman-studio-manager';
	return array(
		'logoType'       => __( 'Please choose a PNG, JPEG or WebP image.', $d ),
		'logoSize'       => __( 'That file is too large. Please choose an image under 2 MB.', $d ),
		'logoInvalid'    => __( 'We couldn’t read that image. Please try a different file.', $d ),
		'logoSmall'      => __( 'That image is very small. Please use a logo at least 16 pixels in each direction.', $d ),
		'logoAdded'      => __( 'Logo added to your website preview.', $d ),
		'logoRemoved'    => __( 'Logo removed. Your clinic name is shown instead.', $d ),
		'replaceLogo'    => __( 'Replace logo', $d ),
		'resetConfirm'   => __( 'Restore the original demo content? Your changes and logo will be removed.', $d ),
		'resetDone'      => __( 'Demo content restored.', $d ),
		'deviceChanged'  => __( 'Previewing at %1$s width (%2$s px).', $d ),
		'scaleLabel'     => __( '%1$s px · shown at %2$s%%', $d ),
		'actualSize'     => __( '%s px · actual size', $d ),
		'required'       => __( 'Please fill in this field.', $d ),
		'email'          => __( 'Please enter a valid email address, e.g. name@example.com.', $d ),
		'phone'          => __( 'Please enter a valid phone number, including your country or area code.', $d ),
		'name'           => __( 'Please enter your full name.', $d ),
		'consent'        => __( 'Please confirm we may contact you about your project.', $d ),
		'tooLong'        => __( 'Please shorten this to %d characters or fewer.', $d ),
		'errorSummary'   => __( 'Please correct the highlighted fields.', $d ),
		'sending'        => __( 'Sending…', $d ),
		'network'        => __( 'We couldn’t reach our server, so your enquiry has not been sent. Please check your connection and try again.', $d ),
		'serverError'    => __( 'Something went wrong and your enquiry has not been sent. Please try again in a moment.', $d ),
		'rateLimited'    => __( 'You’ve sent several enquiries recently. Please wait a little while before trying again.', $d ),
		'customised'     => __( '%d details personalised', $d ),
		'noneCustomised' => __( 'Demo content (not yet personalised)', $d ),
		'logoIncluded'   => __( 'Logo included', $d ),
		'noLogo'         => __( 'No logo uploaded', $d ),
	);
}

/** Headline markup in the theme's style, with a plain fallback. */
function deskman_tpl_heading( $text, $dark = false ) {
	if ( $dark && function_exists( 'dm_hd' ) ) { return dm_hd( $text ); }
	if ( ! $dark && function_exists( 'dm_h' ) ) { return dm_h( $text ); }
	return esc_html( str_replace( array( '{', '}', '|' ), array( '', '', ' ' ), (string) $text ) );
}

/** The editable gallery copy (Deskman → Settings → Site · Pages). */
function deskman_tpl_copy( $key, $default ) {
	$v = function_exists( 'deskman_opt' ) ? deskman_opt( $key, $default ) : $default;
	return ( $v === '' || $v === null ) ? $default : $v;
}

/** Data for the gallery view. */
function deskman_tpl_gallery_data() {
	$industries = deskman_tpl_industries();
	$soon_slug  = isset( $_GET['coming-soon'] ) ? sanitize_title( wp_unslash( $_GET['coming-soon'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$featured   = null;
	foreach ( $industries as $i ) {
		if ( $i['available'] ) { $featured = $i; break; }
	}
	return array(
		'industries'  => $industries,
		'featured'    => $featured,
		'coming_soon' => $soon_slug ? deskman_tpl_industry( $soon_slug ) : null,
		'contact_url' => function_exists( 'dm_link' ) ? dm_link( 'contact' ) : deskman_url( 'contact' ),
	);
}

/**
 * The template itself, as a standalone document for the preview frame.
 * Rendered with demo copy only; the visitor's details are applied in their
 * own browser.
 */
function deskman_tpl_render_preview( $slug ) {
	$industry = deskman_tpl_industry( $slug );
	if ( ! $industry || ! $industry['available'] ) {
		status_header( 404 );
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );
		echo '<!doctype html><meta charset="utf-8"><title>' . esc_html__( 'Template not found', 'deskman-studio-manager' ) . '</title><p>' . esc_html__( 'This template is not available yet.', 'deskman-studio-manager' ) . '</p>';
		return;
	}
	$template = deskman_tpl_template( $industry['template'] );
	$values   = deskman_tpl_defaults( $template['id'] );

	status_header( 200 );
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'X-Robots-Tag: noindex, nofollow' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( "Content-Security-Policy: frame-ancestors 'self'" );
	header( 'Referrer-Policy: same-origin' );

	$assets = array( 'styles' => array(), 'scripts' => array(), 'preload' => array() );
	foreach ( array_keys( $assets ) as $type ) {
		foreach ( isset( $template[ $type ] ) ? (array) $template[ $type ] : array() as $rel ) {
			/* Preloaded fonts must match the URL the stylesheet requests. */
			$assets[ $type ][] = 'preload' === $type ? DESKMAN_TPL_URL . $rel : add_query_arg( 'ver', deskman_tpl_ver( $rel ), DESKMAN_TPL_URL . $rel );
		}
	}
	$config = array(
		'template'   => $template['id'],
		'storageKey' => deskman_tpl_storage_key( $template['id'] ),
		'schema'     => deskman_tpl_fields_js( $template['id'] ),
		'i18n'       => array( 'previewNotice' => __( 'This is a live preview. Appointment requests will be connected when your website is built.', 'deskman-studio-manager' ) ),
	);

	include $template['dir'] . 'preview.php';
}

/* ==========================================================================
   "Start My Project" — into the existing lead inbox
   ========================================================================== */

add_action( 'rest_api_init', function () {
	register_rest_route( 'deskman/v1', '/template-enquiry', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'deskman_tpl_handle_enquiry',
		/* A public form, protected the same way as the contact form: nonce,
		   honeypot, signed render time, spam score and per-IP rate limit. */
		'permission_callback' => '__return_true',
	) );
} );

/**
 * Handle a template enquiry.
 *
 * Uses the contact form's own rules from leads.php — the same email checks
 * (typos, throwaway inboxes, dead domains), phone check, spam score and
 * quarantine — and stores into Deskman → Forms with the template, the
 * personalisation and the logo attached.
 *
 * One deliberate difference from the contact form: this is an API answering a
 * script on the page, so a person who is rate-limited is told so plainly
 * instead of being shown a success screen for a message that was not stored.
 */
function deskman_tpl_handle_enquiry( WP_REST_Request $req ) {
	$p   = function ( $k ) use ( $req ) { return (string) $req->get_param( $k ); };
	$err = function ( $fields ) {
		return new WP_Error( 'deskman_tpl_invalid', __( 'Please correct the highlighted fields.', 'deskman-studio-manager' ), array( 'status' => 422, 'fields' => $fields ) );
	};

	/* 1. bots: a filled honeypot is the one hard block. The page token only
	   feeds the spam score, as on the contact form, so a long-open tab or a
	   cached page never costs a real lead. Nothing is stored here. */
	$age = function_exists( 'deskman_lead_token_age' ) ? deskman_lead_token_age( $p( 'token' ) ) : -1;
	if ( trim( $p( 'website' ) ) !== '' ) {
		return new WP_Error( 'deskman_tpl_rejected', __( 'Your enquiry could not be sent. Please reload the page and try again.', 'deskman-studio-manager' ), array( 'status' => 400 ) );
	}
	if ( ! wp_verify_nonce( $p( 'deskman_nonce' ), 'deskman_contact' ) ) {
		return new WP_Error( 'deskman_tpl_nonce', __( 'Your session expired before the enquiry could be sent. Please reload the page and try again.', 'deskman-studio-manager' ), array( 'status' => 403 ) );
	}

	/* 2. rate limit (shared with the contact form) */
	$ip = function_exists( 'deskman_lead_ip' ) ? deskman_lead_ip() : '';
	if ( function_exists( 'deskman_lead_throttled' ) && deskman_lead_throttled( $ip ) ) {
		return new WP_Error( 'deskman_tpl_rate', __( 'Too many enquiries from this connection. Please try again later.', 'deskman-studio-manager' ), array( 'status' => 429 ) );
	}

	/* 3. validate */
	$fields   = array();
	$name     = sanitize_text_field( $p( 'name' ) );
	$business = sanitize_text_field( $p( 'business' ) );
	$email    = sanitize_email( $p( 'email' ) );
	$reqs     = sanitize_textarea_field( $p( 'requirements' ) );
	$len      = function ( $s ) { return function_exists( 'mb_strlen' ) ? mb_strlen( $s ) : strlen( $s ); };

	if ( $len( $name ) < 2 || $len( $name ) > 100 ) { $fields['name'] = __( 'Please tell us your name.', 'deskman-studio-manager' ); }
	if ( $len( $business ) < 2 || $len( $business ) > 120 ) { $fields['business'] = __( 'Please enter your clinic or business name.', 'deskman-studio-manager' ); }
	if ( $len( $reqs ) > 3000 ) { $fields['requirements'] = __( 'Please keep additional requirements under 3,000 characters.', 'deskman-studio-manager' ); }
	if ( '1' !== $p( 'consent' ) ) { $fields['consent'] = __( 'Please confirm we may contact you about your project.', 'deskman-studio-manager' ); }

	$echk = function_exists( 'deskman_lead_check_email' ) ? deskman_lead_check_email( $email ) : array( 'ok' => (bool) is_email( $email ), 'msg' => __( 'Please enter a valid email address.', 'deskman-studio-manager' ) );
	if ( ! $echk['ok'] ) { $fields['email'] = $echk['msg']; }

	$phone_required = function_exists( 'deskman_lead_on' ) ? deskman_lead_on( 'lead_phone_required', true ) : true;
	$pchk = function_exists( 'deskman_lead_check_phone' ) ? deskman_lead_check_phone( $p( 'phone' ), $phone_required ) : array( 'ok' => true, 'value' => sanitize_text_field( $p( 'phone' ) ) );
	if ( ! $pchk['ok'] ) { $fields['phone'] = $pchk['msg']; }

	$template_id = sanitize_key( $p( 'template' ) );
	$template    = deskman_tpl_template( $template_id );
	$industry    = deskman_tpl_industry_for_template( $template_id );
	if ( ! $template || ! $industry ) { $fields['template'] = __( 'The selected template is not available.', 'deskman-studio-manager' ); }

	$files = $req->get_file_params();
	$logo  = isset( $files['logo'] ) ? $files['logo'] : null;
	if ( $logo ) {
		$lerr = deskman_tpl_check_logo( $logo );
		if ( $lerr ) { $fields['logo'] = $lerr; }
	}
	if ( $fields ) { return $err( $fields ); }

	$custom = deskman_tpl_clean_customisation( $template_id, json_decode( $p( 'customisation' ), true ) );

	/* 4. the message the inbox shows, and the spam score's input. A summary
	   line keeps an enquiry with no extra notes from scoring as "too short". */
	$msg = trim( $reqs . "\n\n" . sprintf( 'Website template: %s (%s) for %s.', $template['name'], $industry['name'], $business ) );

	$scored = function_exists( 'deskman_lead_score' ) ? deskman_lead_score( array(
		'name'        => $name,
		'msg'         => $msg,
		'phone'       => $pchk['value'],
		'email_local' => isset( $echk['local'] ) ? $echk['local'] : '',
		'js_ok'       => true, // only the page's script can send this
		'elapsed'     => $age,
		'ua'          => function_exists( 'deskman_lead_ua' ) ? deskman_lead_ua() : '',
		'dupe'        => function_exists( 'deskman_lead_is_duplicate' ) ? deskman_lead_is_duplicate( $email, $msg ) : false,
	) ) : array( 'score' => 0, 'reasons' => array() );
	$threshold = function_exists( 'deskman_lead_int' ) ? deskman_lead_int( 'lead_spam_threshold', 5, 1 ) : 5;
	$is_spam   = $scored['score'] >= $threshold;

	/* 5. store — quarantined enquiries are drafts, never discarded */
	$ref = 'DMS-' . gmdate( 'ymd' ) . '-' . strtoupper( wp_generate_password( 5, false, false ) );
	$eid = wp_insert_post( array(
		'post_type'    => 'deskman_enquiry',
		'post_status'  => $is_spam ? 'draft' : 'publish',
		'post_title'   => ( $is_spam ? '[Spam] ' : '' ) . $name . ' — Template: ' . $industry['name'],
		'post_content' => $msg,
	), true );
	if ( is_wp_error( $eid ) || ! $eid ) {
		return new WP_Error( 'deskman_tpl_save', __( 'We could not save your enquiry. Please try again.', 'deskman-studio-manager' ), array( 'status' => 500 ) );
	}

	$logo_file = '';
	if ( $logo ) {
		$logo_file = deskman_tpl_store_logo( $logo );
		if ( is_wp_error( $logo_file ) ) {
			wp_delete_post( $eid, true );
			return $err( array( 'logo' => $logo_file->get_error_message() ) );
		}
	}

	$meta = array(
		'_deskman_email'         => $email,
		'_deskman_phone'         => $pchk['value'],
		'_deskman_company'       => $business,
		'_deskman_ptype'         => 'Website template',
		'_deskman_ip'            => $ip,
		'_deskman_ua'            => function_exists( 'deskman_lead_ua' ) ? deskman_lead_ua() : '',
		'_deskman_score'         => (int) $scored['score'],
		'_deskman_tpl_ref'       => $ref,
		'_deskman_tpl_template'  => $template_id,
		'_deskman_tpl_name'      => $name,
		'_deskman_tpl_custom'    => $custom,
		'_deskman_tpl_logo'      => $logo_file,
		'_deskman_tpl_consent'   => gmdate( 'c' ),
	);
	if ( $scored['reasons'] ) { $meta['_deskman_flags'] = implode( "\n", $scored['reasons'] ); }
	if ( $is_spam ) { $meta['_deskman_spam'] = '1'; }
	foreach ( $meta as $k => $v ) { update_post_meta( $eid, $k, $v ); }

	if ( function_exists( 'deskman_lead_note_submission' ) ) { deskman_lead_note_submission( $ip ); }

	/* 6. notify — not for quarantined enquiries, matching the contact form */
	if ( ! $is_spam ) { deskman_tpl_notify( $eid ); }

	/**
	 * A template enquiry was stored (published or quarantined).
	 *
	 * @param int  $eid     Enquiry post id.
	 * @param bool $is_spam Whether it was quarantined.
	 */
	do_action( 'deskman_template_enquiry', $eid, $is_spam );

	return new WP_REST_Response( array(
		'success'   => true,
		'reference' => $ref,
		/* translators: %s: customer email. */
		'message'   => sprintf( __( 'Our team will review your template and personalisation and reply to %s within one working day to plan the next steps.', 'deskman-studio-manager' ), $email ),
	), 201 );
}

/** Email the studio, in the same shape as a contact-form lead. */
function deskman_tpl_notify( $eid ) {
	$m        = function ( $k ) use ( $eid ) { return get_post_meta( $eid, $k, true ); };
	$one      = function ( $v ) { return trim( preg_replace( '/[\r\n]+/', ' ', (string) $v ) ); };
	$template = deskman_tpl_template( $m( '_deskman_tpl_template' ) );
	$fields   = $template ? deskman_tpl_fields( $template['id'] ) : array();
	$defaults = $template ? deskman_tpl_defaults( $template['id'] ) : array();
	$name     = $one( $m( '_deskman_tpl_name' ) );

	$lines = array(
		"Reference: " . $m( '_deskman_tpl_ref' ),
		"Template: " . ( $template ? $template['name'] : $m( '_deskman_tpl_template' ) ),
		"Name: {$name}",
		"Email: " . $m( '_deskman_email' ),
		"Phone: " . $m( '_deskman_phone' ),
		"Business: " . $one( $m( '_deskman_company' ) ),
		'Logo uploaded: ' . ( $m( '_deskman_tpl_logo' ) ? 'yes (view it in Deskman → Forms)' : 'no' ),
		'',
		'Personalised fields:',
	);
	$changed = 0;
	foreach ( (array) $m( '_deskman_tpl_custom' ) as $k => $v ) {
		if ( isset( $fields[ $k ], $defaults[ $k ] ) && (string) $defaults[ $k ] !== (string) $v ) {
			$lines[] = '- ' . $fields[ $k ]['label'] . ': ' . $one( $v );
			$changed++;
		}
	}
	if ( ! $changed ) { $lines[] = '- none (demo content)'; }
	$lines[] = '';
	$lines[] = get_post_field( 'post_content', $eid );
	$lines[] = '';
	$lines[] = 'Open: ' . admin_url( 'post.php?post=' . (int) $eid . '&action=edit' );

	$to = function_exists( 'deskman_opt' ) ? deskman_opt( 'contact_email', get_option( 'admin_email' ) ) : get_option( 'admin_email' );
	wp_mail(
		$to,
		sprintf( 'New template enquiry from %s — Deskman Studio', $name ),
		implode( "\n", $lines ) . "\n",
		array( 'Reply-To: ' . $name . ' <' . $m( '_deskman_email' ) . '>' )
	);
}

/* ==========================================================================
   Logos: validated, re-encoded and kept out of the media library
   ========================================================================== */

/** Private directory for logos, with web access blocked on Apache. */
function deskman_tpl_protect_uploads() {
	$up = wp_upload_dir( null, false );
	if ( ! empty( $up['error'] ) ) { return false; }
	$dir = trailingslashit( $up['basedir'] ) . DESKMAN_TPL_UPLOADS;
	if ( ! wp_mkdir_p( $dir ) ) { return false; }
	$files = array(
		'.htaccess'  => "<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n\tDeny from all\n</IfModule>\n",
		'index.php'  => "<?php\n// Silence is golden.\n",
		'index.html' => '',
	);
	foreach ( array( $dir, dirname( $dir ) ) as $d ) {
		foreach ( $files as $name => $body ) {
			if ( ! file_exists( $d . '/' . $name ) ) { file_put_contents( $d . '/' . $name, $body ); } // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		}
	}
	return $dir;
}

/** Validate an uploaded logo. Returns an error message or ''. */
function deskman_tpl_check_logo( $file ) {
	$mimes = array( 'png' => 'image/png', 'jpg|jpeg|jpe' => 'image/jpeg', 'webp' => 'image/webp' );
	if ( ! is_array( $file ) || ! isset( $file['error'], $file['tmp_name'], $file['size'], $file['name'] ) ) {
		return __( 'The logo upload was not valid.', 'deskman-studio-manager' );
	}
	if ( in_array( $file['error'], array( UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE ), true ) || (int) $file['size'] > DESKMAN_TPL_LOGO_MAX ) {
		return __( 'Your logo must be smaller than 2 MB.', 'deskman-studio-manager' );
	}
	$uploaded = (bool) apply_filters( 'deskman_tpl_is_uploaded_file', is_uploaded_file( $file['tmp_name'] ), $file['tmp_name'] );
	if ( UPLOAD_ERR_OK !== $file['error'] || ! $uploaded ) {
		return __( 'The logo could not be uploaded. Please try again.', 'deskman-studio-manager' );
	}
	$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $mimes );
	$info  = @getimagesize( $file['tmp_name'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	if ( empty( $check['type'] ) || ! in_array( $check['type'], $mimes, true ) || ! $info || ! in_array( $info['mime'], $mimes, true ) ) {
		return __( 'Your logo must be a PNG, JPEG or WebP image.', 'deskman-studio-manager' );
	}
	if ( $info[0] < 16 || $info[1] < 16 || $info[0] > 6000 || $info[1] > 6000 ) {
		return __( 'Your logo must be between 16 and 6000 pixels in each direction.', 'deskman-studio-manager' );
	}
	return '';
}

/** Re-encode a validated logo into the private directory. Returns the file name. */
function deskman_tpl_store_logo( $file ) {
	$dir = deskman_tpl_protect_uploads();
	if ( ! $dir ) { return new WP_Error( 'logo_dir', __( 'The logo could not be saved.', 'deskman-studio-manager' ) ); }
	$info = getimagesize( $file['tmp_name'] );
	$ext  = array( 'image/png' => 'png', 'image/jpeg' => 'jpg', 'image/webp' => 'webp' )[ $info['mime'] ];
	$ed   = wp_get_image_editor( $file['tmp_name'] );
	if ( is_wp_error( $ed ) ) { return new WP_Error( 'logo_read', __( 'Your logo could not be processed. Please try a different image.', 'deskman-studio-manager' ) ); }
	$size = $ed->get_size();
	if ( $size['width'] > 2000 || $size['height'] > 2000 ) { $ed->resize( 2000, 2000, false ); }
	$saved = $ed->save( trailingslashit( $dir ) . gmdate( 'Ym' ) . '-' . wp_generate_password( 32, false, false ) . '.' . $ext, $info['mime'] );
	if ( is_wp_error( $saved ) || empty( $saved['path'] ) ) { return new WP_Error( 'logo_save', __( 'Your logo could not be processed. Please try a different image.', 'deskman-studio-manager' ) ); }
	return wp_basename( $saved['path'] );
}

/** Absolute path of a stored logo, or '' (names are validated, never trusted). */
function deskman_tpl_logo_path( $name ) {
	if ( ! preg_match( '/^[0-9]{6}-[A-Za-z0-9]{32}\.(png|jpg|webp)$/', (string) $name ) ) { return ''; }
	$up   = wp_upload_dir( null, false );
	$path = trailingslashit( $up['basedir'] ) . DESKMAN_TPL_UPLOADS . '/' . $name;
	return is_readable( $path ) ? $path : '';
}

/* Admins view logos through this endpoint only (nonce + capability). */
add_action( 'admin_post_deskman_tpl_logo', function () {
	$eid = isset( $_GET['enquiry'] ) ? absint( $_GET['enquiry'] ) : 0;
	check_admin_referer( 'deskman_tpl_logo_' . $eid );
	if ( ! $eid || 'deskman_enquiry' !== get_post_type( $eid ) || ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to view this file.', 'deskman-studio-manager' ), 403 );
	}
	$path = deskman_tpl_logo_path( get_post_meta( $eid, '_deskman_tpl_logo', true ) );
	if ( ! $path ) { wp_die( esc_html__( 'Logo not found.', 'deskman-studio-manager' ), 404 ); }
	nocache_headers();
	header( 'Content-Type: ' . getimagesize( $path )['mime'] );
	header( 'Content-Length: ' . filesize( $path ) );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Content-Disposition: inline; filename="logo-' . $eid . '.' . pathinfo( $path, PATHINFO_EXTENSION ) . '"' );
	readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
	exit;
} );

/* Deleting an enquiry permanently also deletes its logo. */
add_action( 'before_delete_post', function ( $post_id ) {
	if ( 'deskman_enquiry' !== get_post_type( $post_id ) ) { return; }
	$path = deskman_tpl_logo_path( get_post_meta( $post_id, '_deskman_tpl_logo', true ) );
	if ( $path ) { wp_delete_file( $path ); }
} );

/* ==========================================================================
   Admin: the template, personalisation and logo on the enquiry screen
   ========================================================================== */

add_action( 'add_meta_boxes_deskman_enquiry', function ( $post ) {
	if ( get_post_meta( $post->ID, '_deskman_tpl_template', true ) === '' ) { return; }
	add_meta_box( 'deskman_tpl_meta', __( 'Website template', 'deskman-studio-manager' ), 'deskman_tpl_meta_box', 'deskman_enquiry', 'normal', 'high' );
} );

function deskman_tpl_meta_box( $post ) {
	$m        = function ( $k ) use ( $post ) { return get_post_meta( $post->ID, $k, true ); };
	$template = deskman_tpl_template( $m( '_deskman_tpl_template' ) );
	$fields   = $template ? deskman_tpl_fields( $template['id'] ) : array();
	$defaults = $template ? deskman_tpl_defaults( $template['id'] ) : array();
	$logo     = ( deskman_tpl_logo_path( $m( '_deskman_tpl_logo' ) ) && current_user_can( 'manage_options' ) )
		? wp_nonce_url( admin_url( 'admin-post.php?action=deskman_tpl_logo&enquiry=' . (int) $post->ID ), 'deskman_tpl_logo_' . $post->ID ) : '';

	echo '<table class="widefat striped"><tbody>';
	echo '<tr><th style="width:160px">' . esc_html__( 'Reference', 'deskman-studio-manager' ) . '</th><td><code>' . esc_html( $m( '_deskman_tpl_ref' ) ) . '</code></td></tr>';
	echo '<tr><th>' . esc_html__( 'Name', 'deskman-studio-manager' ) . '</th><td>' . esc_html( $m( '_deskman_tpl_name' ) ) . '</td></tr>';
	echo '<tr><th>' . esc_html__( 'Template', 'deskman-studio-manager' ) . '</th><td>' . esc_html( $template ? $template['name'] : $m( '_deskman_tpl_template' ) ) . '</td></tr>';
	echo '<tr><th>' . esc_html__( 'Logo', 'deskman-studio-manager' ) . '</th><td>';
	if ( $logo ) {
		echo '<a href="' . esc_url( $logo ) . '" target="_blank" rel="noopener"><img src="' . esc_url( $logo ) . '" alt="' . esc_attr__( 'Uploaded logo', 'deskman-studio-manager' ) . '" style="max-width:240px;max-height:110px;padding:8px;border:1px solid #dcdcde;border-radius:6px;background:repeating-conic-gradient(#f0f0f1 0 25%,#fff 0 50%) 0 0/16px 16px"></a>';
	} else {
		esc_html_e( 'No logo uploaded', 'deskman-studio-manager' );
	}
	echo '</td></tr>';
	echo '<tr><th>' . esc_html__( 'Consent', 'deskman-studio-manager' ) . '</th><td>' . esc_html( $m( '_deskman_tpl_consent' ) ) . '</td></tr>';
	echo '</tbody></table>';

	echo '<h4>' . esc_html__( 'Personalisation', 'deskman-studio-manager' ) . '</h4>';
	echo '<table class="widefat striped"><tbody>';
	foreach ( (array) $m( '_deskman_tpl_custom' ) as $k => $v ) {
		if ( ! isset( $fields[ $k ] ) ) { continue; }
		$demo   = isset( $defaults[ $k ] ) && (string) $defaults[ $k ] === (string) $v;
		$swatch = 'color' === $fields[ $k ]['type'] ? '<span style="display:inline-block;width:14px;height:14px;border-radius:4px;vertical-align:-2px;margin-right:6px;box-shadow:inset 0 0 0 1px rgba(0,0,0,.15);background:' . esc_attr( $v ) . '"></span>' : '';
		echo '<tr><th style="width:220px">' . esc_html( $fields[ $k ]['label'] )
			. ( $demo ? ' <span style="color:#8c8f94;font-weight:400">' . esc_html__( '(demo)', 'deskman-studio-manager' ) . '</span>' : '' )
			. '</th><td>' . $swatch . nl2br( esc_html( 'select' === $fields[ $k ]['type'] && isset( $fields[ $k ]['options'][ $v ] ) ? $fields[ $k ]['options'][ $v ] : $v ) ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</tbody></table>';
	if ( $template ) {
		echo '<p><a class="button" href="' . esc_url( deskman_tpl_preview_url( deskman_tpl_industry_for_template( $template['id'] )['slug'] ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Open the template (demo content)', 'deskman-studio-manager' ) . '</a></p>';
	}
}

/* Label the Templates page in Pages, like WordPress does for the front page. */
add_filter( 'display_post_states', function ( $states, $post ) {
	if ( (int) $post->ID === deskman_tpl_page_id() ) { $states['deskman_tpl'] = __( 'Templates gallery', 'deskman-studio-manager' ); }
	return $states;
}, 10, 2 );
