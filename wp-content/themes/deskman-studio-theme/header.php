<?php
/**
 * Document head, announcement bar and the site nav.
 * Every page opens on a dark hero, so the nav is the dark variant everywhere.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$dm_cur   = dm_current();
$dm_brand = strtoupper( dm_opt( 'brand_name', 'Deskman' ) );
$dm_sub   = strtoupper( dm_opt( 'brand_sub', 'Studio' ) );
$dm_logo  = get_theme_file_uri( 'assets/img/deskman-symbol.png' );
$dm_items = array(
	'services' => array( __( 'Services', 'deskman-studio' ), dm_link( 'services' ) ),
	'work'     => array( __( 'Work', 'deskman-studio' ), dm_link( 'work' ) ),
	'process'  => array( __( 'Process', 'deskman-studio' ), dm_link( 'process' ) ),
	'packages' => array( __( 'Packages', 'deskman-studio' ), dm_link( 'packages' ) ),
	'insights' => array( __( 'Insights', 'deskman-studio' ), dm_link( 'insights' ) ),
	'about'    => array( __( 'About', 'deskman-studio' ), dm_link( 'about' ) ),
);
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#08070C">
<script>document.documentElement.className+=' js';setTimeout(function(){document.documentElement.className+=' shown'},2500);</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); dm_sprite(); ?>
<a class="skip" href="#main"><?php esc_html_e( 'Skip to content', 'deskman-studio' ); ?></a>

<?php if ( dm_opt( 'v5_bar_on', '1' ) ) : ?>
<div class="bar"><div class="wrap">
	<b><?php dm_e( 'v5_bar_b', 'Now booking projects' ); ?></b><span>· <?php dm_e( 'v5_bar_t', 'Custom WordPress themes, plugins & SEO' ); ?></span>
	<a href="<?php echo esc_url( dm_link( 'contact' ) ); ?>"><?php dm_e( 'v5_bar_cta', 'Start a project' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a>
</div></div>
<?php endif; ?>

<header class="nav" id="dmNav">
	<div class="wrap">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( trim( dm_opt( 'brand_name', 'Deskman' ) . ' ' . dm_opt( 'brand_sub', 'Studio' ) ) ); ?> — <?php esc_attr_e( 'home', 'deskman-studio' ); ?>">
			<img src="<?php echo esc_url( $dm_logo ); ?>" width="30" height="30" alt=""><b><?php echo esc_html( $dm_brand ); ?></b><em><?php echo esc_html( $dm_sub ); ?></em>
		</a>
		<nav aria-label="<?php esc_attr_e( 'Primary', 'deskman-studio' ); ?>">
			<ul>
				<?php foreach ( $dm_items as $k => $it ) : $on = ( $k === $dm_cur ); ?>
					<?php if ( 'services' === $k ) : ?>
						<li class="has-dd">
							<a class="<?php echo $on ? 'on' : ''; ?>" href="<?php echo esc_url( $it[1] ); ?>" aria-haspopup="true"<?php echo $on ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $it[0] ); ?><?php echo dm_i( 'down' ); // phpcs:ignore ?></a>
							<div class="dd">
								<?php foreach ( dm_services() as $slug => $s ) : ?>
									<a href="<?php echo esc_url( dm_service_url( $slug ) ); ?>"><span class="ico"><?php echo dm_i( $s['icon'] ); // phpcs:ignore ?></span><span><b><?php echo esc_html( $s['title'] ); ?></b><small><?php echo esc_html( $s['menu'] ); ?></small></span></a>
								<?php endforeach; ?>
								<a class="dd__foot" href="<?php echo esc_url( $it[1] ); ?>"><span><?php esc_html_e( 'Not sure what you need? See all services', 'deskman-studio' ); ?></span><?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a>
							</div>
						</li>
					<?php else : ?>
						<li><a class="<?php echo $on ? 'on' : ''; ?>" href="<?php echo esc_url( $it[1] ); ?>"<?php echo $on ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $it[0] ); ?></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</nav>
		<div class="act">
			<a class="btn btn--g" href="<?php echo esc_url( dm_link( 'contact' ) ); ?>"><?php esc_html_e( 'Contact', 'deskman-studio' ); ?></a>
			<a class="btn btn--v" href="<?php echo esc_url( dm_link( 'call' ) ); ?>"><?php dm_e( 'v5_nav_cta', 'Book a call' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a>
			<button class="burger" type="button" aria-expanded="false" aria-controls="dmMnav"><?php echo dm_i( 'menu' ); // phpcs:ignore ?><span class="screen-reader-text"><?php esc_html_e( 'Menu', 'deskman-studio' ); ?></span></button>
		</div>
	</div>
</header>

<div class="mnav" id="dmMnav" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'deskman-studio' ); ?>">
	<div class="mnav__top">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( $dm_logo ); ?>" width="30" height="30" alt=""><b><?php echo esc_html( $dm_brand ); ?></b></a>
		<button class="burger" type="button" data-close style="display:grid"><?php echo dm_i( 'x' ); // phpcs:ignore ?><span class="screen-reader-text"><?php esc_html_e( 'Close menu', 'deskman-studio' ); ?></span></button>
	</div>
	<?php foreach ( $dm_items as $k => $it ) : ?>
		<a class="big" href="<?php echo esc_url( $it[1] ); ?>"<?php echo $k === $dm_cur ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $it[0] ); ?><?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a>
	<?php endforeach; ?>
	<a class="big" href="<?php echo esc_url( dm_link( 'contact' ) ); ?>"><?php esc_html_e( 'Contact', 'deskman-studio' ); ?><?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a>
	<a class="btn btn--v btn--lg" href="<?php echo esc_url( dm_link( 'call' ) ); ?>"><?php dm_e( 'v5_nav_cta', 'Book a call' ); ?> <?php echo dm_i( 'arrow' ); // phpcs:ignore ?></a>
	<a class="btn btn--g btn--lg" href="mailto:<?php echo esc_attr( sanitize_email( dm_email() ) ); ?>"><?php echo dm_i( 'mail' ); // phpcs:ignore ?><?php echo esc_html( dm_email() ); ?></a>
</div>
<main id="main">
