<?php
/**
 * Industry categories shown on the Templates page.
 *
 * To add an industry, append an entry. To make an industry live, build a
 * template in site-templates/<template-id>/ (see dental/ for the pattern),
 * register it in site-templates/templates.php and set 'template' => '<id>'.
 * Any industry without a registered, available template is shown as
 * "Coming soon" and can never open a template route.
 *
 * Keys:
 *  - name       Display name.
 *  - summary    One-line description used on the card.
 *  - thumbnail  Image path relative to assets/site-templates/img/.
 *  - template   Template id from config/templates.php, or '' when not ready.
 *
 * Filter: deskman_site_template_industries.
 *
 * @package Deskman_Studio_Manager
 */

defined( 'ABSPATH' ) || exit;

return array(
	'dental-clinics'       => array(
		'name'      => __( 'Dental Clinics', 'deskman-studio-manager' ),
		'summary'   => __( 'Calm, trustworthy websites that turn visitors into booked appointments.', 'deskman-studio-manager' ),
		'thumbnail' => 'thumbs/dental-clinics.webp',
		'template'  => 'dental',
	),
	'hospitals'            => array(
		'name'      => __( 'Hospitals', 'deskman-studio-manager' ),
		'summary'   => __( 'Departments, specialists and patient information made easy to find.', 'deskman-studio-manager' ),
		'thumbnail' => 'thumbs/hospitals.svg',
		'template'  => '',
	),
	'schools'              => array(
		'name'      => __( 'Schools', 'deskman-studio-manager' ),
		'summary'   => __( 'Admissions, news and parent information in one welcoming place.', 'deskman-studio-manager' ),
		'thumbnail' => 'thumbs/schools.svg',
		'template'  => '',
	),
	'colleges-universities' => array(
		'name'      => __( 'Colleges & Universities', 'deskman-studio-manager' ),
		'summary'   => __( 'Programmes, campus life and applications for future students.', 'deskman-studio-manager' ),
		'thumbnail' => 'thumbs/colleges-universities.svg',
		'template'  => '',
	),
	'teachers-tutors'      => array(
		'name'      => __( 'Teachers & Tutors', 'deskman-studio-manager' ),
		'summary'   => __( 'Personal sites that showcase subjects, results and lesson booking.', 'deskman-studio-manager' ),
		'thumbnail' => 'thumbs/teachers-tutors.svg',
		'template'  => '',
	),
	'real-estate'          => array(
		'name'      => __( 'Real Estate', 'deskman-studio-manager' ),
		'summary'   => __( 'Property listings and agent profiles with strong enquiry paths.', 'deskman-studio-manager' ),
		'thumbnail' => 'thumbs/real-estate.svg',
		'template'  => '',
	),
	'solar-energy'         => array(
		'name'      => __( 'Solar Energy', 'deskman-studio-manager' ),
		'summary'   => __( 'Explain savings, showcase installs and capture survey requests.', 'deskman-studio-manager' ),
		'thumbnail' => 'thumbs/solar-energy.svg',
		'template'  => '',
	),
	'salons-beauty'        => array(
		'name'      => __( 'Salons & Beauty', 'deskman-studio-manager' ),
		'summary'   => __( 'Elegant menus of treatments, stylists and easy booking.', 'deskman-studio-manager' ),
		'thumbnail' => 'thumbs/salons-beauty.svg',
		'template'  => '',
	),
	'restaurants-cafes'    => array(
		'name'      => __( 'Restaurants & Cafés', 'deskman-studio-manager' ),
		'summary'   => __( 'Mouth-watering menus, opening hours and table reservations.', 'deskman-studio-manager' ),
		'thumbnail' => 'thumbs/restaurants-cafes.svg',
		'template'  => '',
	),
	'professional-services' => array(
		'name'      => __( 'Professional Services', 'deskman-studio-manager' ),
		'summary'   => __( 'Credible sites for consultants, accountants, lawyers and agencies.', 'deskman-studio-manager' ),
		'thumbnail' => 'thumbs/professional-services.svg',
		'template'  => '',
	),
);
