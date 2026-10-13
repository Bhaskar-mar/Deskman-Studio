<?php
/**
 * Deskman Studio Manager — custom post types, taxonomy, project meta.
 *
 * Registers the Projects portfolio system used by the theme's Work archive and
 * single-project template, plus a private Enquiries type that stores contact
 * submissions. Everything hangs off the "deskman-studio" admin menu so it reads
 * as one studio back end rather than a scatter of default WordPress screens.
 *
 * @package Deskman_Studio_Manager
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** The meta keys a project carries. Single source of truth for the editor,
 *  the theme template and the asset checker. */
function deskman_project_fields() {
	return array(
		'number'   => array( 'label' => 'Project number',    'type' => 'text',     'hint' => 'e.g. 01 — shown large on the project page. Leave blank to auto-number.' ),
		'year'     => array( 'label' => 'Year',              'type' => 'text',     'hint' => 'e.g. 2025' ),
		'client'   => array( 'label' => 'Client',            'type' => 'text',     'hint' => 'Who the work was for.' ),
		'services' => array( 'label' => 'Services provided', 'type' => 'text',     'hint' => 'Comma separated — e.g. Branding, Web, WordPress.' ),
		'tech'     => array( 'label' => 'Technologies',      'type' => 'text',     'hint' => 'Comma separated — e.g. WordPress, PHP, GSAP.' ),
		'live_url' => array( 'label' => 'Live project URL',  'type' => 'url',      'hint' => 'The public site. Powers the “Visit live project” button.' ),
		'challenge'=> array( 'label' => 'The challenge',     'type' => 'textarea', 'hint' => 'The problem the project set out to solve.' ),
		'did'      => array( 'label' => 'What we did',       'type' => 'textarea', 'hint' => 'The work delivered.' ),
		'outcome'  => array( 'label' => 'Outcome / highlights','type'=> 'textarea', 'hint' => 'The result — measurable where possible.' ),
		'img_hero' => array( 'label' => 'Hero image',        'type' => 'image',    'hint' => 'Large visual at the top of the project page.' ),
		'img_desktop' => array( 'label' => 'Desktop screenshot', 'type' => 'image','hint' => 'Main desktop screen of the work.' ),
		'img_mobile'  => array( 'label' => 'Mobile screenshot',  'type' => 'image','hint' => 'A mobile screen of the work.' ),
		'gallery'  => array( 'label' => 'Gallery images',    'type' => 'gallery',  'hint' => 'Additional screens. Choose one or more.' ),
		'featured' => array( 'label' => 'Featured project',  'type' => 'checkbox', 'hint' => 'Featured projects can be surfaced first.' ),
	);
}

/** Read one project meta value with a safe default. Theme calls this too. */
if ( ! function_exists( 'deskman_project_field' ) ) {
	function deskman_project_field( $post_id, $key, $default = '' ) {
		$val = get_post_meta( (int) $post_id, '_deskman_' . $key, true );
		if ( $val === '' || $val === false || $val === null ) { return $default; }
		return $val;
	}
}

/** Resolve an image field (stored as an attachment ID) to a URL, or ''.
 *  Falls back to treating a stored value as a raw URL for resilience. */
if ( ! function_exists( 'deskman_project_image_url' ) ) {
	function deskman_project_image_url( $post_id, $key, $size = 'large' ) {
		$val = deskman_project_field( $post_id, $key, '' );
		if ( $val === '' ) { return ''; }
		if ( is_numeric( $val ) ) {
			$src = wp_get_attachment_image_url( (int) $val, $size );
			return $src ? $src : '';
		}
		return esc_url_raw( $val );
	}
}

/** Register the portfolio type, its category taxonomy and the enquiry store. */
add_action( 'init', 'deskman_register_types' );
function deskman_register_types() {

	register_post_type( 'deskman_project', array(
		'labels' => array(
			'name'               => __( 'Projects', 'deskman-studio-manager' ),
			'singular_name'      => __( 'Project', 'deskman-studio-manager' ),
			'add_new'            => __( 'Add Project', 'deskman-studio-manager' ),
			'add_new_item'       => __( 'Add Project', 'deskman-studio-manager' ),
			'edit_item'          => __( 'Edit Project', 'deskman-studio-manager' ),
			'new_item'           => __( 'New Project', 'deskman-studio-manager' ),
			'view_item'          => __( 'View Project', 'deskman-studio-manager' ),
			'search_items'       => __( 'Search Projects', 'deskman-studio-manager' ),
			'not_found'          => __( 'No projects yet', 'deskman-studio-manager' ),
			'menu_name'          => __( 'Projects', 'deskman-studio-manager' ),
		),
		'public'        => true,
		'has_archive'   => false,
		'show_in_menu'  => 'deskman-studio',
		'show_in_rest'  => true,
		'menu_position' => 5,
		'rewrite'       => array( 'slug' => 'work', 'with_front' => false ),
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
		'menu_icon'     => 'dashicons-portfolio',
	) );

	register_taxonomy( 'deskman_project_cat', 'deskman_project', array(
		'labels' => array(
			'name'          => __( 'Project Categories', 'deskman-studio-manager' ),
			'singular_name' => __( 'Category', 'deskman-studio-manager' ),
			'menu_name'     => __( 'Categories', 'deskman-studio-manager' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'work-category', 'with_front' => false ),
	) );

	register_post_type( 'deskman_enquiry', array(
		'labels' => array(
			'name'          => __( 'Enquiries', 'deskman-studio-manager' ),
			'singular_name' => __( 'Enquiry', 'deskman-studio-manager' ),
			'menu_name'     => __( 'Enquiries', 'deskman-studio-manager' ),
			'edit_item'     => __( 'View Enquiry', 'deskman-studio-manager' ),
			'not_found'     => __( 'No enquiries yet', 'deskman-studio-manager' ),
		),
		'public'            => false,
		'show_ui'           => true,
		'show_in_menu'      => false, // linked from the Deskman menu as "Forms".
		'capability_type'   => 'post',
		'capabilities'      => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'      => true,
		'supports'          => array( 'title' ),
		'menu_icon'         => 'dashicons-email',
	) );
}

/* --------------------------------------------------------------------------
   Project meta box
   -------------------------------------------------------------------------- */

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'deskman_project_meta',
		__( 'Project details', 'deskman-studio-manager' ),
		'deskman_project_meta_box',
		'deskman_project',
		'normal',
		'high'
	);
} );

function deskman_project_meta_box( $post ) {
	wp_nonce_field( 'deskman_project_save', 'deskman_project_nonce' );
	$fields = deskman_project_fields();
	echo '<style>
		.dsk-mb{display:grid;grid-template-columns:1fr 1fr;gap:16px}
		.dsk-mb .full{grid-column:1 / -1}
		.dsk-mb label{display:block;font-weight:600;margin-bottom:4px}
		.dsk-mb .hint{color:#666;font-size:12px;margin-top:3px}
		.dsk-mb input[type=text],.dsk-mb input[type=url],.dsk-mb textarea{width:100%}
		.dsk-mb textarea{min-height:80px}
		.dsk-img{display:flex;gap:10px;align-items:flex-start}
		.dsk-img img{max-width:120px;height:auto;border:1px solid #ddd;border-radius:6px;display:none}
		.dsk-img img[src]{display:block}
	</style>';
	echo '<div class="dsk-mb">';
	foreach ( $fields as $key => $f ) {
		$val   = get_post_meta( $post->ID, '_deskman_' . $key, true );
		$full  = in_array( $f['type'], array( 'textarea', 'gallery' ), true ) ? ' full' : '';
		$id    = 'dsk_' . $key;
		echo '<div class="fld' . esc_attr( $full ) . '">';
		echo '<label for="' . esc_attr( $id ) . '">' . esc_html( $f['label'] ) . '</label>';

		switch ( $f['type'] ) {
			case 'textarea':
				echo '<textarea id="' . esc_attr( $id ) . '" name="deskman_meta[' . esc_attr( $key ) . ']">' . esc_textarea( (string) $val ) . '</textarea>';
				break;
			case 'checkbox':
				echo '<label style="font-weight:400"><input type="checkbox" id="' . esc_attr( $id ) . '" name="deskman_meta[' . esc_attr( $key ) . ']" value="1" ' . checked( $val, '1', false ) . ' /> ' . esc_html__( 'Yes', 'deskman-studio-manager' ) . '</label>';
				break;
			case 'image':
				$src = is_numeric( $val ) ? wp_get_attachment_image_url( (int) $val, 'medium' ) : '';
				echo '<div class="dsk-img">';
				echo '<img class="dsk-img-preview" src="' . esc_url( $src ? $src : '' ) . '" alt="" />';
				echo '<div>';
				echo '<input type="hidden" class="dsk-img-id" name="deskman_meta[' . esc_attr( $key ) . ']" value="' . esc_attr( (string) $val ) . '" />';
				echo '<button type="button" class="button dsk-pick">' . esc_html__( 'Choose image', 'deskman-studio-manager' ) . '</button> ';
				echo '<button type="button" class="button-link dsk-clear">' . esc_html__( 'Remove', 'deskman-studio-manager' ) . '</button>';
				echo '</div></div>';
				break;
			case 'gallery':
				echo '<input type="text" id="' . esc_attr( $id ) . '" class="dsk-gallery-ids" name="deskman_meta[' . esc_attr( $key ) . ']" value="' . esc_attr( (string) $val ) . '" readonly /> ';
				echo '<button type="button" class="button dsk-pick-gallery">' . esc_html__( 'Choose images', 'deskman-studio-manager' ) . '</button> ';
				echo '<button type="button" class="button-link dsk-clear-gallery">' . esc_html__( 'Clear', 'deskman-studio-manager' ) . '</button>';
				break;
			case 'url':
				echo '<input type="url" id="' . esc_attr( $id ) . '" name="deskman_meta[' . esc_attr( $key ) . ']" value="' . esc_attr( (string) $val ) . '" placeholder="https://" />';
				break;
			default:
				echo '<input type="text" id="' . esc_attr( $id ) . '" name="deskman_meta[' . esc_attr( $key ) . ']" value="' . esc_attr( (string) $val ) . '" />';
		}
		echo '<p class="hint">' . esc_html( $f['hint'] ) . '</p>';
		echo '</div>';
	}
	echo '</div>';
}

/** Save project meta. */
add_action( 'save_post_deskman_project', 'deskman_project_save', 10, 2 );
function deskman_project_save( $post_id, $post ) {
	if ( ! isset( $_POST['deskman_project_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['deskman_project_nonce'] ), 'deskman_project_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	$fields = deskman_project_fields();
	$in     = isset( $_POST['deskman_meta'] ) && is_array( $_POST['deskman_meta'] ) ? wp_unslash( $_POST['deskman_meta'] ) : array();

	foreach ( $fields as $key => $f ) {
		$raw = isset( $in[ $key ] ) ? $in[ $key ] : '';
		switch ( $f['type'] ) {
			case 'checkbox':
				$clean = ( $raw === '1' ) ? '1' : '0';
				break;
			case 'textarea':
				$clean = sanitize_textarea_field( $raw );
				break;
			case 'url':
				$clean = esc_url_raw( trim( $raw ) );
				break;
			case 'image':
				$clean = is_numeric( $raw ) ? (string) absint( $raw ) : '';
				break;
			case 'gallery':
				$ids   = array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) );
				$clean = implode( ',', $ids );
				break;
			default:
				$clean = sanitize_text_field( $raw );
		}
		update_post_meta( $post_id, '_deskman_' . $key, $clean );
	}
}

/** Media picker JS for the project editor (loaded only there). */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== 'deskman_project' ) { return; }
	wp_enqueue_media();
	$js = <<<JS
jQuery(function($){
	$(document).on('click','.dsk-pick',function(e){
		e.preventDefault();
		var box=$(this).closest('.dsk-img');
		var frame=wp.media({title:'Choose image',button:{text:'Use image'},library:{type:'image'},multiple:false});
		frame.on('select',function(){
			var a=frame.state().get('selection').first().toJSON();
			box.find('.dsk-img-id').val(a.id);
			var u=(a.sizes&&a.sizes.medium)?a.sizes.medium.url:a.url;
			box.find('.dsk-img-preview').attr('src',u);
		});
		frame.open();
	});
	$(document).on('click','.dsk-clear',function(e){
		e.preventDefault();
		var box=$(this).closest('.dsk-img');
		box.find('.dsk-img-id').val('');
		box.find('.dsk-img-preview').attr('src','');
	});
	$(document).on('click','.dsk-pick-gallery',function(e){
		e.preventDefault();
		var fld=$(this).closest('.fld');
		var frame=wp.media({title:'Choose images',button:{text:'Use images'},library:{type:'image'},multiple:true});
		frame.on('select',function(){
			var ids=[];
			frame.state().get('selection').each(function(m){ ids.push(m.id); });
			fld.find('.dsk-gallery-ids').val(ids.join(','));
		});
		frame.open();
	});
	$(document).on('click','.dsk-clear-gallery',function(e){
		e.preventDefault();
		$(this).closest('.fld').find('.dsk-gallery-ids').val('');
	});
});
JS;
	wp_add_inline_script( 'jquery-core', $js );
} );

/** Show enquiry detail (read-only) on the enquiry editor via a meta box. */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'deskman_enquiry_meta', __( 'Enquiry', 'deskman-studio-manager' ), 'deskman_enquiry_meta_box', 'deskman_enquiry', 'normal', 'high' );
} );
function deskman_enquiry_meta_box( $post ) {
	$spam  = get_post_meta( $post->ID, '_deskman_spam', true ) === '1';
	$flags = (string) get_post_meta( $post->ID, '_deskman_flags', true );
	$score = get_post_meta( $post->ID, '_deskman_score', true );

	/* A quarantined message is a lead the filter chose not to email. It is
	   shown first, in full, with the exact rules it broke — the admin has to
	   be able to see at a glance whether the filter was right, and fix the
	   settings if it was not. */
	if ( $spam ) {
		echo '<div style="border-left:4px solid #d63638;background:#fcf0f1;padding:12px 14px;margin:0 0 14px">';
		echo '<strong>' . esc_html__( 'Held back as spam — not emailed.', 'deskman-studio-manager' ) . '</strong> ';
		echo esc_html__( 'If this is a real enquiry, publish it and consider raising the spam score threshold under Deskman Studio → Settings → Leads & spam filtering.', 'deskman-studio-manager' );
		echo '</div>';
	}

	$phone = get_post_meta( $post->ID, '_deskman_phone', true );
	$email = get_post_meta( $post->ID, '_deskman_email', true );

	$rows = array(
		'Email'          => $email,
		'Phone'          => $phone,
		'Company'        => get_post_meta( $post->ID, '_deskman_company', true ),
		'Current site'   => get_post_meta( $post->ID, '_deskman_website', true ),
		'Project type'   => get_post_meta( $post->ID, '_deskman_ptype', true ),
		'Budget'         => get_post_meta( $post->ID, '_deskman_budget', true ),
		'Timeline'       => get_post_meta( $post->ID, '_deskman_timeline', true ),
	);
	echo '<table class="widefat striped"><tbody>';
	foreach ( $rows as $k => $v ) {
		if ( $v === '' ) { continue; }
		$cell = esc_html( $v );
		/* Make the two things you actually act on clickable. */
		if ( $k === 'Email' && is_email( $v ) ) {
			$cell = '<a href="mailto:' . esc_attr( $v ) . '">' . esc_html( $v ) . '</a>';
		} elseif ( $k === 'Phone' ) {
			$cell = '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $v ) ) . '">' . esc_html( $v ) . '</a>';
		}
		echo '<tr><th style="width:160px">' . esc_html( $k ) . '</th><td>' . $cell . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</tbody></table>';
	echo '<h4>' . esc_html__( 'Message', 'deskman-studio-manager' ) . '</h4>';
	echo '<p style="white-space:pre-wrap">' . esc_html( $post->post_content ) . '</p>';

	/* Provenance, collapsed. Useful when triaging, noise the rest of the time. */
	$ip = get_post_meta( $post->ID, '_deskman_ip', true );
	$ua = get_post_meta( $post->ID, '_deskman_ua', true );
	if ( $flags !== '' || $ip || $ua || $score !== '' ) {
		echo '<details style="margin-top:14px"><summary style="cursor:pointer">'
			. esc_html__( 'Delivery & filter details', 'deskman-studio-manager' ) . '</summary>';
		echo '<table class="widefat striped" style="margin-top:8px"><tbody>';
		if ( $score !== '' ) {
			echo '<tr><th style="width:160px">' . esc_html__( 'Spam score', 'deskman-studio-manager' )
				. '</th><td>' . esc_html( $score ) . '</td></tr>';
		}
		if ( $flags !== '' ) {
			echo '<tr><th>' . esc_html__( 'Flags raised', 'deskman-studio-manager' )
				. '</th><td style="white-space:pre-wrap">' . esc_html( $flags ) . '</td></tr>';
		}
		if ( $ip ) { echo '<tr><th>IP</th><td>' . esc_html( $ip ) . '</td></tr>'; }
		if ( $ua ) { echo '<tr><th>User agent</th><td>' . esc_html( $ua ) . '</td></tr>'; }
		echo '</tbody></table></details>';
	}
}

/* Enquiry list columns. The default post list shows a title and a date, which
   is not enough to triage a lead — you need to see how to reach them and
   whether the filter flagged it, without opening each one. */
add_filter( 'manage_deskman_enquiry_posts_columns', function ( $cols ) {
	$out = array();
	foreach ( $cols as $k => $v ) {
		$out[ $k ] = $v;
		if ( $k === 'title' ) {
			$out['dsk_email'] = __( 'Email', 'deskman-studio-manager' );
			$out['dsk_phone'] = __( 'Phone', 'deskman-studio-manager' );
			$out['dsk_flag']  = __( 'Status', 'deskman-studio-manager' );
		}
	}
	return $out;
} );

add_action( 'manage_deskman_enquiry_posts_custom_column', function ( $col, $post_id ) {
	if ( $col === 'dsk_email' ) {
		$v = get_post_meta( $post_id, '_deskman_email', true );
		echo $v ? '<a href="mailto:' . esc_attr( $v ) . '">' . esc_html( $v ) . '</a>' : '—';
	} elseif ( $col === 'dsk_phone' ) {
		$v = get_post_meta( $post_id, '_deskman_phone', true );
		echo $v ? '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $v ) ) . '">' . esc_html( $v ) . '</a>' : '—';
	} elseif ( $col === 'dsk_flag' ) {
		if ( get_post_meta( $post_id, '_deskman_spam', true ) === '1' ) {
			echo '<span style="color:#d63638;font-weight:600">' . esc_html__( 'Held as spam', 'deskman-studio-manager' ) . '</span>';
		} else {
			echo '<span style="color:#00784a">' . esc_html__( 'Emailed', 'deskman-studio-manager' ) . '</span>';
		}
	}
}, 10, 2 );
