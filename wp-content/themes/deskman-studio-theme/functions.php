<?php
/**
 * Deskman Studio — theme functions (v5, SaaS design system).
 *
 * The theme renders; the "Deskman Studio Manager" plugin stores content,
 * projects, leads, location pages and the chatbot. Every template falls back
 * to built-in copy, so the site still renders with the plugin switched off.
 *
 * Files:
 *   inc/helpers.php   options, links, projects, area pages
 *   inc/content.php   services, packages, process, FAQ (defaults + overrides)
 *   inc/icons.php     inline SVG sprite + dm_i()
 *   inc/seo.php       meta, Open Graph, JSON-LD
 *   assets/css/ds.css one design system for every page
 *   assets/js/site.js nav, tabs, filters, forms, reveal (no dependencies)
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'DM_THEME_VER', '5.0.1' );

require_once get_theme_file_path( 'inc/helpers.php' );
require_once get_theme_file_path( 'inc/icons.php' );
require_once get_theme_file_path( 'inc/content.php' );
require_once get_theme_file_path( 'inc/seo.php' );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	register_nav_menus( array( 'primary' => __( 'Primary', 'deskman-studio' ), 'footer' => __( 'Footer', 'deskman-studio' ) ) );
	add_image_size( 'dm-card', 1200, 800, true );
} );

/* File-modified time as the asset version, so a changed file is never served from cache. */
function dm_ver( $rel ) {
	$p = get_theme_file_path( $rel );
	return file_exists( $p ) ? (string) filemtime( $p ) : DM_THEME_VER;
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'dm-fonts', 'https://fonts.googleapis.com/css2?family=Onest:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap', array(), null );
	wp_enqueue_style( 'dm-ds', get_theme_file_uri( 'assets/css/ds.css' ), array( 'dm-fonts' ), dm_ver( 'assets/css/ds.css' ) );
	wp_enqueue_script( 'dm-site', get_theme_file_uri( 'assets/js/site.js' ), array(), dm_ver( 'assets/js/site.js' ), true );
	/* Block styles are only needed where block content is rendered. */
	if ( is_front_page() || is_page_template( array( 'page-service.php' ) ) || is_page( array( 'services', 'work', 'pricing', 'packages', 'studio', 'about', 'contact' ) ) ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'global-styles' );
	}
}, 20 );

/* Preconnect to the font host before the stylesheet request. */
add_filter( 'wp_resource_hints', function ( $urls, $rel ) {
	if ( 'preconnect' === $rel ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}, 10, 2 );

/* Fewer bytes in <head>: emoji script and a few legacy links. */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );

/* Body classes the stylesheet keys off. */
add_filter( 'body_class', function ( $c ) {
	$c[] = 'dm';
	return $c;
} );

/* Excerpts for cards. */
add_filter( 'excerpt_length', function () { return 22; } );
add_filter( 'excerpt_more', function () { return '…'; } );

/* Archive titles without the "Category:" prefix. */
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

/* Insights: 9 per page fits the 3-column grid. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && ( $q->is_home() || $q->is_category() || $q->is_tag() ) ) {
		$q->set( 'posts_per_page', 9 );
	}
} );

/**
 * Template choices for pages created before this design existed: the plugin
 * creates "Pricing" and "Studio" pages; route any page called packages/about
 * to the same templates.
 */
add_filter( 'page_template', function ( $template ) {
	$slug = get_post_field( 'post_name', get_queried_object_id() );
	$map  = array( 'packages' => 'page-pricing.php', 'about' => 'page-studio.php' );
	if ( isset( $map[ $slug ] ) ) {
		$t = locate_template( $map[ $slug ] );
		if ( $t ) { return $t; }
	}
	return $template;
} );
