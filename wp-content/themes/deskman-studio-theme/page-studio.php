<?php
/**
 * Template Name: About
 * Used for the "studio" page (and an "about" page if one exists).
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
?>
<div class="dark">
<section class="phero" style="padding-bottom:72px">
	<i class="gridbg"></i><i class="glow g1"></i>
	<div class="wrap split" style="position:relative">
		<div>
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'deskman-studio' ); ?></a> <?php echo dm_i( 'right' ); // phpcs:ignore ?> <b><?php esc_html_e( 'About', 'deskman-studio' ); ?></b></div>
			<h1><?php echo dm_hd( dm_opt( 'v5_about_h', 'A studio small enough to care, {senior enough to ship.}' ) ); // phpcs:ignore ?></h1>
			<p class="lead"><?php dm_e( 'v5_about_lead', 'Deskman is an independent web studio in Visakhapatnam. We design, build and look after custom WordPress websites — so what goes live is exactly what was drawn.' ); ?></p>
		</div>
		<div class="dcard" style="padding:28px">
			<span class="label"><i></i><?php esc_html_e( 'The studio', 'deskman-studio' ); ?></span>
			<div class="kv" style="margin-top:22px;grid-template-columns:1fr 1fr">
				<div style="border-bottom:1px solid var(--dl)"><small><?php esc_html_e( 'Based in', 'deskman-studio' ); ?></small><b><?php dm_e( 'contact_location', 'Visakhapatnam, India' ); ?></b></div>
				<div style="border-right:0;border-bottom:1px solid var(--dl)"><small><?php esc_html_e( 'Working', 'deskman-studio' ); ?></small><b><?php esc_html_e( 'Worldwide, remotely', 'deskman-studio' ); ?></b></div>
				<div><small><?php esc_html_e( 'Focus', 'deskman-studio' ); ?></small><b><?php esc_html_e( 'Custom WordPress', 'deskman-studio' ); ?></b></div>
				<div style="border-right:0"><small><?php esc_html_e( 'Built', 'deskman-studio' ); ?></small><b><?php esc_html_e( '100% in-house', 'deskman-studio' ); ?></b></div>
			</div>
			<img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/deskman-logo.png' ) ); ?>" alt="<?php esc_attr_e( 'Deskman Studio', 'deskman-studio' ); ?>" width="200" height="65" style="width:200px;height:auto;margin-top:28px" loading="lazy">
		</div>
	</div>
</section>
</div>

<section class="sec">
	<div class="wrap">
		<div class="shead rv"><div><span class="label"><i></i><?php esc_html_e( 'How we work', 'deskman-studio' ); ?></span><h2 class="h2"><?php echo dm_h( 'Four principles {behind every project.}' ); // phpcs:ignore ?></h2></div></div>
		<div class="std">
			<?php foreach ( dm_principles() as $n => $pr ) : ?>
				<div class="card val rv"><span class="num"><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></span><h3 style="font-size:19px;letter-spacing:-.02em;margin-top:22px"><?php echo esc_html( $pr[0] ); ?></h3><p><?php echo esc_html( $pr[1] ); ?></p></div>
			<?php endforeach; ?>
		</div>
	</div>
</section>


<section class="sec dark" style="overflow:hidden"><i class="gridbg"></i>
	<div class="wrap g12" style="position:relative;align-items:center">
		<div style="grid-column:span 5" class="rv"><span class="label"><i></i><?php esc_html_e( 'Our toolkit', 'deskman-studio' ); ?></span><h2 class="h2" style="margin-top:18px"><?php echo dm_hd( 'Proven tools, {used with care.}' ); // phpcs:ignore ?></h2><p class="lead" style="margin-top:16px"><?php esc_html_e( 'We build on WordPress because it lets you own your website and content — then add only what your business needs.', 'deskman-studio' ); ?></p></div>
		<div style="grid-column:7 / span 6;display:flex;flex-wrap:wrap;gap:10px" class="rv">
			<?php foreach ( dm_lines( 'v5_toolkit', array( 'WordPress', 'Block editor', 'PHP', 'JavaScript', 'WooCommerce', 'REST APIs', 'Figma', 'Schema.org', 'Search Console', 'Analytics' ) ) as $t ) : ?><span class="chiptag" style="height:40px;padding:0 16px;font-size:14px"><?php echo esc_html( $t ); ?></span><?php endforeach; ?>
		</div>
	</div>
</section>

<section class="sec">
	<div class="wrap">
		<div class="shead rv"><div><span class="label"><i></i><?php esc_html_e( 'Recent work', 'deskman-studio' ); ?></span><h2 class="h2"><?php echo dm_h( 'A few things {we\'ve built.}' ); // phpcs:ignore ?></h2></div><a class="btn btn--w" href="<?php echo esc_url( dm_link( 'work' ) ); ?>"><?php esc_html_e( 'View all projects', 'deskman-studio' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a></div>
		<div class="works"><?php foreach ( array_slice( dm_projects( 6 ), 0, 3 ) as $p ) { get_template_part( 'template-parts/work-card', null, array( 'p' => $p ) ); } ?></div>
	</div>
</section>

<?php get_template_part( 'template-parts/cta', null, array( 'style' => 'padding-top:0' ) ); ?>
<?php get_footer(); ?>
