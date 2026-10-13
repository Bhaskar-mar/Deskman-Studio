<?php
/**
 * Template Name: Service — single
 * One service page (child of /services/). The service is chosen by the page
 * slug (old slugs are mapped by dm_service_alias()). Anything typed into the
 * page in the editor appears under the designed sections.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

$all  = dm_services();
$slug = dm_service_alias( get_post_field( 'post_name', get_queried_object_id() ) );
$s    = isset( $all[ $slug ] ) ? $all[ $slug ] : reset( $all );
if ( ! isset( $all[ $slug ] ) ) { $slug = key( $all ); }
$GLOBALS['dm_service'] = $s;
?>
<div class="dark">
<section class="phero" style="padding-bottom:64px">
	<i class="gridbg"></i><i class="glow g1"></i>
	<div class="wrap split" style="position:relative">
		<div>
			<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'deskman-studio' ); ?></a> <?php echo dm_i( 'right' ); // phpcs:ignore ?> <a href="<?php echo esc_url( dm_link( 'services' ) ); ?>"><?php esc_html_e( 'Services', 'deskman-studio' ); ?></a> <?php echo dm_i( 'right' ); // phpcs:ignore ?> <b><?php echo esc_html( $s['title'] ); ?></b></div>
			<h1><?php echo dm_hd( $s['h1'] ); // phpcs:ignore ?></h1>
			<p class="lead"><?php echo esc_html( $s['lead'] ); ?></p>
			<div class="hero__cta" style="justify-content:flex-start"><a class="btn btn--v btn--lg" href="<?php echo esc_url( add_query_arg( 'type', rawurlencode( $s['title'] ), dm_link( 'contact' ) ) ); ?>"><?php esc_html_e( 'Get a quote', 'deskman-studio' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a><a class="btn btn--g btn--lg" href="<?php echo esc_url( dm_link( 'work' ) ); ?>"><?php esc_html_e( 'See examples', 'deskman-studio' ); ?></a></div>
		</div>
		<?php if ( 'custom-wordpress-themes' === $slug ) : ?>
			<div style="box-shadow:var(--shd);border-radius:16px"><?php get_template_part( 'template-parts/editor-shot' ); ?></div>
		<?php else : ?>
			<div class="app">
				<div class="app__bar"><span class="dots"><i></i><i></i><i></i></span><span class="url"><?php echo dm_i( 'lock' ); // phpcs:ignore ?>Deskman · <?php echo esc_html( $s['title'] ); ?></span><span class="sample"><?php esc_html_e( 'Preview', 'deskman-studio' ); ?></span></div>
				<div style="padding:18px;display:grid;grid-template-columns:1fr 1fr;gap:10px">
					<?php foreach ( array_slice( $s['features'], 0, 4 ) as $f ) : ?>
						<div class="kpi"><small><?php echo dm_i( $f[0] ); // phpcs:ignore ?><?php echo esc_html( $f[1] ); ?></small><span class="pill pill--ok" style="margin-top:10px"><i></i><?php esc_html_e( 'Included', 'deskman-studio' ); ?></span></div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<div class="wrap" style="position:relative;margin-top:56px">
		<div class="kv"><?php foreach ( $s['kv'] as $k => $v ) : ?><div><small><?php echo esc_html( $k ); ?></small><b><?php echo esc_html( $v ); ?></b></div><?php endforeach; ?></div>
	</div>
</section>
</div>

<section class="sec">
	<div class="wrap">
		<div class="shead rv"><div><span class="label"><i></i><?php esc_html_e( 'What\'s included', 'deskman-studio' ); ?></span><h2 class="h2"><?php echo dm_h( 'Everything you need — {nothing you don\'t.}' ); // phpcs:ignore ?></h2></div><p class="lead"><?php echo esc_html( $s['short'] ); ?></p></div>
		<div class="std std--3">
			<?php foreach ( $s['features'] as $f ) : ?>
				<div class="card val rv"><span class="ico"><?php echo dm_i( $f[0] ); // phpcs:ignore ?></span><h3 style="font-size:19px;letter-spacing:-.02em;margin-top:22px"><?php echo esc_html( $f[1] ); ?></h3><p><?php echo esc_html( $f[2] ); ?></p></div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="sec" style="padding-top:0">
	<div class="wrap g12" style="align-items:start">
		<div style="grid-column:span 5" class="rv"><span class="label"><i></i><?php esc_html_e( 'How it works', 'deskman-studio' ); ?></span><h2 class="h2" style="margin-top:18px"><?php echo dm_h( 'From brief to {launch in four steps.}' ); // phpcs:ignore ?></h2><p class="lead" style="margin-top:16px"><?php esc_html_e( 'You see the plan before work starts, and click through a staging site before anything goes live.', 'deskman-studio' ); ?></p></div>
		<ol style="grid-column:7 / span 6;display:grid;gap:12px">
			<?php foreach ( array( array( 'Discover & scope', 'We map goals, pages and features, then send a fixed quote.' ), array( 'Design', 'Wireframes, then the full visual design for desktop and mobile.' ), array( 'Build on staging', 'We build, connect and test everything on a private staging site.' ), array( 'Launch & train', 'Go live, set up redirects and analytics, and train your team.' ) ) as $n => $st ) : ?>
				<li class="step3 rv" style="display:flex;gap:16px;border-style:solid;background:#fff"><b><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></b><div><h3 style="font-size:17px;letter-spacing:-.02em"><?php echo esc_html( $st[0] ); ?></h3><p><?php echo esc_html( $st[1] ); ?></p></div></li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>


<section class="sec" style="padding-top:0">
	<div class="wrap">
		<div class="shead rv"><div><span class="label"><i></i>FAQ</span><h2 class="h2"><?php echo dm_h( 'Questions about {' . $s['title'] . '.}' ); // phpcs:ignore ?></h2></div></div>
		<?php get_template_part( 'template-parts/faq', null, array( 'items' => $s['faq'] ) ); ?>
	</div>
</section>

<section class="sec" style="padding-top:0">
	<div class="wrap">
		<div class="shead rv"><div><span class="label"><i></i><?php esc_html_e( 'Related services', 'deskman-studio' ); ?></span><h2 class="h2"><?php echo dm_h( 'Often {paired with this.}' ); // phpcs:ignore ?></h2></div></div>
		<div class="svc-grid">
			<?php $n = 0; foreach ( $all as $k => $r ) : if ( $k === $slug || $n >= 3 ) { continue; } $n++; ?>
				<a class="card svc rv" href="<?php echo esc_url( dm_service_url( $k ) ); ?>"><span class="ico"><?php echo dm_i( $r['icon'] ); // phpcs:ignore ?></span><h3 class="h3"><?php echo esc_html( $r['title'] ); ?></h3><p><?php echo esc_html( $r['short'] ); ?></p><span class="link"><?php esc_html_e( 'Learn more', 'deskman-studio' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></span></a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/cta', null, array( 'style' => 'padding-top:0' ) ); ?>
<?php get_footer(); ?>
