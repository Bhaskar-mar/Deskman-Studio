<?php
/**
 * Area pages index.
 *
 * The one place every location page is listed. It is not in the navigation
 * either — it exists so the set has a single crawlable hub, so a sitemap
 * submission has somewhere sensible to point, and so you have one URL to hand
 * to a directory listing or an ad campaign.
 *
 * @package Deskman_Studio_Manager
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>
<main id="top" class="dsa-main">

	<section class="dsa-hero dsa-hero-short">
		<div class="wrap">
			<p class="dsa-eyebrow"><span class="dsa-dot" aria-hidden="true"></span><?php esc_html_e( 'Where we work', 'deskman-studio-manager' ); ?></p>
			<h1 class="dsa-h1"><?php esc_html_e( 'Locations', 'deskman-studio-manager' ); ?></h1>
			<p class="dsa-lead">
				<?php esc_html_e( 'The cities and regions we work with. Pick a place to see what we do there and how to get in touch.', 'deskman-studio-manager' ); ?>
			</p>
		</div>
	</section>

	<section class="dsa-sec">
		<div class="wrap">
			<?php if ( have_posts() ) : ?>
				<ul class="dsa-related-list dsa-related-list-lg">
					<?php while ( have_posts() ) : the_post(); $id = get_the_ID(); ?>
						<li>
							<a href="<?php the_permalink(); ?>">
								<span class="dsa-related-city"><?php echo esc_html( deskman_area_field( $id, 'city', get_the_title() ) ); ?></span>
								<span class="dsa-related-svc"><?php echo esc_html( deskman_area_field( $id, 'service' ) ); ?></span>
							</a>
						</li>
					<?php endwhile; ?>
				</ul>

				<?php
				the_posts_pagination( array(
					'mid_size'  => 2,
					'prev_text' => esc_html__( 'Previous', 'deskman-studio-manager' ),
					'next_text' => esc_html__( 'Next', 'deskman-studio-manager' ),
				) );
				?>
			<?php else : ?>
				<p class="dsa-lead"><?php esc_html_e( 'No location pages have been published yet.', 'deskman-studio-manager' ); ?></p>
			<?php endif; ?>
		</div>
	</section>

</main>
<?php
get_footer();
