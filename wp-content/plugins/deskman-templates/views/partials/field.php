<?php
/**
 * Single customiser field, rendered from the template's schema.
 *
 * Variables: $dmt_key, $dmt_field, $dmt_context ('panel' | 'quick').
 *
 * Inputs share a data-field attribute; the customiser script keeps every
 * input for the same key in sync, so the quick-start dialog and the panel
 * edit the same state.
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

$dmt_id      = 'dmt-' . $dmt_context . '-' . $dmt_key;
$dmt_help_id = $dmt_id . '-help';
$dmt_err_id  = $dmt_id . '-err';
$dmt_desc    = trim( ( $dmt_field['help'] ? $dmt_help_id : '' ) . ' ' . $dmt_err_id );
$dmt_type    = $dmt_field['type'];
?>
<div class="dmt-field dmt-field--<?php echo esc_attr( $dmt_type ); ?>">
	<?php if ( 'logo' === $dmt_type ) : ?>
		<span class="dmt-field__label" id="<?php echo esc_attr( $dmt_id . '-label' ); ?>"><?php echo esc_html( $dmt_field['label'] ); ?></span>
		<div class="dmt-logo" data-dmt-logo-field>
			<div class="dmt-logo__preview" data-dmt-logo-preview aria-hidden="true">
				<?php echo DMT_Frontend::icon( 'upload' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<div class="dmt-logo__controls">
				<label class="dmt-btn dmt-btn--soft dmt-btn--sm dmt-logo__pick" for="<?php echo esc_attr( $dmt_id ); ?>">
					<span data-dmt-logo-pick-label><?php esc_html_e( 'Upload logo', 'deskman-templates' ); ?></span>
				</label>
				<input class="dmt-logo__input" id="<?php echo esc_attr( $dmt_id ); ?>" type="file" accept="image/png,image/jpeg,image/webp" data-dmt-logo-input aria-labelledby="<?php echo esc_attr( $dmt_id . '-label' ); ?>" aria-describedby="<?php echo esc_attr( $dmt_desc ); ?>">
				<button type="button" class="dmt-btn dmt-btn--link dmt-btn--sm" data-dmt-logo-remove hidden><?php esc_html_e( 'Remove', 'deskman-templates' ); ?></button>
			</div>
		</div>
	<?php elseif ( 'color' === $dmt_type ) : ?>
		<label for="<?php echo esc_attr( $dmt_id ); ?>"><?php echo esc_html( $dmt_field['label'] ); ?></label>
		<div class="dmt-color">
			<input id="<?php echo esc_attr( $dmt_id ); ?>" type="color" data-field="<?php echo esc_attr( $dmt_key ); ?>" value="<?php echo esc_attr( strtolower( $dmt_field['default'] ) ); ?>" aria-describedby="<?php echo esc_attr( $dmt_desc ); ?>">
			<input class="dmt-color__hex" type="text" data-field="<?php echo esc_attr( $dmt_key ); ?>" data-hex value="<?php echo esc_attr( $dmt_field['default'] ); ?>" maxlength="7" spellcheck="false" autocomplete="off" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: field label. */ __( '%s (hex code)', 'deskman-templates' ), $dmt_field['label'] ) ); ?>" aria-describedby="<?php echo esc_attr( $dmt_err_id ); ?>">
		</div>
	<?php else : ?>
		<label for="<?php echo esc_attr( $dmt_id ); ?>"><?php echo esc_html( $dmt_field['label'] ); ?></label>
		<?php if ( 'textarea' === $dmt_type ) : ?>
			<textarea id="<?php echo esc_attr( $dmt_id ); ?>" data-field="<?php echo esc_attr( $dmt_key ); ?>" rows="<?php echo esc_attr( $dmt_field['rows'] ); ?>" maxlength="<?php echo esc_attr( $dmt_field['max'] ); ?>" placeholder="<?php echo esc_attr( $dmt_field['default'] ); ?>" aria-describedby="<?php echo esc_attr( $dmt_desc ); ?>"><?php echo esc_textarea( $dmt_field['default'] ); ?></textarea>
		<?php else : ?>
			<input
				id="<?php echo esc_attr( $dmt_id ); ?>"
				type="<?php echo esc_attr( in_array( $dmt_type, array( 'email', 'tel', 'url' ), true ) ? $dmt_type : 'text' ); ?>"
				data-field="<?php echo esc_attr( $dmt_key ); ?>"
				value="<?php echo esc_attr( $dmt_field['default'] ); ?>"
				placeholder="<?php echo esc_attr( $dmt_field['default'] ? $dmt_field['default'] : ( 'url' === $dmt_type ? 'https://' : '' ) ); ?>"
				maxlength="<?php echo esc_attr( $dmt_field['max'] ); ?>"
				<?php echo ! empty( $dmt_field['autocomplete'] ) ? 'autocomplete="' . esc_attr( $dmt_field['autocomplete'] ) . '"' : ''; ?>
				aria-describedby="<?php echo esc_attr( $dmt_desc ); ?>"
			>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( $dmt_field['help'] ) : ?>
		<p class="dmt-field__help" id="<?php echo esc_attr( $dmt_help_id ); ?>"><?php echo esc_html( $dmt_field['help'] ); ?></p>
	<?php endif; ?>
	<p class="dmt-field__error" id="<?php echo esc_attr( $dmt_err_id ); ?>" data-field-error="<?php echo esc_attr( $dmt_key ); ?>" role="alert"></p>
</div>
