<?php
/**
 * FAQ grid as native <details> (works with no JS). Args: 'items' => [[q, a], …].
 * The same items feed the FAQPage structured data (inc/seo.php).
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$items = isset( $args['items'] ) ? $args['items'] : dm_faq();
?>
<div class="faq">
	<?php foreach ( $items as $n => $qa ) : ?>
		<details class="qa rv"<?php echo $n < 2 ? ' open' : ''; ?>>
			<summary><h3 style="display:flex;justify-content:space-between;gap:16px;font-size:16px;letter-spacing:-.015em;font-weight:600" class="qh"><?php echo esc_html( $qa[0] ); ?><span aria-hidden="true"></span></h3></summary>
			<p><?php echo esc_html( $qa[1] ); ?></p>
		</details>
	<?php endforeach; ?>
</div>
