<?php
/**
 * Template Name: Packages
 * Used for the "pricing" page (and a "packages" page if one exists).
 * No prices are published: every package leads to a scoped, fixed quote.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
$pk = dm_packages();
?>
<div class="dark">
<?php get_template_part( 'template-parts/phero', null, array(
	'crumb' => __( 'Packages', 'deskman-studio' ),
	'h'     => dm_opt( 'v5_pkp_h', 'Clear packages. {Honest, fixed quotes.}' ),
	'lead'  => dm_opt( 'v5_pkp_lead', 'Every project is scoped to what you actually need. Choose a starting point, book a short call, and get a fixed quote — no surprises later.' ),
) ); ?>
</div>

<section class="sec" style="padding-top:56px">
	<div class="wrap">
		<div style="display:flex;justify-content:center;margin-bottom:32px"><span class="seg" role="tablist"><button type="button" class="on" data-seg="new" aria-selected="true"><?php esc_html_e( 'New website', 'deskman-studio' ); ?></button><button type="button" data-seg="existing" aria-selected="false"><?php esc_html_e( 'Existing website', 'deskman-studio' ); ?></button></span></div>
		<?php get_template_part( 'template-parts/plans', null, array( 'pk' => $pk ) ); ?>
	</div>
</section>

<section class="sec" style="padding-top:0">
	<div class="wrap">
		<div class="shead rv"><div><span class="label"><i></i><?php esc_html_e( 'Compare', 'deskman-studio' ); ?></span><h2 class="h2"><?php echo dm_h( 'What\'s in {each package.}' ); // phpcs:ignore ?></h2></div></div>
		<div class="card rv" style="overflow-x:auto">
			<table class="tbl" style="font-size:14px;min-width:640px">
				<thead><tr style="background:var(--bg)"><th scope="col" style="text-align:left;color:var(--muted);font-family:var(--m);font-size:11.5px;font-weight:500;letter-spacing:.06em;text-transform:uppercase;padding:16px 24px"><?php esc_html_e( 'Feature', 'deskman-studio' ); ?></th>
					<?php foreach ( $pk['new'] as $plan ) : ?><th scope="col" style="text-align:left;padding:16px 24px;font-weight:600;color:<?php echo $plan['pop'] ? 'var(--v)' : 'var(--ink)'; ?>"><?php echo esc_html( $plan['name'] ); ?></th><?php endforeach; ?></tr></thead>
				<tbody>
				<?php
				$rows = array(
					array( 'Custom design', 'y', 'y', 'y' ),
					array( 'Custom theme & editable blocks', '-', 'y', 'y' ),
					array( 'Enquiry forms & lead inbox', 'y', 'y', 'y' ),
					array( 'Technical & local SEO', 'On-page', 'y', 'y' ),
					array( 'Custom plugins & integrations', '-', 'Optional', 'y' ),
					array( 'WooCommerce / bookings / memberships', '-', 'Optional', 'y' ),
					array( 'Team training & written guide', '-', 'y', 'y' ),
				);
				foreach ( $rows as $r ) : ?>
					<tr><td style="padding:14px 24px"><?php echo esc_html( $r[0] ); ?></td>
						<?php for ( $c = 1; $c <= 3; $c++ ) : ?><td style="padding:14px 24px"><?php
							if ( 'y' === $r[ $c ] ) { echo '<span style="color:var(--v)">' . dm_i( 'check' ) . '<span class="screen-reader-text">' . esc_html__( 'Included', 'deskman-studio' ) . '</span></span>'; } // phpcs:ignore
							elseif ( '-' === $r[ $c ] ) { echo '<span style="color:var(--faint)" aria-label="' . esc_attr__( 'Not included', 'deskman-studio' ) . '">—</span>'; }
							else { echo esc_html( $r[ $c ] ); }
						?></td><?php endfor; ?></tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</section>

<section class="sec dark" style="overflow:hidden"><i class="gridbg"></i>
	<div class="wrap" style="position:relative">
		<div class="shead rv"><div><span class="label"><i></i><?php esc_html_e( 'Care plans', 'deskman-studio' ); ?></span><h2 class="h2"><?php echo dm_hd( 'Already have a site? {We\'ll look after it.}' ); // phpcs:ignore ?></h2></div><a class="btn btn--l btn--lg" href="<?php echo esc_url( add_query_arg( 'plan', rawurlencode( 'Care plan' ), dm_link( 'contact' ) ) ); ?>"><?php esc_html_e( 'Ask about care plans', 'deskman-studio' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a></div>
		<div class="std">
			<?php foreach ( array_slice( dm_services()['care-plans']['features'], 0, 4 ) as $f ) : ?>
				<div class="dcard rv"><span class="ico"><?php echo dm_i( $f[0] ); // phpcs:ignore ?></span><h3 style="font-size:16px;margin-top:16px"><?php echo esc_html( $f[1] ); ?></h3><p style="font-size:13.5px;margin-top:6px"><?php echo esc_html( $f[2] ); ?></p></div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="sec">
	<div class="wrap">
		<div class="shead rv"><div><span class="label"><i></i>FAQ</span><h2 class="h2"><?php echo dm_h( 'Questions about {pricing.}' ); // phpcs:ignore ?></h2></div></div>
		<?php get_template_part( 'template-parts/faq', null, array( 'items' => dm_faq() ) ); ?>
	</div>
</section>

<?php get_template_part( 'template-parts/cta', null, array( 'style' => 'padding-top:0' ) ); ?>
<?php get_footer(); ?>
