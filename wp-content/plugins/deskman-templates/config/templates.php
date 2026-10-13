<?php
/**
 * Registered website templates.
 *
 * Each template lives in its own folder under /templates/<id>/ and contains:
 *  - fields.php   The editable field schema with demo defaults (customer data
 *                 is never stored here; it only lives in the visitor's browser
 *                 until they submit an enquiry).
 *  - preview.php  The complete, responsive website markup rendered inside the
 *                 customiser's preview frame.
 *  - (assets)     Template CSS/JS registered below.
 *
 * Third parties can modify the list with the `dmt_templates` filter.
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

return array(
	'dental' => array(
		'name'        => __( 'Brightway — Dental Clinic', 'deskman-templates' ),
		'description' => __( 'A calm, premium website for dental practices with services, team profiles, opening hours and a prominent appointment call to action.', 'deskman-templates' ),
		'dir'         => DMT_DIR . 'templates/dental/',
		'styles'      => array( 'assets/css/template-dental.css' ),
		'scripts'     => array( 'assets/js/template-dental.js' ),
		'preload'     => array( 'assets/fonts/plus-jakarta-sans-latin.woff2' ),
	),
);
