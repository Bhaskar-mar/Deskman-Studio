<?php
/**
 * Design Name: Default location page
 * Description: The standard area page — hero, intro, services, localities, why-us, FAQ, related areas and CTA, in the studio's own visual language.
 * Cities:
 *
 * This is the design every area page uses unless a city-specific design folder
 * exists. It is a complete template: it calls get_header() and get_footer()
 * itself, exactly as the file it replaces did, so the page keeps the real site
 * nav, mega menu, footer and stylesheets.
 *
 * To build a custom design for one city, copy this whole folder to
 * area-designs/{city-slug}/ and edit it. See AREA-DESIGN-GUIDE.md.
 *
 * Bundled with the plugin rather than the theme so the feature travels with
 * the thing that creates the content — an area page renders correctly the
 * moment the plugin is active. The theme can still take it over completely by
 * defining its own single-deskman_area.php, which template_include checks for
 * first.
 *
 * get_header() and get_footer() are used deliberately: the page picks up the
 * real site nav, the mega menu, the footer and the theme's own stylesheets, so
 * a location page looks like the rest of the site rather than a landing page
 * bolted onto the side of it. Everything specific to this template is
 * namespaced .dsa- in assets/areas.css and leans on the theme's design tokens,
 * with fallbacks for when the plugin is used under a different theme.
 *
 * Every section is skipped when its field is empty, so a half-filled page
 * renders as a shorter page, never a broken one.
 *
 * @package Deskman_Studio_Manager
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

while ( have_posts() ) :
	the_post();
	$id = get_the_ID();

	$city       = deskman_area_field( $id, 'city' );
	$region     = deskman_area_field( $id, 'region', deskman_area_opt( 'region' ) );
	$service    = deskman_area_field( $id, 'service', deskman_area_opt( 'service' ) );
	$phone      = deskman_area_field( $id, 'phone', deskman_area_opt( 'phone' ) );
	$localities = deskman_area_list( deskman_area_field( $id, 'localities' ) );
	$contact    = deskman_url( 'contact' );
	?>

<main id="top" class="dsa-main">

	<section class="dsa-hero">
		<div class="wrap">
			<nav class="dsa-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'deskman-studio-manager' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'deskman-studio-manager' ); ?></a>
				<span aria-hidden="true">/</span>
				<?php $archive = get_post_type_archive_link( DESKMAN_AREA_CPT ); ?>
				<?php if ( $archive ) : ?>
					<a href="<?php echo esc_url( $archive ); ?>"><?php esc_html_e( 'Locations', 'deskman-studio-manager' ); ?></a>
					<span aria-hidden="true">/</span>
				<?php endif; ?>
				<span aria-current="page"><?php echo esc_html( $city ? $city : get_the_title() ); ?></span>
			</nav>

			<?php $eyebrow = deskman_area_field( $id, 'hero_eyebrow' ); ?>
			<?php if ( $eyebrow ) : ?>
				<p class="dsa-eyebrow"><span class="dsa-dot" aria-hidden="true"></span><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<h1 class="dsa-h1"><?php echo esc_html( deskman_area_field( $id, 'hero_h', get_the_title() ) ); ?></h1>

			<?php $sub = deskman_area_field( $id, 'hero_sub' ); ?>
			<?php if ( $sub ) : ?>
				<p class="dsa-lead"><?php echo esc_html( $sub ); ?></p>
			<?php endif; ?>

			<div class="dsa-actions">
				<a class="dsa-btn dsa-btn-solid" href="<?php echo esc_url( $contact ); ?>">
					<?php echo esc_html( deskman_area_field( $id, 'cta_label', __( 'Start a project', 'deskman-studio-manager' ) ) ); ?>
					<span aria-hidden="true">&rarr;</span>
				</a>
				<?php if ( $phone ) : ?>
					<a class="dsa-btn dsa-btn-ghost" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>">
						<?php echo esc_html( $phone ); ?>
					</a>
				<?php endif; ?>
			</div>

			<?php $stats = deskman_area_pairs( deskman_area_field( $id, 'stats' ) ); ?>
			<?php if ( $stats ) : ?>
				<ul class="dsa-stats">
					<?php foreach ( $stats as $s ) : ?>
						<li>
							<span class="dsa-stat-v"><?php echo esc_html( $s['t'] ); ?></span>
							<?php if ( $s['d'] !== '' ) : ?>
								<span class="dsa-stat-l"><?php echo esc_html( $s['d'] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</section>

	<?php $intro = deskman_area_field( $id, 'intro_body' ); ?>
	<?php if ( $intro ) : ?>
		<section class="dsa-sec dsa-intro">
			<div class="wrap dsa-split">
				<div class="dsa-split-head">
					<h2 class="dsa-h2"><?php echo esc_html( deskman_area_field( $id, 'intro_h' ) ); ?></h2>
				</div>
				<div class="dsa-prose">
					<?php
					/* The field holds plain text with blank lines between
					   paragraphs. Split on those and escape each one, rather
					   than storing markup in the database. */
					foreach ( preg_split( '/\n\s*\n/', $intro ) as $para ) {
						$para = trim( $para );
						if ( $para === '' ) { continue; }
						echo '<p>' . esc_html( $para ) . '</p>';
					}
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php $services = deskman_area_pairs( deskman_area_field( $id, 'services' ) ); ?>
	<?php if ( $services ) : ?>
		<section class="dsa-sec dsa-services">
			<div class="wrap">
				<h2 class="dsa-h2 dsa-h2-c"><?php echo esc_html( deskman_area_field( $id, 'services_h', __( 'What we do', 'deskman-studio-manager' ) ) ); ?></h2>
				<div class="dsa-grid">
					<?php foreach ( $services as $i => $s ) : ?>
						<article class="dsa-card">
							<span class="dsa-num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
							<h3><?php echo esc_html( $s['t'] ); ?></h3>
							<?php if ( $s['d'] !== '' ) : ?><p><?php echo esc_html( $s['d'] ); ?></p><?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php $why = deskman_area_pairs( deskman_area_field( $id, 'why' ) ); ?>
	<?php if ( $why ) : ?>
		<section class="dsa-sec dsa-why">
			<div class="wrap">
				<h2 class="dsa-h2"><?php echo esc_html( deskman_area_field( $id, 'why_h', __( 'Why work with us', 'deskman-studio-manager' ) ) ); ?></h2>
				<ul class="dsa-rows">
					<?php foreach ( $why as $w ) : ?>
						<li>
							<h3><?php echo esc_html( $w['t'] ); ?></h3>
							<?php if ( $w['d'] !== '' ) : ?><p><?php echo esc_html( $w['d'] ); ?></p><?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php $process = deskman_area_pairs( deskman_area_field( $id, 'process' ) ); ?>
	<?php if ( $process ) : ?>
		<section class="dsa-sec dsa-process">
			<div class="wrap">
				<h2 class="dsa-h2 dsa-h2-c"><?php echo esc_html( deskman_area_field( $id, 'process_h', __( 'How we work', 'deskman-studio-manager' ) ) ); ?></h2>
				<ol class="dsa-steps">
					<?php foreach ( $process as $i => $p ) : ?>
						<li>
							<span class="dsa-step-n"><?php echo esc_html( (string) ( $i + 1 ) ); ?></span>
							<h3><?php echo esc_html( $p['t'] ); ?></h3>
							<?php if ( $p['d'] !== '' ) : ?><p><?php echo esc_html( $p['d'] ); ?></p><?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $localities ) : ?>
		<section class="dsa-sec dsa-localities">
			<div class="wrap">
				<h2 class="dsa-h2 dsa-h2-c">
					<?php
					printf(
						/* translators: %s: city name. */
						esc_html__( 'Working across %s', 'deskman-studio-manager' ),
						esc_html( $city )
					);
					?>
				</h2>
				<ul class="dsa-chips">
					<?php foreach ( $localities as $loc ) : ?>
						<li><?php echo esc_html( $loc ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<?php $faqs = deskman_area_pairs( deskman_area_field( $id, 'faq' ) ); ?>
	<?php if ( $faqs ) : ?>
		<section class="dsa-sec dsa-faq">
			<div class="wrap dsa-split">
				<div class="dsa-split-head">
					<h2 class="dsa-h2"><?php echo esc_html( deskman_area_field( $id, 'faq_h', __( 'Common questions', 'deskman-studio-manager' ) ) ); ?></h2>
				</div>
				<div class="dsa-faq-list">
					<?php foreach ( $faqs as $f ) : ?>
						<details class="dsa-q">
							<summary><?php echo esc_html( $f['t'] ); ?></summary>
							<?php if ( $f['d'] !== '' ) : ?><p><?php echo esc_html( $f['d'] ); ?></p><?php endif; ?>
						</details>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	/* Any extra content typed into the normal WordPress editor is printed
	   here, so a page can carry a case study, an embed or a table without
	   needing a new field for it. */
	$extra = trim( (string) get_the_content() );
	if ( $extra !== '' ) :
		?>
		<section class="dsa-sec">
			<div class="wrap"><div class="dsa-prose dsa-prose-wide"><?php the_content(); ?></div></div>
		</section>
	<?php endif; ?>

	<section class="dsa-cta">
		<div class="wrap">
			<h2 class="dsa-cta-h"><?php echo esc_html( deskman_area_field( $id, 'cta_h', __( 'Let\'s talk about your project', 'deskman-studio-manager' ) ) ); ?></h2>
			<?php $cta_sub = deskman_area_field( $id, 'cta_sub' ); ?>
			<?php if ( $cta_sub ) : ?><p class="dsa-cta-p"><?php echo esc_html( $cta_sub ); ?></p><?php endif; ?>
			<div class="dsa-actions dsa-actions-c">
				<a class="dsa-btn dsa-btn-solid" href="<?php echo esc_url( $contact ); ?>">
					<?php echo esc_html( deskman_area_field( $id, 'cta_btn', __( 'Start a project', 'deskman-studio-manager' ) ) ); ?>
					<span aria-hidden="true">&rarr;</span>
				</a>
				<?php $email = trim( (string) deskman_opt( 'contact_email', '' ) ); ?>
				<?php if ( $email ) : ?>
					<a class="dsa-btn dsa-btn-ghost dsa-btn-light" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
				<?php endif; ?>
			</div>

			<?php $map = trim( (string) deskman_area_field( $id, 'map' ) ); ?>
			<?php if ( $map ) : ?>
				<div class="dsa-map">
					<iframe src="<?php echo esc_url( $map ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
						title="<?php echo esc_attr( sprintf( /* translators: %s: city name. */ __( 'Map of %s', 'deskman-studio-manager' ), $city ) ); ?>"></iframe>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php
	$related = ( deskman_area_opt( 'related' ) === '1' )
		? deskman_area_related( $id, (int) deskman_area_opt( 'related_max' ) )
		: array();
	if ( $related ) :
		?>
		<section class="dsa-sec dsa-related">
			<div class="wrap">
				<h2 class="dsa-h3"><?php esc_html_e( 'We also work in', 'deskman-studio-manager' ); ?></h2>
				<ul class="dsa-related-list">
					<?php foreach ( $related as $r ) : ?>
						<li>
							<a href="<?php echo esc_url( get_permalink( $r ) ); ?>">
								<span class="dsa-related-city"><?php echo esc_html( deskman_area_field( $r->ID, 'city', get_the_title( $r ) ) ); ?></span>
								<span class="dsa-related-svc"><?php echo esc_html( deskman_area_field( $r->ID, 'service' ) ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

</main>

<?php
endwhile;

get_footer();
