<?php
/**
 * Reusable industry card.
 *
 * Variables: $industry (array from DMT_Registry::industries()).
 *
 * Available industries link to their customiser. Coming-soon industries
 * render as a button that only shows an explanatory message, so they can
 * never open a non-existent template.
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

$dmt_card_id = 'dmt-card-' . $industry['slug'];
$dmt_thumb   = DMT_Registry::image_url( $industry['thumbnail'] );
?>
<?php if ( $industry['available'] ) : ?>
	<a class="dmt-card dmt-card--live" id="<?php echo esc_attr( $dmt_card_id ); ?>" href="<?php echo esc_url( DMT_Routes::industry_url( $industry['slug'] ) ); ?>" aria-describedby="<?php echo esc_attr( $dmt_card_id . '-desc' ); ?>">
		<span class="dmt-card__media">
			<?php if ( $dmt_thumb ) : ?>
				<img src="<?php echo esc_url( $dmt_thumb ); ?>" alt="" width="640" height="400" loading="lazy" decoding="async">
			<?php endif; ?>
			<span class="dmt-badge dmt-badge--live"><span class="dmt-badge__dot"></span><?php esc_html_e( 'Live template', 'deskman-templates' ); ?></span>
		</span>
		<span class="dmt-card__body">
			<span class="dmt-card__title"><?php echo esc_html( $industry['name'] ); ?></span>
			<span class="dmt-card__desc" id="<?php echo esc_attr( $dmt_card_id . '-desc' ); ?>"><?php echo esc_html( $industry['summary'] ); ?></span>
			<span class="dmt-card__cta"><?php esc_html_e( 'Customise & preview', 'deskman-templates' ); ?><?php echo DMT_Frontend::icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</span>
	</a>
<?php else : ?>
	<button type="button" class="dmt-card dmt-card--soon" id="<?php echo esc_attr( $dmt_card_id ); ?>" data-dmt-soon="<?php echo esc_attr( $industry['name'] ); ?>" aria-describedby="<?php echo esc_attr( $dmt_card_id . '-desc' ); ?>">
		<span class="dmt-card__media">
			<?php if ( $dmt_thumb ) : ?>
				<img src="<?php echo esc_url( $dmt_thumb ); ?>" alt="" width="640" height="400" loading="lazy" decoding="async">
			<?php endif; ?>
			<span class="dmt-badge dmt-badge--soon"><?php esc_html_e( 'Coming soon', 'deskman-templates' ); ?></span>
		</span>
		<span class="dmt-card__body">
			<span class="dmt-card__title"><?php echo esc_html( $industry['name'] ); ?></span>
			<span class="dmt-card__desc" id="<?php echo esc_attr( $dmt_card_id . '-desc' ); ?>"><?php echo esc_html( $industry['summary'] ); ?></span>
			<span class="dmt-card__cta dmt-card__cta--muted"><?php esc_html_e( 'Templates in preparation', 'deskman-templates' ); ?></span>
		</span>
	</button>
<?php endif; ?>
