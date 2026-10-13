<?php
/**
 * Template Name: Services
 * The /services/ overview.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
$svc = dm_services();
?>
<div class="dark">
<section class="phero">
	<i class="gridbg"></i><i class="glow g1"></i>
	<div class="wrap split" style="position:relative">
		<div>
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'deskman-studio' ); ?></a> <?php echo dm_i( 'right' ); // phpcs:ignore ?> <b><?php esc_html_e( 'Services', 'deskman-studio' ); ?></b></div>
			<h1><?php echo dm_hd( dm_opt( 'v5_svc_h', 'Everything your website needs, {from one team.}' ) ); // phpcs:ignore ?></h1>
			<p class="lead"><?php dm_e( 'v5_svc_lead', 'Design, custom development, SEO and care — scoped together, so your theme, plugins and search visibility are built to work as one system.' ); ?></p>
			<div class="hero__cta" style="justify-content:flex-start"><a class="btn btn--v btn--lg" href="<?php echo esc_url( dm_link( 'contact' ) ); ?>"><?php esc_html_e( 'Start a project', 'deskman-studio' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a><a class="btn btn--g btn--lg" href="<?php echo esc_url( dm_link( 'packages' ) ); ?>"><?php esc_html_e( 'See packages', 'deskman-studio' ); ?></a></div>
		</div>
		<div class="dcard" style="padding:22px">
			<div class="pane__h" style="padding:0 0 14px;border-color:var(--dl)"><span style="color:var(--dt);font-size:13px;font-weight:600"><?php esc_html_e( 'Your website, as a system', 'deskman-studio' ); ?></span><span class="sample"><?php esc_html_e( 'Overview', 'deskman-studio' ); ?></span></div>
			<div style="display:grid;gap:10px;margin-top:14px">
				<?php foreach ( array( array( 'theme', 'Theme', 'Design system · blocks · templates', 'Layer 1' ), array( 'plug', 'Plugins', 'Forms · bookings · integrations', 'Layer 2' ), array( 'search', 'SEO', 'Schema · meta · local pages', 'Layer 3' ) ) as $l ) : ?>
					<div class="kpi" style="display:flex;align-items:center;gap:14px"><span class="ico"><?php echo dm_i( $l[0] ); // phpcs:ignore ?></span><div><b style="font-size:14px"><?php echo esc_html( $l[1] ); ?></b><small style="display:block"><?php echo esc_html( $l[2] ); ?></small></div><span class="pill pill--v" style="margin-left:auto"><i></i><?php echo esc_html( $l[3] ); ?></span></div>
				<?php endforeach; ?>
				<div class="kpi" style="display:flex;align-items:center;gap:14px;border-style:dashed"><span class="ico" style="background:rgba(161,255,98,.1);border-color:rgba(161,255,98,.3);color:var(--lime)"><?php echo dm_i( 'shield' ); // phpcs:ignore ?></span><div><b style="font-size:14px"><?php esc_html_e( 'Care', 'deskman-studio' ); ?></b><small style="display:block"><?php esc_html_e( 'Updates · backups · improvements', 'deskman-studio' ); ?></small></div><span class="pill pill--ok" style="margin-left:auto"><i></i><?php esc_html_e( 'Ongoing', 'deskman-studio' ); ?></span></div>
			</div>
		</div>
	</div>
</section>
</div>

<section class="sec">
	<div class="wrap">
		<div class="shead rv"><div><span class="label"><i></i><?php esc_html_e( 'Core services', 'deskman-studio' ); ?></span><h2 class="h2"><?php echo dm_h( 'Pick one, {or combine them.}' ); // phpcs:ignore ?></h2></div><p class="lead"><?php esc_html_e( 'Most clients start with a website and add SEO and a care plan. Every service also works on its own.', 'deskman-studio' ); ?></p></div>
		<div class="svc-grid">
			<?php foreach ( $svc as $slug => $s ) : ?>
				<article class="card svc rv" id="<?php echo esc_attr( $slug ); ?>"><span class="ico"><?php echo dm_i( $s['icon'] ); // phpcs:ignore ?></span><h3 class="h3"><?php echo esc_html( $s['title'] ); ?></h3><p><?php echo esc_html( $s['short'] ); ?></p>
					<ul><?php foreach ( $s['bullets'] as $b ) : ?><li class="check"><?php echo dm_i( 'check' ); // phpcs:ignore ?><?php echo esc_html( $b ); ?></li><?php endforeach; ?></ul>
					<a class="link" href="<?php echo esc_url( dm_service_url( $slug ) ); ?>"><?php esc_html_e( 'Learn more', 'deskman-studio' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?><span class="screen-reader-text"> — <?php echo esc_html( $s['title'] ); ?></span></a></article>
			<?php endforeach; ?>
		</div>
		<div class="card rv" style="margin-top:16px;padding:18px 22px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;box-shadow:none;border-style:dashed;background:transparent"><b style="font-size:14.5px;margin-right:6px"><?php esc_html_e( 'Also from the studio', 'deskman-studio' ); ?></b>
			<?php foreach ( dm_lines( 'v5_also', array( 'UI/UX design', 'Brand identity', 'Landing pages', 'Website redesigns', 'Migrations to WordPress', 'Content & copywriting' ) ) as $t ) : ?><span class="chiptag"><?php echo esc_html( $t ); ?></span><?php endforeach; ?></div>
	</div>
</section>

<section class="sec dark" style="overflow:hidden"><i class="gridbg"></i>
	<div class="wrap" style="position:relative">
		<div class="shead rv"><div><span class="label"><i></i><?php esc_html_e( 'Who it\'s for', 'deskman-studio' ); ?></span><h2 class="h2"><?php echo dm_hd( 'Built for businesses {that have outgrown templates.}' ); // phpcs:ignore ?></h2></div></div>
		<div class="std std--3">
			<?php foreach ( array( array( 'users', 'Service businesses', 'Clinics, schools, consultants and studios that need enquiries, bookings and local search.' ), array( 'cart', 'Online stores', 'Brands selling online who need a store that is fast, trustworthy and easy to manage.' ), array( 'layers', 'Platforms & startups', 'Directories, marketplaces and products that need custom features on a solid CMS.' ) ) as $w ) : ?>
				<div class="dcard rv"><span class="ico"><?php echo dm_i( $w[0] ); // phpcs:ignore ?></span><h3 style="font-size:16px;margin-top:16px"><?php echo esc_html( $w[1] ); ?></h3><p style="font-size:13.5px;margin-top:6px"><?php echo esc_html( $w[2] ); ?></p></div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/cta' ); ?>
<?php get_footer(); ?>
