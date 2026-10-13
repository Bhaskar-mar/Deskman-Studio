<?php
/**
 * Front-end rendering: Templates gallery, customiser and preview documents.
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

/**
 * Front-end controller.
 */
class DMT_Frontend {

	/**
	 * Current view: 'gallery' | 'customiser' | ''.
	 *
	 * @var string
	 */
	private static $view = '';

	/**
	 * Industry being customised.
	 *
	 * @var array|null
	 */
	private static $industry = null;

	/**
	 * Hook front-end behaviour.
	 */
	public static function init() {
		add_shortcode( 'deskman_templates', array( __CLASS__, 'shortcode' ) );
		add_action( 'template_redirect', array( __CLASS__, 'route' ), 5 );
		add_filter( 'template_include', array( __CLASS__, 'template_include' ), 99 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'document_title_parts', array( __CLASS__, 'title' ) );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_filter( 'redirect_canonical', array( __CLASS__, 'redirect_canonical' ) );
	}

	/**
	 * Resolve which view the current request needs.
	 */
	public static function route() {
		$preview = get_query_var( 'dmt_preview' );
		if ( $preview ) {
			self::render_preview_document( sanitize_title( $preview ) );
			exit;
		}

		$page_id = DMT_Routes::page_id();
		if ( ! $page_id || ! is_page( $page_id ) ) {
			return;
		}

		$slug = sanitize_title( (string) get_query_var( 'dmt_industry' ) );
		if ( '' === $slug ) {
			self::$view = 'gallery';
			return;
		}

		$industry = DMT_Registry::industry( $slug );
		if ( ! $industry ) {
			self::not_found();
			return;
		}
		if ( ! $industry['available'] ) {
			// Coming-soon industries never open a template: return to the
			// gallery and explain that the templates are being prepared.
			wp_safe_redirect( add_query_arg( 'coming-soon', $slug, DMT_Routes::gallery_url() ) . '#industries' );
			exit;
		}

		self::$view     = 'customiser';
		self::$industry = $industry;
	}

	/**
	 * Keep WordPress from "correcting" our sub-routes back to the page URL.
	 *
	 * @param string|false $redirect Canonical redirect.
	 * @return string|false
	 */
	public static function redirect_canonical( $redirect ) {
		if ( get_query_var( 'dmt_industry' ) || get_query_var( 'dmt_preview' ) ) {
			return false;
		}
		return $redirect;
	}

	/**
	 * Send a proper 404.
	 */
	private static function not_found() {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}

	/**
	 * Render our full-width page shell for the Templates page.
	 *
	 * @param string $template Template file chosen by WordPress.
	 * @return string
	 */
	public static function template_include( $template ) {
		if ( self::$view ) {
			return DMT_DIR . 'views/page-shell.php';
		}
		return $template;
	}

	/**
	 * Current view name.
	 *
	 * @return string
	 */
	public static function view() {
		return self::$view;
	}

	/**
	 * Main content for the shell.
	 */
	public static function render_main() {
		if ( 'customiser' === self::$view && self::$industry ) {
			self::render_customiser( self::$industry );
			return;
		}
		self::render_gallery();
	}

	/**
	 * [deskman_templates] shortcode: renders the gallery anywhere.
	 *
	 * @return string
	 */
	public static function shortcode() {
		if ( 'customiser' === self::$view ) {
			// The page shell already renders the customiser.
			return '';
		}
		self::enqueue_gallery_assets();
		ob_start();
		self::render_gallery();
		return ob_get_clean();
	}

	/**
	 * Render the gallery.
	 */
	public static function render_gallery() {
		$industries  = DMT_Registry::industries();
		$coming_soon = isset( $_GET['coming-soon'] ) ? DMT_Registry::industry( sanitize_title( wp_unslash( $_GET['coming-soon'] ) ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display flag.
		$featured    = null;
		foreach ( $industries as $industry ) {
			if ( $industry['available'] ) {
				$featured = $industry;
				break;
			}
		}
		$contact_page = get_page_by_path( apply_filters( 'dmt_contact_page_path', 'contact' ) );
		$contact_url  = ( $contact_page && 'publish' === $contact_page->post_status ) ? get_permalink( $contact_page ) : '';

		include DMT_DIR . 'views/gallery.php';
	}

	/**
	 * Render the customiser for an industry.
	 *
	 * @param array $industry Industry.
	 */
	public static function render_customiser( array $industry ) {
		$template = DMT_Registry::template( $industry['template'] );
		$groups   = DMT_Fields::groups( $template['id'] );
		include DMT_DIR . 'views/customiser.php';
	}

	/**
	 * Output the standalone preview document for an industry's template.
	 *
	 * This is a complete, responsive HTML page (not a screenshot) rendered
	 * inside the customiser's iframe. It contains only demo defaults; the
	 * visitor's own details are applied in their browser.
	 *
	 * @param string $slug Industry slug.
	 */
	public static function render_preview_document( $slug ) {
		$industry = DMT_Registry::industry( $slug );
		if ( ! $industry || ! $industry['available'] ) {
			status_header( 404 );
			nocache_headers();
			header( 'Content-Type: text/html; charset=utf-8' );
			echo '<!doctype html><meta charset="utf-8"><title>' . esc_html__( 'Template not found', 'deskman-templates' ) . '</title><p>' . esc_html__( 'This template is not available yet.', 'deskman-templates' ) . '</p>';
			return;
		}

		$template = DMT_Registry::template( $industry['template'] );
		$values   = DMT_Fields::defaults( $template['id'] );

		status_header( 200 );
		header( 'Content-Type: text/html; charset=utf-8' );
		header( 'X-Robots-Tag: noindex, nofollow' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( "Content-Security-Policy: frame-ancestors 'self'" );
		header( 'Referrer-Policy: same-origin' );

		$assets = array(
			'styles'  => array(),
			'scripts' => array(),
			'preload' => array(),
		);
		foreach ( array( 'styles', 'scripts', 'preload' ) as $type ) {
			foreach ( isset( $template[ $type ] ) ? (array) $template[ $type ] : array() as $path ) {
				$url = DMT_URL . ltrim( $path, '/' );
				// Preloaded fonts must match the URL the stylesheet requests.
				$assets[ $type ][] = 'preload' === $type ? $url : add_query_arg( 'ver', DMT_VERSION, $url );
			}
		}

		$config = array(
			'template'   => $template['id'],
			'storageKey' => self::storage_key( $template['id'] ),
			'schema'     => DMT_Fields::for_js( $template['id'] ),
			'i18n'       => array(
				'previewNotice' => __( 'This is a live preview. Appointment requests will be connected when your website is built.', 'deskman-templates' ),
			),
		);

		include $template['dir'] . 'preview.php';
	}

	/**
	 * sessionStorage key for a template's customisation state.
	 *
	 * @param string $template_id Template id.
	 * @return string
	 */
	public static function storage_key( $template_id ) {
		return 'dmt:state:' . $template_id . ':v1';
	}

	/**
	 * Enqueue assets for our views.
	 */
	public static function enqueue() {
		if ( 'gallery' === self::$view ) {
			self::enqueue_gallery_assets();
		} elseif ( 'customiser' === self::$view && self::$industry ) {
			self::enqueue_customiser_assets( self::$industry );
		}
	}

	/**
	 * Gallery assets.
	 */
	public static function enqueue_gallery_assets() {
		wp_enqueue_style( 'dmt-tokens', DMT_URL . 'assets/css/tokens.css', array(), DMT_VERSION );
		wp_enqueue_style( 'dmt-gallery', DMT_URL . 'assets/css/gallery.css', array( 'dmt-tokens' ), DMT_VERSION );
		wp_enqueue_script( 'dmt-gallery', DMT_URL . 'assets/js/gallery.js', array(), DMT_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	}

	/**
	 * Customiser assets and configuration.
	 *
	 * @param array $industry Industry.
	 */
	public static function enqueue_customiser_assets( array $industry ) {
		$template = DMT_Registry::template( $industry['template'] );

		wp_enqueue_style( 'dmt-tokens', DMT_URL . 'assets/css/tokens.css', array(), DMT_VERSION );
		wp_enqueue_style( 'dmt-customiser', DMT_URL . 'assets/css/customiser.css', array( 'dmt-tokens' ), DMT_VERSION );
		wp_enqueue_script( 'dmt-customiser', DMT_URL . 'assets/js/customiser.js', array(), DMT_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );

		$privacy_url = get_privacy_policy_url();

		wp_add_inline_script(
			'dmt-customiser',
			'window.DMT_CUSTOMISER = ' . wp_json_encode(
				array(
					'template'      => $template['id'],
					'templateName'  => $template['name'],
					'industry'      => $industry['slug'],
					'industryName'  => $industry['name'],
					'storageKey'    => self::storage_key( $template['id'] ),
					'schema'        => DMT_Fields::for_js( $template['id'] ),
					'endpoint'      => esc_url_raw( rest_url( DMT_Enquiries::REST_NAMESPACE . '/enquiries' ) ),
					'logo'          => array(
						'maxBytes' => DMT_Enquiries::LOGO_MAX_BYTES,
						'types'    => array_values( DMT_Enquiries::LOGO_MIMES ),
					),
					'privacyUrl'    => $privacy_url ? esc_url_raw( $privacy_url ) : '',
					'galleryUrl'    => esc_url_raw( DMT_Routes::gallery_url() ),
					'i18n'          => self::customiser_strings(),
				)
			) . ';',
			'before'
		);
	}

	/**
	 * Translatable strings used by the customiser script.
	 *
	 * @return array
	 */
	private static function customiser_strings() {
		return array(
			'logoType'        => __( 'Please choose a PNG, JPEG or WebP image.', 'deskman-templates' ),
			'logoSize'        => __( 'That file is too large. Please choose an image under 2 MB.', 'deskman-templates' ),
			'logoInvalid'     => __( 'We couldn’t read that image. Please try a different file.', 'deskman-templates' ),
			'logoSmall'       => __( 'That image is very small. Please use a logo at least 32 pixels tall.', 'deskman-templates' ),
			'replaceLogo'     => __( 'Replace logo', 'deskman-templates' ),
			'logoAdded'       => __( 'Logo added to your website preview.', 'deskman-templates' ),
			'logoRemoved'     => __( 'Logo removed. Your clinic name is shown instead.', 'deskman-templates' ),
			'resetConfirm'    => __( 'Restore the original demo content? Your changes and logo will be removed.', 'deskman-templates' ),
			'resetDone'       => __( 'Demo content restored.', 'deskman-templates' ),
			'deviceChanged'   => __( 'Previewing at %1$s width (%2$s px).', 'deskman-templates' ),
			'scaleLabel'      => __( '%1$s px · shown at %2$s%%', 'deskman-templates' ),
			'actualSize'      => __( '%s px · actual size', 'deskman-templates' ),
			'required'        => __( 'Please fill in this field.', 'deskman-templates' ),
			'email'           => __( 'Please enter a valid email address, e.g. name@example.com.', 'deskman-templates' ),
			'phone'           => __( 'Please enter a valid phone number (digits, spaces, + ( ) - only).', 'deskman-templates' ),
			'name'            => __( 'Please enter your full name.', 'deskman-templates' ),
			'consent'         => __( 'Please confirm we may contact you about your project.', 'deskman-templates' ),
			'tooLong'         => __( 'Please shorten this to %d characters or fewer.', 'deskman-templates' ),
			'errorSummary'    => __( 'Please correct the highlighted fields.', 'deskman-templates' ),
			'sending'         => __( 'Sending…', 'deskman-templates' ),
			'network'         => __( 'We couldn’t reach our server, so your enquiry has not been sent. Please check your connection and try again.', 'deskman-templates' ),
			'serverError'     => __( 'Something went wrong and your enquiry has not been sent. Please try again in a moment.', 'deskman-templates' ),
			'rateLimited'     => __( 'You’ve sent several enquiries recently. Please wait a little while before trying again.', 'deskman-templates' ),
			'customised'      => __( '%d details personalised', 'deskman-templates' ),
			'noneCustomised'  => __( 'Demo content (not yet personalised)', 'deskman-templates' ),
			'logoIncluded'    => __( 'Logo included', 'deskman-templates' ),
			'noLogo'          => __( 'No logo uploaded', 'deskman-templates' ),
			'previewLoading'  => __( 'Loading preview…', 'deskman-templates' ),
		);
	}

	/**
	 * Document title for the customiser.
	 *
	 * @param array $parts Title parts.
	 * @return array
	 */
	public static function title( $parts ) {
		if ( 'customiser' === self::$view && self::$industry ) {
			/* translators: %s: industry name. */
			$parts['title'] = sprintf( __( '%s Website Template — Customise & Preview', 'deskman-templates' ), self::$industry['name'] );
		}
		return $parts;
	}

	/**
	 * Body classes.
	 *
	 * @param array $classes Classes.
	 * @return array
	 */
	public static function body_class( $classes ) {
		if ( self::$view ) {
			$classes[] = 'dmt-view';
			$classes[] = 'dmt-view--' . self::$view;
		}
		return $classes;
	}

	/**
	 * Inline SVG icon from assets/img/icons.
	 *
	 * @param string $name Icon name.
	 * @return string Safe SVG markup (bundled files only).
	 */
	public static function icon( $name ) {
		static $cache = array();
		$name = sanitize_key( $name );
		if ( ! isset( $cache[ $name ] ) ) {
			$file           = DMT_DIR . 'assets/img/icons/' . $name . '.svg';
			$cache[ $name ] = is_readable( $file ) ? trim( (string) file_get_contents( $file ) ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- bundled asset.
		}
		return $cache[ $name ];
	}
}
