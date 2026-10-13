<?php
/**
 * Template Name: Work
 * The portfolio: every published project, with category filters.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

$projects = dm_projects( 60 );
$counts   = array();
foreach ( $projects as $p ) {
	foreach ( (array) $p['cats'] as $c ) { $k = sanitize_title( $c ); $counts[ $k ] = array( $c, isset( $counts[ $k ] ) ? $counts[ $k ][1] + 1 : 1 ); }
}
?>
<div class="dark">
<?php get_template_part( 'template-parts/phero', null, array(
	'crumb' => __( 'Work', 'deskman-studio' ),
	'h'     => dm_opt( 'v5_work_page_h', 'Websites and platforms, {live and working.}' ),
	'lead'  => dm_opt( 'v5_work_page_lead', 'A selection of projects we\'ve designed and built — from WooCommerce stores to school websites and city-wide platforms.' ),
) ); ?>
</div>

<section class="sec" style="padding-top:56px">
	<div class="wrap">
		<?php if ( count( $counts ) > 1 ) : ?>
			<div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:28px">
				<div class="filters" role="group" aria-label="<?php esc_attr_e( 'Filter projects', 'deskman-studio' ); ?>" data-filter=".works">
					<button type="button" class="on" data-f="*" aria-pressed="true"><?php esc_html_e( 'All', 'deskman-studio' ); ?> <em><?php echo count( $projects ); ?></em></button>
					<?php foreach ( $counts as $k => $c ) : ?><button type="button" data-f="<?php echo esc_attr( $k ); ?>" aria-pressed="false"><?php echo esc_html( $c[0] ); ?> <em><?php echo (int) $c[1]; ?></em></button><?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="works" style="grid-template-columns:repeat(2,1fr);gap:24px">
			<?php foreach ( $projects as $p ) { get_template_part( 'template-parts/work-card', null, array( 'p' => $p ) ); } ?>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/cta', null, array( 'style' => 'padding-top:0' ) ); ?>
<?php get_footer(); ?>
