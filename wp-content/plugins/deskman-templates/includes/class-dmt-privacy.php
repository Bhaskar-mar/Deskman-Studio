<?php
/**
 * Personal data export / erasure (Tools → Export/Erase Personal Data) and
 * suggested privacy policy text.
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

/**
 * Privacy integration.
 */
class DMT_Privacy {

	/**
	 * Hook privacy tools.
	 */
	public static function init() {
		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_eraser' ) );
		add_action( 'admin_init', array( __CLASS__, 'policy_text' ) );
	}

	/**
	 * Enquiries for an email address.
	 *
	 * @param string $email Email.
	 * @param int    $page  Page.
	 * @return WP_Post[]
	 */
	private static function find( $email, $page ) {
		return get_posts(
			array(
				'post_type'      => DMT_Enquiries::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 50,
				'paged'          => max( 1, (int) $page ),
				'meta_key'       => '_dmt_email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $email, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
	}

	/**
	 * Register exporter.
	 *
	 * @param array $exporters Exporters.
	 * @return array
	 */
	public static function register_exporter( $exporters ) {
		$exporters['deskman-templates'] = array(
			'exporter_friendly_name' => __( 'Deskman Studio template enquiries', 'deskman-templates' ),
			'callback'               => array( __CLASS__, 'export' ),
		);
		return $exporters;
	}

	/**
	 * Register eraser.
	 *
	 * @param array $erasers Erasers.
	 * @return array
	 */
	public static function register_eraser( $erasers ) {
		$erasers['deskman-templates'] = array(
			'eraser_friendly_name' => __( 'Deskman Studio template enquiries', 'deskman-templates' ),
			'callback'             => array( __CLASS__, 'erase' ),
		);
		return $erasers;
	}

	/**
	 * Export enquiries for an email.
	 *
	 * @param string $email Email.
	 * @param int    $page  Page.
	 * @return array
	 */
	public static function export( $email, $page = 1 ) {
		$items = array();
		$posts = self::find( $email, $page );
		foreach ( $posts as $post ) {
			$data = array();
			foreach ( array( 'reference', 'name', 'business', 'email', 'phone', 'template', 'requirements', 'consent_at' ) as $key ) {
				$data[] = array(
					'name'  => ucfirst( str_replace( '_', ' ', $key ) ),
					'value' => (string) get_post_meta( $post->ID, '_dmt_' . $key, true ),
				);
			}
			foreach ( (array) get_post_meta( $post->ID, '_dmt_customisation', true ) as $key => $value ) {
				$data[] = array(
					'name'  => $key,
					'value' => (string) $value,
				);
			}
			$items[] = array(
				'group_id'    => 'dmt-enquiries',
				'group_label' => __( 'Website template enquiries', 'deskman-templates' ),
				'item_id'     => 'dmt-enquiry-' . $post->ID,
				'data'        => $data,
			);
		}
		return array(
			'data' => $items,
			'done' => count( $posts ) < 50,
		);
	}

	/**
	 * Erase enquiries for an email.
	 *
	 * @param string $email Email.
	 * @param int    $page  Page (always 1: posts are deleted as we go).
	 * @return array
	 */
	public static function erase( $email, $page = 1 ) {
		unset( $page );
		$posts   = self::find( $email, 1 );
		$removed = false;
		foreach ( $posts as $post ) {
			$removed = (bool) wp_delete_post( $post->ID, true ) || $removed;
		}
		return array(
			'items_removed'  => $removed,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => count( $posts ) < 50,
		);
	}

	/**
	 * Suggested privacy policy content.
	 */
	public static function policy_text() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}
		wp_add_privacy_policy_content(
			__( 'Deskman Studio Templates', 'deskman-templates' ),
			wp_kses_post(
				'<p>' . __( 'When you personalise a website template, the details you enter (including any logo) stay in your browser and are not sent to us. If you choose “Start My Project”, we store your name, email address, phone number, business name, selected template, personalisation choices, uploaded logo and any additional requirements so we can respond to your enquiry. This information is only accessible to our administrators and is kept until your project enquiry is resolved or you ask us to delete it.', 'deskman-templates' ) . '</p>'
			)
		);
	}
}
