<?php
/**
 * Not found.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<div class="dark">
<section class="nf">
	<i class="gridbg"></i><i class="glow" style="width:900px;height:600px;left:50%;top:-200px;transform:translateX(-50%)"></i>
	<div class="wrap" style="position:relative">
		<b class="grad">404</b>
		<h1 class="h2" style="margin-top:10px"><span class="grad"><?php esc_html_e( 'This page wandered off.', 'deskman-studio' ); ?></span></h1>
		<p class="lead" style="margin:14px auto 0"><?php esc_html_e( 'The link may be old or mistyped. Try one of these instead:', 'deskman-studio' ); ?></p>
		<div class="hero__cta" style="flex-wrap:wrap">
			<a class="btn btn--v btn--lg" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Go home', 'deskman-studio' ); ?></a>
			<a class="btn btn--g btn--lg" href="<?php echo esc_url( dm_link( 'services' ) ); ?>"><?php esc_html_e( 'Services', 'deskman-studio' ); ?></a>
			<a class="btn btn--g btn--lg" href="<?php echo esc_url( dm_link( 'work' ) ); ?>"><?php esc_html_e( 'Work', 'deskman-studio' ); ?></a>
			<a class="btn btn--g btn--lg" href="<?php echo esc_url( dm_link( 'contact' ) ); ?>"><?php esc_html_e( 'Contact', 'deskman-studio' ); ?></a>
		</div>
	</div>
</section>
</div>
<?php get_footer(); ?>
