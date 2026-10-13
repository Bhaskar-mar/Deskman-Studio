<?php
/**
 * Footer: brand + newsletter, link columns, wordmark, legal row.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$dm_brand   = dm_opt( 'brand_name', 'Deskman' );
$dm_socials = dm_socials();
$dm_sent    = isset( $_GET['deskman_contact'] ) ? sanitize_key( wp_unslash( $_GET['deskman_contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
?>
</main>
<footer class="foot">
	<div class="wrap">
		<div class="foot__top">
			<div>
				<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/deskman-symbol.png' ) ); ?>" width="30" height="30" alt="" loading="lazy"><b><?php echo esc_html( strtoupper( $dm_brand ) ); ?></b><em><?php echo esc_html( strtoupper( dm_opt( 'brand_sub', 'Studio' ) ) ); ?></em></a>
				<p><?php dm_e( 'v5_foot_blurb', 'Custom WordPress themes, plugins and SEO for growing businesses. Based in Visakhapatnam, working worldwide.' ); ?></p>
				<form class="news" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="deskman_contact">
					<?php wp_nonce_field( 'deskman_contact', 'deskman_nonce' ); ?>
					<input type="hidden" name="cf_name" value="Newsletter subscriber">
					<input type="hidden" name="cf_msg" value="Newsletter signup from the site footer.">
					<input type="hidden" name="cf_ptype" value="Newsletter">
					<input type="hidden" name="cf_kind" value="newsletter">
					<input type="hidden" name="cf_t" value="<?php echo esc_attr( function_exists( 'deskman_lead_form_token' ) ? deskman_lead_form_token() : '' ); ?>">
					<input type="hidden" name="cf_js" value="">
					<span class="hp" aria-hidden="true"><input type="text" name="cf_hp" tabindex="-1" autocomplete="off"></span>
					<label class="screen-reader-text" for="dmNews"><?php esc_html_e( 'Email address', 'deskman-studio' ); ?></label>
					<input id="dmNews" type="email" name="cf_email" required placeholder="<?php esc_attr_e( 'Studio notes, monthly', 'deskman-studio' ); ?>" style="flex:1;min-width:0;background:none;border:0;color:var(--dt);padding:0 10px;font:inherit;font-size:13.5px">
					<button class="btn btn--v" type="submit"><?php esc_html_e( 'Subscribe', 'deskman-studio' ); ?></button>
				</form>
			</div>
			<div><h5><?php esc_html_e( 'Services', 'deskman-studio' ); ?></h5><ul>
				<?php foreach ( dm_services() as $slug => $s ) : ?><li><a href="<?php echo esc_url( dm_service_url( $slug ) ); ?>"><?php echo esc_html( $s['title'] ); ?></a></li><?php endforeach; ?>
			</ul></div>
			<div><h5><?php esc_html_e( 'Studio', 'deskman-studio' ); ?></h5><ul>
				<li><a href="<?php echo esc_url( dm_link( 'work' ) ); ?>"><?php esc_html_e( 'Work', 'deskman-studio' ); ?></a></li>
				<li><a href="<?php echo esc_url( dm_link( 'templates' ) ); ?>"><?php esc_html_e( 'Website templates', 'deskman-studio' ); ?></a></li>
				<li><a href="<?php echo esc_url( dm_link( 'process' ) ); ?>"><?php esc_html_e( 'Process', 'deskman-studio' ); ?></a></li>
				<li><a href="<?php echo esc_url( dm_link( 'packages' ) ); ?>"><?php esc_html_e( 'Packages', 'deskman-studio' ); ?></a></li>
				<li><a href="<?php echo esc_url( dm_link( 'about' ) ); ?>"><?php esc_html_e( 'About', 'deskman-studio' ); ?></a></li>
				<li><a href="<?php echo esc_url( dm_link( 'insights' ) ); ?>"><?php esc_html_e( 'Insights', 'deskman-studio' ); ?></a></li>
			</ul></div>
			<div><h5><?php esc_html_e( 'Contact', 'deskman-studio' ); ?></h5><ul>
				<li><a href="mailto:<?php echo esc_attr( sanitize_email( dm_email() ) ); ?>"><?php echo esc_html( dm_email() ); ?></a></li>
				<li><?php dm_e( 'contact_location', 'Visakhapatnam, India' ); ?></li>
				<li><a href="<?php echo esc_url( dm_link( 'call' ) ); ?>"><?php esc_html_e( 'Book a call', 'deskman-studio' ); ?></a></li>
			</ul></div>
			<div><h5><?php esc_html_e( 'Follow', 'deskman-studio' ); ?></h5><ul>
				<?php if ( $dm_socials ) : foreach ( $dm_socials as $so ) : ?>
					<li><a href="<?php echo esc_url( $so['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $so['label'] ); ?></a></li>
				<?php endforeach; else : ?>
					<li><a href="<?php echo esc_url( dm_link( 'insights' ) ); ?>"><?php esc_html_e( 'Insights', 'deskman-studio' ); ?></a></li>
					<li><a href="<?php echo esc_url( get_feed_link() ); ?>">RSS</a></li>
				<?php endif; ?>
			</ul></div>
		</div>
		<?php if ( 'sent' === $dm_sent ) : ?><p class="notice notice--ok" style="margin-top:28px" role="status"><?php echo dm_i( 'check' ); // phpcs:ignore ?><?php esc_html_e( 'Thanks — we’ve got your message.', 'deskman-studio' ); ?></p><?php endif; ?>
		<?php get_template_part( 'template-parts/locations' ); ?>
		<div class="foot__mark"><b aria-hidden="true"><?php echo esc_html( $dm_brand ); ?></b><span class="status"><i></i><?php dm_e( 'v5_status', 'Accepting new projects' ); ?></span></div>
		<div class="foot__base"><span><?php echo esc_html( dm_opt( 'v5_copy', '© ' . gmdate( 'Y' ) . ' ' . trim( $dm_brand . ' ' . dm_opt( 'brand_sub', 'Studio' ) ) . '. All rights reserved.' ) ); ?></span>
			<ul>
				<li><a href="<?php echo esc_url( dm_page_url( 'privacy-policy' ) ); ?>"><?php esc_html_e( 'Privacy', 'deskman-studio' ); ?></a></li>
				<li><a href="<?php echo esc_url( dm_page_url( 'terms-and-conditions' ) ); ?>"><?php esc_html_e( 'Terms', 'deskman-studio' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/wp-sitemap.xml' ) ); ?>"><?php esc_html_e( 'Sitemap', 'deskman-studio' ); ?></a></li>
			</ul>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
