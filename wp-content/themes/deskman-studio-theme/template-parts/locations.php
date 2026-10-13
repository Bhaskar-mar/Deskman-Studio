<?php
/**
 * Location pages (plugin), linked from the footer so they are never orphans.
 * Prints nothing when none are published.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$areas = deskman_area_links( 12 );
if ( ! $areas ) { return; }
$all = deskman_area_archive_url();
?>
<nav class="dskl" aria-label="<?php esc_attr_e( 'Locations we work in', 'deskman-studio' ); ?>">
	<div class="wrap">
		<h2 class="dskl-h"><?php esc_html_e( 'Working with businesses in', 'deskman-studio' ); ?></h2>
		<ul class="dskl-list">
			<?php foreach ( $areas as $a ) : ?>
				<li><a href="<?php echo esc_url( $a['url'] ); ?>"<?php echo get_queried_object_id() === $a['id'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $a['label'] ); ?></a></li>
			<?php endforeach; ?>
			<?php if ( $all ) : ?><li><a href="<?php echo esc_url( $all ); ?>"><?php esc_html_e( 'All locations', 'deskman-studio' ); ?></a></li><?php endif; ?>
		</ul>
	</div>
</nav>
