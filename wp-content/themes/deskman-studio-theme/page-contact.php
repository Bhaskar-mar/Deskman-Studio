<?php
/**
 * Template Name: Contact
 * The full enquiry form plus contact options and "what happens next".
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<div class="dark">
<section class="phero" style="padding-bottom:96px">
	<i class="gridbg"></i><i class="glow g1"></i><i class="glow glow--l" style="width:600px;height:500px;right:-200px;bottom:-200px"></i>
	<div class="wrap g12" style="position:relative;align-items:start">
		<div style="grid-column:span 5">
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'deskman-studio' ); ?></a> <?php echo dm_i( 'right' ); // phpcs:ignore ?> <b><?php esc_html_e( 'Contact', 'deskman-studio' ); ?></b></div>
			<h1><?php echo dm_hd( dm_opt( 'v5_contact_h', 'Let\'s talk about {your website.}' ) ); // phpcs:ignore ?></h1>
			<p class="lead"><?php dm_e( 'v5_contact_lead', 'Tell us what you\'re planning. You\'ll get a reply within one working day — from the person who will actually work on it.' ); ?></p>
			<div style="display:grid;gap:12px;margin-top:36px">
				<a class="dcard" href="mailto:<?php echo esc_attr( sanitize_email( dm_email() ) ); ?>" style="display:flex;gap:14px;align-items:center;padding:16px 18px"><span class="ico"><?php echo dm_i( 'mail' ); // phpcs:ignore ?></span><span><small class="mono" style="color:var(--df)"><?php esc_html_e( 'EMAIL', 'deskman-studio' ); ?></small><b style="display:block;font-size:15px"><?php echo esc_html( dm_email() ); ?></b></span></a>
				<?php if ( dm_opt( 'v5_call_url', '' ) !== '' ) : ?>
					<a class="dcard" href="<?php echo esc_url( dm_opt( 'v5_call_url', '' ) ); ?>" target="_blank" rel="noopener" style="display:flex;gap:14px;align-items:center;padding:16px 18px"><span class="ico"><?php echo dm_i( 'cal' ); // phpcs:ignore ?></span><span><small class="mono" style="color:var(--df)"><?php esc_html_e( 'CALL', 'deskman-studio' ); ?></small><b style="display:block;font-size:15px"><?php esc_html_e( 'Book a 20-minute call', 'deskman-studio' ); ?></b></span><span class="link" style="margin-left:auto"><?php esc_html_e( 'Pick a time', 'deskman-studio' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></span></a>
				<?php endif; ?>
				<?php if ( dm_opt( 'seo_phone', '' ) !== '' ) : ?>
					<a class="dcard" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', dm_opt( 'seo_phone', '' ) ) ); ?>" style="display:flex;gap:14px;align-items:center;padding:16px 18px"><span class="ico"><?php echo dm_i( 'phone' ); // phpcs:ignore ?></span><span><small class="mono" style="color:var(--df)"><?php esc_html_e( 'PHONE', 'deskman-studio' ); ?></small><b style="display:block;font-size:15px"><?php dm_e( 'seo_phone', '' ); ?></b></span></a>
				<?php endif; ?>
				<div class="dcard" style="display:flex;gap:14px;align-items:center;padding:16px 18px"><span class="ico"><?php echo dm_i( 'pin' ); // phpcs:ignore ?></span><span><small class="mono" style="color:var(--df)"><?php esc_html_e( 'STUDIO', 'deskman-studio' ); ?></small><b style="display:block;font-size:15px"><?php dm_e( 'contact_location', 'Visakhapatnam, India' ); ?> · <?php esc_html_e( 'working worldwide', 'deskman-studio' ); ?></b></span></div>
			</div>
		</div>
		<div style="grid-column:7 / span 6"><?php get_template_part( 'template-parts/enquiry-form', null, array( 'full' => true, 'id' => 'contact' ) ); ?></div>
	</div>
</section>
</div>

<section class="sec">
	<div class="wrap">
		<div class="shead rv"><div><span class="label"><i></i><?php esc_html_e( 'What happens next', 'deskman-studio' ); ?></span><h2 class="h2"><?php echo dm_h( 'Three steps {to a fixed quote.}' ); // phpcs:ignore ?></h2></div></div>
		<ol class="steps3">
			<?php foreach ( array( array( 'We reply within a day', 'With a few questions and times for a short call.' ), array( 'A 20-minute call', 'We talk through goals, pages and features — no sales script.' ), array( 'You get a fixed quote', 'A written scope, timeline and price you can plan around.' ) ) as $n => $st ) : ?>
				<li class="step3 rv"><b><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></b><h3 style="font-size:17px;margin-top:16px;letter-spacing:-.02em"><?php echo esc_html( $st[0] ); ?></h3><p><?php echo esc_html( $st[1] ); ?></p></li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
<?php get_footer(); ?>
