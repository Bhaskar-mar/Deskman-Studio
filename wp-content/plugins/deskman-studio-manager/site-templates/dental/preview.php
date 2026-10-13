<?php
/**
 * Dental clinic website template.
 *
 * A complete, responsive website rendered with demo defaults. Elements carry
 * data-bind attributes; template-dental.js applies the visitor's
 * personalisation live (from the customiser via postMessage, or from this
 * browser's session storage when opened in a new tab).
 *
 * Variables: $values (demo defaults), $template, $assets, $config.
 *
 * @package Deskman_Studio_Manager
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/parts/helpers.php';

$v = $values;

$services = array(
	1 => 'general',
	2 => 'cleaning',
	3 => 'cosmetic',
	4 => 'whitening',
	5 => 'implant',
	6 => 'orthodontics',
);

$team = array(
	array(
		'name'     => 'dentist_name',
		'role'     => 'dentist_role',
		'bio'      => __( 'Leads the practice with a calm, unhurried approach and a focus on clear explanations and preventive care.', 'deskman-studio-manager' ),
		'portrait' => array( 'skin' => '#E8B896', 'hair' => '#2B1E16', 'style' => 'short', 'outfit' => 'primary' ),
	),
	array(
		'name'     => 'team_2_name',
		'role'     => 'team_2_role',
		'bio'      => __( 'Plans natural-looking restorative and cosmetic treatment, from single fillings to complete smile makeovers.', 'deskman-studio-manager' ),
		'portrait' => array( 'skin' => '#B9805A', 'hair' => '#1B1412', 'style' => 'bun', 'outfit' => 'secondary' ),
	),
	array(
		'name'     => 'team_3_name',
		'role'     => 'team_3_role',
		'bio'      => __( 'Provides gentle hygiene appointments and practical advice to help patients keep their smiles healthy at home.', 'deskman-studio-manager' ),
		'portrait' => array( 'skin' => '#F2CDB0', 'hair' => '#9A5B2E', 'style' => 'curly', 'outfit' => 'primary' ),
	),
);

$testimonials = array(
	__( 'The team explained every step and made me feel completely at ease. I actually look forward to my check-ups now.', 'deskman-studio-manager' ),
	__( 'Booking was simple, the clinic is spotless and my children love coming here. Friendly from start to finish.', 'deskman-studio-manager' ),
	__( 'I was nervous about treatment, but everything was gentle and clearly planned. I am so happy with my new smile.', 'deskman-studio-manager' ),
);

$trust = array(
	array( 'shield', __( 'Modern dental care', 'deskman-studio-manager' ), __( 'Up-to-date techniques and careful hygiene standards at every visit.', 'deskman-studio-manager' ) ),
	array( 'heart', __( 'Patient-focused treatment', 'deskman-studio-manager' ), __( 'Unhurried appointments and plans built around your comfort.', 'deskman-studio-manager' ) ),
	array( 'team', __( 'Experienced professionals', 'deskman-studio-manager' ), __( 'A caring team that explains every option in plain language.', 'deskman-studio-manager' ) ),
	array( 'family', __( 'Care for all ages', 'deskman-studio-manager' ), __( 'Welcoming, family-friendly dentistry from first teeth onwards.', 'deskman-studio-manager' ) ),
);

$nav = array(
	'#services' => __( 'Services', 'deskman-studio-manager' ),
	'#about'    => __( 'About Us', 'deskman-studio-manager' ),
	'#team'     => __( 'Our Team', 'deskman-studio-manager' ),
	'#contact'  => __( 'Contact', 'deskman-studio-manager' ),
);

$brand = function ( $context ) use ( $v ) {
	ob_start();
	?>
	<a class="dt-brand dt-brand--<?php echo esc_attr( $context ); ?>" href="#top" data-brand>
		<img class="dt-brand__logo" data-logo-img alt="<?php echo esc_attr( $v['clinic_name'] ); ?>" hidden>
		<span class="dt-brand__mark" aria-hidden="true"><?php echo dmt_dental_svg( 'icons/tooth' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<span class="dt-brand__name" data-bind="clinic_name"><?php echo esc_html( $v['clinic_name'] ); ?></span>
	</a>
	<?php
	return ob_get_clean();
};
?>
<!doctype html>
<html <?php language_attributes(); ?> class="dt-no-js">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<title data-bind-title><?php echo esc_html( $v['clinic_name'] ); ?></title>
	<meta name="description" content="<?php echo esc_attr( $v['description'] ); ?>">
	<?php foreach ( $assets['preload'] as $dmt_href ) : ?>
		<link rel="preload" href="<?php echo esc_url( $dmt_href ); ?>" as="font" type="font/woff2" crossorigin>
	<?php endforeach; ?>
	<?php foreach ( $assets['styles'] as $dmt_href ) : ?>
		<link rel="stylesheet" href="<?php echo esc_url( $dmt_href ); ?>">
	<?php endforeach; ?>
	<style id="dt-brand-vars">:root{<?php echo esc_html( deskman_tpl_css_vars( $v['color_primary'], $v['color_secondary'], $v['color_accent'] ) ); ?>}</style>
	<script>document.documentElement.classList.remove('dt-no-js');document.documentElement.classList.add('dt-js');</script>
</head>
<body class="dt-body" id="top">
<a class="dt-skip" href="#main"><?php esc_html_e( 'Skip to content', 'deskman-studio-manager' ); ?></a>

<header class="dt-header" data-header>
	<div class="dt-container dt-header__inner">
		<?php echo $brand( 'header' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>

		<nav class="dt-nav" id="dt-nav" aria-label="<?php esc_attr_e( 'Main', 'deskman-studio-manager' ); ?>" data-nav>
			<ul class="dt-nav__list">
				<?php foreach ( $nav as $dmt_href => $dmt_label ) : ?>
					<li><a class="dt-nav__link" href="<?php echo esc_attr( $dmt_href ); ?>"><?php echo esc_html( $dmt_label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<div class="dt-nav__mobile-extra">
				<a class="dt-btn dt-btn--primary dt-btn--block" href="#appointment" data-bind="cta_text"><?php echo esc_html( $v['cta_text'] ); ?></a>
				<a class="dt-nav__phone" href="<?php echo esc_attr( dmt_dental_tel( $v['phone'] ) ); ?>" data-bind-tel="phone">
					<?php echo dmt_dental_svg( 'icons/phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span data-bind="phone"><?php echo esc_html( $v['phone'] ); ?></span>
				</a>
			</div>
		</nav>

		<div class="dt-header__actions">
			<a class="dt-header__phone" href="<?php echo esc_attr( dmt_dental_tel( $v['phone'] ) ); ?>" data-bind-tel="phone">
				<span class="dt-header__phone-icon"><?php echo dmt_dental_svg( 'icons/phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span data-bind="phone"><?php echo esc_html( $v['phone'] ); ?></span>
			</a>
			<a class="dt-btn dt-btn--primary dt-header__cta" href="#appointment"><span data-bind="cta_text"><?php echo esc_html( $v['cta_text'] ); ?></span></a>
			<button class="dt-menu-btn" type="button" aria-expanded="false" aria-controls="dt-nav" data-menu-toggle>
				<span class="dt-menu-btn__open"><?php echo dmt_dental_svg( 'icons/menu' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="dt-menu-btn__close"><?php echo dmt_dental_svg( 'icons/close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="dt-sr"><?php esc_html_e( 'Menu', 'deskman-studio-manager' ); ?></span>
			</button>
		</div>
	</div>
</header>

<main id="main">

	<section class="dt-hero" aria-labelledby="dt-hero-title">
		<div class="dt-hero__bg" aria-hidden="true"></div>
		<div class="dt-container dt-hero__grid">
			<div class="dt-hero__copy" data-reveal>
				<p class="dt-pill"><span class="dt-pill__dot" aria-hidden="true"></span><?php esc_html_e( 'Welcome to', 'deskman-studio-manager' ); ?> <span data-bind="clinic_name"><?php echo esc_html( $v['clinic_name'] ); ?></span></p>
				<h1 id="dt-hero-title" class="dt-hero__title" data-bind="hero_heading"><?php echo esc_html( $v['hero_heading'] ); ?></h1>
				<p class="dt-hero__text" data-bind="hero_text"><?php echo esc_html( $v['hero_text'] ); ?></p>
				<div class="dt-hero__actions">
					<a class="dt-btn dt-btn--primary dt-btn--lg" href="#appointment">
						<?php echo dmt_dental_svg( 'icons/calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span data-bind="cta_text"><?php echo esc_html( $v['cta_text'] ); ?></span>
					</a>
					<a class="dt-btn dt-btn--outline dt-btn--lg" href="#services"><?php esc_html_e( 'Explore Our Services', 'deskman-studio-manager' ); ?></a>
				</div>
				<dl class="dt-hero__facts">
					<div>
						<dt><?php echo dmt_dental_svg( 'icons/phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Call us', 'deskman-studio-manager' ); ?></dt>
						<dd><a href="<?php echo esc_attr( dmt_dental_tel( $v['phone'] ) ); ?>" data-bind-tel="phone"><span data-bind="phone"><?php echo esc_html( $v['phone'] ); ?></span></a></dd>
					</div>
					<div>
						<dt><?php echo dmt_dental_svg( 'icons/pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Visit us', 'deskman-studio-manager' ); ?></dt>
						<dd data-bind-firstline="address"><?php echo esc_html( strtok( $v['address'], "\n" ) ); ?></dd>
					</div>
				</dl>
			</div>

			<div class="dt-hero__visual" data-reveal>
				<div class="dt-hero__art">
					<?php echo dmt_dental_svg( 'hero' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="dt-float dt-float--care" aria-hidden="true">
					<span class="dt-float__icon"><?php echo dmt_dental_svg( 'icons/heart' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span><strong><?php esc_html_e( 'Gentle care', 'deskman-studio-manager' ); ?></strong><small><?php esc_html_e( 'Comfort comes first', 'deskman-studio-manager' ); ?></small></span>
				</div>
				<div class="dt-float dt-float--book" aria-hidden="true">
					<span class="dt-float__icon dt-float__icon--alt"><?php echo dmt_dental_svg( 'icons/calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span><strong><?php esc_html_e( 'Easy booking', 'deskman-studio-manager' ); ?></strong><small data-bind="phone"><?php echo esc_html( $v['phone'] ); ?></small></span>
				</div>
			</div>
		</div>
	</section>

	<section class="dt-trust" aria-label="<?php esc_attr_e( 'Why patients choose us', 'deskman-studio-manager' ); ?>">
		<div class="dt-container">
			<ul class="dt-trust__list">
				<?php foreach ( $trust as $i => $item ) : ?>
					<li class="dt-trust__item" data-reveal style="--d:<?php echo (int) $i; ?>">
						<span class="dt-trust__icon"><?php echo dmt_dental_svg( 'icons/' . $item[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span>
							<strong class="dt-trust__title"><?php echo esc_html( $item[1] ); ?></strong>
							<span class="dt-trust__text"><?php echo esc_html( $item[2] ); ?></span>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="dt-section dt-services" id="services" aria-labelledby="dt-services-title">
		<div class="dt-container">
			<header class="dt-head" data-reveal>
				<p class="dt-eyebrow"><?php esc_html_e( 'Our services', 'deskman-studio-manager' ); ?></p>
				<h2 id="dt-services-title" class="dt-h2"><?php esc_html_e( 'Complete care for every smile', 'deskman-studio-manager' ); ?></h2>
				<p class="dt-head__text"><?php esc_html_e( 'Preventive, restorative and cosmetic treatments delivered with the same gentle attention — all in one welcoming clinic.', 'deskman-studio-manager' ); ?></p>
			</header>
			<ul class="dt-services__grid">
				<?php foreach ( $services as $i => $icon ) : ?>
					<li class="dt-service" data-reveal style="--d:<?php echo (int) ( $i - 1 ) % 3; ?>">
						<span class="dt-service__icon"><?php echo dmt_dental_svg( 'icons/' . $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<h3 class="dt-service__title" data-bind="service_<?php echo (int) $i; ?>_name"><?php echo esc_html( $v[ "service_{$i}_name" ] ); ?></h3>
						<p class="dt-service__text" data-bind="service_<?php echo (int) $i; ?>_desc"><?php echo esc_html( $v[ "service_{$i}_desc" ] ); ?></p>
						<a class="dt-service__link" href="#appointment"><?php esc_html_e( 'Book this treatment', 'deskman-studio-manager' ); ?><?php echo dmt_dental_svg( 'icons/arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="dt-section dt-about" id="about" aria-labelledby="dt-about-title">
		<div class="dt-container dt-about__grid">
			<div class="dt-about__visual" data-reveal>
				<div class="dt-about__art"><?php echo dmt_dental_svg( 'about' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<div class="dt-about__badge">
					<span class="dt-about__badge-icon"><?php echo dmt_dental_svg( 'icons/shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span><strong><?php esc_html_e( 'Your comfort, first', 'deskman-studio-manager' ); ?></strong><small><?php esc_html_e( 'Clear plans · No surprises', 'deskman-studio-manager' ); ?></small></span>
				</div>
			</div>
			<div class="dt-about__copy" data-reveal>
				<p class="dt-eyebrow"><?php esc_html_e( 'About us', 'deskman-studio-manager' ); ?></p>
				<h2 id="dt-about-title" class="dt-h2"><?php esc_html_e( 'Welcome to', 'deskman-studio-manager' ); ?> <span data-bind="clinic_name"><?php echo esc_html( $v['clinic_name'] ); ?></span></h2>
				<p class="dt-about__lead" data-bind="description"><?php echo esc_html( $v['description'] ); ?></p>
				<p data-bind="about_text"><?php echo esc_html( $v['about_text'] ); ?></p>
				<ul class="dt-checks">
					<li><?php echo dmt_dental_svg( 'icons/check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Clear explanations and written treatment plans', 'deskman-studio-manager' ); ?></li>
					<li><?php echo dmt_dental_svg( 'icons/check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'A calm, gentle approach for nervous patients', 'deskman-studio-manager' ); ?></li>
					<li><?php echo dmt_dental_svg( 'icons/check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Modern equipment and careful hygiene standards', 'deskman-studio-manager' ); ?></li>
				</ul>
				<p class="dt-about__sign">
					<span data-bind="dentist_name"><?php echo esc_html( $v['dentist_name'] ); ?></span>
					<small data-bind="dentist_role"><?php echo esc_html( $v['dentist_role'] ); ?></small>
				</p>
			</div>
		</div>
	</section>

	<section class="dt-section dt-team" id="team" aria-labelledby="dt-team-title">
		<div class="dt-container">
			<header class="dt-head" data-reveal>
				<p class="dt-eyebrow"><?php esc_html_e( 'Our team', 'deskman-studio-manager' ); ?></p>
				<h2 id="dt-team-title" class="dt-h2"><?php esc_html_e( 'Friendly faces who care about your smile', 'deskman-studio-manager' ); ?></h2>
				<p class="dt-demo-note"><?php esc_html_e( 'Example profiles — replace with your team’s real photos and details.', 'deskman-studio-manager' ); ?></p>
			</header>
			<ul class="dt-team__grid">
				<?php foreach ( $team as $i => $member ) : ?>
					<li class="dt-member" data-reveal style="--d:<?php echo (int) $i; ?>">
						<div class="dt-member__photo"><?php echo dmt_dental_portrait( $member['portrait'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed parameters. ?></div>
						<div class="dt-member__body">
							<h3 class="dt-member__name" data-bind="<?php echo esc_attr( $member['name'] ); ?>"><?php echo esc_html( $v[ $member['name'] ] ); ?></h3>
							<p class="dt-member__role" data-bind="<?php echo esc_attr( $member['role'] ); ?>"><?php echo esc_html( $v[ $member['role'] ] ); ?></p>
							<p class="dt-member__bio"><?php echo esc_html( $member['bio'] ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="dt-section dt-testimonials" aria-labelledby="dt-testimonials-title">
		<div class="dt-container">
			<header class="dt-head" data-reveal>
				<p class="dt-eyebrow"><?php esc_html_e( 'Patient stories', 'deskman-studio-manager' ); ?></p>
				<h2 id="dt-testimonials-title" class="dt-h2"><?php esc_html_e( 'What our patients say', 'deskman-studio-manager' ); ?></h2>
				<p class="dt-demo-note"><?php esc_html_e( 'Demonstration content — these are not real reviews. Replace them with genuine patient feedback.', 'deskman-studio-manager' ); ?></p>
			</header>
			<ul class="dt-quotes">
				<?php foreach ( $testimonials as $i => $quote ) : ?>
					<li class="dt-quote" data-reveal style="--d:<?php echo (int) $i; ?>">
						<figure>
							<span class="dt-quote__mark"><?php echo dmt_dental_svg( 'icons/quote' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<blockquote><p><?php echo esc_html( $quote ); ?></p></blockquote>
							<figcaption>
								<span class="dt-quote__avatar" aria-hidden="true"><?php echo esc_html( chr( 65 + $i ) ); ?></span>
								<span><strong><?php esc_html_e( 'Example patient', 'deskman-studio-manager' ); ?></strong><small><?php esc_html_e( 'Sample testimonial', 'deskman-studio-manager' ); ?></small></span>
							</figcaption>
						</figure>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="dt-section dt-appointment" id="appointment" aria-labelledby="dt-appointment-title">
		<div class="dt-container">
			<div class="dt-appointment__panel" data-reveal>
				<div class="dt-appointment__copy">
					<p class="dt-eyebrow dt-eyebrow--light"><?php esc_html_e( 'Appointments', 'deskman-studio-manager' ); ?></p>
					<h2 id="dt-appointment-title" class="dt-h2"><?php esc_html_e( 'Ready for a healthier, brighter smile?', 'deskman-studio-manager' ); ?></h2>
					<p><?php esc_html_e( 'Request an appointment and our team will get back to you to confirm a time that suits you. New patients and families are always welcome.', 'deskman-studio-manager' ); ?></p>
					<div class="dt-appointment__actions">
						<a class="dt-btn dt-btn--light dt-btn--lg" href="#appointment" data-demo-action>
							<?php echo dmt_dental_svg( 'icons/calendar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span data-bind="cta_text"><?php echo esc_html( $v['cta_text'] ); ?></span>
						</a>
						<a class="dt-btn dt-btn--ghost-light dt-btn--lg" href="<?php echo esc_attr( dmt_dental_tel( $v['phone'] ) ); ?>" data-bind-tel="phone">
							<?php echo dmt_dental_svg( 'icons/phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span data-bind="phone"><?php echo esc_html( $v['phone'] ); ?></span>
						</a>
					</div>
				</div>

				<div class="dt-contact" id="contact">
					<div class="dt-contact__map"><?php echo dmt_dental_svg( 'map' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<dl class="dt-contact__list">
						<div>
							<dt><span class="dt-contact__icon"><?php echo dmt_dental_svg( 'icons/pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php esc_html_e( 'Address', 'deskman-studio-manager' ); ?></dt>
							<dd><address data-bind-ml="address"><?php echo nl2br( esc_html( $v['address'] ), false ); ?></address></dd>
						</div>
						<div>
							<dt><span class="dt-contact__icon"><?php echo dmt_dental_svg( 'icons/phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php esc_html_e( 'Phone', 'deskman-studio-manager' ); ?></dt>
							<dd><a href="<?php echo esc_attr( dmt_dental_tel( $v['phone'] ) ); ?>" data-bind-tel="phone"><span data-bind="phone"><?php echo esc_html( $v['phone'] ); ?></span></a></dd>
						</div>
						<div>
							<dt><span class="dt-contact__icon"><?php echo dmt_dental_svg( 'icons/mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php esc_html_e( 'Email', 'deskman-studio-manager' ); ?></dt>
							<dd><a href="<?php echo esc_attr( 'mailto:' . $v['email'] ); ?>" data-bind-mailto="email"><span data-bind="email"><?php echo esc_html( $v['email'] ); ?></span></a></dd>
						</div>
						<div class="dt-contact__hours">
							<dt><span class="dt-contact__icon"><?php echo dmt_dental_svg( 'icons/clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><?php esc_html_e( 'Opening hours', 'deskman-studio-manager' ); ?></dt>
							<dd><ul class="dt-hours" data-bind-hours="hours"><?php echo dmt_dental_hours( $v['hours'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></ul></dd>
						</div>
					</dl>
				</div>
			</div>
		</div>
	</section>
</main>

<footer class="dt-footer">
	<div class="dt-container dt-footer__grid">
		<div class="dt-footer__brand">
			<?php echo $brand( 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>
			<p data-bind="description"><?php echo esc_html( $v['description'] ); ?></p>
			<ul class="dt-social" data-social-list>
				<li data-social="facebook_url" <?php echo $v['facebook_url'] ? '' : 'hidden'; ?>><a href="<?php echo esc_url( $v['facebook_url'] ? $v['facebook_url'] : '#' ); ?>" rel="noopener" target="_blank" aria-label="Facebook"><?php echo dmt_dental_svg( 'icons/facebook' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></li>
				<li data-social="instagram_url" <?php echo $v['instagram_url'] ? '' : 'hidden'; ?>><a href="<?php echo esc_url( $v['instagram_url'] ? $v['instagram_url'] : '#' ); ?>" rel="noopener" target="_blank" aria-label="Instagram"><?php echo dmt_dental_svg( 'icons/instagram' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></li>
			</ul>
		</div>
		<nav class="dt-footer__col" aria-label="<?php esc_attr_e( 'Footer', 'deskman-studio-manager' ); ?>">
			<h2 class="dt-footer__title"><?php esc_html_e( 'Explore', 'deskman-studio-manager' ); ?></h2>
			<ul>
				<?php foreach ( $nav as $dmt_href => $dmt_label ) : ?>
					<li><a href="<?php echo esc_attr( $dmt_href ); ?>"><?php echo esc_html( $dmt_label ); ?></a></li>
				<?php endforeach; ?>
				<li><a href="#appointment" data-bind="cta_text"><?php echo esc_html( $v['cta_text'] ); ?></a></li>
			</ul>
		</nav>
		<div class="dt-footer__col">
			<h2 class="dt-footer__title"><?php esc_html_e( 'Contact', 'deskman-studio-manager' ); ?></h2>
			<ul>
				<li><a href="<?php echo esc_attr( dmt_dental_tel( $v['phone'] ) ); ?>" data-bind-tel="phone"><span data-bind="phone"><?php echo esc_html( $v['phone'] ); ?></span></a></li>
				<li><a href="<?php echo esc_attr( 'mailto:' . $v['email'] ); ?>" data-bind-mailto="email"><span data-bind="email"><?php echo esc_html( $v['email'] ); ?></span></a></li>
				<li><address data-bind-ml="address"><?php echo nl2br( esc_html( $v['address'] ), false ); ?></address></li>
			</ul>
		</div>
		<div class="dt-footer__col">
			<h2 class="dt-footer__title"><?php esc_html_e( 'Opening hours', 'deskman-studio-manager' ); ?></h2>
			<ul class="dt-hours dt-hours--footer" data-bind-hours="hours"><?php echo dmt_dental_hours( $v['hours'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?></ul>
		</div>
	</div>
	<div class="dt-container dt-footer__bottom">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <span data-bind="clinic_name"><?php echo esc_html( $v['clinic_name'] ); ?></span>. <?php esc_html_e( 'All rights reserved.', 'deskman-studio-manager' ); ?></p>
		<p><?php esc_html_e( 'Website by Deskman Studio', 'deskman-studio-manager' ); ?></p>
	</div>
</footer>

<div class="dt-toast" data-toast role="status" aria-live="polite" hidden></div>

<script>window.DMT_PREVIEW = <?php echo wp_json_encode( $config ); ?>;</script>
<?php foreach ( $assets['scripts'] as $dmt_src ) : ?>
	<script src="<?php echo esc_url( $dmt_src ); ?>"></script>
<?php endforeach; ?>
</body>
</html>
