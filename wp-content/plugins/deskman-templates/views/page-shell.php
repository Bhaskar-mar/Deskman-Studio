<?php
/**
 * Full-width page shell for the Templates gallery and customiser.
 *
 * Uses the active theme's own header and footer so the site navigation,
 * branding and typography stay consistent:
 *  - classic themes: get_header() / get_footer();
 *  - block themes: the theme's "header" and "footer" template parts.
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

if ( wp_is_block_theme() ) {
	// Render template parts before wp_head() so their block styles are enqueued.
	$dmt_header = do_blocks( '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->' );
	ob_start();
	DMT_Frontend::render_main();
	$dmt_main   = ob_get_clean();
	$dmt_footer = do_blocks( '<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->' );
	?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks">
	<?php
	echo $dmt_header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered block markup.
	?>
	<main id="dmt-main" class="dmt-main">
		<?php
		echo $dmt_main; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in views.
		?>
	</main>
	<?php
	echo $dmt_footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered block markup.
	?>
</div>
<?php wp_footer(); ?>
</body>
</html>
	<?php
	return;
}

get_header();
?>
<main id="dmt-main" class="dmt-main">
	<?php DMT_Frontend::render_main(); ?>
</main>
<?php
get_footer();
