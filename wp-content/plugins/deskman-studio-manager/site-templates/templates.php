<?php
/**
 * Ready website templates.
 *
 * Each template lives in its own folder under site-templates/<id>/ and contains:
 *  - fields.php   The editable field schema with demo defaults (customer data
 *                 is never stored here; it only lives in the visitor's browser
 *                 until they submit an enquiry).
 *  - preview.php  The complete, responsive website markup rendered inside the
 *                 customiser's preview frame.
 *  - (assets)     CSS/JS/fonts under assets/site-templates/, listed below.
 *
 * Filter: deskman_site_templates.
 *
 * @package Deskman_Studio_Manager
 */

defined( 'ABSPATH' ) || exit;

return array(
	'dental' => array(
		'name'        => __( 'Brightway — Dental Clinic', 'deskman-studio-manager' ),
		'description' => __( 'A calm, premium website for dental practices with services, team profiles, opening hours and a prominent appointment call to action.', 'deskman-studio-manager' ),
		'dir'         => DESKMAN_TPL_DIR . 'dental/',
		'styles'      => array( 'template-dental.css' ),
		'scripts'     => array( 'template-dental.js' ),
		'preload'     => array( 'fonts/plus-jakarta-sans-latin.woff2' ),
	),
);
