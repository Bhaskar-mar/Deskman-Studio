<?php
/**
 * The enquiry form, posting to the plugin's handler
 * (admin-post.php?action=deskman_contact → lead inbox + email).
 *
 * Args: 'full' => bool (adds company, website, timeline), 'id' => prefix.
 *
 * Spam protection matches the handler: two honeypots (cf_hp, cf_url), a
 * signed render time (cf_t) and a JS flag (cf_js, set by site.js). On a
 * rejected submission the handler redirects back with a token; the values
 * and the reason are recalled here so nothing typed is lost.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$full = ! empty( $args['full'] );
$px   = isset( $args['id'] ) ? sanitize_key( $args['id'] ) : 'f';

$state = isset( $_GET['deskman_contact'] ) ? sanitize_key( wp_unslash( $_GET['deskman_contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$back  = null;
if ( 'error' === $state && isset( $_GET['dsk'] ) && function_exists( 'deskman_lead_recall' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
	$back = deskman_lead_recall( sanitize_text_field( wp_unslash( $_GET['dsk'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
}
$old = function ( $k ) use ( $back ) { return ( $back && isset( $back['values'][ $k ] ) ) ? (string) $back['values'][ $k ] : ''; };
$bad = function ( $code ) use ( $back ) { return $back && isset( $back['code'] ) && strpos( (string) $back['code'], $code ) === 0 ? ' is-bad' : ''; };

$phone_req = function_exists( 'deskman_lead_on' ) ? deskman_lead_on( 'lead_phone_required', true ) : true;
$plan      = isset( $_GET['plan'] ) ? sanitize_text_field( wp_unslash( $_GET['plan'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$pre_type  = $old( 'ptype' ) !== '' ? $old( 'ptype' ) : ( isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification
$ptypes    = dm_ptypes();
if ( $pre_type === '' ) { $pre_type = $ptypes[0]; }
if ( ! in_array( $pre_type, $ptypes, true ) ) { array_unshift( $ptypes, $pre_type ); } // e.g. a service page's "Get a quote"
?>
<div class="form" id="<?php echo esc_attr( $px ); ?>-enquiry">
<?php if ( 'sent' === $state ) : ?>
	<div class="sent" role="status">
		<span class="ico"><?php echo dm_i( 'check' ); // phpcs:ignore ?></span>
		<h3><?php esc_html_e( 'Message received.', 'deskman-studio' ); ?></h3>
		<p><?php dm_e( 'form_success', 'Thanks — your message is on its way. We’ll reply within one working day.' ); ?></p>
	</div>
<?php else : ?>
	<?php if ( $full ) : ?>
		<h3 class="h3" style="color:#fff"><?php dm_e( 'v5_form_h', 'Project enquiry' ); ?></h3>
		<p class="small" style="margin:4px 0 6px"><?php esc_html_e( 'Takes about a minute.', 'deskman-studio' ); ?></p>
	<?php endif; ?>
	<?php if ( $back && ! empty( $back['msg'] ) ) : ?>
		<p class="notice notice--err" role="alert"><?php echo dm_i( 'alert' ); // phpcs:ignore ?><span><?php echo esc_html( $back['msg'] ); ?></span></p>
	<?php endif; ?>
	<?php if ( $plan !== '' ) : ?>
		<p class="notice notice--ok"><?php echo dm_i( 'check' ); // phpcs:ignore ?><span><?php esc_html_e( 'Selected:', 'deskman-studio' ); ?> <b><?php echo esc_html( $plan ); ?></b></span></p>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-enquiry novalidate>
		<input type="hidden" name="action" value="deskman_contact">
		<?php wp_nonce_field( 'deskman_contact', 'deskman_nonce' ); ?>
		<input type="hidden" name="cf_t" value="<?php echo esc_attr( function_exists( 'deskman_lead_form_token' ) ? deskman_lead_form_token() : '' ); ?>">
		<input type="hidden" name="cf_js" value="">
		<?php if ( $plan !== '' ) : ?><input type="hidden" name="cf_plan" value="<?php echo esc_attr( $plan ); ?>"><?php endif; ?>
		<span class="hp" aria-hidden="true"><label>Leave blank<input type="text" name="cf_hp" tabindex="-1" autocomplete="off"></label><label>Leave empty<input type="text" name="cf_url" tabindex="-1" autocomplete="off"></label></span>

		<div class="f2">
			<label class="fx<?php echo esc_attr( $bad( 'name' ) ); ?>"><span><?php esc_html_e( 'Name', 'deskman-studio' ); ?> <em>*</em></span><input type="text" name="cf_name" required autocomplete="name" placeholder="<?php esc_attr_e( 'Full name', 'deskman-studio' ); ?>" value="<?php echo esc_attr( $old( 'name' ) ); ?>"></label>
			<label class="fx<?php echo esc_attr( $bad( 'email' ) ); ?>"><span><?php esc_html_e( 'Email', 'deskman-studio' ); ?> <em>*</em></span><input type="email" name="cf_email" required autocomplete="email" placeholder="you@company.com" value="<?php echo esc_attr( $old( 'email' ) ); ?>"></label>
		</div>
		<div class="f2">
			<label class="fx<?php echo esc_attr( $bad( 'phone' ) ); ?>"><span><?php esc_html_e( 'Phone / WhatsApp', 'deskman-studio' ); ?><?php echo $phone_req ? ' <em>*</em>' : ''; ?></span><input type="tel" name="cf_phone" <?php echo $phone_req ? 'required' : ''; ?> autocomplete="tel" placeholder="+91 98765 43210" value="<?php echo esc_attr( $old( 'phone_in' ) ); ?>"></label>
			<?php if ( $full ) : ?>
				<label class="fx"><span><?php esc_html_e( 'Company', 'deskman-studio' ); ?></span><input type="text" name="cf_company" autocomplete="organization" placeholder="<?php esc_attr_e( 'Company name', 'deskman-studio' ); ?>" value="<?php echo esc_attr( $old( 'company' ) ); ?>"></label>
			<?php else : ?>
				<label class="fx"><span><?php esc_html_e( 'Website (if any)', 'deskman-studio' ); ?></span><input type="url" name="cf_website" placeholder="https://" value="<?php echo esc_attr( $old( 'website' ) ); ?>"></label>
			<?php endif; ?>
		</div>
		<?php if ( $full ) : ?>
			<label class="fx"><span><?php esc_html_e( 'Current website (if any)', 'deskman-studio' ); ?></span><input type="url" name="cf_website" placeholder="https://" value="<?php echo esc_attr( $old( 'website' ) ); ?>"></label>
		<?php endif; ?>

		<fieldset class="fx" style="border:0;padding:0"><span><?php esc_html_e( 'What do you need?', 'deskman-studio' ); ?> <em>*</em></span>
			<legend class="screen-reader-text"><?php esc_html_e( 'Project type', 'deskman-studio' ); ?></legend>
			<div class="chips">
				<?php foreach ( $ptypes as $t ) : ?>
					<label><input type="radio" name="cf_ptype" value="<?php echo esc_attr( $t ); ?>" <?php checked( $pre_type, $t ); ?>><span><?php echo esc_html( $t ); ?></span></label>
				<?php endforeach; ?>
			</div>
		</fieldset>

		<?php if ( $full ) : ?>
			<fieldset class="fx" style="border:0;padding:0"><span><?php esc_html_e( 'Ideal timeline', 'deskman-studio' ); ?></span>
				<legend class="screen-reader-text"><?php esc_html_e( 'Timeline', 'deskman-studio' ); ?></legend>
				<div class="chips">
					<?php foreach ( array( 'ASAP', '1–2 months', '3+ months', 'Not sure' ) as $t ) : ?>
						<label><input type="radio" name="cf_timeline" value="<?php echo esc_attr( $t ); ?>" <?php checked( $old( 'timeline' ), $t ); ?>><span><?php echo esc_html( $t ); ?></span></label>
					<?php endforeach; ?>
				</div>
			</fieldset>
		<?php endif; ?>

		<label class="fx<?php echo esc_attr( $bad( 'msg' ) ); ?>"><span><?php esc_html_e( 'Tell us about the project', 'deskman-studio' ); ?> <em>*</em></span><textarea name="cf_msg" required rows="4" placeholder="<?php esc_attr_e( 'Goals, pages, features, links you like…', 'deskman-studio' ); ?>"><?php echo esc_textarea( $old( 'msg' ) ); ?></textarea></label>

		<button class="btn btn--v btn--lg" type="submit" style="width:100%;margin-top:16px"><?php esc_html_e( 'Send enquiry', 'deskman-studio' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></button>
		<small class="fine"><?php dm_e( 'v5_form_fine', 'We only use your details to reply. No spam, ever.' ); ?></small>
	</form>
<?php endif; ?>
</div>
