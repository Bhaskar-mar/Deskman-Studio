<?php
/**
 * Industry and template registry.
 *
 * Reads the configuration-based content in /config so new industries and
 * templates can be added without touching any rendering code.
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registry of industries and templates.
 */
class DMT_Registry {

	/**
	 * Cached industries.
	 *
	 * @var array|null
	 */
	private static $industries = null;

	/**
	 * Cached templates.
	 *
	 * @var array|null
	 */
	private static $templates = null;

	/**
	 * All registered templates, keyed by id.
	 *
	 * @return array
	 */
	public static function templates() {
		if ( null === self::$templates ) {
			$templates = apply_filters( 'dmt_templates', require DMT_DIR . 'config/templates.php' );
			$clean     = array();
			foreach ( (array) $templates as $id => $template ) {
				$id = sanitize_key( $id );
				if ( '' === $id || empty( $template['dir'] ) || ! is_readable( trailingslashit( $template['dir'] ) . 'preview.php' ) ) {
					continue;
				}
				$template['id']   = $id;
				$template['dir']  = trailingslashit( $template['dir'] );
				$clean[ $id ]     = $template;
			}
			self::$templates = $clean;
		}
		return self::$templates;
	}

	/**
	 * Single template by id.
	 *
	 * @param string $id Template id.
	 * @return array|null
	 */
	public static function template( $id ) {
		$templates = self::templates();
		return isset( $templates[ $id ] ) ? $templates[ $id ] : null;
	}

	/**
	 * All industries, keyed by slug, with a resolved 'available' flag.
	 *
	 * @return array
	 */
	public static function industries() {
		if ( null === self::$industries ) {
			$industries = apply_filters( 'dmt_industries', require DMT_DIR . 'config/industries.php' );
			$clean      = array();
			foreach ( (array) $industries as $slug => $industry ) {
				$slug = sanitize_title( $slug );
				if ( '' === $slug || empty( $industry['name'] ) ) {
					continue;
				}
				$template              = ! empty( $industry['template'] ) ? self::template( $industry['template'] ) : null;
				$industry['slug']      = $slug;
				$industry['summary']   = isset( $industry['summary'] ) ? $industry['summary'] : '';
				$industry['thumbnail'] = isset( $industry['thumbnail'] ) ? $industry['thumbnail'] : '';
				$industry['available'] = (bool) $template;
				$industry['template']  = $template ? $template['id'] : '';
				$clean[ $slug ]        = $industry;
			}
			self::$industries = $clean;
		}
		return self::$industries;
	}

	/**
	 * Single industry by slug.
	 *
	 * @param string $slug Industry slug.
	 * @return array|null
	 */
	public static function industry( $slug ) {
		$industries = self::industries();
		return isset( $industries[ $slug ] ) ? $industries[ $slug ] : null;
	}

	/**
	 * The industry that uses a given template.
	 *
	 * @param string $template_id Template id.
	 * @return array|null
	 */
	public static function industry_for_template( $template_id ) {
		foreach ( self::industries() as $industry ) {
			if ( $industry['available'] && $industry['template'] === $template_id ) {
				return $industry;
			}
		}
		return null;
	}

	/**
	 * Public URL of an image in assets/img.
	 *
	 * @param string $path Path relative to assets/img.
	 * @return string
	 */
	public static function image_url( $path ) {
		return $path ? DMT_URL . 'assets/img/' . ltrim( $path, '/' ) : '';
	}

	/**
	 * Clear caches (used by tests and after filters change).
	 */
	public static function flush() {
		self::$industries = null;
		self::$templates  = null;
	}
}
