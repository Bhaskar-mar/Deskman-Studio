<?php
/**
 * Admin screens for template enquiries.
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin UI.
 */
class DMT_Admin {

	/**
	 * Hook admin UI.
	 */
	public static function init() {
		$type = DMT_Enquiries::POST_TYPE;
		add_filter( "manage_{$type}_posts_columns", array( __CLASS__, 'columns' ) );
		add_action( "manage_{$type}_posts_custom_column", array( __CLASS__, 'column' ), 10, 2 );
		add_action( "add_meta_boxes_{$type}", array( __CLASS__, 'meta_boxes' ) );
		add_action( "save_post_{$type}", array( __CLASS__, 'save_status' ), 10, 2 );
		add_action( 'admin_post_dmt_logo', array( __CLASS__, 'serve_logo' ) );
		add_action( 'before_delete_post', array( __CLASS__, 'delete_logo' ) );
		add_filter( 'post_row_actions', array( __CLASS__, 'row_actions' ), 10, 2 );
		add_action( 'admin_head', array( __CLASS__, 'styles' ) );
		add_filter( 'display_post_states', array( __CLASS__, 'page_state' ), 10, 2 );
	}

	/**
	 * Label the Templates page in the Pages list.
	 *
	 * @param array   $states States.
	 * @param WP_Post $post   Post.
	 * @return array
	 */
	public static function page_state( $states, $post ) {
		if ( (int) $post->ID === DMT_Routes::page_id() ) {
			$states['dmt'] = __( 'Templates Page', 'deskman-templates' );
		}
		return $states;
	}

	/**
	 * List table columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function columns( $columns ) {
		return array(
			'cb'           => $columns['cb'],
			'title'        => __( 'Enquiry', 'deskman-templates' ),
			'dmt_ref'      => __( 'Reference', 'deskman-templates' ),
			'dmt_contact'  => __( 'Contact', 'deskman-templates' ),
			'dmt_template' => __( 'Template', 'deskman-templates' ),
			'dmt_status'   => __( 'Status', 'deskman-templates' ),
			'date'         => __( 'Received', 'deskman-templates' ),
		);
	}

	/**
	 * Render list table column.
	 *
	 * @param string $column  Column.
	 * @param int    $post_id Post id.
	 */
	public static function column( $column, $post_id ) {
		switch ( $column ) {
			case 'dmt_ref':
				echo '<code>' . esc_html( get_post_meta( $post_id, '_dmt_reference', true ) ) . '</code>';
				break;
			case 'dmt_contact':
				$email = get_post_meta( $post_id, '_dmt_email', true );
				echo esc_html( get_post_meta( $post_id, '_dmt_name', true ) ) . '<br>';
				echo '<a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a><br>';
				echo esc_html( get_post_meta( $post_id, '_dmt_phone', true ) );
				break;
			case 'dmt_template':
				$template = DMT_Registry::template( get_post_meta( $post_id, '_dmt_template', true ) );
				echo esc_html( $template ? $template['name'] : get_post_meta( $post_id, '_dmt_template', true ) );
				break;
			case 'dmt_status':
				$labels = DMT_Enquiries::status_labels();
				$status = get_post_meta( $post_id, '_dmt_status', true );
				echo '<span class="dmt-status dmt-status--' . esc_attr( $status ) . '">' . esc_html( isset( $labels[ $status ] ) ? $labels[ $status ] : $status ) . '</span>';
				break;
		}
	}

	/**
	 * Row actions: no quick edit for enquiries.
	 *
	 * @param array   $actions Actions.
	 * @param WP_Post $post    Post.
	 * @return array
	 */
	public static function row_actions( $actions, $post ) {
		if ( DMT_Enquiries::POST_TYPE === $post->post_type ) {
			unset( $actions['inline hide-if-no-js'] );
			if ( isset( $actions['edit'] ) ) {
				$actions['edit'] = '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . esc_html__( 'View details', 'deskman-templates' ) . '</a>';
			}
		}
		return $actions;
	}

	/**
	 * Meta boxes.
	 */
	public static function meta_boxes() {
		remove_meta_box( 'submitdiv', DMT_Enquiries::POST_TYPE, 'side' );
		add_meta_box( 'dmt_details', __( 'Enquiry details', 'deskman-templates' ), array( __CLASS__, 'details_box' ), DMT_Enquiries::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'dmt_status', __( 'Status', 'deskman-templates' ), array( __CLASS__, 'status_box' ), DMT_Enquiries::POST_TYPE, 'side', 'high' );
	}

	/**
	 * Details meta box (read-only).
	 *
	 * @param WP_Post $post Post.
	 */
	public static function details_box( $post ) {
		$get      = function ( $key ) use ( $post ) {
			return get_post_meta( $post->ID, '_dmt_' . $key, true );
		};
		$template = DMT_Registry::template( $get( 'template' ) );
		$fields   = $template ? DMT_Fields::flat( $template['id'] ) : array();
		$custom   = (array) $get( 'customisation' );
		$defaults = $template ? DMT_Fields::defaults( $template['id'] ) : array();
		$logo     = DMT_Enquiries::logo_path( $get( 'logo' ) ) ? wp_nonce_url( admin_url( 'admin-post.php?action=dmt_logo&enquiry=' . (int) $post->ID ), 'dmt_logo_' . $post->ID ) : '';
		?>
		<table class="widefat striped dmt-admin-table">
			<tbody>
				<tr><th><?php esc_html_e( 'Reference', 'deskman-templates' ); ?></th><td><code><?php echo esc_html( $get( 'reference' ) ); ?></code></td></tr>
				<tr><th><?php esc_html_e( 'Name', 'deskman-templates' ); ?></th><td><?php echo esc_html( $get( 'name' ) ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Business', 'deskman-templates' ); ?></th><td><?php echo esc_html( $get( 'business' ) ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Email', 'deskman-templates' ); ?></th><td><a href="<?php echo esc_url( 'mailto:' . $get( 'email' ) ); ?>"><?php echo esc_html( $get( 'email' ) ); ?></a></td></tr>
				<tr><th><?php esc_html_e( 'Phone', 'deskman-templates' ); ?></th><td><?php echo esc_html( $get( 'phone' ) ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Template', 'deskman-templates' ); ?></th><td><?php echo esc_html( $template ? $template['name'] : $get( 'template' ) ); ?></td></tr>
				<tr><th><?php esc_html_e( 'Additional requirements', 'deskman-templates' ); ?></th><td><?php echo $get( 'requirements' ) ? nl2br( esc_html( $get( 'requirements' ) ) ) : '&mdash;'; ?></td></tr>
				<tr><th><?php esc_html_e( 'Logo', 'deskman-templates' ); ?></th><td>
					<?php if ( $logo ) : ?>
						<a href="<?php echo esc_url( $logo ); ?>" target="_blank" rel="noopener"><img class="dmt-admin-logo" src="<?php echo esc_url( $logo ); ?>" alt="<?php esc_attr_e( 'Uploaded logo', 'deskman-templates' ); ?>"></a>
					<?php else : ?>
						<?php esc_html_e( 'No logo uploaded', 'deskman-templates' ); ?>
					<?php endif; ?>
				</td></tr>
				<tr><th><?php esc_html_e( 'Consent given', 'deskman-templates' ); ?></th><td><?php echo esc_html( $get( 'consent_at' ) ); ?></td></tr>
			</tbody>
		</table>

		<h3><?php esc_html_e( 'Personalisation', 'deskman-templates' ); ?></h3>
		<table class="widefat striped dmt-admin-table">
			<thead><tr><th><?php esc_html_e( 'Field', 'deskman-templates' ); ?></th><th><?php esc_html_e( 'Value', 'deskman-templates' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( $custom as $key => $value ) : ?>
				<?php
				if ( ! isset( $fields[ $key ] ) ) {
					continue;
				}
				$changed = isset( $defaults[ $key ] ) && (string) $defaults[ $key ] !== (string) $value;
				?>
				<tr>
					<th>
						<?php echo esc_html( $fields[ $key ]['label'] ); ?>
						<?php if ( ! $changed ) : ?>
							<span class="dmt-admin-default"><?php esc_html_e( '(demo default)', 'deskman-templates' ); ?></span>
						<?php endif; ?>
					</th>
					<td>
						<?php if ( 'color' === $fields[ $key ]['type'] ) : ?>
							<span class="dmt-admin-swatch" style="background:<?php echo esc_attr( $value ); ?>"></span>
						<?php endif; ?>
						<?php echo nl2br( esc_html( $value ) ); ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Status meta box.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function status_box( $post ) {
		$status = get_post_meta( $post->ID, '_dmt_status', true );
		wp_nonce_field( 'dmt_status_' . $post->ID, 'dmt_status_nonce' );
		?>
		<p>
			<label for="dmt-status-select" class="screen-reader-text"><?php esc_html_e( 'Status', 'deskman-templates' ); ?></label>
			<select id="dmt-status-select" name="dmt_status" class="widefat">
				<?php foreach ( DMT_Enquiries::status_labels() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $status, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<?php submit_button( __( 'Update status', 'deskman-templates' ), 'primary', 'dmt_save', false ); ?>
		</p>
		<p class="description">
			<?php
			/* translators: %s: date. */
			echo esc_html( sprintf( __( 'Received %s', 'deskman-templates' ), get_the_date( '', $post ) . ' ' . get_the_time( '', $post ) ) );
			?>
		</p>
		<?php if ( current_user_can( 'delete_post', $post->ID ) ) : ?>
			<p><a class="submitdelete" href="<?php echo esc_url( get_delete_post_link( $post->ID, '', true ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Permanently delete this enquiry and its logo?', 'deskman-templates' ) ); ?>');"><?php esc_html_e( 'Delete permanently', 'deskman-templates' ); ?></a></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Save the status (the only editable value).
	 *
	 * @param int     $post_id Post id.
	 * @param WP_Post $post    Post.
	 */
	public static function save_status( $post_id, $post ) {
		if ( ! isset( $_POST['dmt_status_nonce'], $_POST['dmt_status'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dmt_status_nonce'] ) ), 'dmt_status_' . $post_id ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$status = sanitize_key( wp_unslash( $_POST['dmt_status'] ) );
		if ( in_array( $status, DMT_Enquiries::STATUSES, true ) ) {
			update_post_meta( $post_id, '_dmt_status', $status );
		}
		// Keep enquiries private regardless of what the edit form posted.
		if ( 'private' !== $post->post_status ) {
			remove_action( 'save_post_' . DMT_Enquiries::POST_TYPE, array( __CLASS__, 'save_status' ), 10 );
			wp_update_post(
				array(
					'ID'          => $post_id,
					'post_status' => 'private',
				)
			);
		}
	}

	/**
	 * Stream an enquiry's logo to an authorised admin.
	 */
	public static function serve_logo() {
		$post_id = isset( $_GET['enquiry'] ) ? absint( $_GET['enquiry'] ) : 0;
		check_admin_referer( 'dmt_logo_' . $post_id );
		if ( ! $post_id || DMT_Enquiries::POST_TYPE !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'You are not allowed to view this file.', 'deskman-templates' ), 403 );
		}
		$path = DMT_Enquiries::logo_path( get_post_meta( $post_id, '_dmt_logo', true ) );
		if ( ! $path ) {
			wp_die( esc_html__( 'Logo not found.', 'deskman-templates' ), 404 );
		}
		$info = getimagesize( $path );
		nocache_headers();
		header( 'Content-Type: ' . ( $info ? $info['mime'] : 'application/octet-stream' ) );
		header( 'Content-Length: ' . filesize( $path ) );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Content-Disposition: inline; filename="logo-' . $post_id . '.' . pathinfo( $path, PATHINFO_EXTENSION ) . '"' );
		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		exit;
	}

	/**
	 * Remove a stored logo when its enquiry is permanently deleted.
	 *
	 * @param int $post_id Post id.
	 */
	public static function delete_logo( $post_id ) {
		if ( DMT_Enquiries::POST_TYPE !== get_post_type( $post_id ) ) {
			return;
		}
		$path = DMT_Enquiries::logo_path( get_post_meta( $post_id, '_dmt_logo', true ) );
		if ( $path ) {
			wp_delete_file( $path );
		}
	}

	/**
	 * Small admin styles.
	 */
	public static function styles() {
		$screen = get_current_screen();
		if ( ! $screen || DMT_Enquiries::POST_TYPE !== $screen->post_type ) {
			return;
		}
		?>
		<style>
			.dmt-admin-table th { width: 30%; font-weight: 600; }
			.dmt-admin-default { color: #8c8f94; font-weight: 400; font-size: 12px; }
			.dmt-admin-swatch { display: inline-block; width: 14px; height: 14px; border-radius: 4px; vertical-align: -2px; margin-right: 6px; box-shadow: inset 0 0 0 1px rgba(0,0,0,.15); }
			.dmt-admin-logo { max-width: 240px; max-height: 120px; background: repeating-conic-gradient(#f0f0f1 0 25%, #fff 0 50%) 0 0 / 16px 16px; padding: 8px; border: 1px solid #dcdcde; border-radius: 6px; }
			.dmt-status { display: inline-block; padding: 2px 8px; border-radius: 999px; background: #f0f0f1; font-size: 12px; }
			.dmt-status--new { background: #e7f0ff; color: #1d4ed8; }
			.dmt-status--won { background: #e6f7ef; color: #067647; }
			#post-body-content #titlediv #title { pointer-events: none; background: #f6f7f7; }
		</style>
		<?php
	}
}
