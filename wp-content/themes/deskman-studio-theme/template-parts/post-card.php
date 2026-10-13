<?php
/**
 * One blog card. Uses the featured image, or a generated cover (category
 * gradient + icon + short title) so the grid never shows an empty box.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$cats = get_the_category();
$cat  = $cats ? $cats[0]->name : __( 'Insights', 'deskman-studio' );
$key  = strtolower( $cat );
$look = array(
	'wordpress'   => array( 'theme', '135deg,#6840FF,#21154F' ),
	'seo'         => array( 'search', '135deg,#0B0A10,#3A2A9C' ),
	'care'        => array( 'shield', '135deg,#1F6B2A,#0B0A10' ),
	'performance' => array( 'gauge', '135deg,#1E1B28,#6840FF' ),
	'woocommerce' => array( 'cart', '135deg,#3B2C9E,#0B0A10' ),
	'plugins'     => array( 'plug', '135deg,#8B6CFF,#4527C9' ),
);
$l    = isset( $look[ $key ] ) ? $look[ $key ] : array( 'doc', '135deg,#2A2370,#0B0A10' );
$mins = max( 1, (int) round( str_word_count( wp_strip_all_tags( get_the_content() ) ) / 220 ) );
?>
<article class="icard rv" data-cats="<?php echo esc_attr( implode( ' ', wp_list_pluck( $cats, 'slug' ) ) ); ?>">
	<a href="<?php the_permalink(); ?>" style="display:flex;flex-direction:column;height:100%">
		<div class="icard__img" style="background:linear-gradient(<?php echo esc_attr( $l[1] ); ?>)">
			<?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'dm-card', array( 'loading' => 'lazy', 'alt' => '' ) ); else : ?>
				<span class="cover"><small><?php echo esc_html( $cat ); ?></small><span style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px"><b><?php echo esc_html( wp_trim_words( get_the_title(), 5, '…' ) ); ?></b><?php echo dm_i( $l[0] ); // phpcs:ignore ?></span></span>
			<?php endif; ?>
		</div>
		<div class="icard__b">
			<div class="meta"><span class="chiptag"><?php echo esc_html( $cat ); ?></span><?php printf( esc_html( _n( '%d min read', '%d min read', $mins, 'deskman-studio' ) ), (int) $mins ); ?></div>
			<h3><?php the_title(); ?></h3>
			<p><?php echo esc_html( get_the_excerpt() ); ?></p>
		</div>
	</a>
</article>
