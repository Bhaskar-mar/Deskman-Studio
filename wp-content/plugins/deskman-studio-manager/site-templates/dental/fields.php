<?php
/**
 * Editable field schema for the dental clinic template (v2).
 *
 * The customiser form, the live preview bindings and the server-side
 * validation of enquiries are all generated from this schema. Defaults are
 * clearly illustrative demo copy and make no claims (awards, ratings, patient
 * numbers, credentials) a real clinic would have to stand behind.
 *
 * Field keys:
 *  - type     text | textarea | tel | email | url | color | logo | select
 *  - label    Visible label.
 *  - default  Demo value.
 *  - options  select only: value => label. Select values are applied to the
 *             template as attributes on <html> (data-<key>), so a style
 *             switch is pure CSS.
 *  - max      Maximum length (characters).
 *  - help     Optional hint shown under the field.
 *  - quick    true to include in the quick-start dialog.
 *  - required true if the field may not be left empty in the preview
 *             (falls back to the default when cleared). Optional fields that
 *             are left empty hide their section in the template.
 *
 * @package Deskman_Studio_Manager
 */

defined( 'ABSPATH' ) || exit;

$d = 'deskman-studio-manager';

/* Treatments: name and description (icons are fixed in preview.php). */
$services = array(
	1 => array( __( 'General Dentistry', $d ), __( 'Check-ups, fillings and preventive care that keep teeth and gums healthy for life.', $d ) ),
	2 => array( __( 'Hygiene & Cleaning', $d ), __( 'Gentle, thorough hygiene visits that leave your mouth feeling genuinely fresh.', $d ) ),
	3 => array( __( 'Cosmetic Dentistry', $d ), __( 'Veneers, bonding and smile design, planned around your face and your goals.', $d ) ),
	4 => array( __( 'Teeth Whitening', $d ), __( 'Dentist-supervised whitening for a brighter smile that still looks like you.', $d ) ),
	5 => array( __( 'Dental Implants', $d ), __( 'Long-lasting replacements for missing teeth that look, feel and work naturally.', $d ) ),
	6 => array( __( 'Orthodontics', $d ), __( 'Clear aligners and modern braces to straighten teeth comfortably, at any age.', $d ) ),
);
$service_fields = array();
foreach ( $services as $i => $s ) {
	/* translators: %d: treatment number. */
	$service_fields[ "service_{$i}_name" ] = array( 'type' => 'text', 'label' => sprintf( __( 'Treatment %d · name', $d ), $i ), 'default' => $s[0], 'max' => 40, 'required' => true );
	/* translators: %d: treatment number. */
	$service_fields[ "service_{$i}_desc" ] = array( 'type' => 'textarea', 'label' => sprintf( __( 'Treatment %d · description', $d ), $i ), 'default' => $s[1], 'max' => 160, 'rows' => 2, 'required' => true );
}

/* Highlights band. Facts about how the clinic works, not claims to verify. */
$stats = array(
	1 => array( 'Mon–Sat', __( 'Open six days a week', $d ) ),
	2 => array( '6', __( 'Treatments under one roof', $d ) ),
	3 => array( '1:1', __( 'Time with your own dentist', $d ) ),
);
$stat_fields = array();
foreach ( $stats as $i => $s ) {
	/* translators: %d: highlight number. */
	$stat_fields[ "stat_{$i}_value" ] = array( 'type' => 'text', 'label' => sprintf( __( 'Highlight %d · figure', $d ), $i ), 'default' => $s[0], 'max' => 12, 'required' => true );
	/* translators: %d: highlight number. */
	$stat_fields[ "stat_{$i}_label" ] = array( 'type' => 'text', 'label' => sprintf( __( 'Highlight %d · label', $d ), $i ), 'default' => $s[1], 'max' => 40, 'required' => true );
}

$faqs = array(
	1 => array( __( 'Are you taking new patients?', $d ), __( 'Yes — new patients and families are always welcome. Request an appointment online or call us and we’ll find a time that suits you.', $d ) ),
	2 => array( __( 'What happens at my first visit?', $d ), __( 'We get to know you, check your teeth and gums, take any X-rays you need and talk you through what we see — then agree a plan together.', $d ) ),
	3 => array( __( 'I’m nervous about the dentist. Can you help?', $d ), __( 'Absolutely. Tell us when you book. We take things at your pace, explain each step before we start, and you can ask us to pause at any time.', $d ) ),
	4 => array( __( 'How can I pay for treatment?', $d ), __( 'You’ll always get a written plan and cost before treatment starts. See the payment options below, or ask us about spreading the cost.', $d ) ),
);
$faq_fields = array();
foreach ( $faqs as $i => $f ) {
	/* translators: %d: question number. */
	$faq_fields[ "faq_{$i}_q" ] = array( 'type' => 'text', 'label' => sprintf( __( 'Question %d', $d ), $i ), 'default' => $f[0], 'max' => 90, 'required' => true );
	/* translators: %d: question number. */
	$faq_fields[ "faq_{$i}_a" ] = array( 'type' => 'textarea', 'label' => sprintf( __( 'Answer %d', $d ), $i ), 'default' => $f[1], 'max' => 320, 'rows' => 3, 'required' => true );
}

return array(
	'business' => array(
		'label'       => __( 'Business details', $d ),
		'description' => __( 'Tell us about your clinic. Your website updates as you type.', $d ),
		'fields'      => array(
			'clinic_name'  => array( 'type' => 'text', 'label' => __( 'Clinic or business name', $d ), 'default' => 'Brightway Dental Care', 'max' => 60, 'quick' => true, 'required' => true, 'autocomplete' => 'organization' ),
			'dentist_name' => array( 'type' => 'text', 'label' => __( "Dentist's name", $d ), 'default' => 'Dr. Alex Morgan', 'max' => 60, 'quick' => true, 'required' => true ),
			'description'  => array( 'type' => 'textarea', 'label' => __( 'Short clinic description', $d ), 'default' => __( 'A modern, family-friendly dental practice offering gentle, personalised care for every smile.', $d ), 'max' => 220, 'rows' => 3, 'required' => true ),
			'phone'        => array( 'type' => 'tel', 'label' => __( 'Contact number', $d ), 'default' => '+1 (555) 010-2030', 'max' => 25, 'quick' => true, 'required' => true, 'autocomplete' => 'tel' ),
			'whatsapp'     => array( 'type' => 'tel', 'label' => __( 'WhatsApp number (optional)', $d ), 'help' => __( 'Adds a WhatsApp chat button. Leave empty to hide it.', $d ), 'default' => '', 'max' => 25 ),
			'email'        => array( 'type' => 'email', 'label' => __( 'Email address', $d ), 'default' => 'hello@brightwaydental.example', 'max' => 100, 'required' => true, 'autocomplete' => 'email' ),
			'address'      => array( 'type' => 'textarea', 'label' => __( 'Clinic address', $d ), 'default' => "24 Harbour Street, Suite 3\nYour City, ST 00000", 'max' => 160, 'rows' => 2, 'required' => true ),
			'hours'        => array( 'type' => 'textarea', 'label' => __( 'Opening hours', $d ), 'help' => __( 'One line per day or range, e.g. "Saturday: 9:00 am – 2:00 pm".', $d ), 'default' => "Monday – Friday: 8:30 am – 6:00 pm\nSaturday: 9:00 am – 2:00 pm\nSunday: Closed", 'max' => 300, 'rows' => 4, 'required' => true ),
			'cta_text'     => array( 'type' => 'text', 'label' => __( 'Appointment button text', $d ), 'default' => __( 'Book an Appointment', $d ), 'max' => 30, 'required' => true ),
		),
	),
	'branding' => array(
		'label'       => __( 'Look & feel', $d ),
		'description' => __( 'Your logo, colours and the style of the site.', $d ),
		'fields'      => array(
			'logo'            => array( 'type' => 'logo', 'label' => __( 'Logo', $d ), 'help' => __( 'PNG, JPEG or WebP, up to 2 MB. A wide logo with a transparent background works best.', $d ), 'default' => '', 'quick' => true ),
			'color_primary'   => array( 'type' => 'color', 'label' => __( 'Primary brand colour', $d ), 'help' => __( 'Buttons, links and key highlights.', $d ), 'default' => '#1E6FD9', 'required' => true ),
			'color_secondary' => array( 'type' => 'color', 'label' => __( 'Secondary brand colour', $d ), 'help' => __( 'Icons, badges and supporting accents.', $d ), 'default' => '#0E9F9A', 'required' => true ),
			'color_accent'    => array( 'type' => 'color', 'label' => __( 'Accent colour', $d ), 'help' => __( 'Soft highlights and backgrounds.', $d ), 'default' => '#8FE3C8', 'required' => true ),
			'font_style'      => array( 'type' => 'select', 'label' => __( 'Typography', $d ), 'default' => 'modern', 'required' => true, 'options' => array(
				'modern'    => __( 'Modern — clean and confident', $d ),
				'editorial' => __( 'Editorial — elegant serif headlines', $d ),
				'friendly'  => __( 'Friendly — warm and characterful', $d ),
				'minimal'   => __( 'Minimal — quiet and precise', $d ),
			) ),
			'corners'         => array( 'type' => 'select', 'label' => __( 'Corners', $d ), 'default' => 'round', 'required' => true, 'options' => array(
				'round' => __( 'Rounded', $d ),
				'soft'  => __( 'Softly rounded', $d ),
				'sharp' => __( 'Crisp and square', $d ),
			) ),
			'tone'            => array( 'type' => 'select', 'label' => __( 'Background tone', $d ), 'default' => 'white', 'required' => true, 'options' => array(
				'white' => __( 'Clean white', $d ),
				'warm'  => __( 'Warm ivory', $d ),
				'cool'  => __( 'Cool mint', $d ),
			) ),
		),
	),
	'content'  => array(
		'label'       => __( 'Homepage content', $d ),
		'description' => __( 'Optional. Fine-tune the headline, introduction and notices.', $d ),
		'fields'      => array(
			'hero_heading'  => array( 'type' => 'text', 'label' => __( 'Main headline', $d ), 'help' => __( 'Wrap words in {curly braces} to highlight them.', $d ), 'default' => __( 'Gentle, modern dentistry for {healthier, happier} smiles', $d ), 'max' => 90, 'required' => true ),
			'hero_text'     => array( 'type' => 'textarea', 'label' => __( 'Headline description', $d ), 'default' => __( 'From routine check-ups to complete smile makeovers, our team provides calm, comfortable care tailored to you and your family.', $d ), 'max' => 240, 'rows' => 3, 'required' => true ),
			'about_text'    => array( 'type' => 'textarea', 'label' => __( 'Clinic introduction', $d ), 'default' => __( 'We believe a visit to the dentist should feel reassuring. We take time to listen, explain every option clearly and plan treatment around your comfort, goals and budget — so you always know what to expect.', $d ), 'max' => 500, 'rows' => 5, 'required' => true ),
			'announcement'  => array( 'type' => 'text', 'label' => __( 'Announcement bar (optional)', $d ), 'help' => __( 'A short line above the header. Leave empty to hide it.', $d ), 'default' => __( 'Now welcoming new patients and families', $d ), 'max' => 80 ),
			'emergency'     => array( 'type' => 'text', 'label' => __( 'Urgent-care note (optional)', $d ), 'help' => __( 'Shown as a call-out with your phone number. Leave empty to hide it.', $d ), 'default' => __( 'In pain or chipped a tooth? Call us and we’ll advise you on the next step.', $d ), 'max' => 120 ),
			'payments'      => array( 'type' => 'textarea', 'label' => __( 'Payment options (one per line)', $d ), 'help' => __( 'Leave empty to hide the section.', $d ), 'default' => __( "Written treatment plan and cost before you start\nCard, cash and bank transfer\nMost dental insurance plans — ask us about yours\nSpread the cost of larger treatments", $d ), 'max' => 400, 'rows' => 4 ),
			'facebook_url'  => array( 'type' => 'url', 'label' => __( 'Facebook page (optional)', $d ), 'help' => __( 'Leave empty to hide the icon.', $d ), 'default' => '', 'max' => 200 ),
			'instagram_url' => array( 'type' => 'url', 'label' => __( 'Instagram profile (optional)', $d ), 'help' => __( 'Leave empty to hide the icon.', $d ), 'default' => '', 'max' => 200 ),
		),
	),
	'services' => array(
		'label'       => __( 'Treatments', $d ),
		'description' => __( 'Optional. Rename treatments or adjust their descriptions.', $d ),
		'fields'      => $service_fields,
	),
	'highlights' => array(
		'label'       => __( 'Highlights', $d ),
		'description' => __( 'Optional. Three short facts about how your clinic works.', $d ),
		'fields'      => $stat_fields,
	),
	'faq'      => array(
		'label'       => __( 'Questions patients ask', $d ),
		'description' => __( 'Optional. Answer the questions you hear most often.', $d ),
		'fields'      => $faq_fields,
	),
	'team'     => array(
		'label'       => __( 'Team', $d ),
		'description' => __( 'Optional. Example team profiles until you share your own.', $d ),
		'fields'      => array(
			'dentist_role' => array( 'type' => 'text', 'label' => __( 'Lead dentist role', $d ), 'default' => __( 'Principal Dentist', $d ), 'max' => 50, 'required' => true ),
			'team_2_name'  => array( 'type' => 'text', 'label' => __( 'Team member 2 name', $d ), 'default' => 'Dr. Sam Rivera', 'max' => 60, 'required' => true ),
			'team_2_role'  => array( 'type' => 'text', 'label' => __( 'Team member 2 role', $d ), 'default' => __( 'Cosmetic & Restorative Dentist', $d ), 'max' => 50, 'required' => true ),
			'team_3_name'  => array( 'type' => 'text', 'label' => __( 'Team member 3 name', $d ), 'default' => 'Jordan Lee', 'max' => 60, 'required' => true ),
			'team_3_role'  => array( 'type' => 'text', 'label' => __( 'Team member 3 role', $d ), 'default' => __( 'Dental Hygienist', $d ), 'max' => 50, 'required' => true ),
		),
	),
);
