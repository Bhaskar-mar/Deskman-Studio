<?php
/**
 * Industry categories shown on the Templates page.
 *
 * To add an industry, append an entry. To make an industry live, build a
 * template in /templates/<template-id>/ (see templates/dental for the pattern),
 * register it in config/templates.php and set 'template' => '<template-id>'.
 * Any industry without a registered, available template is shown as
 * "Coming soon" and can never open a template route.
 *
 * Keys:
 *  - name       Display name.
 *  - summary    One-line description used on the card.
 *  - thumbnail  Image path relative to assets/img/.
 *  - template   Template id from config/templates.php, or '' when not ready.
 *
 * Third parties can modify the list with the `dmt_industries` filter.
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

return array(
	'dental-clinics'       => array(
		'name'      => __( 'Dental Clinics', 'deskman-templates' ),
		'summary'   => __( 'Calm, trustworthy websites that turn visitors into booked appointments.', 'deskman-templates' ),
		'thumbnail' => 'thumbs/dental-clinics.webp',
		'template'  => 'dental',
	),
	'hospitals'            => array(
		'name'      => __( 'Hospitals', 'deskman-templates' ),
		'summary'   => __( 'Departments, specialists and patient information made easy to find.', 'deskman-templates' ),
		'thumbnail' => 'thumbs/hospitals.svg',
		'template'  => '',
	),
	'schools'              => array(
		'name'      => __( 'Schools', 'deskman-templates' ),
		'summary'   => __( 'Admissions, news and parent information in one welcoming place.', 'deskman-templates' ),
		'thumbnail' => 'thumbs/schools.svg',
		'template'  => '',
	),
	'colleges-universities' => array(
		'name'      => __( 'Colleges & Universities', 'deskman-templates' ),
		'summary'   => __( 'Programmes, campus life and applications for future students.', 'deskman-templates' ),
		'thumbnail' => 'thumbs/colleges-universities.svg',
		'template'  => '',
	),
	'teachers-tutors'      => array(
		'name'      => __( 'Teachers & Tutors', 'deskman-templates' ),
		'summary'   => __( 'Personal sites that showcase subjects, results and lesson booking.', 'deskman-templates' ),
		'thumbnail' => 'thumbs/teachers-tutors.svg',
		'template'  => '',
	),
	'real-estate'          => array(
		'name'      => __( 'Real Estate', 'deskman-templates' ),
		'summary'   => __( 'Property listings and agent profiles with strong enquiry paths.', 'deskman-templates' ),
		'thumbnail' => 'thumbs/real-estate.svg',
		'template'  => '',
	),
	'solar-energy'         => array(
		'name'      => __( 'Solar Energy', 'deskman-templates' ),
		'summary'   => __( 'Explain savings, showcase installs and capture survey requests.', 'deskman-templates' ),
		'thumbnail' => 'thumbs/solar-energy.svg',
		'template'  => '',
	),
	'salons-beauty'        => array(
		'name'      => __( 'Salons & Beauty', 'deskman-templates' ),
		'summary'   => __( 'Elegant menus of treatments, stylists and easy booking.', 'deskman-templates' ),
		'thumbnail' => 'thumbs/salons-beauty.svg',
		'template'  => '',
	),
	'restaurants-cafes'    => array(
		'name'      => __( 'Restaurants & Cafés', 'deskman-templates' ),
		'summary'   => __( 'Mouth-watering menus, opening hours and table reservations.', 'deskman-templates' ),
		'thumbnail' => 'thumbs/restaurants-cafes.svg',
		'template'  => '',
	),
	'professional-services' => array(
		'name'      => __( 'Professional Services', 'deskman-templates' ),
		'summary'   => __( 'Credible sites for consultants, accountants, lawyers and agencies.', 'deskman-templates' ),
		'thumbnail' => 'thumbs/professional-services.svg',
		'template'  => '',
	),
);
