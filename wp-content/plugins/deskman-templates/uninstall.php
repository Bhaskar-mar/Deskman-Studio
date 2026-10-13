<?php
/**
 * Uninstall: remove plugin options and capabilities.
 *
 * Customer enquiries are business records and are kept by default. To remove
 * them (and their logos) on uninstall, define DMT_REMOVE_DATA as true in
 * wp-config.php before deleting the plugin.
 *
 * @package DeskmanTemplates
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'dmt_flush_rewrites' );
delete_option( 'dmt_menu_item_id' );

$dmt_role = get_role( 'administrator' );
if ( $dmt_role ) {
	foreach ( array( 'edit_dmt_enquiries', 'edit_others_dmt_enquiries', 'edit_private_dmt_enquiries', 'edit_published_dmt_enquiries', 'read_private_dmt_enquiries', 'delete_dmt_enquiries', 'delete_others_dmt_enquiries', 'delete_private_dmt_enquiries', 'delete_published_dmt_enquiries', 'publish_dmt_enquiries' ) as $dmt_cap ) {
		$dmt_role->remove_cap( $dmt_cap );
	}
}

if ( defined( 'DMT_REMOVE_DATA' ) && DMT_REMOVE_DATA ) {
	$dmt_ids = get_posts(
		array(
			'post_type'      => 'dmt_enquiry',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	foreach ( $dmt_ids as $dmt_id ) {
		wp_delete_post( $dmt_id, true );
	}
	$dmt_uploads = wp_upload_dir( null, false );
	$dmt_dir     = trailingslashit( $dmt_uploads['basedir'] ) . 'dmt-private';
	if ( is_dir( $dmt_dir ) ) {
		foreach ( (array) glob( $dmt_dir . '/{,.}*', GLOB_BRACE ) as $dmt_file ) {
			if ( is_file( $dmt_file ) ) {
				wp_delete_file( $dmt_file );
			}
		}
		@rmdir( $dmt_dir ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
	}
	delete_option( 'dmt_page_id' );
}
