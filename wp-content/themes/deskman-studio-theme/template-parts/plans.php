<?php
/**
 * Package cards, two sets switched by the "New / Existing website" control.
 * Each button opens the contact form with the plan pre-selected (?plan=).
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$pk = isset( $args['pk'] ) ? $args['pk'] : dm_packages();
foreach ( array( 'new', 'existing' ) as $set ) :
?>
<div class="plans" data-set="<?php echo esc_attr( $set ); ?>"<?php echo 'new' === $set ? '' : ' hidden'; ?>>
	<?php foreach ( $pk[ $set ] as $plan ) : $url = add_query_arg( 'plan', rawurlencode( $plan['name'] ), dm_link( 'contact' ) ); ?>
		<div class="plan<?php echo $plan['pop'] ? ' pop' : ''; ?> rv">
			<div class="top"><h3><?php echo esc_html( $plan['name'] ); ?></h3><?php if ( $plan['pop'] ) : ?><span class="badge"><?php esc_html_e( 'Most chosen', 'deskman-studio' ); ?></span><?php else : ?><span class="ico"><?php echo dm_i( $plan['icon'] ); // phpcs:ignore ?></span><?php endif; ?></div>
			<div class="price"><b><?php echo esc_html( $plan['scope'] ); ?></b><?php echo esc_html( $plan['note'] ); ?></div>
			<p><?php echo esc_html( $plan['for'] ); ?></p>
			<a class="btn <?php echo $plan['pop'] ? 'btn--v' : 'btn--w'; ?>" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Get a quote', 'deskman-studio' ); ?><?php echo $plan['pop'] ? ' ' . dm_i( 'arrow' ) : ''; // phpcs:ignore ?></a>
			<ul><?php foreach ( $plan['items'] as $it ) : ?><li class="check"><?php echo dm_i( 'check' ); // phpcs:ignore ?><?php echo esc_html( $it ); ?></li><?php endforeach; ?></ul>
		</div>
	<?php endforeach; ?>
</div>
<?php endforeach; ?>
