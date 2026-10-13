<?php
/**
 * Small helpers shared by every template.
 *
 * The Deskman Studio Manager plugin defines deskman_opt() / deskman_url();
 * the fallbacks here keep the theme working if the plugin is switched off.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'deskman_opt' ) ) {
	function deskman_opt( $key, $default = '' ) { return $default; }
}
if ( ! function_exists( 'deskman_url' ) ) {
	function deskman_url( $slug ) {
		$slug = trim( (string) $slug, '/' );
		if ( $slug === '' ) { return home_url( '/' ); }
		$p = get_page_by_path( $slug );
		return $p ? get_permalink( $p ) : home_url( '/' . $slug . '/' );
	}
}

/** Option with a default; empty stored values fall back to the default. */
function dm_opt( $key, $default = '' ) {
	$v = deskman_opt( $key, $default );
	return ( $v === '' || $v === null ) ? $default : $v;
}

/** Echo an escaped option. */
function dm_e( $key, $default = '' ) { echo esc_html( dm_opt( $key, $default ) ); }

/**
 * Plain text → heading markup. Two tokens only, added after escaping:
 *   |      line break
 *   {run}  accent ('vv' on light sections, 'gradv' on dark ones)
 */
function dm_h( $text, $dark = false ) {
	$esc = esc_html( (string) $text );
	$esc = preg_replace( '/\s*\|\s*/', '<br>', $esc );
	$cls = $dark ? 'gradv' : 'vv';
	return preg_replace( '/\{([^{}]{1,200})\}/u', '<span class="' . $cls . '">$1</span>', $esc );
}

/** Dark-section headline: first part in the silver gradient, {accent} in violet→lime. */
function dm_hd( $text ) {
	$esc   = esc_html( (string) $text );
	$parts = preg_split( '/(\{[^{}]{1,200}\})/u', $esc, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
	$out   = '';
	foreach ( $parts as $p ) {
		$accent = ( $p[0] === '{' && substr( $p, -1 ) === '}' );
		$inner  = preg_replace( '/\s*\|\s*/', '<br>', $accent ? substr( $p, 1, -1 ) : $p );
		$out   .= '<span class="' . ( $accent ? 'gradv' : 'grad' ) . '">' . $inner . '</span>';
	}
	return $out;
}

/** Textarea option → array of non-empty trimmed lines. */
function dm_lines( $key, $default_lines ) {
	$raw = trim( (string) deskman_opt( $key, '' ) );
	if ( $raw === '' ) { return $default_lines; }
	$out = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) ) );
	return $out ? $out : $default_lines;
}

/** First existing page among slugs, else a sensible URL. */
function dm_page_url( $slugs, $fallback = '' ) {
	foreach ( (array) $slugs as $s ) {
		$p = get_page_by_path( $s );
		if ( $p && 'publish' === $p->post_status ) { return get_permalink( $p ); }
	}
	return $fallback !== '' ? $fallback : deskman_url( is_array( $slugs ) ? $slugs[0] : $slugs );
}

function dm_link( $what ) {
	switch ( $what ) {
		case 'contact':  return dm_page_url( 'contact' );
		case 'work':     return dm_page_url( 'work' );
		case 'services': return dm_page_url( 'services' );
		case 'packages': return dm_page_url( array( 'packages', 'pricing' ) );
		case 'about':    return dm_page_url( array( 'about', 'studio' ) );
		case 'process':  return home_url( '/#process' );
		case 'templates': return function_exists( 'deskman_tpl_gallery_url' ) ? deskman_tpl_gallery_url() : dm_page_url( 'templates' );
		case 'insights':
			$id = (int) get_option( 'page_for_posts' );
			return $id ? get_permalink( $id ) : dm_page_url( array( 'insights', 'blog' ) );
		case 'call':     return dm_opt( 'v5_call_url', '' ) !== '' ? dm_opt( 'v5_call_url', '' ) : dm_page_url( 'contact' );
	}
	return home_url( '/' );
}

/** Which top-level nav item is current. */
function dm_current() {
	if ( is_front_page() ) { return 'home'; }
	if ( is_singular( 'deskman_project' ) || is_page( 'work' ) ) { return 'work'; }
	if ( is_page( 'services' ) || is_page_template( 'page-service.php' ) ) { return 'services'; }
	if ( is_page( array( 'packages', 'pricing' ) ) ) { return 'packages'; }
	if ( is_page( array( 'about', 'studio' ) ) ) { return 'about'; }
	if ( is_page( 'contact' ) ) { return 'contact'; }
	if ( is_page( 'templates' ) || ( function_exists( 'deskman_tpl_page_id' ) && deskman_tpl_page_id() && is_page( deskman_tpl_page_id() ) ) ) { return 'templates'; }
	if ( is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_author() || is_date() ) { return 'insights'; }
	return '';
}

/** The shared contact email. */
function dm_email() { return dm_opt( 'contact_email', 'hi@deskmanstudio.com' ); }

/** Social profiles with a real URL (bare "#" counts as unset). */
function dm_socials() {
	$out = array();
	foreach ( array( 'social_in' => array( 'linkedin', 'LinkedIn' ), 'social_ig' => array( 'instagram', 'Instagram' ), 'social_x' => array( 'xsocial', 'X' ), 'social_yt' => array( 'youtube', 'YouTube' ) ) as $k => $m ) {
		$u = trim( (string) deskman_opt( $k, '' ) );
		if ( $u !== '' && $u !== '#' ) { $out[] = array( 'url' => $u, 'icon' => $m[0], 'label' => $m[1] ); }
	}
	return $out;
}

/* ---------------------------------------------------------------- projects */
if ( ! function_exists( 'deskman_project_field' ) ) {
	function deskman_project_field( $post_id, $key, $default = '' ) {
		$v = get_post_meta( (int) $post_id, '_deskman_' . $key, true );
		return ( $v === '' || $v === false || $v === null ) ? $default : $v;
	}
}
if ( ! function_exists( 'deskman_project_image_url' ) ) {
	function deskman_project_image_url( $post_id, $key, $size = 'large' ) {
		$v = deskman_project_field( $post_id, $key, '' );
		if ( $v === '' ) { return ''; }
		if ( is_numeric( $v ) ) { $s = wp_get_attachment_image_url( (int) $v, $size ); return $s ? $s : ''; }
		return esc_url_raw( $v );
	}
}

/** Bundled screenshot for a known project slug. */
function dm_bundled_img( $slug ) {
	$map = array( 'pineprompts' => 'pineprompts', 'jhumkana' => 'jhumkana', 'vizagmap' => 'vizagmap', 'ivy-crest-school' => 'ivy-crest', 'ivy-crest' => 'ivy-crest', 'the-ivy-crest-school' => 'ivy-crest' );
	return isset( $map[ $slug ] ) ? get_theme_file_uri( 'assets/img/' . $map[ $slug ] . '.webp' ) : '';
}

/** One project as a flat array, from a deskman_project post. */
function dm_project( $p ) {
	$img = deskman_project_image_url( $p->ID, 'img_desktop' );
	if ( ! $img ) { $img = deskman_project_image_url( $p->ID, 'img_hero' ); }
	if ( ! $img && has_post_thumbnail( $p ) ) { $img = get_the_post_thumbnail_url( $p, 'large' ); }
	if ( ! $img ) { $img = dm_bundled_img( $p->post_name ); }
	$terms = get_the_terms( $p, 'deskman_project_cat' );
	$cats  = ( $terms && ! is_wp_error( $terms ) ) ? wp_list_pluck( $terms, 'name' ) : array();
	$tech  = array_filter( array_map( 'trim', explode( ',', (string) deskman_project_field( $p->ID, 'tech', '' ) ) ) );
	$live  = (string) deskman_project_field( $p->ID, 'live_url', '' );
	return array(
		'id'    => $p->ID,
		'name'  => get_the_title( $p ),
		'short' => has_excerpt( $p ) ? get_the_excerpt( $p ) : wp_trim_words( wp_strip_all_tags( $p->post_content ), 18 ),
		'year'  => (string) deskman_project_field( $p->ID, 'year', get_the_date( 'Y', $p ) ),
		'cats'  => $cats,
		'tags'  => array_slice( array_values( array_unique( array_merge( $cats, $tech ) ) ), 0, 3 ),
		'img'   => $img,
		'url'   => get_permalink( $p ),
		'live'  => $live,
		'host'  => $live ? preg_replace( '#^https?://(www\.)?|/$#', '', $live ) : '',
	);
}

/** Published projects (featured first), or the four shipped ones as a fallback. */
function dm_projects( $limit = 12 ) {
	$list = array();
	if ( post_type_exists( 'deskman_project' ) ) {
		$q = new WP_Query( array( 'post_type' => 'deskman_project', 'post_status' => 'publish', 'posts_per_page' => $limit, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ), 'no_found_rows' => true ) );
		$feat = array(); $rest = array();
		foreach ( $q->posts as $p ) {
			$row = dm_project( $p );
			if ( deskman_project_field( $p->ID, 'featured', '' ) ) { $feat[] = $row; } else { $rest[] = $row; }
		}
		$list = array_merge( $feat, $rest );
	}
	if ( $list ) { return $list; }
	$img = function ( $f ) { return get_theme_file_uri( 'assets/img/' . $f . '.webp' ); };
	return array(
		array( 'id' => 0, 'name' => 'Jhumkana', 'short' => 'A modern jewellery store rooted in Indian craft.', 'year' => '2025', 'cats' => array( 'E-commerce', 'Branding' ), 'tags' => array( 'WooCommerce', 'Branding', 'WordPress' ), 'img' => $img( 'jhumkana' ), 'url' => 'https://jhumkana.com/', 'live' => 'https://jhumkana.com/', 'host' => 'jhumkana.com' ),
		array( 'id' => 0, 'name' => 'VizagMap', 'short' => "Vizag's companies, startups and jobs on one map.", 'year' => '2026', 'cats' => array( 'Platform' ), 'tags' => array( 'Platform', 'Maps API', 'WordPress' ), 'img' => $img( 'vizagmap' ), 'url' => 'https://vizagmap.com/', 'live' => 'https://vizagmap.com/', 'host' => 'vizagmap.com' ),
		array( 'id' => 0, 'name' => 'The Ivy Crest School', 'short' => 'A premium site for a modern learning ecosystem.', 'year' => '2025', 'cats' => array( 'Branding', 'Web' ), 'tags' => array( 'Branding', 'WordPress' ), 'img' => $img( 'ivy-crest' ), 'url' => 'https://theivycrestschool.com/', 'live' => 'https://theivycrestschool.com/', 'host' => 'theivycrestschool.com' ),
		array( 'id' => 0, 'name' => 'PinePrompts', 'short' => 'Discover, explore and share AI prompts.', 'year' => '2025', 'cats' => array( 'Product' ), 'tags' => array( 'Product design', 'Web app' ), 'img' => $img( 'pineprompts' ), 'url' => 'https://pineprompts.com/', 'live' => 'https://pineprompts.com/', 'host' => 'pineprompts.com' ),
	);
}

/* ---------------------------------------------------------------- area pages
   The plugin's location pages; linked from the footer so they are never orphans. */
if ( ! function_exists( 'deskman_area_links' ) ) {
	function deskman_area_links( $limit = 12 ) {
		static $cache = null;
		if ( $cache === null ) {
			$cache = array();
			$cpt   = defined( 'DESKMAN_AREA_CPT' ) ? DESKMAN_AREA_CPT : 'deskman_area';
			if ( post_type_exists( $cpt ) ) {
				$q = new WP_Query( array( 'post_type' => $cpt, 'post_status' => 'publish', 'posts_per_page' => 24, 'orderby' => 'title', 'order' => 'ASC', 'no_found_rows' => true ) );
				foreach ( $q->posts as $p ) {
					$city    = trim( (string) get_post_meta( $p->ID, '_dsk_area_city', true ) );
					$cache[] = array( 'id' => (int) $p->ID, 'label' => $city !== '' ? $city : wp_strip_all_tags( get_the_title( $p ) ), 'url' => get_permalink( $p ) );
				}
			}
		}
		return array_slice( $cache, 0, (int) $limit );
	}
}
if ( ! function_exists( 'deskman_area_archive_url' ) ) {
	function deskman_area_archive_url() {
		$cpt = defined( 'DESKMAN_AREA_CPT' ) ? DESKMAN_AREA_CPT : 'deskman_area';
		if ( ! post_type_exists( $cpt ) ) { return ''; }
		$u = get_post_type_archive_link( $cpt );
		return $u ? $u : '';
	}
}

/** Body content typed into a page (block editor), shown under the designed sections. */
function dm_page_body() {
	if ( ! is_singular() ) { return; }
	$c = get_post_field( 'post_content', get_queried_object_id() );
	if ( trim( wp_strip_all_tags( $c ) ) === '' ) { return; }
	echo '<section class="sec" style="padding-top:0"><div class="wrap"><div class="prose">';
	echo apply_filters( 'the_content', $c ); // phpcs:ignore WordPress.Security.EscapeOutput
	echo '</div></div></section>';
}
