<?php
/**
 * Any other page (privacy, terms, custom pages): designed header + the
 * page's block-editor content.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

while ( have_posts() ) : the_post();
?>
<div class="dark">
<?php get_template_part( 'template-parts/phero', null, array(
	'crumb' => get_the_title(),
	'h'     => get_the_title(),
	'lead'  => has_excerpt() ? get_the_excerpt() : '',
	'after' => '<p class="mono" style="margin-top:18px;color:var(--df)">' . esc_html( sprintf( /* translators: %s: date */ __( 'Updated %s', 'deskman-studio' ), get_the_modified_date() ) ) . '</p>',
) ); ?>
</div>
<section class="sec">
	<div class="wrap" style="max-width:820px">
		<article class="prose" style="margin:0 auto"><?php the_content(); ?></article>
		<?php wp_link_pages( array( 'before' => '<nav class="pager">', 'after' => '</nav>' ) ); ?>
	</div>
</section>
<?php endwhile; ?>
<?php get_footer(); ?>
