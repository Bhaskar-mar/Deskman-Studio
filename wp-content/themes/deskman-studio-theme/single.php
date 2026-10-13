<?php
/**
 * One Insights article.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

while ( have_posts() ) : the_post();
	$cats = get_the_category();
	$mins = max( 1, (int) round( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 220 ) );
?>
<div class="dark">
<section class="phero" style="padding-bottom:<?php echo has_post_thumbnail() ? '0' : '72px'; ?>">
	<i class="gridbg"></i><i class="glow g1"></i>
	<div class="wrap" style="position:relative;max-width:900px">
		<div class="crumbs"><a href="<?php echo esc_url( dm_link( 'insights' ) ); ?>"><?php esc_html_e( 'Insights', 'deskman-studio' ); ?></a><?php if ( $cats ) : ?> <?php echo dm_i( 'right' ); // phpcs:ignore ?> <a href="<?php echo esc_url( get_category_link( $cats[0] ) ); ?>"><b><?php echo esc_html( $cats[0]->name ); ?></b></a><?php endif; ?></div>
		<h1 style="font-size:52px"><span class="grad"><?php the_title(); ?></span></h1>
		<div class="byline"><?php echo get_avatar( get_the_author_meta( 'ID' ), 64, '', '' ); ?><span><?php the_author(); ?> · <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time> · <?php printf( esc_html( _n( '%d min read', '%d min read', $mins, 'deskman-studio' ) ), (int) $mins ); ?></span></div>
		<?php if ( has_post_thumbnail() ) : ?><div class="shot" style="margin-top:40px"><?php the_post_thumbnail( 'large', array( 'style' => 'border-radius:14px;width:100%;height:auto' ) ); ?></div><?php endif; ?>
	</div>
</section>
</div>

<section class="sec">
	<div class="wrap" style="max-width:820px">
		<article class="prose" style="margin:0 auto"><?php the_content(); ?></article>
		<?php wp_link_pages( array( 'before' => '<nav class="pager">', 'after' => '</nav>' ) ); ?>
		<?php $tags = get_the_tags(); if ( $tags ) : ?><div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:40px"><?php foreach ( $tags as $t ) : ?><a class="chiptag" href="<?php echo esc_url( get_tag_link( $t ) ); ?>">#<?php echo esc_html( $t->name ); ?></a><?php endforeach; ?></div><?php endif; ?>
		<div class="card" style="margin-top:40px;padding:24px;display:flex;gap:18px;align-items:center;flex-wrap:wrap">
			<span class="ico"><?php echo dm_i( 'msg' ); // phpcs:ignore ?></span>
			<div style="flex:1;min-width:220px"><b style="font-size:16px"><?php esc_html_e( 'Want this done for your site?', 'deskman-studio' ); ?></b><p class="small" style="margin-top:2px"><?php esc_html_e( 'Tell us what you need — we reply within one working day.', 'deskman-studio' ); ?></p></div>
			<a class="btn btn--v" href="<?php echo esc_url( dm_link( 'contact' ) ); ?>"><?php esc_html_e( 'Start a project', 'deskman-studio' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a>
		</div>
		<?php if ( comments_open() || get_comments_number() ) { echo '<div class="prose" style="margin-top:56px">'; comments_template(); echo '</div>'; } ?>
	</div>
</section>

<?php
	$rel = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3, 'post__not_in' => array( get_the_ID() ), 'category__in' => wp_list_pluck( $cats, 'term_id' ), 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
	if ( $rel->have_posts() ) :
?>
<section class="sec" style="padding-top:0">
	<div class="wrap">
		<div class="shead"><div><span class="label"><i></i><?php esc_html_e( 'Keep reading', 'deskman-studio' ); ?></span><h2 class="h2"><?php echo dm_h( 'More from {the studio.}' ); // phpcs:ignore ?></h2></div></div>
		<div class="works"><?php while ( $rel->have_posts() ) : $rel->the_post(); get_template_part( 'template-parts/post-card' ); endwhile; wp_reset_postdata(); ?></div>
	</div>
</section>
<?php endif; endwhile; ?>
<?php get_footer(); ?>
