<?php
/**
 * Insights: the blog index, category/tag/author/date archives and search.
 * (home.php, archive.php and search.php all load this file.)
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

if ( is_search() ) {
	/* translators: %s: search query */
	$h = sprintf( __( 'Results for {%s}', 'deskman-studio' ), get_search_query() );
	$lead = '';
} elseif ( is_archive() ) {
	$h = get_the_archive_title() . '|{' . __( 'from the studio.', 'deskman-studio' ) . '}';
	$lead = wp_strip_all_tags( get_the_archive_description() );
} else {
	$h = dm_opt( 'v5_blog_h', 'Notes on building {better WordPress sites.}' );
	$lead = dm_opt( 'v5_blog_lead', 'Practical guides on WordPress, SEO, performance and running a website that keeps working for your business.' );
}
$cats = get_categories( array( 'hide_empty' => true ) );
$blog = dm_link( 'insights' );
?>
<div class="dark">
<?php get_template_part( 'template-parts/phero', null, array( 'crumb' => __( 'Insights', 'deskman-studio' ), 'h' => $h, 'lead' => $lead ) ); ?>
</div>

<section class="sec" style="padding-top:56px">
	<div class="wrap">
		<?php if ( count( $cats ) > 1 ) : ?>
			<nav class="filters" aria-label="<?php esc_attr_e( 'Categories', 'deskman-studio' ); ?>" style="margin-bottom:28px">
				<a class="<?php echo ( is_home() ? 'on' : '' ); ?>" href="<?php echo esc_url( $blog ); ?>" style="height:36px;display:inline-flex;align-items:center;padding:0 14px;border-radius:10px;border:1px solid var(--line);background:<?php echo is_home() ? 'var(--ink);color:#fff' : '#fff'; ?>;font-size:13.5px;font-weight:500"><?php esc_html_e( 'All', 'deskman-studio' ); ?></a>
				<?php foreach ( $cats as $c ) : $on = is_category( $c->term_id ); ?>
					<a href="<?php echo esc_url( get_category_link( $c ) ); ?>"<?php echo $on ? ' aria-current="page"' : ''; ?> style="height:36px;display:inline-flex;align-items:center;padding:0 14px;border-radius:10px;border:1px solid <?php echo $on ? 'var(--ink)' : 'var(--line)'; ?>;background:<?php echo $on ? 'var(--ink);color:#fff' : '#fff'; ?>;font-size:13.5px;font-weight:500;white-space:nowrap"><?php echo esc_html( $c->name ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="works">
				<?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/post-card' ); endwhile; ?>
			</div>
			<nav class="pager" aria-label="<?php esc_attr_e( 'Pages', 'deskman-studio' ); ?>"><?php echo paginate_links( array( 'prev_text' => dm_i( 'arrow-left' ) . '<span class="screen-reader-text">' . __( 'Previous', 'deskman-studio' ) . '</span>', 'next_text' => dm_i( 'arrow' ) . '<span class="screen-reader-text">' . __( 'Next', 'deskman-studio' ) . '</span>' ) ); // phpcs:ignore ?></nav>
		<?php else : ?>
			<div class="empty"><b style="display:block;font-size:18px;color:var(--ink)"><?php esc_html_e( 'Nothing here yet.', 'deskman-studio' ); ?></b><p style="margin-top:6px"><?php esc_html_e( 'New articles are on the way. Meanwhile, see what we\'ve built.', 'deskman-studio' ); ?></p><a class="btn btn--k" href="<?php echo esc_url( dm_link( 'work' ) ); ?>" style="margin-top:18px"><?php esc_html_e( 'View our work', 'deskman-studio' ); ?></a></div>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/cta', null, array( 'style' => 'padding-top:0' ) ); ?>
<?php get_footer(); ?>
