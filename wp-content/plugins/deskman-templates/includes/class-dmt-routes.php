<?php
/**
 * Routing for the Templates page, customiser and preview documents.
 *
 *   /templates/                       Gallery (a normal WordPress page).
 *   /templates/<industry>/            Customiser for an available template.
 *   /templates/<industry>/preview/    Standalone responsive preview document
 *                                     loaded inside the customiser's iframe.
 *
 * The base path follows the Templates page's own slug, so renaming the page
 * in WordPress keeps every route working.
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

/**
 * Rewrite rules and URL helpers.
 */
class DMT_Routes {

	const OPTION_PAGE  = 'dmt_page_id';
	const OPTION_FLUSH = 'dmt_flush_rewrites';

	/**
	 * Hook routing.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rules' ), 20 );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'save_post_page', array( __CLASS__, 'maybe_schedule_flush' ), 10, 1 );
	}

	/**
	 * ID of the Templates page (0 when missing).
	 *
	 * @return int
	 */
	public static function page_id() {
		$id = (int) get_option( self::OPTION_PAGE );
		if ( $id && 'page' === get_post_type( $id ) && 'trash' !== get_post_status( $id ) ) {
			return $id;
		}
		return 0;
	}

	/**
	 * Register query vars.
	 *
	 * @param array $vars Query vars.
	 * @return array
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'dmt_industry';
		$vars[] = 'dmt_preview';
		return $vars;
	}

	/**
	 * Add rewrite rules beneath the Templates page.
	 */
	public static function add_rules() {
		$page_id = self::page_id();
		if ( ! $page_id ) {
			return;
		}
		$base = trim( get_page_uri( $page_id ), '/' );
		if ( '' === $base ) {
			return;
		}
		$base = preg_quote( $base, '#' );

		add_rewrite_rule( '^' . $base . '/([a-z0-9-]+)/preview/?$', 'index.php?dmt_preview=$matches[1]', 'top' );
		add_rewrite_rule( '^' . $base . '/([a-z0-9-]+)/?$', 'index.php?page_id=' . $page_id . '&dmt_industry=$matches[1]', 'top' );

		if ( get_option( self::OPTION_FLUSH ) ) {
			delete_option( self::OPTION_FLUSH );
			flush_rewrite_rules( false );
		}
	}

	/**
	 * Flush rules on the next request when the Templates page slug changes.
	 *
	 * @param int $post_id Saved page id.
	 */
	public static function maybe_schedule_flush( $post_id ) {
		if ( (int) $post_id === self::page_id() ) {
			update_option( self::OPTION_FLUSH, 1, false );
		}
	}

	/**
	 * Gallery URL.
	 *
	 * @return string
	 */
	public static function gallery_url() {
		$page_id = self::page_id();
		return $page_id ? get_permalink( $page_id ) : home_url( '/' );
	}

	/**
	 * Customiser URL for an industry.
	 *
	 * @param string $slug Industry slug.
	 * @return string
	 */
	public static function industry_url( $slug ) {
		if ( get_option( 'permalink_structure' ) ) {
			return trailingslashit( self::gallery_url() ) . rawurlencode( $slug ) . '/';
		}
		return add_query_arg( 'dmt_industry', rawurlencode( $slug ), self::gallery_url() );
	}

	/**
	 * Preview document URL for an industry.
	 *
	 * @param string $slug Industry slug.
	 * @return string
	 */
	public static function preview_url( $slug ) {
		if ( get_option( 'permalink_structure' ) ) {
			return trailingslashit( self::gallery_url() ) . rawurlencode( $slug ) . '/preview/';
		}
		return add_query_arg( 'dmt_preview', rawurlencode( $slug ), home_url( '/' ) );
	}
}
