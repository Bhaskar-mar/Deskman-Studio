<?php
/**
 * Template customiser: personalisation panel, live device preview and the
 * "Start My Project" enquiry form.
 *
 * A complete template (calls get_header() / get_footer()) so the page keeps
 * the real site nav and footer. The form is generated from the template's
 * field schema, so a new template gets a working customiser just by shipping
 * its own fields.php.
 *
 * Override: copy to {theme}/site-templates/views/customiser.php.
 *
 * @package Deskman_Studio_Manager
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

list( , $industry ) = deskman_tpl_view();
$template = deskman_tpl_template( $industry['template'] );
$groups   = deskman_tpl_groups( $template['id'] );

get_header();

$dmt_preview_url = deskman_tpl_preview_url( $industry['slug'] );
$dmt_devices     = array(
	'mobile'  => array( __( 'Mobile', 'deskman-studio-manager' ), 390, 844 ),
	'tablet'  => array( __( 'Tablet', 'deskman-studio-manager' ), 768, 1024 ),
	'laptop'  => array( __( 'Laptop', 'deskman-studio-manager' ), 1366, 768 ),
	'desktop' => array( __( 'Desktop', 'deskman-studio-manager' ), 1440, 900 ),
);
$dmt_quick       = array();
foreach ( $groups as $dmt_group ) {
	foreach ( $dmt_group['fields'] as $dmt_key => $dmt_field ) {
		if ( $dmt_field['quick'] ) {
			$dmt_quick[ $dmt_key ] = $dmt_field;
		}
	}
}
$dmt_privacy     = get_page_by_path( 'privacy-policy' );
$dmt_privacy_url = ( $dmt_privacy && 'publish' === $dmt_privacy->post_status ) ? get_permalink( $dmt_privacy ) : get_privacy_policy_url();
?>
<div class="dmt dmt-cz" data-dmt-customiser data-tab="edit">

	<div class="dmt-cz__bar">
		<a class="dmt-cz__back" href="<?php echo esc_url( deskman_tpl_gallery_url() ); ?>">
			<?php echo deskman_tpl_icon( 'arrow-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span><?php esc_html_e( 'All templates', 'deskman-studio-manager' ); ?></span>
		</a>
		<div class="dmt-cz__heading">
			<h1 class="dmt-cz__title"><?php echo esc_html( $template['name'] ); ?></h1>
			<p class="dmt-cz__subtitle"><?php echo esc_html( $industry['name'] ); ?> · <?php esc_html_e( 'Live customiser', 'deskman-studio-manager' ); ?></p>
		</div>

		<div class="dmt-devices" role="radiogroup" aria-label="<?php esc_attr_e( 'Preview device size', 'deskman-studio-manager' ); ?>">
			<?php foreach ( $dmt_devices as $dmt_id => $dmt_device ) : ?>
				<button type="button" class="dmt-devices__btn" role="radio" aria-checked="<?php echo 'laptop' === $dmt_id ? 'true' : 'false'; ?>" tabindex="<?php echo 'laptop' === $dmt_id ? '0' : '-1'; ?>" data-device="<?php echo esc_attr( $dmt_id ); ?>" data-width="<?php echo esc_attr( $dmt_device[1] ); ?>" data-height="<?php echo esc_attr( $dmt_device[2] ); ?>" data-label="<?php echo esc_attr( $dmt_device[0] ); ?>" title="<?php echo esc_attr( $dmt_device[0] . ' · ' . $dmt_device[1] . 'px' ); ?>">
					<?php echo deskman_tpl_icon( $dmt_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="dmt-devices__label"><?php echo esc_html( $dmt_device[0] ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>

		<div class="dmt-cz__actions">
			<button type="button" class="dmt-btn dmt-btn--glass dmt-btn--sm" data-dmt-reset>
				<?php echo deskman_tpl_icon( 'reset' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php esc_html_e( 'Reset', 'deskman-studio-manager' ); ?></span>
			</button>
			<button type="button" class="dmt-btn dmt-btn--primary dmt-btn--sm dmt-cz__start-top" data-dmt-start>
				<?php esc_html_e( 'Start My Project', 'deskman-studio-manager' ); ?>
			</button>
		</div>
	</div>

	<div class="dmt-cz__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Customiser view', 'deskman-studio-manager' ); ?>">
		<button type="button" role="tab" id="dmt-tab-edit" aria-controls="dmt-panel" aria-selected="true" data-dmt-tab="edit">
			<?php echo deskman_tpl_icon( 'edit' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Customise', 'deskman-studio-manager' ); ?>
		</button>
		<button type="button" role="tab" id="dmt-tab-preview" aria-controls="dmt-stage" aria-selected="false" tabindex="-1" data-dmt-tab="preview">
			<?php echo deskman_tpl_icon( 'eye' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Preview', 'deskman-studio-manager' ); ?>
		</button>
	</div>

	<div class="dmt-cz__body">
		<aside id="dmt-panel" class="dmt-cz__panel" role="tabpanel" aria-labelledby="dmt-tab-edit">
			<form class="dmt-form" data-dmt-form novalidate>
				<p class="dmt-cz__intro"><?php esc_html_e( 'Personalise the template below — every change appears in the preview straight away. Nothing is published, and your details stay in this browser until you choose to start a project.', 'deskman-studio-manager' ); ?></p>

				<?php
				$dmt_first = true;
				foreach ( $groups as $dmt_group_id => $dmt_group ) :
					?>
					<details class="dmt-group" <?php echo ( $dmt_first || 'branding' === $dmt_group_id ) ? 'open' : ''; ?>>
						<summary class="dmt-group__summary">
							<span class="dmt-group__title"><?php echo esc_html( $dmt_group['label'] ); ?></span>
							<?php if ( ! empty( $dmt_group['description'] ) ) : ?>
								<span class="dmt-group__desc"><?php echo esc_html( $dmt_group['description'] ); ?></span>
							<?php endif; ?>
						</summary>
						<div class="dmt-group__fields">
							<?php
							foreach ( $dmt_group['fields'] as $dmt_key => $dmt_field ) {
								$dmt_context = 'panel';
								include deskman_tpl_locate( 'views/partials/field.php' );
							}
							?>
						</div>
					</details>
					<?php
					$dmt_first = false;
				endforeach;
				?>
			</form>

			<div class="dmt-cz__panel-foot">
				<button type="button" class="dmt-btn dmt-btn--primary dmt-btn--block dmt-btn--lg" data-dmt-start>
					<?php esc_html_e( 'Start My Project', 'deskman-studio-manager' ); ?>
					<?php echo deskman_tpl_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
				<p class="dmt-cz__foot-note"><?php esc_html_e( 'Send your choices to Deskman Studio. Nothing is published automatically.', 'deskman-studio-manager' ); ?></p>
			</div>
		</aside>

		<section id="dmt-stage" class="dmt-cz__stage" role="tabpanel" aria-labelledby="dmt-tab-preview" data-dmt-stage>
			<div class="dmt-stage__frame" data-dmt-frame data-device="laptop">
				<div class="dmt-device" data-dmt-device>
					<div class="dmt-device__chrome" aria-hidden="true">
						<span class="dmt-device__dots"><i></i><i></i><i></i></span>
						<span class="dmt-device__url"><?php echo deskman_tpl_icon( 'lock' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span data-dmt-url>www.brightwaydentalcare.com</span></span>
					</div>
					<div class="dmt-device__screen" data-dmt-screen>
						<iframe
							class="dmt-device__iframe"
							data-dmt-iframe
							src="<?php echo esc_url( $dmt_preview_url ); ?>"
							title="<?php esc_attr_e( 'Live preview of your personalised website', 'deskman-studio-manager' ); ?>"
							width="1366"
							height="768"
							loading="eager"
						></iframe>
					</div>
				</div>
			</div>
			<p class="dmt-stage__meta" aria-live="polite">
				<span data-dmt-size>1366 px</span>
				<a class="dmt-stage__open" href="<?php echo esc_url( $dmt_preview_url ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'Open full preview', 'deskman-studio-manager' ); ?>
					<?php echo deskman_tpl_icon( 'external' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="screen-reader-text dmt-sr"><?php esc_html_e( '(opens in a new tab)', 'deskman-studio-manager' ); ?></span>
				</a>
			</p>
		</section>
	</div>

	<div class="dmt-cz__mobilebar">
		<button type="button" class="dmt-btn dmt-btn--primary dmt-btn--block" data-dmt-start><?php esc_html_e( 'Start My Project', 'deskman-studio-manager' ); ?></button>
	</div>

	<p class="dmt-sr" role="status" aria-live="polite" data-dmt-live></p>

	<?php /* Quick start: shown on a visitor's first visit so they see their own details immediately. */ ?>
	<dialog class="dmt-dialog dmt-dialog--quick" data-dmt-quick aria-labelledby="dmt-quick-title" aria-describedby="dmt-quick-desc">
		<form method="dialog" class="dmt-dialog__inner" data-dmt-quick-form novalidate>
			<header class="dmt-dialog__head">
				<p class="dmt-eyebrow"><?php echo esc_html( $industry['name'] ); ?></p>
				<h2 id="dmt-quick-title" class="dmt-dialog__title"><?php esc_html_e( 'Let’s make this website yours', 'deskman-studio-manager' ); ?></h2>
				<p id="dmt-quick-desc" class="dmt-dialog__desc"><?php esc_html_e( 'Add a few details to see your clinic on the template. You can change everything later — or skip this and explore the demo first.', 'deskman-studio-manager' ); ?></p>
			</header>
			<div class="dmt-dialog__body dmt-quick__fields">
				<?php
				foreach ( $dmt_quick as $dmt_key => $dmt_field ) {
					$dmt_context = 'quick';
					include deskman_tpl_locate( 'views/partials/field.php' );
				}
				?>
			</div>
			<footer class="dmt-dialog__foot">
				<button type="submit" value="skip" class="dmt-btn dmt-btn--ghost"><?php esc_html_e( 'Skip — explore the demo', 'deskman-studio-manager' ); ?></button>
				<button type="submit" value="apply" class="dmt-btn dmt-btn--primary"><?php esc_html_e( 'Show my website', 'deskman-studio-manager' ); ?><?php echo deskman_tpl_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			</footer>
		</form>
	</dialog>

	<?php /* Start My Project: real submission to the WordPress REST endpoint. */ ?>
	<dialog class="dmt-dialog dmt-dialog--enquiry" data-dmt-enquiry aria-labelledby="dmt-enquiry-title">
		<div class="dmt-dialog__inner">
			<button type="button" class="dmt-dialog__close" data-dmt-close aria-label="<?php esc_attr_e( 'Close', 'deskman-studio-manager' ); ?>"><?php echo deskman_tpl_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>

			<div data-dmt-enquiry-form-wrap>
				<header class="dmt-dialog__head">
					<p class="dmt-eyebrow"><?php esc_html_e( 'Start my project', 'deskman-studio-manager' ); ?></p>
					<h2 id="dmt-enquiry-title" class="dmt-dialog__title"><?php esc_html_e( 'Send your website to Deskman Studio', 'deskman-studio-manager' ); ?></h2>
					<p class="dmt-dialog__desc"><?php esc_html_e( 'We’ll receive your selected template and personalisation, review it, and get in touch to plan your website. Nothing is published until you approve it.', 'deskman-studio-manager' ); ?></p>
				</header>

				<form class="dmt-form dmt-enquiry" data-dmt-enquiry-form novalidate>
					<div class="dmt-errors" data-dmt-errors tabindex="-1" role="alert" hidden></div>

					<div class="dmt-summary" data-dmt-summary>
						<div class="dmt-summary__logo" data-dmt-summary-logo></div>
						<div class="dmt-summary__text">
							<p class="dmt-summary__label"><?php esc_html_e( 'Selected template', 'deskman-studio-manager' ); ?></p>
							<p class="dmt-summary__name"><?php echo esc_html( $template['name'] ); ?></p>
							<p class="dmt-summary__meta" data-dmt-summary-meta></p>
						</div>
						<div class="dmt-summary__swatches" data-dmt-summary-swatches aria-hidden="true"></div>
					</div>

					<div class="dmt-form__grid">
						<div class="dmt-field">
							<label for="dmt-e-name"><?php esc_html_e( 'Your name', 'deskman-studio-manager' ); ?> <span class="dmt-req" aria-hidden="true">*</span></label>
							<input id="dmt-e-name" name="name" type="text" autocomplete="name" required maxlength="100" aria-describedby="dmt-e-name-err">
							<p class="dmt-field__error" id="dmt-e-name-err" data-error-for="name"></p>
						</div>
						<div class="dmt-field">
							<label for="dmt-e-business"><?php esc_html_e( 'Clinic or business name', 'deskman-studio-manager' ); ?> <span class="dmt-req" aria-hidden="true">*</span></label>
							<input id="dmt-e-business" name="business" type="text" autocomplete="organization" required maxlength="120" aria-describedby="dmt-e-business-err">
							<p class="dmt-field__error" id="dmt-e-business-err" data-error-for="business"></p>
						</div>
						<div class="dmt-field">
							<label for="dmt-e-email"><?php esc_html_e( 'Email address', 'deskman-studio-manager' ); ?> <span class="dmt-req" aria-hidden="true">*</span></label>
							<input id="dmt-e-email" name="email" type="email" autocomplete="email" required maxlength="100" aria-describedby="dmt-e-email-err">
							<p class="dmt-field__error" id="dmt-e-email-err" data-error-for="email"></p>
						</div>
						<div class="dmt-field">
							<label for="dmt-e-phone"><?php esc_html_e( 'Contact number', 'deskman-studio-manager' ); ?> <span class="dmt-req" aria-hidden="true">*</span></label>
							<input id="dmt-e-phone" name="phone" type="tel" autocomplete="tel" required maxlength="25" aria-describedby="dmt-e-phone-err">
							<p class="dmt-field__error" id="dmt-e-phone-err" data-error-for="phone"></p>
						</div>
						<div class="dmt-field dmt-field--full">
							<label for="dmt-e-template"><?php esc_html_e( 'Selected template', 'deskman-studio-manager' ); ?></label>
							<input id="dmt-e-template" type="text" value="<?php echo esc_attr( $template['name'] . ' (' . $industry['name'] . ')' ); ?>" readonly>
						</div>
						<div class="dmt-field dmt-field--full">
							<label for="dmt-e-req"><?php esc_html_e( 'Any additional requirements?', 'deskman-studio-manager' ); ?> <span class="dmt-optional"><?php esc_html_e( '(optional)', 'deskman-studio-manager' ); ?></span></label>
							<textarea id="dmt-e-req" name="requirements" rows="4" maxlength="3000" placeholder="<?php esc_attr_e( 'e.g. online booking, extra pages, a domain you already own, launch timing…', 'deskman-studio-manager' ); ?>" aria-describedby="dmt-e-req-err"></textarea>
							<p class="dmt-field__error" id="dmt-e-req-err" data-error-for="requirements"></p>
						</div>
						<div class="dmt-field dmt-field--full dmt-field--check">
							<input id="dmt-e-consent" name="consent" type="checkbox" value="1" required aria-describedby="dmt-e-consent-err">
							<label for="dmt-e-consent">
								<?php esc_html_e( 'I agree that Deskman Studio may store these details and contact me about this website project.', 'deskman-studio-manager' ); ?>
								<?php if ( $dmt_privacy_url ) : ?>
									<a href="<?php echo esc_url( $dmt_privacy_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Privacy policy', 'deskman-studio-manager' ); ?></a>
								<?php endif; ?>
							</label>
							<p class="dmt-field__error" id="dmt-e-consent-err" data-error-for="consent"></p>
						</div>
						<div class="dmt-hp" aria-hidden="true">
							<label for="dmt-e-website"><?php esc_html_e( 'Leave this field empty', 'deskman-studio-manager' ); ?></label>
							<input id="dmt-e-website" name="website" type="text" tabindex="-1" autocomplete="off">
						</div>
						<input type="hidden" name="token" value="<?php echo esc_attr( function_exists( 'deskman_lead_form_token' ) ? deskman_lead_form_token() : '' ); ?>">
						<?php wp_nonce_field( 'deskman_contact', 'deskman_nonce', false ); ?>
					</div>

					<div class="dmt-enquiry__foot">
						<p class="dmt-enquiry__status" data-dmt-status role="status" aria-live="polite"></p>
						<button type="submit" class="dmt-btn dmt-btn--primary dmt-btn--lg" data-dmt-submit>
							<span data-dmt-submit-label><?php esc_html_e( 'Send my project enquiry', 'deskman-studio-manager' ); ?></span>
						</button>
					</div>
				</form>
			</div>

			<div class="dmt-success" data-dmt-success hidden tabindex="-1">
				<span class="dmt-success__icon"><?php echo deskman_tpl_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<h2 class="dmt-dialog__title"><?php esc_html_e( 'Thank you — your project enquiry has been received', 'deskman-studio-manager' ); ?></h2>
				<p data-dmt-success-text></p>
				<p class="dmt-success__ref"><?php esc_html_e( 'Reference', 'deskman-studio-manager' ); ?>: <strong data-dmt-success-ref></strong></p>
				<div class="dmt-actions dmt-actions--center">
					<button type="button" class="dmt-btn dmt-btn--primary" data-dmt-close><?php esc_html_e( 'Back to my preview', 'deskman-studio-manager' ); ?></button>
					<a class="dmt-btn dmt-btn--ghost" href="<?php echo esc_url( deskman_tpl_gallery_url() ); ?>"><?php esc_html_e( 'Browse templates', 'deskman-studio-manager' ); ?></a>
				</div>
			</div>
		</div>
	</dialog>
</div>
<?php
get_footer();
