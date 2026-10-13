<?php
/**
 * Deskman Studio Manager — admin experience.
 *
 * Builds the single "Deskman" menu the studio works from: Dashboard, Projects,
 * Services, Testimonials, Forms (enquiries), Settings, Setup and an Asset
 * checker. Projects and Enquiries are real screens (their CPTs); Services and
 * Testimonials deep-link into the existing Settings sections so there is one
 * content system, not two.
 *
 * @package Deskman_Studio_Manager
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** The whole Deskman admin menu, in the order the brief asks for. */
add_action( 'admin_menu', 'deskman_admin_menu', 9 );
function deskman_admin_menu() {

	add_menu_page(
		'Deskman Studio',
		'Deskman',
		'manage_options',
		'deskman-studio',
		'deskman_dashboard_page',
		'dashicons-location-alt',
		58
	);

	add_submenu_page( 'deskman-studio', 'Dashboard', 'Dashboard', 'manage_options', 'deskman-studio', 'deskman_dashboard_page' );

	// Projects + Add Project come from the CPT (show_in_menu => deskman-studio).

	// Services & Testimonials → the matching Settings sections (one content system).
	$settings = admin_url( 'admin.php?page=deskman-settings' );
	add_submenu_page( 'deskman-studio', 'Services', 'Services', 'manage_options', $settings . '#dsk-sec-services' );
	add_submenu_page( 'deskman-studio', 'Testimonials', 'Testimonials', 'manage_options', $settings . '#dsk-sec-testimonials' );

	// Pages & Media → core WordPress screens, surfaced here for convenience.
	add_submenu_page( 'deskman-studio', 'Pages', 'Pages', 'manage_options', 'edit.php?post_type=page' );
	add_submenu_page( 'deskman-studio', 'Media', 'Media', 'upload_files', 'upload.php' );

	// Forms → the enquiry store.
	add_submenu_page( 'deskman-studio', 'Forms', 'Forms', 'manage_options', 'edit.php?post_type=deskman_enquiry' );

	// Asset checker.
	add_submenu_page( 'deskman-studio', 'Project assets', 'Asset checker', 'manage_options', 'deskman-assets', 'deskman_assets_page' );

	// Settings (the existing schema form).
	add_submenu_page( 'deskman-studio', 'Settings', 'Settings', 'manage_options', 'deskman-settings', 'deskman_settings_page' );

	// Setup wizard.
	add_submenu_page( 'deskman-studio', 'Setup', 'Setup', 'manage_options', 'deskman-setup', 'deskman_setup_page' );
}

/** Small shared header for our custom screens. */
function deskman_admin_head( $title, $sub = '' ) {
	echo '<div class="wrap">';
	echo '<h1><span class="dashicons dashicons-location-alt" style="font-size:26px;width:26px;height:26px;vertical-align:-3px;color:#6840FF"></span> ' . esc_html( $title ) . '</h1>';
	if ( $sub ) { echo '<p style="max-width:760px;color:#555">' . esc_html( $sub ) . '</p>'; }
}

/* --------------------------------------------------------------------------
   Dashboard
   -------------------------------------------------------------------------- */
function deskman_dashboard_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$state    = get_option( DESKMAN_SETUP_OPTION );
	$done     = is_array( $state ) && ! empty( $state['completed'] );
	$projects = wp_count_posts( 'deskman_project' );
	$enq      = wp_count_posts( 'deskman_enquiry' );
	$pub      = isset( $projects->publish ) ? (int) $projects->publish : 0;
	$enq_n    = isset( $enq->publish ) ? (int) $enq->publish : 0;

	/* Area pages are counted as published + drafts, because the generator
	   creates drafts on purpose — a dashboard showing "0" while eight drafts
	   sit waiting to be read would be actively misleading. */
	$areas    = post_type_exists( 'deskman_area' ) ? wp_count_posts( 'deskman_area' ) : null;
	$area_pub = ( $areas && isset( $areas->publish ) ) ? (int) $areas->publish : 0;
	$area_dft = ( $areas && isset( $areas->draft ) ) ? (int) $areas->draft : 0;
	$leads    = post_type_exists( 'deskman_cb_lead' ) ? wp_count_posts( 'deskman_cb_lead' ) : null;
	$lead_n   = ( $leads && isset( $leads->publish ) ) ? (int) $leads->publish : 0;

	deskman_admin_head( 'Deskman Studio', 'Your studio at a glance.' );

	if ( ! $done ) {
		echo '<div class="notice notice-warning" style="padding:14px 16px"><p style="margin:0 0 10px"><strong>Deskman Studio is ready to be configured.</strong> Run setup to create the pages, navigation, projects and homepage.</p>';
		echo '<a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=deskman-setup' ) ) . '">Start Setup &rarr;</a></div>';
	}

	echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:16px;margin-top:20px">';
	$cards = array(
		array( 'Projects', $pub, admin_url( 'edit.php?post_type=deskman_project' ), '' ),
		array( 'Enquiries', $enq_n, admin_url( 'edit.php?post_type=deskman_enquiry' ), '' ),
		array( 'Area pages', $area_pub, admin_url( 'edit.php?post_type=deskman_area' ), $area_dft ? $area_dft . ' draft' . ( $area_dft === 1 ? '' : 's' ) . ' waiting' : '' ),
		array( 'Chat leads', $lead_n, admin_url( 'edit.php?post_type=deskman_cb_lead' ), '' ),
		array( 'Setup', $done ? 'Complete' : 'Pending', admin_url( 'admin.php?page=deskman-setup' ), '' ),
	);
	foreach ( $cards as $c ) {
		echo '<a href="' . esc_url( $c[2] ) . '" style="text-decoration:none;color:inherit"><div style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:18px">';
		echo '<div style="font-size:28px;font-weight:700;color:#201d1d">' . esc_html( (string) $c[1] ) . '</div>';
		echo '<div style="color:#666;margin-top:4px">' . esc_html( $c[0] ) . '</div>';
		if ( ! empty( $c[3] ) ) {
			echo '<div style="color:#b26200;margin-top:4px;font-size:12px;font-weight:600">' . esc_html( $c[3] ) . '</div>';
		}
		echo '</div></a>';
	}
	echo '</div>';

	echo '<h2 style="margin-top:28px">Quick links</h2>';
	echo '<p>';
	echo '<a class="button" href="' . esc_url( home_url( '/' ) ) . '" target="_blank">View website &rarr;</a> ';
	echo '<a class="button button-primary" href="' . esc_url( admin_url( 'admin.php?page=deskman-areas' ) ) . '">Generate an area page</a> ';
	echo '<a class="button" href="' . esc_url( admin_url( 'post-new.php?post_type=deskman_project' ) ) . '">Add project</a> ';
	echo '<a class="button" href="' . esc_url( admin_url( 'admin.php?page=deskman-settings' ) ) . '">Customize content</a> ';
	echo '<a class="button" href="' . esc_url( admin_url( 'admin.php?page=deskman-chatbot' ) ) . '">Chatbot</a> ';
	echo '<a class="button" href="' . esc_url( admin_url( 'admin.php?page=deskman-assets' ) ) . '">Check project assets</a>';
	echo '</p>';
	echo '</div>';
}

/* --------------------------------------------------------------------------
   Setup wizard
   -------------------------------------------------------------------------- */
function deskman_setup_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }

	$log = array();
	$did = false;
	$reset = 0;

	if ( isset( $_POST['deskman_setup_submit'] ) && check_admin_referer( 'deskman_setup', 'deskman_setup_nonce' ) ) {
		$steps = array(
			'pages'    => ! empty( $_POST['step_pages'] ),
			'projects' => ! empty( $_POST['step_projects'] ),
			'menus'    => ! empty( $_POST['step_menus'] ),
			'homepage' => ! empty( $_POST['step_homepage'] ),
			'demo'     => ! empty( $_POST['step_demo'] ),
		);
		$log = deskman_setup_run( $steps );
		$did = true;
	}

	if ( isset( $_POST['deskman_reset_submit'] ) && check_admin_referer( 'deskman_reset', 'deskman_reset_nonce' ) ) {
		$reset = deskman_setup_reset_demo();
	}

	$state = get_option( DESKMAN_SETUP_OPTION );
	$done  = is_array( $state ) && ! empty( $state['completed'] );

	deskman_admin_head( 'Setup', 'Let\'s set up your studio website. This is safe to run more than once — nothing is duplicated.' );

	if ( $did ) {
		echo '<div class="notice notice-success" style="padding:14px 16px"><p style="margin:0 0 8px"><strong>Your Deskman Studio website is ready.</strong></p>';
		echo '<p style="margin:0 0 10px">';
		echo '<a class="button button-primary" href="' . esc_url( home_url( '/' ) ) . '" target="_blank">View Website &rarr;</a> ';
		echo '<a class="button" href="' . esc_url( admin_url( 'admin.php?page=deskman-settings' ) ) . '">Customize &rarr;</a> ';
		echo '<a class="button" href="' . esc_url( admin_url( 'edit.php?post_type=deskman_project' ) ) . '">Manage Projects &rarr;</a>';
		echo '</p>';
		if ( $log ) {
			echo '<details style="margin-top:6px"><summary>What happened</summary><ul style="margin:8px 0 0 18px;list-style:disc">';
			foreach ( $log as $line ) { echo '<li>' . esc_html( $line ) . '</li>'; }
			echo '</ul></details>';
		}
		echo '</div>';
	}
	if ( $reset ) {
		echo '<div class="notice notice-info"><p>Removed ' . esc_html( (string) $reset ) . ' demo project(s). Your own projects were left untouched.</p></div>';
	}

	// Setup form.
	echo '<form method="post" style="background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:20px;max-width:720px;margin-top:10px">';
	wp_nonce_field( 'deskman_setup', 'deskman_setup_nonce' );
	echo '<h2 style="margin-top:0">Choose what to set up</h2>';
	$boxes = array(
		'step_pages'    => 'Create required pages (Home, Work, Services, Studio, Contact, Privacy, Terms)',
		'step_projects' => 'Install portfolio structure (project categories)',
		'step_demo'     => 'Add the four Deskman starter projects (demo content)',
		'step_homepage' => 'Set the static homepage',
		'step_menus'    => 'Create navigation menus',
	);
	foreach ( $boxes as $name => $label ) {
		echo '<p style="margin:.4em 0"><label><input type="checkbox" name="' . esc_attr( $name ) . '" value="1" checked /> ' . esc_html( $label ) . '</label></p>';
	}
	echo '<p style="margin:.4em 0;color:#777"><label><input type="checkbox" disabled /> Import optional demo images — none are bundled. Add real screenshots via the Asset checker after setup.</label></p>';
	echo '<p style="margin-top:16px"><button class="button button-primary" name="deskman_setup_submit" value="1">' . ( $done ? 'Run setup again' : 'Complete Setup' ) . '</button></p>';
	echo '</form>';

	// Reset demo (safe).
	echo '<form method="post" style="margin-top:18px;max-width:720px" onsubmit="return confirm(\'Remove only the demo projects this plugin created? Your own projects and all pages stay.\');">';
	wp_nonce_field( 'deskman_reset', 'deskman_reset_nonce' );
	echo '<h2>Remove demo content</h2>';
	echo '<p style="color:#555">Trashes only the four starter projects the plugin created. It never deletes your pages, your own projects, media or any other content.</p>';
	echo '<p><button class="button" name="deskman_reset_submit" value="1">Remove demo projects</button></p>';
	echo '</form>';
	echo '</div>';
}

/* --------------------------------------------------------------------------
   Asset checker
   -------------------------------------------------------------------------- */
function deskman_assets_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	deskman_admin_head( 'Project assets', 'Which portfolio images are still missing. Upload the real screenshots — the plugin never invents them.' );

	$projects = get_posts( array(
		'post_type'   => 'deskman_project',
		'post_status' => 'any',
		'numberposts' => -1,
		'orderby'     => 'menu_order title',
		'order'       => 'ASC',
	) );

	if ( ! $projects ) {
		echo '<p>No projects yet. <a href="' . esc_url( admin_url( 'post-new.php?post_type=deskman_project' ) ) . '">Add your first project</a> or run <a href="' . esc_url( admin_url( 'admin.php?page=deskman-setup' ) ) . '">Setup</a>.</p></div>';
		return;
	}

	$checks = array(
		'_thumbnail'   => 'Featured image',
		'_deskman_img_hero'    => 'Hero image',
		'_deskman_img_desktop' => 'Desktop screenshot',
		'_deskman_img_mobile'  => 'Mobile screenshot',
	);

	echo '<table class="widefat striped" style="max-width:900px;margin-top:10px"><thead><tr><th>Project</th>';
	foreach ( $checks as $label ) { echo '<th>' . esc_html( $label ) . '</th>'; }
	echo '<th></th></tr></thead><tbody>';

	foreach ( $projects as $p ) {
		echo '<tr><td><strong>' . esc_html( get_the_title( $p ) ) . '</strong></td>';
		foreach ( $checks as $key => $label ) {
			$has = false;
			if ( $key === '_thumbnail' ) {
				$has = has_post_thumbnail( $p->ID );
			} else {
				$v = get_post_meta( $p->ID, $key, true );
				$has = ( $v !== '' && ( ! is_numeric( $v ) || wp_get_attachment_url( (int) $v ) ) );
			}
			echo '<td>' . ( $has
				? '<span style="color:#1a7f37;font-weight:600">&#10003;</span>'
				: '<span style="color:#b32d2e;font-weight:600">&#10007;</span>' ) . '</td>';
		}
		echo '<td><a class="button button-small" href="' . esc_url( get_edit_post_link( $p->ID ) ) . '">Upload asset &rarr;</a></td>';
		echo '</tr>';
	}
	echo '</tbody></table>';
	echo '</div>';
}
