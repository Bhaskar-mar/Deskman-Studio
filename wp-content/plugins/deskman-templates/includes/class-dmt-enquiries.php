<?php
/**
 * Project enquiries: storage, REST submission endpoint, secure logo handling
 * and notifications.
 *
 * Enquiries are stored as a private, non-public custom post type that only
 * administrators can read. Uploaded logos are re-encoded and stored outside
 * the media library in a protected directory with random file names.
 *
 * Other systems (CRM, existing contact workflows) can hook into
 * `dmt_enquiry_submitted`.
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enquiry handling.
 */
class DMT_Enquiries {

	const POST_TYPE      = 'dmt_enquiry';
	const REST_NAMESPACE = 'deskman-templates/v1';
	const LOGO_MAX_BYTES = 2097152; // 2 MB.
	const LOGO_MIMES     = array(
		'png'          => 'image/png',
		'jpg|jpeg|jpe' => 'image/jpeg',
		'webp'         => 'image/webp',
	);
	const UPLOAD_SUBDIR  = 'dmt-private';
	const MIN_FILL_SECS  = 3;
	const RATE_LIMIT     = 5;
	const RATE_WINDOW    = HOUR_IN_SECONDS;
	const STATUSES       = array( 'new', 'contacted', 'in_progress', 'won', 'closed' );

	/**
	 * Hook enquiry handling.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Labels for statuses.
	 *
	 * @return array
	 */
	public static function status_labels() {
		return array(
			'new'         => __( 'New', 'deskman-templates' ),
			'contacted'   => __( 'Contacted', 'deskman-templates' ),
			'in_progress' => __( 'In progress', 'deskman-templates' ),
			'won'         => __( 'Project started', 'deskman-templates' ),
			'closed'      => __( 'Closed', 'deskman-templates' ),
		);
	}

	/**
	 * Register the private enquiry post type.
	 */
	public static function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'          => __( 'Template Enquiries', 'deskman-templates' ),
					'singular_name' => __( 'Template Enquiry', 'deskman-templates' ),
					'menu_name'     => __( 'Template Enquiries', 'deskman-templates' ),
					'all_items'     => __( 'All Enquiries', 'deskman-templates' ),
					'edit_item'     => __( 'Enquiry details', 'deskman-templates' ),
					'search_items'  => __( 'Search enquiries', 'deskman-templates' ),
					'not_found'     => __( 'No enquiries yet.', 'deskman-templates' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'show_in_rest'        => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'menu_icon'           => 'dashicons-layout',
				'menu_position'       => 26,
				'supports'            => array( 'title' ),
				'capability_type'     => array( 'dmt_enquiry', 'dmt_enquiries' ),
				'map_meta_cap'        => true,
				'capabilities'        => array(
					'create_posts' => 'do_not_allow',
				),
			)
		);
	}

	/**
	 * Primitive capabilities for the enquiry post type.
	 *
	 * @return string[]
	 */
	public static function caps() {
		return array(
			'edit_dmt_enquiries',
			'edit_others_dmt_enquiries',
			'edit_private_dmt_enquiries',
			'edit_published_dmt_enquiries',
			'read_private_dmt_enquiries',
			'delete_dmt_enquiries',
			'delete_others_dmt_enquiries',
			'delete_private_dmt_enquiries',
			'delete_published_dmt_enquiries',
			'publish_dmt_enquiries',
		);
	}

	/**
	 * Grant enquiry capabilities to administrators only.
	 */
	public static function grant_caps() {
		$role = get_role( 'administrator' );
		if ( ! $role ) {
			return;
		}
		foreach ( self::caps() as $cap ) {
			$role->add_cap( $cap );
		}
	}

	/**
	 * Private storage directory for uploaded logos.
	 *
	 * @return array{path:string,ok:bool}
	 */
	public static function upload_dir() {
		$uploads = wp_upload_dir( null, false );
		$path    = trailingslashit( $uploads['basedir'] ) . self::UPLOAD_SUBDIR;
		return array(
			'path' => $path,
			'ok'   => empty( $uploads['error'] ),
		);
	}

	/**
	 * Create the private directory and block direct web access to it.
	 *
	 * Apache honours .htaccess; for Nginx add an equivalent `deny all` rule
	 * (see README). File names are also long and random.
	 *
	 * @return string|false Directory path or false.
	 */
	public static function protect_upload_dir() {
		$dir = self::upload_dir();
		if ( ! $dir['ok'] || ! wp_mkdir_p( $dir['path'] ) ) {
			return false;
		}
		$files = array(
			'.htaccess'  => "# Deskman Templates: private customer uploads.\n<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n\tDeny from all\n</IfModule>\n",
			'index.php'  => "<?php\n// Silence is golden.\n",
			'index.html' => '',
		);
		foreach ( $files as $name => $contents ) {
			$file = trailingslashit( $dir['path'] ) . $name;
			if ( ! file_exists( $file ) ) {
				file_put_contents( $file, $contents ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			}
		}
		return $dir['path'];
	}

	/**
	 * Signed form token recording when the form was rendered.
	 *
	 * @return string
	 */
	public static function form_token() {
		$time = (string) time();
		return $time . '.' . hash_hmac( 'sha256', $time, wp_salt( 'nonce' ) . 'dmt-enquiry' );
	}

	/**
	 * Validate a form token. Tokens do not expire (pages may be cached), but
	 * must be authentic and older than a few seconds.
	 *
	 * @param string $token Token.
	 * @return bool
	 */
	public static function verify_token( $token ) {
		$parts = explode( '.', (string) $token );
		if ( 2 !== count( $parts ) || ! ctype_digit( $parts[0] ) ) {
			return false;
		}
		$expected = hash_hmac( 'sha256', $parts[0], wp_salt( 'nonce' ) . 'dmt-enquiry' );
		if ( ! hash_equals( $expected, $parts[1] ) ) {
			return false;
		}
		return ( time() - (int) $parts[0] ) >= (int) apply_filters( 'dmt_min_fill_seconds', self::MIN_FILL_SECS );
	}

	/**
	 * Register REST routes.
	 */
	public static function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/enquiries',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'handle_submission' ),
				// Public form: anyone may submit, abuse is limited by the
				// honeypot, signed timing token and per-IP rate limit.
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Validate the submitted contact details.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return array{data:array,errors:array}
	 */
	public static function validate( WP_REST_Request $request ) {
		$errors = array();
		$data   = array();

		$name = sanitize_text_field( (string) $request->get_param( 'name' ) );
		if ( mb_strlen( $name ) < 2 || mb_strlen( $name ) > 100 ) {
			$errors['name'] = __( 'Please enter your full name.', 'deskman-templates' );
		}
		$data['name'] = $name;

		$business = sanitize_text_field( (string) $request->get_param( 'business' ) );
		if ( mb_strlen( $business ) < 2 || mb_strlen( $business ) > 120 ) {
			$errors['business'] = __( 'Please enter your clinic or business name.', 'deskman-templates' );
		}
		$data['business'] = $business;

		$email = sanitize_email( (string) $request->get_param( 'email' ) );
		if ( ! $email || ! is_email( $email ) || strlen( $email ) > 100 ) {
			$errors['email'] = __( 'Please enter a valid email address.', 'deskman-templates' );
		}
		$data['email'] = $email;

		$phone = trim( (string) $request->get_param( 'phone' ) );
		$digits = preg_replace( '/\D/', '', $phone );
		if ( ! preg_match( '/^[0-9+()\-.\s]{6,25}$/', $phone ) || strlen( $digits ) < 6 || strlen( $digits ) > 18 ) {
			$errors['phone'] = __( 'Please enter a valid phone number.', 'deskman-templates' );
		}
		$data['phone'] = sanitize_text_field( $phone );

		$requirements = sanitize_textarea_field( (string) $request->get_param( 'requirements' ) );
		if ( mb_strlen( $requirements ) > 3000 ) {
			$errors['requirements'] = __( 'Please keep additional requirements under 3,000 characters.', 'deskman-templates' );
		}
		$data['requirements'] = $requirements;

		if ( '1' !== (string) $request->get_param( 'consent' ) ) {
			$errors['consent'] = __( 'Please confirm we may contact you about your project.', 'deskman-templates' );
		}

		$template_id = sanitize_key( (string) $request->get_param( 'template' ) );
		$industry    = DMT_Registry::industry_for_template( $template_id );
		if ( ! $industry ) {
			$errors['template'] = __( 'The selected template is not available.', 'deskman-templates' );
		}
		$data['template'] = $template_id;
		$data['industry'] = $industry ? $industry['slug'] : '';

		$raw_custom = json_decode( (string) $request->get_param( 'customisation' ), true );
		$data['customisation'] = $industry ? DMT_Fields::sanitize_customisation( $template_id, is_array( $raw_custom ) ? $raw_custom : array() ) : array();

		return array(
			'data'   => $data,
			'errors' => $errors,
		);
	}

	/**
	 * Handle a "Start My Project" submission.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function handle_submission( WP_REST_Request $request ) {
		// Bots: honeypot filled or token missing/too fast. Nothing is stored.
		if ( '' !== trim( (string) $request->get_param( 'website' ) ) || ! self::verify_token( (string) $request->get_param( 'token' ) ) ) {
			return new WP_Error( 'dmt_rejected', __( 'Your enquiry could not be submitted. Please reload the page and try again.', 'deskman-templates' ), array( 'status' => 400 ) );
		}

		$rate_key = 'dmt_rl_' . md5( self::client_ip() . wp_salt( 'auth' ) );
		$count    = (int) get_transient( $rate_key );
		if ( $count >= (int) apply_filters( 'dmt_rate_limit', self::RATE_LIMIT ) ) {
			return new WP_Error( 'dmt_rate_limited', __( 'Too many enquiries from this connection. Please try again later.', 'deskman-templates' ), array( 'status' => 429 ) );
		}

		$result = self::validate( $request );
		$files  = $request->get_file_params();
		$logo   = isset( $files['logo'] ) ? $files['logo'] : null;

		$logo_error = $logo ? self::check_logo( $logo ) : '';
		if ( $logo_error ) {
			$result['errors']['logo'] = $logo_error;
		}

		if ( $result['errors'] ) {
			return new WP_Error(
				'dmt_invalid',
				__( 'Please correct the highlighted fields.', 'deskman-templates' ),
				array(
					'status' => 422,
					'fields' => $result['errors'],
				)
			);
		}

		$data      = $result['data'];
		$template  = DMT_Registry::template( $data['template'] );
		$reference = self::generate_reference();

		$post_id = wp_insert_post(
			array(
				'post_type'   => self::POST_TYPE,
				'post_status' => 'private',
				/* translators: 1: business name, 2: template name. */
				'post_title'  => sprintf( __( '%1$s — %2$s', 'deskman-templates' ), $data['business'], $template['name'] ),
				'post_author' => 0,
			),
			true
		);
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return new WP_Error( 'dmt_save_failed', __( 'We could not save your enquiry. Please try again.', 'deskman-templates' ), array( 'status' => 500 ) );
		}

		$logo_file = '';
		if ( $logo ) {
			$logo_file = self::store_logo( $logo );
			if ( is_wp_error( $logo_file ) ) {
				wp_delete_post( $post_id, true );
				return new WP_Error(
					'dmt_invalid',
					__( 'Please correct the highlighted fields.', 'deskman-templates' ),
					array(
						'status' => 422,
						'fields' => array( 'logo' => $logo_file->get_error_message() ),
					)
				);
			}
		}

		$meta = array(
			'_dmt_reference'     => $reference,
			'_dmt_name'          => $data['name'],
			'_dmt_email'         => $data['email'],
			'_dmt_phone'         => $data['phone'],
			'_dmt_business'      => $data['business'],
			'_dmt_template'      => $data['template'],
			'_dmt_industry'      => $data['industry'],
			'_dmt_customisation' => $data['customisation'],
			'_dmt_requirements'  => $data['requirements'],
			'_dmt_logo'          => $logo_file,
			'_dmt_status'        => 'new',
			'_dmt_consent_at'    => gmdate( 'c' ),
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		set_transient( $rate_key, $count + 1, self::RATE_WINDOW );

		self::notify( $post_id );

		/**
		 * Fires after a template project enquiry is stored.
		 *
		 * Use this to forward enquiries to an existing CRM or contact workflow.
		 *
		 * @param int   $post_id Enquiry post id.
		 * @param array $data    Sanitised enquiry data.
		 */
		do_action( 'dmt_enquiry_submitted', $post_id, $data );

		return new WP_REST_Response(
			array(
				'success'   => true,
				'reference' => $reference,
				'message'   => sprintf(
					/* translators: %s: customer email. */
					__( 'Our team will review your template and personalisation and contact you at %s to plan the next steps.', 'deskman-templates' ),
					$data['email']
				),
			),
			201
		);
	}

	/**
	 * Unique, human-friendly reference, e.g. DMS-261013-4F7K.
	 *
	 * @return string
	 */
	private static function generate_reference() {
		$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$suffix   = '';
		for ( $i = 0; $i < 5; $i++ ) {
			$suffix .= $alphabet[ random_int( 0, strlen( $alphabet ) - 1 ) ];
		}
		return 'DMS-' . gmdate( 'ymd' ) . '-' . $suffix;
	}

	/**
	 * Best-effort client IP for rate limiting (never stored).
	 *
	 * @return string
	 */
	private static function client_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return (string) apply_filters( 'dmt_client_ip', $ip );
	}

	/**
	 * Validate an uploaded logo without moving it.
	 *
	 * @param array $file Entry from $_FILES.
	 * @return string Error message or ''.
	 */
	public static function check_logo( $file ) {
		if ( ! is_array( $file ) || ! isset( $file['error'], $file['tmp_name'], $file['size'], $file['name'] ) ) {
			return __( 'The logo upload was not valid.', 'deskman-templates' );
		}
		if ( UPLOAD_ERR_INI_SIZE === $file['error'] || UPLOAD_ERR_FORM_SIZE === $file['error'] || (int) $file['size'] > self::LOGO_MAX_BYTES ) {
			return __( 'Your logo must be smaller than 2 MB.', 'deskman-templates' );
		}
		if ( UPLOAD_ERR_OK !== $file['error'] || ! self::is_uploaded( $file['tmp_name'] ) ) {
			return __( 'The logo could not be uploaded. Please try again.', 'deskman-templates' );
		}

		$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], self::LOGO_MIMES );
		$info  = @getimagesize( $file['tmp_name'] ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- invalid images are expected input.
		if ( empty( $check['type'] ) || ! in_array( $check['type'], self::LOGO_MIMES, true ) || ! $info || ! in_array( $info['mime'], self::LOGO_MIMES, true ) ) {
			return __( 'Your logo must be a PNG, JPEG or WebP image.', 'deskman-templates' );
		}
		if ( $info[0] < 16 || $info[1] < 16 || $info[0] > 6000 || $info[1] > 6000 ) {
			return __( 'Your logo must be between 16 and 6000 pixels in each dimension.', 'deskman-templates' );
		}
		return '';
	}

	/**
	 * Whether a temp file is a genuine upload (filterable for tests).
	 *
	 * @param string $path Temp path.
	 * @return bool
	 */
	private static function is_uploaded( $path ) {
		return (bool) apply_filters( 'dmt_is_uploaded_file', is_uploaded_file( $path ), $path );
	}

	/**
	 * Re-encode and store a validated logo in the private directory.
	 *
	 * Re-encoding through the WordPress image editor strips metadata and any
	 * non-image payload.
	 *
	 * @param array $file Entry from $_FILES.
	 * @return string|WP_Error Stored file name.
	 */
	public static function store_logo( $file ) {
		$dir = self::protect_upload_dir();
		if ( ! $dir ) {
			return new WP_Error( 'dmt_logo_dir', __( 'The logo could not be saved.', 'deskman-templates' ) );
		}
		$info = getimagesize( $file['tmp_name'] );
		$ext  = strtok( (string) array_search( $info['mime'], self::LOGO_MIMES, true ), '|' );
		$name = gmdate( 'Ym' ) . '-' . wp_generate_password( 32, false, false ) . '.' . $ext;
		$dest = trailingslashit( $dir ) . $name;

		$editor = wp_get_image_editor( $file['tmp_name'] );
		if ( is_wp_error( $editor ) ) {
			return new WP_Error( 'dmt_logo_read', __( 'Your logo could not be processed. Please try a different image.', 'deskman-templates' ) );
		}
		$size = $editor->get_size();
		if ( $size['width'] > 2000 || $size['height'] > 2000 ) {
			$editor->resize( 2000, 2000, false );
		}
		$saved = $editor->save( $dest, $info['mime'] );
		if ( is_wp_error( $saved ) || empty( $saved['path'] ) ) {
			return new WP_Error( 'dmt_logo_save', __( 'Your logo could not be processed. Please try a different image.', 'deskman-templates' ) );
		}
		return wp_basename( $saved['path'] );
	}

	/**
	 * Absolute path of a stored logo, validated against the private dir.
	 *
	 * @param string $name Stored file name.
	 * @return string Path or ''.
	 */
	public static function logo_path( $name ) {
		if ( ! $name || ! preg_match( '/^[0-9]{6}-[A-Za-z0-9]{32}\.(png|jpg|webp)$/', $name ) ) {
			return '';
		}
		$dir  = self::upload_dir();
		$path = trailingslashit( $dir['path'] ) . $name;
		return is_readable( $path ) ? $path : '';
	}

	/**
	 * Email the studio (and acknowledge the customer).
	 *
	 * @param int $post_id Enquiry id.
	 */
	public static function notify( $post_id ) {
		$get      = function ( $key ) use ( $post_id ) {
			return get_post_meta( $post_id, '_dmt_' . $key, true );
		};
		$template = DMT_Registry::template( $get( 'template' ) );
		$fields   = DMT_Fields::flat( $get( 'template' ) );
		$custom   = (array) $get( 'customisation' );
		$site     = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$clean    = function ( $value ) {
			return trim( preg_replace( '/[\r\n]+/', ' ', (string) $value ) );
		};

		$lines   = array();
		$lines[] = sprintf( 'Reference: %s', $get( 'reference' ) );
		$lines[] = sprintf( 'Template: %s', $template ? $template['name'] : $get( 'template' ) );
		$lines[] = '';
		$lines[] = sprintf( 'Name: %s', $get( 'name' ) );
		$lines[] = sprintf( 'Business: %s', $get( 'business' ) );
		$lines[] = sprintf( 'Email: %s', $get( 'email' ) );
		$lines[] = sprintf( 'Phone: %s', $get( 'phone' ) );
		$lines[] = sprintf( 'Logo uploaded: %s', $get( 'logo' ) ? 'Yes (view in WordPress admin)' : 'No' );
		$lines[] = '';
		$lines[] = 'Additional requirements:';
		$lines[] = $get( 'requirements' ) ? $get( 'requirements' ) : '—';
		$lines[] = '';
		$lines[] = 'Personalisation:';
		foreach ( $custom as $key => $value ) {
			if ( isset( $fields[ $key ] ) ) {
				$lines[] = sprintf( '- %s: %s', $fields[ $key ]['label'], $clean( $value ) );
			}
		}
		$lines[] = '';
		$lines[] = 'Open in WordPress: ' . admin_url( 'post.php?post=' . (int) $post_id . '&action=edit' );

		$to      = apply_filters( 'dmt_enquiry_recipient', get_option( 'admin_email' ), $post_id );
		$subject = sprintf( '[%s] New template project enquiry: %s', $site, $clean( $get( 'business' ) ) );
		$headers = array( 'Reply-To: ' . $clean( $get( 'name' ) ) . ' <' . $get( 'email' ) . '>' );
		wp_mail( $to, $subject, implode( "\n", $lines ), $headers );

		if ( apply_filters( 'dmt_send_customer_acknowledgement', true, $post_id ) ) {
			$ack = array(
				sprintf( 'Hi %s,', $clean( $get( 'name' ) ) ),
				'',
				sprintf( 'Thank you for starting a website project with %s. We have received your enquiry for the "%s" template.', $site, $template ? $template['name'] : '' ),
				sprintf( 'Your reference is %s.', $get( 'reference' ) ),
				'',
				'Our team will review your details and personalisation and get in touch to plan the next steps. Nothing will be published without your approval.',
				'',
				$site,
				home_url( '/' ),
			);
			wp_mail( $get( 'email' ), sprintf( 'We received your website project enquiry (%s)', $get( 'reference' ) ), implode( "\n", $ack ) );
		}
	}
}
