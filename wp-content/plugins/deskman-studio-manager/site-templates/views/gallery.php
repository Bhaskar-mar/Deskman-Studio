<?php
/**
 * Templates gallery — /templates/.
 *
 * A complete template (calls get_header() / get_footer()), built from the
 * theme's own blocks: the dark page hero, .shead section heads, .steps3 and
 * the shared "Start a project" CTA. Everything specific to this page is
 * namespaced .dmt- in assets/site-templates/site-templates.css.
 *
 * Headline and intro are editable in Deskman → Settings → Site · Pages.
 * Override: copy to {theme}/site-templates/views/gallery.php.
 *
 * @package Deskman_Studio_Manager
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$dmt          = deskman_tpl_gallery_data();
$industries   = $dmt['industries'];
$featured     = $dmt['featured'];
$coming_soon  = $dmt['coming_soon'];
$featured_url = $featured ? deskman_tpl_industry_url( $featured['slug'] ) : '';
$live_count   = count( wp_list_filter( $industries, array( 'available' => true ) ) );
$arrow        = deskman_tpl_icon( 'arrow-right' );

get_header();
?>
<div class="dark">
<section class="phero dmt-hero" aria-labelledby="dmt-hero-title">
	<i class="gridbg"></i><i class="glow g1"></i><i class="glow glow--l dmt-hero__lime"></i>
	<div class="wrap split" style="position:relative">
		<div>
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'deskman-studio-manager' ); ?></a> <?php echo deskman_tpl_icon( 'chevron' ); // phpcs:ignore ?> <b><?php echo esc_html( get_the_title( deskman_tpl_page_id() ) ); ?></b></div>
			<h1 id="dmt-hero-title"><?php echo deskman_tpl_heading( deskman_tpl_copy( 'v5_tpl_h', 'Website templates, {made for your industry.}' ), true ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
			<p class="lead"><?php echo esc_html( deskman_tpl_copy( 'v5_tpl_lead', 'Pick a design built for your kind of business, add your name, logo and colours, and preview it live on mobile, tablet, laptop and desktop. When it feels right, we turn it into your finished website.' ) ); ?></p>
			<div class="dmt-hero__actions">
				<?php if ( $featured_url ) : ?>
					<a class="btn btn--v btn--lg" href="<?php echo esc_url( $featured_url ); ?>">
						<?php
						/* translators: %s: industry, e.g. "Dental Clinics". */
						echo esc_html( sprintf( __( 'Try the %s template', 'deskman-studio-manager' ), $featured['name'] ) );
						?>
						<?php echo $arrow; // phpcs:ignore ?>
					</a>
				<?php endif; ?>
				<a class="btn btn--g btn--lg" href="#industries"><?php esc_html_e( 'Browse industries', 'deskman-studio-manager' ); ?></a>
			</div>
			<ul class="dmt-hero__meta">
				<li><?php echo deskman_tpl_icon( 'check' ); // phpcs:ignore ?><?php esc_html_e( 'Live preview, no sign-up', 'deskman-studio-manager' ); ?></li>
				<li><?php echo deskman_tpl_icon( 'check' ); // phpcs:ignore ?><?php esc_html_e( 'Your logo & colours', 'deskman-studio-manager' ); ?></li>
				<li><?php echo deskman_tpl_icon( 'check' ); // phpcs:ignore ?><?php esc_html_e( 'Every screen size', 'deskman-studio-manager' ); ?></li>
			</ul>
		</div>

		<div class="dmt-hero__visual" aria-hidden="true">
			<div class="app dmt-hero__app">
				<div class="app__bar"><div class="dots"><i></i><i></i><i></i></div><div class="url"><?php echo deskman_tpl_icon( 'lock' ); // phpcs:ignore ?>yourclinic.com</div></div>
				<?php if ( $featured && $featured['thumbnail'] ) : ?>
					<img src="<?php echo esc_url( deskman_tpl_img( $featured['thumbnail'] ) ); ?>" alt="" width="1200" height="750" fetchpriority="high" decoding="async">
				<?php endif; ?>
			</div>
			<div class="dcard dmt-chip dmt-chip--colours"><span class="mono"><?php esc_html_e( 'BRAND', 'deskman-studio-manager' ); ?></span><span class="dmt-swatches"><i style="--c:#1E6FD9"></i><i style="--c:#0E9F9A"></i><i style="--c:#8FE3C8"></i><i style="--c:#6840FF"></i></span></div>
			<div class="dcard dmt-chip dmt-chip--devices"><?php echo deskman_tpl_icon( 'mobile' ) . deskman_tpl_icon( 'tablet' ) . deskman_tpl_icon( 'laptop' ) . deskman_tpl_icon( 'desktop' ); // phpcs:ignore ?></div>
			<div class="dcard dmt-chip dmt-chip--logo"><span class="ico"><?php echo deskman_tpl_icon( 'upload' ); // phpcs:ignore ?></span><span><?php esc_html_e( 'Your logo here', 'deskman-studio-manager' ); ?></span></div>
		</div>
	</div>
</section>
</div>

<section class="sec" aria-labelledby="dmt-steps-title">
	<div class="wrap">
		<div class="shead rv">
			<div><span class="label"><i></i><?php esc_html_e( 'How it works', 'deskman-studio-manager' ); ?></span><h2 class="h2" id="dmt-steps-title"><?php echo deskman_tpl_heading( 'From template {to your own website.}' ); // phpcs:ignore ?></h2></div>
			<p class="lead"><?php esc_html_e( 'Personalising takes a couple of minutes. Nothing is published — you are simply seeing your business on a real, responsive design before we build it.', 'deskman-studio-manager' ); ?></p>
		</div>
		<ol class="steps3">
			<?php
			$steps = array(
				array( __( 'Choose your industry', 'deskman-studio-manager' ), __( 'Start from a design built around how customers in your field search, compare and get in touch.', 'deskman-studio-manager' ) ),
				array( __( 'Personalise & preview', 'deskman-studio-manager' ), __( 'Add your name, contact details, opening hours, logo and colours, and watch the site update on every screen size.', 'deskman-studio-manager' ) ),
				array( __( 'Start your project', 'deskman-studio-manager' ), __( 'Send us your choices in one click. We reply within a working day with a fixed quote, then build and launch it.', 'deskman-studio-manager' ) ),
			);
			foreach ( $steps as $n => $st ) :
				?>
				<li class="step3 rv"><b><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></b><h3 style="font-size:17px;margin-top:16px;letter-spacing:-.02em"><?php echo esc_html( $st[0] ); ?></h3><p><?php echo esc_html( $st[1] ); ?></p></li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>

<section class="sec dmt-industries" id="industries" aria-labelledby="dmt-industries-title">
	<div class="wrap">
		<div class="shead rv">
			<div><span class="label"><i></i><?php esc_html_e( 'Industries', 'deskman-studio-manager' ); ?></span><h2 class="h2" id="dmt-industries-title"><?php echo deskman_tpl_heading( 'Find a design {made for your business.}' ); // phpcs:ignore ?></h2></div>
			<p class="lead">
				<?php
				echo esc_html( sprintf(
					/* translators: 1: live templates, 2: industries. */
					_n( '%1$d live template and %2$d industries on the way. More are added regularly.', '%1$d live templates and %2$d industries on the way. More are added regularly.', $live_count, 'deskman-studio-manager' ),
					$live_count,
					count( $industries ) - $live_count
				) );
				?>
			</p>
		</div>

		<div class="dmt-notice" data-dmt-notice role="status" aria-live="polite" data-template="<?php /* translators: %s: industry name. */ echo esc_attr__( 'Our %s templates are being prepared and will be available soon.', 'deskman-studio-manager' ); ?>" <?php echo $coming_soon ? '' : 'hidden'; ?>>
			<span class="dmt-notice__icon"><?php echo deskman_tpl_icon( 'clock' ); // phpcs:ignore ?></span>
			<p class="dmt-notice__text" data-dmt-notice-text>
				<?php
				if ( $coming_soon ) {
					/* translators: %s: industry name. */
					echo esc_html( sprintf( __( 'Our %s templates are being prepared and will be available soon.', 'deskman-studio-manager' ), $coming_soon['name'] ) );
				}
				?>
			</p>
			<a class="dmt-notice__link" href="<?php echo esc_url( $dmt['contact_url'] ); ?>"><?php esc_html_e( 'Need one sooner? We can design it for you', 'deskman-studio-manager' ); ?></a>
			<button type="button" class="dmt-notice__close" data-dmt-notice-close aria-label="<?php esc_attr_e( 'Dismiss message', 'deskman-studio-manager' ); ?>"><?php echo deskman_tpl_icon( 'close' ); // phpcs:ignore ?></button>
		</div>

		<ul class="dmt-cards">
			<?php foreach ( $industries as $industry ) : ?>
				<li class="dmt-cards__item rv"><?php include deskman_tpl_locate( 'views/partials/industry-card.php' ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<?php
if ( locate_template( 'template-parts/cta.php' ) ) {
	get_template_part( 'template-parts/cta' );
}
get_footer();
