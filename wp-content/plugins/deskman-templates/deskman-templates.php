<?php
/**
 * Plugin Name:       Deskman Studio Templates
 * Plugin URI:        https://deskmanstudio.com/templates/
 * Description:       Industry website templates gallery with a live, brandable template customiser, responsive device previews and a "Start My Project" enquiry flow.
 * Version:           1.0.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Deskman Studio
 * Author URI:        https://deskmanstudio.com/
 * License:           GPL-2.0-or-later
 * Text Domain:       deskman-templates
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

define( 'DMT_VERSION', '1.0.0' );
define( 'DMT_FILE', __FILE__ );
define( 'DMT_DIR', plugin_dir_path( __FILE__ ) );
define( 'DMT_URL', plugin_dir_url( __FILE__ ) );

require_once DMT_DIR . 'includes/class-dmt-registry.php';
require_once DMT_DIR . 'includes/class-dmt-fields.php';
require_once DMT_DIR . 'includes/class-dmt-color.php';
require_once DMT_DIR . 'includes/class-dmt-routes.php';
require_once DMT_DIR . 'includes/class-dmt-frontend.php';
require_once DMT_DIR . 'includes/class-dmt-enquiries.php';
require_once DMT_DIR . 'includes/class-dmt-admin.php';
require_once DMT_DIR . 'includes/class-dmt-privacy.php';
require_once DMT_DIR . 'includes/class-dmt-plugin.php';

register_activation_hook( __FILE__, array( 'DMT_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'DMT_Plugin', 'deactivate' ) );

DMT_Plugin::boot();
