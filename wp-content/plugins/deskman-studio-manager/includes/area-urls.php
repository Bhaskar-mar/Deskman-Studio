<?php
/**
 * Area page URLs.
 *
 * By default a location page lives under a base — /locations/warangal/. Set
 * the URL base to nothing and it lives at the root instead, on whatever slug
 * the page itself carries:
 *
 *   /best-digital-marketing-in-visakhapatnam/
 *
 * WHY THIS NEEDS ITS OWN CODE
 *
 * Registering the post type with an empty rewrite slug does not work. WordPress
 * would generate the catch-all rule ^([^/]+)/?$ for it, which matches every
 * single-segment URL on the site — so /contact/ and /pricing/ would be looked
 * up as area pages first and 404 when none existed. The base exists precisely
 * to keep that from happening.
 *
 * So in root mode the post type is registered with rewrite disabled, and one
 * exact rule is added per published area page:
 *
 *   ^best-digital-marketing-in-visakhapatnam/?$
 *
 * Nothing else on the site can match those rules, because they are literal
 * slugs rather than a pattern. A handful of location pages means a handful of
 * rules, which is well within what WordPress carries anyway.
 *
 * The slug list is cached in an option and rebuilt whenever an area page is
 * saved, trashed, restored or deleted, so the rules follow the content without
 * a manual permalink flush.
 *
 * COLLISIONS
 *
 * If a real page or post already owns a slug, no rule is added for the area
 * page — the existing page keeps its URL — and the conflict is reported in the
 * admin rather than silently breaking one of the two.
 *
 * @package Deskman_Studio_Manager
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'DESKMAN_AREA_SLUGS_OPTION', 'deskman_area_root_slugs' );

/** True when area pages sit at the site root (URL base left empty). */
function deskman_area_root_mode() {
	$o = get_option( DESKMAN_AREA_OPTION );
	if ( ! is_array( $o ) ) { $o = array(); }
	/* Read the stored value directly: deskman_area_opt() falls back to the
	   default for an empty string, and here an empty string is a real choice. */
	return array_key_exists( 'slug', $o ) && trim( (string) $o['slug'] ) === '';
}

/**
 * The published area page slugs, minus any that clash with an existing page or
 * post. Cached, because this runs on every request in root mode.
 *
 * @param bool $fresh Skip the cache and rebuild.
 */
function deskman_area_root_slugs( $fresh = false ) {
	if ( ! $fresh ) {
		$cached = get_option( DESKMAN_AREA_SLUGS_OPTION );
		if ( is_array( $cached ) ) { return $cached; }
	}

	$slugs = array();
	$posts = get_posts( array(
		'post_type'        => DESKMAN_AREA_CPT,
		'post_status'      => array( 'publish', 'private' ),
		'numberposts'      => 300,
		'fields'           => 'ids',
		'suppress_filters' => false,
	) );

	foreach ( $posts as $id ) {
		$slug = get_post_field( 'post_name', $id );
		if ( $slug === '' ) { continue; }
		if ( deskman_area_slug_taken( $slug, $id ) ) { continue; }
		$slugs[ $slug ] = (int) $id;
	}

	update_option( DESKMAN_AREA_SLUGS_OPTION, $slugs, false );
	return $slugs;
}

/**
 * Does a page or post already own this slug?
 *
 * @param string $slug    Slug to test.
 * @param int    $exclude Area page ID to ignore.
 */
function deskman_area_slug_taken( $slug, $exclude = 0 ) {
	$hit = get_posts( array(
		'name'             => $slug,
		'post_type'        => array( 'page', 'post' ),
		'post_status'      => array( 'publish', 'private', 'draft', 'pending', 'future' ),
		'numberposts'      => 1,
		'fields'           => 'ids',
		'suppress_filters' => false,
	) );
	if ( ! $hit ) { return false; }
	return ( (int) $hit[0] !== (int) $exclude );
}

/* --------------------------------------------------------------------------
   Rules
   -------------------------------------------------------------------------- */

/* After the post type is registered (init, priority 5). */
add_action( 'init', function () {
	if ( ! deskman_area_root_mode() ) { return; }
	foreach ( deskman_area_root_slugs() as $slug => $id ) {
		add_rewrite_rule(
			'^' . preg_quote( $slug, '/' ) . '/?$',
			'index.php?post_type=' . DESKMAN_AREA_CPT . '&name=' . $slug,
			'top'
		);
	}
}, 10 );

/** Permalinks must match the rules above, or every link on the site is wrong. */
add_filter( 'post_type_link', function ( $link, $post ) {
	if ( ! $post || $post->post_type !== DESKMAN_AREA_CPT || ! deskman_area_root_mode() ) { return $link; }
	if ( $post->post_name === '' ) { return $link; }
	return home_url( '/' . $post->post_name . '/' );
}, 10, 2 );

/** Rebuild the slug list and the rules whenever the set of pages changes. */
function deskman_area_refresh_urls() {
	if ( ! deskman_area_root_mode() ) { return; }
	$before = get_option( DESKMAN_AREA_SLUGS_OPTION );
	$after  = deskman_area_root_slugs( true );
	if ( $before !== $after ) {
		update_option( 'deskman_area_flush', 1, false );
	}
}
add_action( 'save_post_' . DESKMAN_AREA_CPT, 'deskman_area_refresh_urls', 20 );
add_action( 'deleted_post', function ( $post_id ) {
	if ( get_post_type( $post_id ) === DESKMAN_AREA_CPT ) { deskman_area_refresh_urls(); }
} );
add_action( 'transition_post_status', function ( $new, $old, $post ) {
	if ( $post && $post->post_type === DESKMAN_AREA_CPT && $new !== $old ) { deskman_area_refresh_urls(); }
}, 10, 3 );

/* Switching the base on or off changes how every area URL is built, so the
   cache has to go with it. The existing deskman_area_flush option already
   triggers the rebuild on the next load. */
add_action( 'update_option_' . DESKMAN_AREA_OPTION, function ( $old, $new ) {
	$old_slug = is_array( $old ) && isset( $old['slug'] ) ? (string) $old['slug'] : null;
	$new_slug = is_array( $new ) && isset( $new['slug'] ) ? (string) $new['slug'] : null;
	if ( $old_slug !== $new_slug ) {
		delete_option( DESKMAN_AREA_SLUGS_OPTION );
		update_option( 'deskman_area_flush', 1, false );
		/* Remember the base we came from so the old URLs can be redirected
		   rather than dropped — see below. Only a non-empty base produces URLs
		   worth redirecting. */
		if ( $old_slug !== null && trim( $old_slug ) !== '' ) {
			update_option( 'deskman_area_prev_base', sanitize_title( $old_slug ), false );
		}
	}
}, 10, 2 );

/* --------------------------------------------------------------------------
   Keeping the old URLs alive
   -------------------------------------------------------------------------- */

/**
 * 301 an old based URL to its new root URL.
 *
 * Dropping the base changes every location page's address at once. Anything
 * already indexed or linked would 404, which throws away exactly the ranking
 * the pages were built for — so /locations/warangal/ is redirected to
 * /warangal/ (or whatever the page's slug now is) instead.
 *
 * Runs only on a 404, so it can never shadow a URL that resolves normally.
 */
add_action( 'template_redirect', function () {
	if ( ! is_404() || ! deskman_area_root_mode() ) { return; }

	$base = (string) get_option( 'deskman_area_prev_base', '' );
	if ( $base === '' ) { return; }

	$path = trim( (string) wp_parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );
	$home = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
	if ( $home !== '' && strpos( $path, $home ) === 0 ) {
		$path = trim( substr( $path, strlen( $home ) ), '/' );
	}

	$parts = explode( '/', $path );
	if ( count( $parts ) !== 2 || $parts[0] !== $base || $parts[1] === '' ) { return; }

	$page = get_page_by_path( $parts[1], OBJECT, DESKMAN_AREA_CPT );
	if ( ! $page ) { return; }

	wp_safe_redirect( get_permalink( $page ), 301 );
	exit;
} );

/* --------------------------------------------------------------------------
   Telling the user when something collides
   -------------------------------------------------------------------------- */

add_action( 'admin_notices', function () {
	if ( ! deskman_area_root_mode() || ! current_user_can( 'edit_posts' ) ) { return; }
	$screen = get_current_screen();
	if ( ! $screen || strpos( (string) $screen->id, DESKMAN_AREA_CPT ) === false ) { return; }

	$clashes = array();
	$posts   = get_posts( array(
		'post_type'   => DESKMAN_AREA_CPT,
		'post_status' => array( 'publish', 'private' ),
		'numberposts' => 300,
		'fields'      => 'ids',
	) );
	foreach ( $posts as $id ) {
		$slug = get_post_field( 'post_name', $id );
		if ( $slug !== '' && deskman_area_slug_taken( $slug, $id ) ) {
			$clashes[] = sprintf( '%s (/%s/)', get_the_title( $id ), $slug );
		}
	}
	if ( ! $clashes ) { return; }

	echo '<div class="notice notice-warning"><p><strong>';
	esc_html_e( 'Some area pages share a URL with an existing page or post, so the existing one is being served:', 'deskman-studio-manager' );
	echo '</strong></p><ul style="list-style:disc;margin-left:2em">';
	foreach ( $clashes as $c ) { echo '<li>' . esc_html( $c ) . '</li>'; }
	echo '</ul><p>';
	esc_html_e( 'Give the area page a different slug, or delete the page that is holding the URL.', 'deskman-studio-manager' );
	echo '</p></div>';
} );
