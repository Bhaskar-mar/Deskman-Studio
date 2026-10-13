<?php
/**
 * Plugin bootstrap, activation and deactivation.
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class.
 */
class DMT_Plugin {

	const OPTION_MENU_ITEM = 'dmt_menu_item_id';

	/**
	 * Wire up all components.
	 */
	public static function boot() {
		add_action( 'init', array( __CLASS__, 'load_textdomain' ) );
		DMT_Routes::init();
		DMT_Frontend::init();
		DMT_Enquiries::init();
		DMT_Admin::init();
		DMT_Privacy::init();
	}

	/**
	 * Load translations.
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'deskman-templates', false, dirname( plugin_basename( DMT_FILE ) ) . '/languages' );
	}

	/**
	 * Activation: create the Templates page, grant capabilities, register
	 * routes and add the page to the primary navigation menu.
	 */
	public static function activate() {
		$page_id = self::ensure_page();

		DMT_Enquiries::register_post_type();
		DMT_Enquiries::grant_caps();
		DMT_Enquiries::protect_upload_dir();

		DMT_Routes::add_rules();
		flush_rewrite_rules( false );

		if ( $page_id ) {
			self::add_to_menu( $page_id );
		}
	}

	/**
	 * Deactivation: remove our rewrite rules.
	 */
	public static function deactivate() {
		flush_rewrite_rules( false );
	}

	/**
	 * Create (or adopt) the Templates page.
	 *
	 * @return int Page id.
	 */
	public static function ensure_page() {
		$page_id = DMT_Routes::page_id();
		if ( $page_id ) {
			return $page_id;
		}

		$existing = get_page_by_path( 'templates' );
		if ( $existing && 'trash' !== $existing->post_status ) {
			$page_id = (int) $existing->ID;
		} else {
			$page_id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => __( 'Templates', 'deskman-templates' ),
					'post_name'    => 'templates',
					'post_content' => '<!-- wp:shortcode -->[deskman_templates]<!-- /wp:shortcode -->',
					'post_excerpt' => __( 'Industry-specific website templates you can personalise and preview live, then launch with Deskman Studio.', 'deskman-templates' ),
				),
				true
			);
			if ( is_wp_error( $page_id ) ) {
				return 0;
			}
		}

		update_option( DMT_Routes::OPTION_PAGE, (int) $page_id );
		return (int) $page_id;
	}

	/**
	 * Add the Templates page to the menu assigned to the theme's main
	 * navigation location (once). Sites using a block theme Navigation block
	 * can add the page from the Site Editor; page-list navigation picks it up
	 * automatically.
	 *
	 * @param int $page_id Templates page id.
	 */
	public static function add_to_menu( $page_id ) {
		if ( get_option( self::OPTION_MENU_ITEM ) ) {
			return;
		}

		$locations = get_nav_menu_locations();
		if ( empty( $locations ) ) {
			return;
		}

		$preferred = apply_filters( 'dmt_menu_locations', array( 'primary', 'main', 'menu-1', 'header', 'main-menu', 'primary-menu' ) );
		$menu_id   = 0;
		foreach ( $preferred as $location ) {
			if ( ! empty( $locations[ $location ] ) ) {
				$menu_id = (int) $locations[ $location ];
				break;
			}
		}
		if ( ! $menu_id ) {
			return;
		}

		foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
			if ( 'post_type' === $item->type && (int) $item->object_id === (int) $page_id ) {
				update_option( self::OPTION_MENU_ITEM, (int) $item->ID, false );
				return;
			}
		}

		$item_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => __( 'Templates', 'deskman-templates' ),
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
		if ( ! is_wp_error( $item_id ) ) {
			update_option( self::OPTION_MENU_ITEM, (int) $item_id, false );
		}
	}
}
