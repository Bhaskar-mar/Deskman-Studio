<?php
/**
 * Deskman Studio Manager — per-page SEO meta box.
 *
 * Adds a small "Search / SEO" panel to Pages and Projects so each one can
 * carry its own search title, meta description and (optionally) a noindex
 * flag. The theme's SEO engine (theme inc/seo.php) reads these; when a field
 * is left blank the theme derives a sensible value from the page itself, so
 * this is purely an override layer — nothing breaks if it is never touched.
 *
 * A live character counter is shown against the recommended lengths (≈60 for
 * the title, ≈155 for the description) so whoever is optimising a page for a
 * given keyword can see when they are about to be truncated in results.
 *
 * @package Deskman_Studio_Manager
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Post types that get the SEO panel. */
function deskman_seo_meta_post_types() {
	return apply_filters( 'deskman_seo_meta_post_types', array( 'page', 'post', 'deskman_project' ) );
}

add_action( 'add_meta_boxes', function () {
	foreach ( deskman_seo_meta_post_types() as $pt ) {
		add_meta_box(
			'deskman_seo',
			__( 'Search / SEO', 'deskman-studio-manager' ),
			'deskman_seo_meta_box',
			$pt,
			'normal',
			'high'
		);
	}
} );

/** The panel. */
function deskman_seo_meta_box( $post ) {
	wp_nonce_field( 'deskman_seo_save', 'deskman_seo_nonce' );

	$title   = (string) get_post_meta( $post->ID, '_deskman_seo_title', true );
	$desc    = (string) get_post_meta( $post->ID, '_deskman_seo_desc', true );
	$noindex = get_post_meta( $post->ID, '_deskman_seo_noindex', true ) === '1';

	$preview_url = get_permalink( $post );
	?>
	<style>
		.dsk-seo-field{margin:0 0 18px}
		.dsk-seo-field label{display:block;font-weight:600;margin-bottom:4px}
		.dsk-seo-field input[type=text],.dsk-seo-field textarea{width:100%}
		.dsk-seo-count{float:right;font-weight:400;color:#777}
		.dsk-seo-count.over{color:#b32d2e}
		.dsk-seo-hint{color:#777;margin:4px 0 0}
		.dsk-seo-snippet{border:1px solid #dcdcde;border-radius:8px;padding:12px 14px;background:#fff;max-width:600px}
		.dsk-seo-snippet .u{color:#202124;font-size:13px}
		.dsk-seo-snippet .t{color:#1a0dab;font-size:18px;line-height:1.3;margin:2px 0}
		.dsk-seo-snippet .d{color:#4d5156;font-size:13px;line-height:1.5}
	</style>

	<div class="dsk-seo-field">
		<label for="dsk_seo_title">
			<?php esc_html_e( 'Search title', 'deskman-studio-manager' ); ?>
			<span class="dsk-seo-count" id="dsk_seo_title_c">0</span>
		</label>
		<input type="text" id="dsk_seo_title" name="deskman_seo_title" value="<?php echo esc_attr( $title ); ?>"
			placeholder="<?php esc_attr_e( 'Leave blank to use the page title + brand name', 'deskman-studio-manager' ); ?>" />
		<p class="dsk-seo-hint"><?php esc_html_e( 'The clickable heading in Google. Best kept under ~60 characters. Put the main keyword near the front.', 'deskman-studio-manager' ); ?></p>
	</div>

	<div class="dsk-seo-field">
		<label for="dsk_seo_desc">
			<?php esc_html_e( 'Meta description', 'deskman-studio-manager' ); ?>
			<span class="dsk-seo-count" id="dsk_seo_desc_c">0</span>
		</label>
		<textarea id="dsk_seo_desc" name="deskman_seo_desc" rows="3"
			placeholder="<?php esc_attr_e( 'Leave blank to auto-generate from the page content', 'deskman-studio-manager' ); ?>"><?php echo esc_textarea( $desc ); ?></textarea>
		<p class="dsk-seo-hint"><?php esc_html_e( 'The grey summary under the title in Google. ~140–155 characters. Write it for a person, include the keyword once, and make it a reason to click.', 'deskman-studio-manager' ); ?></p>
	</div>

	<div class="dsk-seo-field">
		<label><input type="checkbox" name="deskman_seo_noindex" value="1" <?php checked( $noindex ); ?> />
			<?php esc_html_e( 'Hide this page from search engines (noindex)', 'deskman-studio-manager' ); ?></label>
		<p class="dsk-seo-hint"><?php esc_html_e( 'Use for thank-you pages or drafts you do not want indexed. Leave off for normal pages.', 'deskman-studio-manager' ); ?></p>
	</div>

	<p style="font-weight:600;margin:0 0 6px"><?php esc_html_e( 'Preview', 'deskman-studio-manager' ); ?></p>
	<div class="dsk-seo-snippet">
		<div class="u"><?php echo esc_html( preg_replace( '#^https?://#', '', (string) $preview_url ) ); ?></div>
		<div class="t" id="dsk_seo_pv_t"></div>
		<div class="d" id="dsk_seo_pv_d"></div>
	</div>

	<script>
	(function(){
		var brand = <?php echo wp_json_encode( deskman_seo_brand_suffix() ); ?>;
		var pageTitle = <?php echo wp_json_encode( get_the_title( $post ) ); ?>;
		var t = document.getElementById('dsk_seo_title');
		var d = document.getElementById('dsk_seo_desc');
		var tc = document.getElementById('dsk_seo_title_c');
		var dc = document.getElementById('dsk_seo_desc_c');
		var pvt = document.getElementById('dsk_seo_pv_t');
		var pvd = document.getElementById('dsk_seo_pv_d');
		function upd(){
			var tv = t.value.trim() || (pageTitle + ' — ' + brand);
			var dv = d.value.trim() || '(auto-generated from the page content)';
			tc.textContent = t.value.length; dc.textContent = d.value.length;
			tc.className = 'dsk-seo-count' + (t.value.length > 60 ? ' over' : '');
			dc.className = 'dsk-seo-count' + (d.value.length > 155 ? ' over' : '');
			pvt.textContent = tv.length > 62 ? tv.slice(0,61) + '…' : tv;
			pvd.textContent = dv.length > 158 ? dv.slice(0,157) + '…' : dv;
		}
		t.addEventListener('input', upd); d.addEventListener('input', upd); upd();
	})();
	</script>
	<?php
}

/** Brand suffix used by the preview snippet ("Page title — Deskman Studio"). */
function deskman_seo_brand_suffix() {
	$name = trim( (string) deskman_opt( 'brand_name', 'Deskman' ) );
	$sub  = trim( (string) deskman_opt( 'brand_sub', 'Studio' ) );
	$full = trim( $name . ' ' . $sub );
	return $full !== '' ? $full : get_bloginfo( 'name' );
}

/** Save. */
add_action( 'save_post', function ( $post_id ) {
	if ( ! isset( $_POST['deskman_seo_nonce'] ) ) { return; }
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['deskman_seo_nonce'] ) ), 'deskman_seo_save' ) ) { return; }
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
	if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

	$title = isset( $_POST['deskman_seo_title'] ) ? sanitize_text_field( wp_unslash( $_POST['deskman_seo_title'] ) ) : '';
	$desc  = isset( $_POST['deskman_seo_desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['deskman_seo_desc'] ) ) : '';
	$noidx = isset( $_POST['deskman_seo_noindex'] ) ? '1' : '0';

	if ( $title !== '' ) { update_post_meta( $post_id, '_deskman_seo_title', $title ); }
	else { delete_post_meta( $post_id, '_deskman_seo_title' ); }

	if ( $desc !== '' ) { update_post_meta( $post_id, '_deskman_seo_desc', $desc ); }
	else { delete_post_meta( $post_id, '_deskman_seo_desc' ); }

	update_post_meta( $post_id, '_deskman_seo_noindex', $noidx );
} );
