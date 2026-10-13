<?php
/**
 * Editable field schema for the dental clinic template.
 *
 * The customiser form, the live preview bindings and the server-side
 * validation of enquiries are all generated from this schema. Defaults are
 * clearly illustrative demo content and are shown until the visitor edits them.
 *
 * Field keys:
 *  - type     text | textarea | tel | email | url | color | logo
 *  - label    Visible label.
 *  - default  Demo value.
 *  - max      Maximum length (characters).
 *  - help     Optional hint shown under the field.
 *  - quick    true to include in the quick-start dialog.
 *  - required true if the field may not be left empty in the preview
 *             (falls back to the default when cleared).
 *
 * @package Deskman_Studio_Manager
 */

defined( 'ABSPATH' ) || exit;

$services = array(
	1 => array( __( 'General Dentistry', 'deskman-studio-manager' ), __( 'Check-ups, fillings and preventive care to keep your teeth and gums healthy for life.', 'deskman-studio-manager' ) ),
	2 => array( __( 'Teeth Cleaning', 'deskman-studio-manager' ), __( 'Professional hygiene visits that gently remove plaque and tartar and leave your smile feeling fresh.', 'deskman-studio-manager' ) ),
	3 => array( __( 'Cosmetic Dentistry', 'deskman-studio-manager' ), __( 'Veneers, bonding and smile design to enhance the shape, shade and balance of your teeth.', 'deskman-studio-manager' ) ),
	4 => array( __( 'Teeth Whitening', 'deskman-studio-manager' ), __( 'Dentist-supervised whitening for a noticeably brighter, natural-looking smile.', 'deskman-studio-manager' ) ),
	5 => array( __( 'Dental Implants', 'deskman-studio-manager' ), __( 'Long-lasting replacements for missing teeth, designed to look, feel and function naturally.', 'deskman-studio-manager' ) ),
	6 => array( __( 'Orthodontics', 'deskman-studio-manager' ), __( 'Clear aligners and modern braces to straighten teeth comfortably at any age.', 'deskman-studio-manager' ) ),
);

$service_fields = array();
foreach ( $services as $i => $service ) {
	$service_fields[ "service_{$i}_name" ] = array(
		'type'     => 'text',
		/* translators: %d: service number. */
		'label'    => sprintf( __( 'Service %d name', 'deskman-studio-manager' ), $i ),
		'default'  => $service[0],
		'max'      => 40,
		'required' => true,
	);
	$service_fields[ "service_{$i}_desc" ] = array(
		'type'     => 'textarea',
		/* translators: %d: service number. */
		'label'    => sprintf( __( 'Service %d description', 'deskman-studio-manager' ), $i ),
		'default'  => $service[1],
		'max'      => 160,
		'rows'     => 2,
		'required' => true,
	);
}

return array(
	'business' => array(
		'label'       => __( 'Business details', 'deskman-studio-manager' ),
		'description' => __( 'Tell us about your clinic. Your website updates as you type.', 'deskman-studio-manager' ),
		'fields'      => array(
			'clinic_name'  => array(
				'type'         => 'text',
				'label'        => __( 'Clinic or business name', 'deskman-studio-manager' ),
				'default'      => 'Brightway Dental Care',
				'max'          => 60,
				'quick'        => true,
				'required'     => true,
				'autocomplete' => 'organization',
			),
			'dentist_name' => array(
				'type'     => 'text',
				'label'    => __( "Dentist's name", 'deskman-studio-manager' ),
				'default'  => 'Dr. Alex Morgan',
				'max'      => 60,
				'quick'    => true,
				'required' => true,
			),
			'description'  => array(
				'type'     => 'textarea',
				'label'    => __( 'Short clinic description', 'deskman-studio-manager' ),
				'default'  => __( 'A modern, family-friendly dental practice offering gentle, personalised care for every smile.', 'deskman-studio-manager' ),
				'max'      => 220,
				'rows'     => 3,
				'required' => true,
			),
			'phone'        => array(
				'type'         => 'tel',
				'label'        => __( 'Contact number', 'deskman-studio-manager' ),
				'default'      => '+1 (555) 010-2030',
				'max'          => 25,
				'quick'        => true,
				'required'     => true,
				'autocomplete' => 'tel',
			),
			'email'        => array(
				'type'         => 'email',
				'label'        => __( 'Email address', 'deskman-studio-manager' ),
				'default'      => 'hello@brightwaydental.example',
				'max'          => 100,
				'required'     => true,
				'autocomplete' => 'email',
			),
			'address'      => array(
				'type'     => 'textarea',
				'label'    => __( 'Clinic address', 'deskman-studio-manager' ),
				'default'  => "24 Harbour Street, Suite 3\nYour City, ST 00000",
				'max'      => 160,
				'rows'     => 2,
				'required' => true,
			),
			'hours'        => array(
				'type'     => 'textarea',
				'label'    => __( 'Opening hours', 'deskman-studio-manager' ),
				'help'     => __( 'One line per day or range, e.g. "Saturday: 9:00 am – 2:00 pm".', 'deskman-studio-manager' ),
				'default'  => "Monday – Friday: 8:30 am – 6:00 pm\nSaturday: 9:00 am – 2:00 pm\nSunday: Closed",
				'max'      => 300,
				'rows'     => 4,
				'required' => true,
			),
			'cta_text'     => array(
				'type'     => 'text',
				'label'    => __( 'Appointment button text', 'deskman-studio-manager' ),
				'default'  => __( 'Book an Appointment', 'deskman-studio-manager' ),
				'max'      => 30,
				'required' => true,
			),
		),
	),
	'branding' => array(
		'label'       => __( 'Branding', 'deskman-studio-manager' ),
		'description' => __( 'Add your logo and choose colours that match your practice.', 'deskman-studio-manager' ),
		'fields'      => array(
			'logo'            => array(
				'type'    => 'logo',
				'label'   => __( 'Logo', 'deskman-studio-manager' ),
				'help'    => __( 'PNG, JPEG or WebP, up to 2 MB. A wide logo with a transparent background works best.', 'deskman-studio-manager' ),
				'default' => '',
				'quick'   => true,
			),
			'color_primary'   => array(
				'type'     => 'color',
				'label'    => __( 'Primary brand colour', 'deskman-studio-manager' ),
				'help'     => __( 'Used for buttons, links and key highlights.', 'deskman-studio-manager' ),
				'default'  => '#1E6FD9',
				'required' => true,
			),
			'color_secondary' => array(
				'type'     => 'color',
				'label'    => __( 'Secondary brand colour', 'deskman-studio-manager' ),
				'help'     => __( 'Used for icons, badges and supporting accents.', 'deskman-studio-manager' ),
				'default'  => '#0E9F9A',
				'required' => true,
			),
			'color_accent'    => array(
				'type'     => 'color',
				'label'    => __( 'Accent colour (optional)', 'deskman-studio-manager' ),
				'help'     => __( 'A soft highlight for backgrounds and decorative details.', 'deskman-studio-manager' ),
				'default'  => '#8FE3C8',
				'required' => true,
			),
		),
	),
	'content'  => array(
		'label'       => __( 'Homepage content', 'deskman-studio-manager' ),
		'description' => __( 'Optional. Fine-tune the headline and introduction.', 'deskman-studio-manager' ),
		'fields'      => array(
			'hero_heading'  => array(
				'type'     => 'text',
				'label'    => __( 'Main hero heading', 'deskman-studio-manager' ),
				'default'  => __( 'Gentle, modern dentistry for healthier, happier smiles', 'deskman-studio-manager' ),
				'max'      => 90,
				'required' => true,
			),
			'hero_text'     => array(
				'type'     => 'textarea',
				'label'    => __( 'Hero description', 'deskman-studio-manager' ),
				'default'  => __( 'From routine check-ups to complete smile makeovers, our team provides calm, comfortable care tailored to you and your family.', 'deskman-studio-manager' ),
				'max'      => 240,
				'rows'     => 3,
				'required' => true,
			),
			'about_text'    => array(
				'type'     => 'textarea',
				'label'    => __( 'Clinic introduction', 'deskman-studio-manager' ),
				'default'  => __( 'We believe a visit to the dentist should feel reassuring. We take time to listen, explain every option clearly and plan treatment around your comfort, goals and budget — so you always know what to expect.', 'deskman-studio-manager' ),
				'max'      => 500,
				'rows'     => 5,
				'required' => true,
			),
			'facebook_url'  => array(
				'type'    => 'url',
				'label'   => __( 'Facebook page (optional)', 'deskman-studio-manager' ),
				'help'    => __( 'Leave empty to hide the icon.', 'deskman-studio-manager' ),
				'default' => '',
				'max'     => 200,
			),
			'instagram_url' => array(
				'type'    => 'url',
				'label'   => __( 'Instagram profile (optional)', 'deskman-studio-manager' ),
				'help'    => __( 'Leave empty to hide the icon.', 'deskman-studio-manager' ),
				'default' => '',
				'max'     => 200,
			),
		),
	),
	'services' => array(
		'label'       => __( 'Services', 'deskman-studio-manager' ),
		'description' => __( 'Optional. Rename services or adjust their descriptions.', 'deskman-studio-manager' ),
		'fields'      => $service_fields,
	),
	'team'     => array(
		'label'       => __( 'Team', 'deskman-studio-manager' ),
		'description' => __( 'Optional. Example team profiles until you share your own.', 'deskman-studio-manager' ),
		'fields'      => array(
			'dentist_role' => array(
				'type'     => 'text',
				'label'    => __( 'Lead dentist role', 'deskman-studio-manager' ),
				'default'  => __( 'Principal Dentist', 'deskman-studio-manager' ),
				'max'      => 50,
				'required' => true,
			),
			'team_2_name'  => array(
				'type'     => 'text',
				'label'    => __( 'Team member 2 name', 'deskman-studio-manager' ),
				'default'  => 'Dr. Sam Rivera',
				'max'      => 60,
				'required' => true,
			),
			'team_2_role'  => array(
				'type'     => 'text',
				'label'    => __( 'Team member 2 role', 'deskman-studio-manager' ),
				'default'  => __( 'Cosmetic & Restorative Dentist', 'deskman-studio-manager' ),
				'max'      => 50,
				'required' => true,
			),
			'team_3_name'  => array(
				'type'     => 'text',
				'label'    => __( 'Team member 3 name', 'deskman-studio-manager' ),
				'default'  => 'Jordan Lee',
				'max'      => 60,
				'required' => true,
			),
			'team_3_role'  => array(
				'type'     => 'text',
				'label'    => __( 'Team member 3 role', 'deskman-studio-manager' ),
				'default'  => __( 'Dental Hygienist', 'deskman-studio-manager' ),
				'max'      => 50,
				'required' => true,
			),
		),
	),
);
