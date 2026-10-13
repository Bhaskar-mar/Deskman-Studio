<?php
/**
 * Templates gallery view.
 *
 * Variables: $industries, $featured, $coming_soon, $contact_url.
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

$dmt_featured_url = $featured ? DMT_Routes::industry_url( $featured['slug'] ) : '';
$dmt_available    = count( wp_list_filter( $industries, array( 'available' => true ) ) );
?>
<div class="dmt dmt-gallery">

	<section class="dmt-hero" aria-labelledby="dmt-hero-title">
		<div class="dmt-wrap dmt-hero__grid">
			<div class="dmt-hero__copy">
				<p class="dmt-eyebrow"><?php esc_html_e( 'Website templates by industry', 'deskman-templates' ); ?></p>
				<h1 id="dmt-hero-title" class="dmt-hero__title">
					<?php esc_html_e( 'See your business on a beautiful website —', 'deskman-templates' ); ?>
					<span class="dmt-gradient-text"><?php esc_html_e( 'before we build it.', 'deskman-templates' ); ?></span>
				</h1>
				<p class="dmt-lead">
					<?php esc_html_e( 'Choose a professionally designed template made for your industry, add your name, logo and colours, and preview it live on mobile, tablet, laptop and desktop. When it feels right, our team turns it into your finished website.', 'deskman-templates' ); ?>
				</p>
				<div class="dmt-actions">
					<?php if ( $dmt_featured_url ) : ?>
						<a class="dmt-btn dmt-btn--primary dmt-btn--lg" href="<?php echo esc_url( $dmt_featured_url ); ?>">
							<?php
							/* translators: %s: industry name, e.g. Dental Clinics. */
							echo esc_html( sprintf( __( 'Customise the %s template', 'deskman-templates' ), $featured['name'] ) );
							?>
							<?php echo DMT_Frontend::icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- bundled SVG. ?>
						</a>
					<?php endif; ?>
					<a class="dmt-btn dmt-btn--ghost dmt-btn--lg" href="#industries"><?php esc_html_e( 'Browse industries', 'deskman-templates' ); ?></a>
				</div>
				<ul class="dmt-hero__points" role="list">
					<li><?php echo DMT_Frontend::icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Live preview — no sign-up', 'deskman-templates' ); ?></li>
					<li><?php echo DMT_Frontend::icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Your logo & brand colours', 'deskman-templates' ); ?></li>
					<li><?php echo DMT_Frontend::icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Responsive on every device', 'deskman-templates' ); ?></li>
				</ul>
			</div>

			<div class="dmt-hero__visual" aria-hidden="true">
				<div class="dmt-hero__glow"></div>
				<div class="dmt-browser dmt-hero__browser">
					<div class="dmt-browser__bar"><span></span><span></span><span></span><i>yourclinic.com</i></div>
					<?php if ( $featured ) : ?>
						<img src="<?php echo esc_url( DMT_Registry::image_url( $featured['thumbnail'] ) ); ?>" alt="" width="1200" height="750" fetchpriority="high" decoding="async">
					<?php endif; ?>
				</div>
				<div class="dmt-hero__chip dmt-hero__chip--colours">
					<span class="dmt-hero__chip-label"><?php esc_html_e( 'Brand colours', 'deskman-templates' ); ?></span>
					<span class="dmt-swatches"><i style="--c:#1E6FD9"></i><i style="--c:#0E9F9A"></i><i style="--c:#8FE3C8"></i><i style="--c:#7C5CFF"></i></span>
				</div>
				<div class="dmt-hero__chip dmt-hero__chip--devices">
					<?php echo DMT_Frontend::icon( 'mobile' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo DMT_Frontend::icon( 'tablet' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo DMT_Frontend::icon( 'laptop' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo DMT_Frontend::icon( 'desktop' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="dmt-hero__chip dmt-hero__chip--logo">
					<span class="dmt-hero__logo-mark"><?php echo DMT_Frontend::icon( 'upload' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span><?php esc_html_e( 'Your logo here', 'deskman-templates' ); ?></span>
				</div>
			</div>
		</div>
	</section>

	<section class="dmt-section dmt-steps" aria-labelledby="dmt-steps-title">
		<div class="dmt-wrap">
			<header class="dmt-section__head">
				<p class="dmt-eyebrow"><?php esc_html_e( 'How it works', 'deskman-templates' ); ?></p>
				<h2 id="dmt-steps-title" class="dmt-h2"><?php esc_html_e( 'From template to your own website in three steps', 'deskman-templates' ); ?></h2>
			</header>
			<ol class="dmt-steps__list" role="list">
				<li class="dmt-step">
					<span class="dmt-step__icon"><?php echo DMT_Frontend::icon( 'grid' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="dmt-step__num">01</span>
					<h3 class="dmt-h3"><?php esc_html_e( 'Choose your industry', 'deskman-templates' ); ?></h3>
					<p><?php esc_html_e( 'Start from a design built around how customers in your field search, compare and get in touch.', 'deskman-templates' ); ?></p>
				</li>
				<li class="dmt-step">
					<span class="dmt-step__icon"><?php echo DMT_Frontend::icon( 'palette' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="dmt-step__num">02</span>
					<h3 class="dmt-h3"><?php esc_html_e( 'Personalise and preview', 'deskman-templates' ); ?></h3>
					<p><?php esc_html_e( 'Add your business name, contact details, logo and brand colours. Watch the website update instantly on every screen size.', 'deskman-templates' ); ?></p>
				</li>
				<li class="dmt-step">
					<span class="dmt-step__icon"><?php echo DMT_Frontend::icon( 'rocket' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="dmt-step__num">03</span>
					<h3 class="dmt-h3"><?php esc_html_e( 'Start your project', 'deskman-templates' ); ?></h3>
					<p><?php esc_html_e( 'Send us your choices in one click. Deskman Studio refines the content, adds your pages and launches your website.', 'deskman-templates' ); ?></p>
				</li>
			</ol>
		</div>
	</section>

	<section id="industries" class="dmt-section dmt-industries" aria-labelledby="dmt-industries-title">
		<div class="dmt-wrap">
			<header class="dmt-section__head dmt-section__head--split">
				<div>
					<p class="dmt-eyebrow"><?php esc_html_e( 'Industries', 'deskman-templates' ); ?></p>
					<h2 id="dmt-industries-title" class="dmt-h2"><?php esc_html_e( 'Find a design made for your business', 'deskman-templates' ); ?></h2>
				</div>
				<p class="dmt-section__aside">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: number of live templates, 2: number of industries. */
							_n( '%1$d live template · %2$d industries and growing', '%1$d live templates · %2$d industries and growing', $dmt_available, 'deskman-templates' ),
							$dmt_available,
							count( $industries )
						)
					);
					?>
				</p>
			</header>

			<div class="dmt-notice" data-dmt-notice role="status" aria-live="polite" data-template="<?php /* translators: %s: industry name. */ echo esc_attr__( 'Our %s templates are being prepared and will be available soon.', 'deskman-templates' ); ?>" <?php echo $coming_soon ? '' : 'hidden'; ?>>
				<span class="dmt-notice__icon"><?php echo DMT_Frontend::icon( 'clock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<p class="dmt-notice__text" data-dmt-notice-text>
					<?php
					if ( $coming_soon ) {
						/* translators: %s: industry name. */
						echo esc_html( sprintf( __( 'Our %s templates are being prepared and will be available soon.', 'deskman-templates' ), $coming_soon['name'] ) );
					}
					?>
				</p>
				<?php if ( $contact_url ) : ?>
					<a class="dmt-notice__link" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Ask us about a custom design', 'deskman-templates' ); ?></a>
				<?php endif; ?>
				<button type="button" class="dmt-notice__close" data-dmt-notice-close aria-label="<?php esc_attr_e( 'Dismiss message', 'deskman-templates' ); ?>"><?php echo DMT_Frontend::icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			</div>

			<ul class="dmt-cards" role="list">
				<?php foreach ( $industries as $industry ) : ?>
					<li class="dmt-cards__item">
						<?php include DMT_DIR . 'views/partials/industry-card.php'; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="dmt-section dmt-included" aria-labelledby="dmt-included-title">
		<div class="dmt-wrap">
			<header class="dmt-section__head">
				<p class="dmt-eyebrow"><?php esc_html_e( 'Included with every template', 'deskman-templates' ); ?></p>
				<h2 id="dmt-included-title" class="dmt-h2"><?php esc_html_e( 'More than a pretty layout', 'deskman-templates' ); ?></h2>
			</header>
			<ul class="dmt-features" role="list">
				<li><span class="dmt-features__icon"><?php echo DMT_Frontend::icon( 'devices' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><h3 class="dmt-h4"><?php esc_html_e( 'Responsive by design', 'deskman-templates' ); ?></h3><p><?php esc_html_e( 'Layouts adapt naturally from phones to wide desktop screens.', 'deskman-templates' ); ?></p></li>
				<li><span class="dmt-features__icon"><?php echo DMT_Frontend::icon( 'palette' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><h3 class="dmt-h4"><?php esc_html_e( 'Your brand, everywhere', 'deskman-templates' ); ?></h3><p><?php esc_html_e( 'Logo, colours and content carried through every section.', 'deskman-templates' ); ?></p></li>
				<li><span class="dmt-features__icon"><?php echo DMT_Frontend::icon( 'target' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><h3 class="dmt-h4"><?php esc_html_e( 'Built for enquiries', 'deskman-templates' ); ?></h3><p><?php esc_html_e( 'Clear calls to action that guide visitors to contact you.', 'deskman-templates' ); ?></p></li>
				<li><span class="dmt-features__icon"><?php echo DMT_Frontend::icon( 'bolt' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><h3 class="dmt-h4"><?php esc_html_e( 'Fast & accessible', 'deskman-templates' ); ?></h3><p><?php esc_html_e( 'Lightweight, semantic markup that is kind to every visitor.', 'deskman-templates' ); ?></p></li>
			</ul>
		</div>
	</section>

	<section class="dmt-section dmt-cta" aria-labelledby="dmt-cta-title">
		<div class="dmt-wrap">
			<div class="dmt-cta__panel">
				<div>
					<h2 id="dmt-cta-title" class="dmt-h2"><?php esc_html_e( 'Ready to see your own website?', 'deskman-templates' ); ?></h2>
					<p><?php esc_html_e( 'Personalise a template in a couple of minutes — then start your project with Deskman Studio when you’re happy.', 'deskman-templates' ); ?></p>
				</div>
				<div class="dmt-actions">
					<?php if ( $dmt_featured_url ) : ?>
						<a class="dmt-btn dmt-btn--light dmt-btn--lg" href="<?php echo esc_url( $dmt_featured_url ); ?>"><?php esc_html_e( 'Try the dental template', 'deskman-templates' ); ?><?php echo DMT_Frontend::icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<?php endif; ?>
					<?php if ( $contact_url ) : ?>
						<a class="dmt-btn dmt-btn--outline-light dmt-btn--lg" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Talk to our team', 'deskman-templates' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>
</div>
