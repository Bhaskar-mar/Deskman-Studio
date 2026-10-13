<?php
/**
 * One case study (deskman_project). Content comes from the project's fields
 * in the plugin: client, year, services, tech, live URL, challenge / what we
 * did / outcome, images and gallery.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

while ( have_posts() ) : the_post();
	$id   = get_the_ID();
	$p    = dm_project( get_post() );
	$f    = function ( $k ) use ( $id ) { return trim( (string) deskman_project_field( $id, $k, '' ) ); };
	$hero = deskman_project_image_url( $id, 'img_hero', 'full' );
	if ( ! $hero ) { $hero = $p['img']; }
	$gallery = array_filter( array_map( 'intval', explode( ',', (string) $f( 'gallery' ) ) ) );
	$sections = array( 'challenge' => __( 'The challenge', 'deskman-studio' ), 'did' => __( 'What we did', 'deskman-studio' ), 'outcome' => __( 'The outcome', 'deskman-studio' ) );
	$kv = array_filter( array( __( 'Client', 'deskman-studio' ) => $f( 'client' ), __( 'Year', 'deskman-studio' ) => $p['year'], __( 'Services', 'deskman-studio' ) => $f( 'services' ), __( 'Built with', 'deskman-studio' ) => $f( 'tech' ) ) );
?>
<div class="dark">
<section class="phero" style="padding-bottom:0">
	<i class="gridbg"></i><i class="glow g1"></i>
	<div class="wrap" style="position:relative">
		<div class="crumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'deskman-studio' ); ?></a> <?php echo dm_i( 'right' ); // phpcs:ignore ?> <a href="<?php echo esc_url( dm_link( 'work' ) ); ?>"><?php esc_html_e( 'Work', 'deskman-studio' ); ?></a> <?php echo dm_i( 'right' ); // phpcs:ignore ?> <b><?php the_title(); ?></b></div>
		<div class="g12" style="margin-top:22px;align-items:end">
			<div style="grid-column:span 7"><h1 style="margin-top:0"><span class="grad"><?php the_title(); ?></span></h1></div>
			<div style="grid-column:9 / span 4"><p class="lead" style="margin:0"><?php echo esc_html( $p['short'] ); ?></p>
				<?php if ( $p['live'] ) : ?><a class="btn btn--l" href="<?php echo esc_url( $p['live'] ); ?>" target="_blank" rel="noopener" style="margin-top:20px"><?php printf( esc_html__( 'Visit %s', 'deskman-studio' ), esc_html( $p['host'] ) ); ?> <?php echo dm_i( 'ur' ); // phpcs:ignore ?></a><?php endif; ?></div>
		</div>
		<?php if ( $kv ) : ?><div class="kv" style="margin-top:44px;--kv:<?php echo (int) count( $kv ); ?>"><?php foreach ( $kv as $k => $v ) : ?><div><small><?php echo esc_html( $k ); ?></small><b><?php echo esc_html( $v ); ?></b></div><?php endforeach; ?></div><?php endif; ?>
		<?php if ( $hero ) : ?><div class="shot" style="margin-top:48px;max-width:none"><img src="<?php echo esc_url( $hero ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" style="border-radius:14px;width:100%;aspect-ratio:16/8;object-fit:cover"></div><?php endif; ?>
	</div>
</section>
</div>

<section class="sec">
	<div class="wrap g12" style="align-items:start">
		<aside style="grid-column:span 4">
			<div class="card" style="padding:24px;position:sticky;top:100px">
				<span class="label"><i></i><?php esc_html_e( 'At a glance', 'deskman-studio' ); ?></span>
				<ul style="display:grid;gap:12px;margin-top:20px"><?php foreach ( array_filter( array_map( 'trim', explode( ',', $f( 'services' ) ? $f( 'services' ) : implode( ',', $p['tags'] ) ) ) ) as $t ) : ?><li class="check"><?php echo dm_i( 'check' ); // phpcs:ignore ?><?php echo esc_html( $t ); ?></li><?php endforeach; ?></ul>
				<a class="btn btn--k" href="<?php echo esc_url( dm_link( 'contact' ) ); ?>" style="width:100%;margin-top:24px"><?php esc_html_e( 'Start a similar project', 'deskman-studio' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a>
			</div>
		</aside>
		<article class="prose" style="grid-column:6 / span 7">
			<?php $first = true; foreach ( $sections as $k => $label ) : if ( $f( $k ) === '' ) { continue; } ?>
				<h2 style="<?php echo $first ? 'margin-top:0' : ''; ?>"><?php echo esc_html( $label ); ?></h2>
				<?php echo wp_kses_post( wpautop( $f( $k ) ) ); $first = false; ?>
			<?php endforeach; ?>
			<?php if ( trim( get_the_content() ) !== '' ) : ?><?php if ( ! $first ) : ?><h2><?php esc_html_e( 'The story', 'deskman-studio' ); ?></h2><?php endif; the_content(); endif; ?>
		</article>
	</div>
	<?php if ( $gallery ) : ?>
		<div class="wrap" style="margin-top:56px;display:grid;grid-template-columns:repeat(2,1fr);gap:20px">
			<?php foreach ( $gallery as $gid ) : ?><figure class="card" style="overflow:hidden;padding:8px"><?php echo wp_get_attachment_image( $gid, 'large', false, array( 'style' => 'border-radius:12px;width:100%;height:auto', 'loading' => 'lazy' ) ); ?></figure><?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>

<?php
	$next = get_adjacent_post( false, '', false );
	if ( ! $next ) { $first_q = get_posts( array( 'post_type' => 'deskman_project', 'numberposts' => 1, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ), 'exclude' => array( $id ) ) ); $next = $first_q ? $first_q[0] : null; }
	if ( $next ) : $np = dm_project( $next );
?>
<section class="sec" style="padding-top:0">
	<div class="wrap">
		<a class="card rv" href="<?php echo esc_url( $np['url'] ); ?>" style="display:grid;grid-template-columns:1fr 1.2fr;overflow:hidden;align-items:center">
			<div style="padding:40px"><span class="label"><i></i><?php esc_html_e( 'Next project', 'deskman-studio' ); ?></span><h2 class="h2" style="margin-top:18px"><?php echo esc_html( $np['name'] ); ?></h2><p class="lead" style="margin-top:10px"><?php echo esc_html( $np['short'] ); ?></p><span class="link" style="margin-top:20px"><?php esc_html_e( 'View case study', 'deskman-studio' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></span></div>
			<?php if ( $np['img'] ) : ?><img src="<?php echo esc_url( $np['img'] ); ?>" alt="" loading="lazy" style="height:100%;width:100%;object-fit:cover;aspect-ratio:16/10"><?php endif; ?>
		</a>
	</div>
</section>
<?php endif; endwhile; ?>
<?php get_template_part( 'template-parts/cta', null, array( 'style' => 'padding-top:0' ) ); ?>
<?php get_footer(); ?>
