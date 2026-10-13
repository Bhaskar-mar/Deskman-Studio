<?php
/**
 * One project card. Args: 'p' => dm_project() array.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$p = $args['p'];
$external = empty( $p['id'] );
$cats = implode( ' ', array_map( 'sanitize_title', (array) $p['cats'] ) );
?>
<article class="wk rv" data-cats="<?php echo esc_attr( $cats ); ?>">
	<a href="<?php echo esc_url( $p['url'] ); ?>"<?php echo $external ? ' target="_blank" rel="noopener"' : ''; ?> style="display:flex;flex-direction:column;height:100%">
		<div class="wk__img">
			<?php if ( $p['img'] ) : ?><img src="<?php echo esc_url( $p['img'] ); ?>" alt="<?php echo esc_attr( sprintf( /* translators: %s: project */ __( '%s website', 'deskman-studio' ), $p['name'] ) ); ?>" loading="lazy" decoding="async" width="880" height="583"><?php endif; ?>
			<span class="go"><?php echo dm_i( 'ur' ); // phpcs:ignore ?></span>
		</div>
		<div class="wk__b">
			<div class="row"><h3><?php echo esc_html( $p['name'] ); ?></h3><span class="mono" style="color:var(--faint)"><?php echo esc_html( $p['host'] ? $p['host'] : $p['year'] ); ?></span></div>
			<p><?php echo esc_html( $p['short'] ); ?></p>
			<?php if ( $p['tags'] ) : ?><div class="tags"><?php foreach ( $p['tags'] as $t ) : ?><span class="chiptag"><?php echo esc_html( $t ); ?></span><?php endforeach; ?></div><?php endif; ?>
		</div>
	</a>
</article>
