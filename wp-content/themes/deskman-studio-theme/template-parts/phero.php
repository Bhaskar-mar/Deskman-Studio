<?php
/**
 * Centered dark page header used by Work, Packages, Insights and plain pages.
 * Args: crumb (string), h (text with | and {accent}), lead, after (raw HTML, already escaped).
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<section class="phero" style="padding-bottom:72px">
	<i class="gridbg"></i><i class="glow g1"></i>
	<div class="wrap" style="position:relative;text-align:center;max-width:880px">
		<div class="crumbs" style="justify-content:center"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'deskman-studio' ); ?></a> <?php echo dm_i( 'right' ); // phpcs:ignore ?> <b><?php echo esc_html( $args['crumb'] ); ?></b></div>
		<h1><?php echo dm_hd( $args['h'] ); // phpcs:ignore ?></h1>
		<?php if ( ! empty( $args['lead'] ) ) : ?><p class="lead" style="margin-left:auto;margin-right:auto"><?php echo esc_html( $args['lead'] ); ?></p><?php endif; ?>
		<?php if ( ! empty( $args['after'] ) ) { echo $args['after']; } // phpcs:ignore ?>
	</div>
</section>
