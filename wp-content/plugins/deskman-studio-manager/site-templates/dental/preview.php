<?php
/**
 * Dental clinic website template (v2).
 *
 * A complete, responsive website rendered with demo copy. Elements carry
 * data-bind* attributes; template-runtime.js applies the visitor's
 * personalisation live (from the customiser by postMessage, or from this
 * browser's session storage when the preview is opened in its own tab).
 *
 *   data-bind="key"          text
 *   data-bind-rich="key"     text with {accent} words highlighted
 *   data-bind-ml="key"       multi-line text (line breaks kept)
 *   data-bind-lines="key"    one <li> per line
 *   data-bind-hours="key"    "Day: time" lines as a two-column list
 *   data-bind-firstline="key"
 *   data-bind-tel / -mailto / -wa="key"   link targets
 *   data-bind-show="key"     hidden while the field is empty
 *   select fields            data-<key> on <html>, styled in CSS
 *
 * Variables: $values (demo defaults), $template, $assets, $config.
 *
 * @package Deskman_Studio_Manager
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/parts/helpers.php';

$v  = $values;
$dd = 'deskman-studio-manager';
$i  = function ( $name ) { return dmt_dental_svg( 'icons/' . $name ); };

$services = array( 1 => 'general', 2 => 'cleaning', 3 => 'cosmetic', 4 => 'whitening', 5 => 'implant', 6 => 'orthodontics' );

$team = array(
	array( 'name' => 'dentist_name', 'role' => 'dentist_role', 'bio' => __( 'Leads the practice with a calm, unhurried approach and a focus on clear explanations and prevention.', $dd ), 'portrait' => array( 'skin' => '#E8B896', 'hair' => '#2B1E16', 'style' => 'short', 'outfit' => 'primary' ) ),
	array( 'name' => 'team_2_name', 'role' => 'team_2_role', 'bio' => __( 'Plans natural-looking restorative and cosmetic work, from a single filling to a complete smile makeover.', $dd ), 'portrait' => array( 'skin' => '#B9805A', 'hair' => '#1B1412', 'style' => 'bun', 'outfit' => 'secondary' ) ),
	array( 'name' => 'team_3_name', 'role' => 'team_3_role', 'bio' => __( 'Gives gentle hygiene appointments and practical advice that helps patients keep their smiles healthy at home.', $dd ), 'portrait' => array( 'skin' => '#F2CDB0', 'hair' => '#9A5B2E', 'style' => 'curly', 'outfit' => 'primary' ) ),
);

$values_row = array(
	array( 'shield', __( 'Modern, careful dentistry', $dd ), __( 'Up-to-date techniques and strict hygiene at every visit.', $dd ) ),
	array( 'heart', __( 'At your pace', $dd ), __( 'Unhurried appointments, planned around your comfort.', $dd ) ),
	array( 'team', __( 'Explained, not sold', $dd ), __( 'Every option in plain language, with a written plan.', $dd ) ),
	array( 'family', __( 'For the whole family', $dd ), __( 'Welcoming care from first teeth to grandparents.', $dd ) ),
);

$steps = array(
	array( 'calendar', __( 'Book a time', $dd ), __( 'Request an appointment online or give us a call. We’ll confirm a time that works for you.', $dd ) ),
	array( 'chat', __( 'Talk it through', $dd ), __( 'A relaxed first visit: a full check-up, any X-rays you need, and time for your questions.', $dd ) ),
	array( 'plan', __( 'Your written plan', $dd ), __( 'Clear options, timings and costs in writing, so you can decide without any pressure.', $dd ) ),
);

$quotes = array(
	array( __( 'The team explained every step and made me feel completely at ease. I actually look forward to my check-ups now.', $dd ), 'A' ),
	array( __( 'Booking was simple, the clinic is spotless and my children love coming here. Friendly from start to finish.', $dd ), 'B' ),
	array( __( 'I was nervous about treatment, but everything was gentle and clearly planned. I’m so happy with my smile.', $dd ), 'C' ),
);

$nav = array(
	'#treatments' => __( 'Treatments', $dd ),
	'#about'      => __( 'About', $dd ),
	'#team'       => __( 'Our Team', $dd ),
	'#faq'        => __( 'FAQs', $dd ),
	'#contact'    => __( 'Contact', $dd ),
);

$brand = function ( $context ) use ( $v, $i ) {
	return '<a class="dt-brand dt-brand--' . esc_attr( $context ) . '" href="#top" data-brand>'
		. '<img class="dt-brand__logo" data-logo-img alt="' . esc_attr( $v['clinic_name'] ) . '" hidden>'
		. '<span class="dt-brand__mark" aria-hidden="true">' . $i( 'tooth' ) . '</span>'
		. '<span class="dt-brand__name" data-bind="clinic_name">' . esc_html( $v['clinic_name'] ) . '</span></a>';
};
$show = function ( $key ) use ( $v ) { return '' === trim( (string) $v[ $key ] ) ? ' hidden' : ''; };
$tel  = esc_attr( dmt_dental_tel( $v['phone'] ) );
$sel  = '';
foreach ( deskman_tpl_fields( $template['id'] ) as $key => $f ) {
	if ( 'select' === $f['type'] ) { $sel .= ' data-' . esc_attr( str_replace( '_', '-', $key ) ) . '="' . esc_attr( $v[ $key ] ) . '"'; }
}
?>
<!doctype html>
<html <?php language_attributes(); ?> class="dt-no-js"<?php echo $sel; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above ?>>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<meta name="robots" content="noindex, nofollow">
	<title><?php echo esc_html( $v['clinic_name'] ); ?></title>
	<meta name="description" content="<?php echo esc_attr( $v['description'] ); ?>">
	<?php foreach ( $assets['preload'] as $href ) : ?>
		<link rel="preload" href="<?php echo esc_url( $href ); ?>" as="font" type="font/woff2" crossorigin>
	<?php endforeach; ?>
	<?php foreach ( $assets['styles'] as $href ) : ?>
		<link rel="stylesheet" href="<?php echo esc_url( $href ); ?>">
	<?php endforeach; ?>
	<style id="dt-brand-vars">:root{<?php echo esc_html( deskman_tpl_css_vars( $v['color_primary'], $v['color_secondary'], $v['color_accent'] ) ); ?>}</style>
	<script>document.documentElement.classList.replace('dt-no-js','dt-js');</script>
</head>
<body class="dt-body" id="top">
<a class="dt-skip" href="#main"><?php esc_html_e( 'Skip to content', $dd ); ?></a>

<div class="dt-announce" data-bind-show="announcement"<?php echo $show( 'announcement' ); // phpcs:ignore ?>>
	<div class="dt-container"><span class="dt-announce__dot" aria-hidden="true"></span><span data-bind="announcement"><?php echo esc_html( $v['announcement'] ); ?></span></div>
</div>

<header class="dt-header" data-header>
	<div class="dt-container dt-header__inner">
		<?php echo $brand( 'header' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above ?>
		<nav class="dt-nav" id="dt-nav" aria-label="<?php esc_attr_e( 'Main', $dd ); ?>" data-nav>
			<ul class="dt-nav__list">
				<?php foreach ( $nav as $href => $label ) : ?>
					<li><a class="dt-nav__link" href="<?php echo esc_attr( $href ); ?>"><?php echo esc_html( $label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<div class="dt-nav__extra">
				<a class="dt-btn dt-btn--primary dt-btn--block" href="#book"><span data-bind="cta_text"><?php echo esc_html( $v['cta_text'] ); ?></span></a>
				<a class="dt-btn dt-btn--quiet dt-btn--block" href="<?php echo $tel; // phpcs:ignore ?>" data-bind-tel="phone"><?php echo $i( 'phone' ); // phpcs:ignore ?><span data-bind="phone"><?php echo esc_html( $v['phone'] ); ?></span></a>
			</div>
		</nav>
		<div class="dt-header__actions">
			<a class="dt-header__phone" href="<?php echo $tel; // phpcs:ignore ?>" data-bind-tel="phone"><?php echo $i( 'phone' ); // phpcs:ignore ?><span data-bind="phone"><?php echo esc_html( $v['phone'] ); ?></span></a>
			<a class="dt-btn dt-btn--primary dt-header__cta" href="#book"><span data-bind="cta_text"><?php echo esc_html( $v['cta_text'] ); ?></span></a>
			<button class="dt-menu-btn" type="button" aria-expanded="false" aria-controls="dt-nav" data-menu-toggle>
				<span class="dt-menu-btn__bars" aria-hidden="true"><i></i><i></i></span>
				<span class="dt-sr"><?php esc_html_e( 'Menu', $dd ); ?></span>
			</button>
		</div>
	</div>
</header>

<main id="main">

	<section class="dt-hero" aria-labelledby="dt-hero-title">
		<div class="dt-hero__wash" aria-hidden="true"></div>
		<div class="dt-container dt-hero__grid">
			<div class="dt-hero__copy" data-reveal>
				<p class="dt-kicker"><span class="dt-kicker__dot" aria-hidden="true"></span><span data-bind="clinic_name"><?php echo esc_html( $v['clinic_name'] ); ?></span></p>
				<h1 id="dt-hero-title" class="dt-display" data-bind-rich="hero_heading"><?php echo dmt_dental_rich( $v['hero_heading'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper ?></h1>
				<p class="dt-lead" data-bind="hero_text"><?php echo esc_html( $v['hero_text'] ); ?></p>
				<div class="dt-hero__actions">
					<a class="dt-btn dt-btn--primary dt-btn--lg" href="#book"><span data-bind="cta_text"><?php echo esc_html( $v['cta_text'] ); ?></span><?php echo $i( 'arrow' ); // phpcs:ignore ?></a>
					<a class="dt-btn dt-btn--outline dt-btn--lg" href="#treatments"><?php esc_html_e( 'Explore treatments', $dd ); ?></a>
				</div>
				<ul class="dt-hero__facts">
					<li><?php echo $i( 'clock' ); // phpcs:ignore ?><span data-bind-firstline="hours"><?php echo esc_html( strtok( $v['hours'], "\n" ) ); ?></span></li>
					<li><?php echo $i( 'pin' ); // phpcs:ignore ?><span data-bind-firstline="address"><?php echo esc_html( strtok( $v['address'], "\n" ) ); ?></span></li>
				</ul>
			</div>

			<div class="dt-hero__visual" data-reveal style="--d:1">
				<div class="dt-hero__frame">
					<div class="dt-hero__art"><?php echo dmt_dental_svg( 'hero' ); // phpcs:ignore ?></div>
				</div>
				<div class="dt-card-float dt-card-float--book" aria-hidden="true">
					<p class="dt-card-float__eyebrow"><?php esc_html_e( 'Request a visit', $dd ); ?></p>
					<div class="dt-card-float__row"><span><?php esc_html_e( 'Treatment', $dd ); ?></span><b data-bind="service_1_name"><?php echo esc_html( $v['service_1_name'] ); ?></b></div>
					<div class="dt-card-float__row"><span><?php esc_html_e( 'Preferred day', $dd ); ?></span><b><?php esc_html_e( 'This week', $dd ); ?></b></div>
					<span class="dt-card-float__btn"><span data-bind="cta_text"><?php echo esc_html( $v['cta_text'] ); ?></span></span>
				</div>
				<div class="dt-card-float dt-card-float--care" aria-hidden="true">
					<span class="dt-card-float__icon"><?php echo $i( 'heart' ); // phpcs:ignore ?></span>
					<span><b><?php esc_html_e( 'Gentle care', $dd ); ?></b><small><?php esc_html_e( 'At your own pace', $dd ); ?></small></span>
				</div>
			</div>
		</div>

		<div class="dt-container">
			<dl class="dt-stats" data-reveal style="--d:2">
				<?php for ( $n = 1; $n <= 3; $n++ ) : ?>
					<div class="dt-stat"><dt data-bind="stat_<?php echo (int) $n; ?>_label"><?php echo esc_html( $v[ "stat_{$n}_label" ] ); ?></dt><dd data-bind="stat_<?php echo (int) $n; ?>_value"><?php echo esc_html( $v[ "stat_{$n}_value" ] ); ?></dd></div>
				<?php endfor; ?>
			</dl>
		</div>
	</section>

	<section class="dt-urgent" aria-label="<?php esc_attr_e( 'Urgent care', $dd ); ?>" data-bind-show="emergency"<?php echo $show( 'emergency' ); // phpcs:ignore ?>>
		<div class="dt-container dt-urgent__inner" data-reveal>
			<span class="dt-urgent__icon"><?php echo $i( 'alert' ); // phpcs:ignore ?></span>
			<p data-bind="emergency"><?php echo esc_html( $v['emergency'] ); ?></p>
			<a class="dt-btn dt-btn--ink" href="<?php echo $tel; // phpcs:ignore ?>" data-bind-tel="phone"><?php echo $i( 'phone' ); // phpcs:ignore ?><span data-bind="phone"><?php echo esc_html( $v['phone'] ); ?></span></a>
		</div>
	</section>

	<section class="dt-values" aria-label="<?php esc_attr_e( 'Our approach', $dd ); ?>">
		<div class="dt-container">
			<ul class="dt-values__list">
				<?php foreach ( $values_row as $n => $row ) : ?>
					<li data-reveal style="--d:<?php echo (int) $n; ?>">
						<span class="dt-values__icon"><?php echo $i( $row[0] ); // phpcs:ignore ?></span>
						<h2 class="dt-values__title"><?php echo esc_html( $row[1] ); ?></h2>
						<p><?php echo esc_html( $row[2] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="dt-section dt-treatments" id="treatments" aria-labelledby="dt-treatments-title">
		<div class="dt-container">
			<header class="dt-head dt-head--split" data-reveal>
				<div>
					<p class="dt-eyebrow"><?php esc_html_e( 'Treatments', $dd ); ?></p>
					<h2 id="dt-treatments-title" class="dt-h2"><?php esc_html_e( 'Everything your smile needs, in one calm place', $dd ); ?></h2>
				</div>
				<p class="dt-head__text"><?php esc_html_e( 'Preventive, restorative and cosmetic care, delivered with the same gentle attention and explained before we begin.', $dd ); ?></p>
			</header>
			<ol class="dt-treatments__grid">
				<?php foreach ( $services as $n => $icon ) : ?>
					<li class="dt-treatment" data-reveal style="--d:<?php echo (int) ( ( $n - 1 ) % 3 ); ?>">
						<div class="dt-treatment__top">
							<span class="dt-treatment__icon"><?php echo $i( $icon ); // phpcs:ignore ?></span>
							<span class="dt-treatment__num" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $n ) ); ?></span>
						</div>
						<h3 class="dt-treatment__title" data-bind="service_<?php echo (int) $n; ?>_name"><?php echo esc_html( $v[ "service_{$n}_name" ] ); ?></h3>
						<p class="dt-treatment__text" data-bind="service_<?php echo (int) $n; ?>_desc"><?php echo esc_html( $v[ "service_{$n}_desc" ] ); ?></p>
						<a class="dt-treatment__link" href="#book"><?php esc_html_e( 'Book this treatment', $dd ); ?><?php echo $i( 'arrow' ); // phpcs:ignore ?></a>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>

	<section class="dt-section dt-about" id="about" aria-labelledby="dt-about-title">
		<div class="dt-container dt-about__grid">
			<div class="dt-about__visual" data-reveal>
				<div class="dt-about__frame"><?php echo dmt_dental_svg( 'about' ); // phpcs:ignore ?></div>
				<figure class="dt-about__note">
					<blockquote><p><?php esc_html_e( '“We’d rather spend an extra ten minutes explaining than have you leave unsure.”', $dd ); ?></p></blockquote>
					<figcaption><span data-bind="dentist_name"><?php echo esc_html( $v['dentist_name'] ); ?></span> · <span data-bind="dentist_role"><?php echo esc_html( $v['dentist_role'] ); ?></span></figcaption>
				</figure>
			</div>
			<div class="dt-about__copy" data-reveal style="--d:1">
				<p class="dt-eyebrow"><?php esc_html_e( 'About the clinic', $dd ); ?></p>
				<h2 id="dt-about-title" class="dt-h2"><?php esc_html_e( 'Welcome to', $dd ); ?> <span data-bind="clinic_name"><?php echo esc_html( $v['clinic_name'] ); ?></span></h2>
				<p class="dt-about__lead" data-bind="description"><?php echo esc_html( $v['description'] ); ?></p>
				<p data-bind="about_text"><?php echo esc_html( $v['about_text'] ); ?></p>
				<ul class="dt-ticks">
					<li><?php echo $i( 'check' ); // phpcs:ignore ?><?php esc_html_e( 'Clear explanations and written treatment plans', $dd ); ?></li>
					<li><?php echo $i( 'check' ); // phpcs:ignore ?><?php esc_html_e( 'A calm, gentle approach for nervous patients', $dd ); ?></li>
					<li><?php echo $i( 'check' ); // phpcs:ignore ?><?php esc_html_e( 'Modern equipment and careful hygiene standards', $dd ); ?></li>
				</ul>
			</div>
		</div>
	</section>

	<section class="dt-section dt-steps" aria-labelledby="dt-steps-title">
		<div class="dt-container">
			<header class="dt-head" data-reveal>
				<p class="dt-eyebrow"><?php esc_html_e( 'Your first visit', $dd ); ?></p>
				<h2 id="dt-steps-title" class="dt-h2"><?php esc_html_e( 'Simple from the very first call', $dd ); ?></h2>
			</header>
			<ol class="dt-steps__list">
				<?php foreach ( $steps as $n => $st ) : ?>
					<li class="dt-step" data-reveal style="--d:<?php echo (int) $n; ?>">
						<span class="dt-step__icon"><?php echo $i( $st[0] ); // phpcs:ignore ?></span>
						<span class="dt-step__num"><?php echo esc_html( sprintf( /* translators: %d: step number */ __( 'Step %d', $dd ), $n + 1 ) ); ?></span>
						<h3 class="dt-step__title"><?php echo esc_html( $st[1] ); ?></h3>
						<p><?php echo esc_html( $st[2] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>

	<section class="dt-section dt-team" id="team" aria-labelledby="dt-team-title">
		<div class="dt-container">
			<header class="dt-head dt-head--split" data-reveal>
				<div>
					<p class="dt-eyebrow"><?php esc_html_e( 'Our team', $dd ); ?></p>
					<h2 id="dt-team-title" class="dt-h2"><?php esc_html_e( 'The people who will look after you', $dd ); ?></h2>
				</div>
				<p class="dt-demo-note"><?php esc_html_e( 'Example profiles — replace with your team’s photos and details.', $dd ); ?></p>
			</header>
			<ul class="dt-team__grid">
				<?php foreach ( $team as $n => $m ) : ?>
					<li class="dt-member" data-reveal style="--d:<?php echo (int) $n; ?>">
						<div class="dt-member__photo"><?php echo dmt_dental_portrait( $m['portrait'] ); // phpcs:ignore ?></div>
						<h3 class="dt-member__name" data-bind="<?php echo esc_attr( $m['name'] ); ?>"><?php echo esc_html( $v[ $m['name'] ] ); ?></h3>
						<p class="dt-member__role" data-bind="<?php echo esc_attr( $m['role'] ); ?>"><?php echo esc_html( $v[ $m['role'] ] ); ?></p>
						<p class="dt-member__bio"><?php echo esc_html( $m['bio'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="dt-section dt-stories" aria-labelledby="dt-stories-title">
		<div class="dt-container">
			<header class="dt-head" data-reveal>
				<p class="dt-eyebrow"><?php esc_html_e( 'Patient stories', $dd ); ?></p>
				<h2 id="dt-stories-title" class="dt-h2"><?php esc_html_e( 'How a visit should feel', $dd ); ?></h2>
				<p class="dt-demo-note"><?php esc_html_e( 'Demonstration content — not real reviews. Replace with genuine patient feedback.', $dd ); ?></p>
			</header>
			<ul class="dt-stories__grid">
				<?php foreach ( $quotes as $n => $q ) : ?>
					<li class="dt-story" data-reveal style="--d:<?php echo (int) $n; ?>">
						<figure>
							<span class="dt-story__mark" aria-hidden="true">“</span>
							<blockquote><p><?php echo esc_html( $q[0] ); ?></p></blockquote>
							<figcaption><span class="dt-story__avatar" aria-hidden="true"><?php echo esc_html( $q[1] ); ?></span><span><b><?php esc_html_e( 'Example patient', $dd ); ?></b><small><?php esc_html_e( 'Sample testimonial', $dd ); ?></small></span></figcaption>
						</figure>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="dt-section dt-help" id="faq" aria-labelledby="dt-faq-title">
		<div class="dt-container dt-help__grid">
			<div class="dt-help__aside" data-reveal>
				<p class="dt-eyebrow"><?php esc_html_e( 'Good to know', $dd ); ?></p>
				<h2 id="dt-faq-title" class="dt-h2"><?php esc_html_e( 'Questions patients ask', $dd ); ?></h2>
				<div class="dt-payments" data-bind-show="payments"<?php echo $show( 'payments' ); // phpcs:ignore ?>>
					<h3 class="dt-payments__title"><?php echo $i( 'card' ); // phpcs:ignore ?><?php esc_html_e( 'Payment options', $dd ); ?></h3>
					<ul class="dt-payments__list" data-bind-lines="payments"><?php echo dmt_dental_lines( $v['payments'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in helper ?></ul>
				</div>
			</div>
			<div class="dt-faq" data-reveal style="--d:1">
				<?php for ( $n = 1; $n <= 4; $n++ ) : ?>
					<details class="dt-faq__item"<?php echo 1 === $n ? ' open' : ''; ?>>
						<summary><span data-bind="faq_<?php echo (int) $n; ?>_q"><?php echo esc_html( $v[ "faq_{$n}_q" ] ); ?></span><i aria-hidden="true"></i></summary>
						<p data-bind="faq_<?php echo (int) $n; ?>_a"><?php echo esc_html( $v[ "faq_{$n}_a" ] ); ?></p>
					</details>
				<?php endfor; ?>
			</div>
		</div>
	</section>

	<section class="dt-section dt-book" id="book" aria-labelledby="dt-book-title">
		<div class="dt-container">
			<div class="dt-book__panel" data-reveal>
				<div class="dt-book__copy">
					<p class="dt-eyebrow dt-eyebrow--light"><?php esc_html_e( 'Appointments', $dd ); ?></p>
					<h2 id="dt-book-title" class="dt-h2"><?php esc_html_e( 'Ready for a healthier, brighter smile?', $dd ); ?></h2>
					<p><?php esc_html_e( 'Send a request and we’ll get back to you to confirm a time. New patients and families are always welcome.', $dd ); ?></p>
					<a class="dt-book__call" href="<?php echo $tel; // phpcs:ignore ?>" data-bind-tel="phone"><?php echo $i( 'phone' ); // phpcs:ignore ?><span><small><?php esc_html_e( 'Prefer to call?', $dd ); ?></small><b data-bind="phone"><?php echo esc_html( $v['phone'] ); ?></b></span></a>
				</div>
				<form class="dt-form" data-demo-form novalidate>
					<div class="dt-form__row">
						<label><span><?php esc_html_e( 'Your name', $dd ); ?></span><input type="text" name="n" autocomplete="off" placeholder="<?php esc_attr_e( 'Full name', $dd ); ?>"></label>
						<label><span><?php esc_html_e( 'Phone', $dd ); ?></span><input type="tel" name="p" autocomplete="off" placeholder="<?php esc_attr_e( 'Best number to reach you', $dd ); ?>"></label>
					</div>
					<label><span><?php esc_html_e( 'Treatment', $dd ); ?></span>
						<select name="t">
							<option><?php esc_html_e( 'Check-up & clean', $dd ); ?></option>
							<?php for ( $n = 1; $n <= 6; $n++ ) : ?><option data-bind="service_<?php echo (int) $n; ?>_name"><?php echo esc_html( $v[ "service_{$n}_name" ] ); ?></option><?php endfor; ?>
						</select>
					</label>
					<fieldset class="dt-form__days"><legend><?php esc_html_e( 'Preferred time', $dd ); ?></legend>
						<?php foreach ( array( __( 'Morning', $dd ), __( 'Afternoon', $dd ), __( 'Evening', $dd ), __( 'Any time', $dd ) ) as $n => $slot ) : ?>
							<label><input type="radio" name="s"<?php echo 3 === $n ? ' checked' : ''; ?>><span><?php echo esc_html( $slot ); ?></span></label>
						<?php endforeach; ?>
					</fieldset>
					<button class="dt-btn dt-btn--primary dt-btn--lg dt-btn--block" type="submit"><span data-bind="cta_text"><?php echo esc_html( $v['cta_text'] ); ?></span><?php echo $i( 'arrow' ); // phpcs:ignore ?></button>
					<p class="dt-form__fine"><?php esc_html_e( 'We’ll only use your details to arrange your appointment.', $dd ); ?></p>
				</form>
			</div>
		</div>
	</section>

	<section class="dt-section dt-contact" id="contact" aria-labelledby="dt-contact-title">
		<div class="dt-container dt-contact__grid">
			<div class="dt-contact__map" data-reveal><?php echo dmt_dental_svg( 'map' ); // phpcs:ignore ?></div>
			<div class="dt-contact__info" data-reveal style="--d:1">
				<p class="dt-eyebrow"><?php esc_html_e( 'Visit us', $dd ); ?></p>
				<h2 id="dt-contact-title" class="dt-h2"><?php esc_html_e( 'Find the clinic', $dd ); ?></h2>
				<dl class="dt-contact__list">
					<div><dt><?php echo $i( 'pin' ); // phpcs:ignore ?><?php esc_html_e( 'Address', $dd ); ?></dt><dd><address data-bind-ml="address"><?php echo nl2br( esc_html( $v['address'] ), false ); ?></address></dd></div>
					<div><dt><?php echo $i( 'phone' ); // phpcs:ignore ?><?php esc_html_e( 'Phone', $dd ); ?></dt><dd><a href="<?php echo $tel; // phpcs:ignore ?>" data-bind-tel="phone"><span data-bind="phone"><?php echo esc_html( $v['phone'] ); ?></span></a></dd></div>
					<div><dt><?php echo $i( 'mail' ); // phpcs:ignore ?><?php esc_html_e( 'Email', $dd ); ?></dt><dd><a href="<?php echo esc_attr( 'mailto:' . $v['email'] ); ?>" data-bind-mailto="email"><span data-bind="email"><?php echo esc_html( $v['email'] ); ?></span></a></dd></div>
					<div class="dt-contact__hours"><dt><?php echo $i( 'clock' ); // phpcs:ignore ?><?php esc_html_e( 'Opening hours', $dd ); ?></dt><dd><ul class="dt-hours" data-bind-hours="hours"><?php echo dmt_dental_hours( $v['hours'] ); // phpcs:ignore ?></ul></dd></div>
				</dl>
			</div>
		</div>
	</section>
</main>

<footer class="dt-footer">
	<div class="dt-container dt-footer__grid">
		<div class="dt-footer__brand">
			<?php echo $brand( 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above ?>
			<p data-bind="description"><?php echo esc_html( $v['description'] ); ?></p>
			<ul class="dt-social">
				<li data-social="facebook_url"<?php echo $show( 'facebook_url' ); // phpcs:ignore ?>><a href="<?php echo esc_url( $v['facebook_url'] ? $v['facebook_url'] : '#' ); ?>" rel="noopener" target="_blank" aria-label="Facebook"><?php echo $i( 'facebook' ); // phpcs:ignore ?></a></li>
				<li data-social="instagram_url"<?php echo $show( 'instagram_url' ); // phpcs:ignore ?>><a href="<?php echo esc_url( $v['instagram_url'] ? $v['instagram_url'] : '#' ); ?>" rel="noopener" target="_blank" aria-label="Instagram"><?php echo $i( 'instagram' ); // phpcs:ignore ?></a></li>
			</ul>
		</div>
		<nav class="dt-footer__col" aria-label="<?php esc_attr_e( 'Footer', $dd ); ?>">
			<h2 class="dt-footer__title"><?php esc_html_e( 'Explore', $dd ); ?></h2>
			<ul>
				<?php foreach ( $nav as $href => $label ) : ?><li><a href="<?php echo esc_attr( $href ); ?>"><?php echo esc_html( $label ); ?></a></li><?php endforeach; ?>
			</ul>
		</nav>
		<div class="dt-footer__col">
			<h2 class="dt-footer__title"><?php esc_html_e( 'Contact', $dd ); ?></h2>
			<ul>
				<li><a href="<?php echo $tel; // phpcs:ignore ?>" data-bind-tel="phone"><span data-bind="phone"><?php echo esc_html( $v['phone'] ); ?></span></a></li>
				<li><a href="<?php echo esc_attr( 'mailto:' . $v['email'] ); ?>" data-bind-mailto="email"><span data-bind="email"><?php echo esc_html( $v['email'] ); ?></span></a></li>
				<li><address data-bind-ml="address"><?php echo nl2br( esc_html( $v['address'] ), false ); ?></address></li>
			</ul>
		</div>
		<div class="dt-footer__col">
			<h2 class="dt-footer__title"><?php esc_html_e( 'Opening hours', $dd ); ?></h2>
			<ul class="dt-hours dt-hours--footer" data-bind-hours="hours"><?php echo dmt_dental_hours( $v['hours'] ); // phpcs:ignore ?></ul>
		</div>
	</div>
	<div class="dt-container dt-footer__bottom">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <span data-bind="clinic_name"><?php echo esc_html( $v['clinic_name'] ); ?></span>. <?php esc_html_e( 'All rights reserved.', $dd ); ?></p>
		<p><?php esc_html_e( 'Website by Deskman Studio', $dd ); ?></p>
	</div>
</footer>

<a class="dt-wa" href="#" data-bind-wa="whatsapp" data-bind-show="whatsapp" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Chat on WhatsApp', $dd ); ?>"<?php echo $show( 'whatsapp' ); // phpcs:ignore ?>><?php echo $i( 'whatsapp' ); // phpcs:ignore ?></a>

<nav class="dt-mbar" aria-label="<?php esc_attr_e( 'Quick actions', $dd ); ?>">
	<a class="dt-mbar__call" href="<?php echo $tel; // phpcs:ignore ?>" data-bind-tel="phone"><?php echo $i( 'phone' ); // phpcs:ignore ?><?php esc_html_e( 'Call', $dd ); ?></a>
	<a class="dt-mbar__book" href="#book"><?php echo $i( 'calendar' ); // phpcs:ignore ?><span data-bind="cta_text"><?php echo esc_html( $v['cta_text'] ); ?></span></a>
</nav>

<div class="dt-toast" data-toast role="status" aria-live="polite" hidden></div>

<script>window.DMT_PREVIEW = <?php echo wp_json_encode( $config ); ?>;</script>
<?php foreach ( $assets['scripts'] as $src ) : ?>
	<script src="<?php echo esc_url( $src ); ?>"></script>
<?php endforeach; ?>
</body>
</html>
