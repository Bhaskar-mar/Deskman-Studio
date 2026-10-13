<?php
/**
 * Shared "start a project" block with a live enquiry form.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<section class="sec" id="start" style="<?php echo esc_attr( isset( $args['style'] ) ? $args['style'] : '' ); ?>">
	<div class="wrap">
		<div class="cta rv">
			<i class="glow"></i><i class="glow glow--l"></i>
			<div>
				<span class="label"><i></i><?php dm_e( 'v5_cta_label', 'Start a project' ); ?></span>
				<h2 class="h2" style="margin-top:20px"><?php echo dm_hd( dm_opt( 'v5_cta_h', 'Let\'s build a website that {works as hard as you do.}' ) ); // phpcs:ignore ?></h2>
				<p class="lead" style="margin-top:18px"><?php dm_e( 'v5_cta_lead', 'Tell us what you need. You\'ll hear back within one working day with honest advice on the best approach — and a clear, fixed quote.' ); ?></p>
				<div style="display:flex;gap:10px;margin-top:28px;flex-wrap:wrap">
					<a class="btn btn--l btn--lg" href="<?php echo esc_url( dm_link( 'call' ) ); ?>"><?php dm_e( 'v5_cta_btn', 'Book a 20-min call' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a>
					<a class="btn btn--g btn--lg" href="mailto:<?php echo esc_attr( sanitize_email( dm_email() ) ); ?>"><?php echo dm_i( 'mail' ); // phpcs:ignore ?><?php echo esc_html( dm_email() ); ?></a>
				</div>
			</div>
			<?php get_template_part( 'template-parts/enquiry-form', null, array( 'id' => 'cta' ) ); ?>
		</div>
	</div>
</section>
