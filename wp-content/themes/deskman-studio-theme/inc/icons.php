<?php
/**
 * Icon system: one inline SVG sprite (printed once in the header) and a
 * helper that references it. 24x24 grid, 1.8 stroke, currentColor.
 *
 * @package Deskman_Studio
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'dm_icon_paths' ) ) {
	function dm_icon_paths() {
		return array(
			'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
			'arrow-left' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
			'ur' => '<path d="M7 17 17 7M8 7h9v9"/>',
			'down' => '<path d="m6 9 6 6 6-6"/>',
			'right' => '<path d="m9 6 6 6-6 6"/>',
			'check' => '<path d="m5 12.5 4.2 4.2L19 7"/>',
			'x' => '<path d="M7 7l10 10M17 7 7 17"/>',
			'plus' => '<path d="M12 5v14M5 12h14"/>',
			'theme' => '<rect x="3" y="4" width="18" height="16" rx="2.5"/><path d="M3 9h18M8 13h4M8 16h7"/>',
			'plug' => '<path d="M9 3.5a2 2 0 0 1 4 0V5h3.5A1.5 1.5 0 0 1 18 6.5V10h-1.5a2 2 0 0 0 0 4H18v3.5a1.5 1.5 0 0 1-1.5 1.5H13v-1.5a2 2 0 0 0-4 0V19H5.5A1.5 1.5 0 0 1 4 17.5V14h1.5a2 2 0 0 0 0-4H4V6.5A1.5 1.5 0 0 1 5.5 5H9z"/>',
			'search' => '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.4-4.4"/>',
			'cart' => '<path d="M3 4h2.4l2.2 11h10.2l2-7.6H6.4"/><circle cx="9" cy="19.3" r="1.4"/><circle cx="16.5" cy="19.3" r="1.4"/>',
			'bolt' => '<path d="M13 2.5 4.5 13.5H11l-1 8 8.5-11H12z"/>',
			'shield' => '<path d="M12 3 5 5.8v5.4c0 4.5 3 8.2 7 9.8 4-1.6 7-5.3 7-9.8V5.8z"/><path d="m9 12 2.2 2.2L15.3 10"/>',
			'pen' => '<path d="M4 20l1-4.6L15.6 4.8a2 2 0 0 1 2.8 0l.8.8a2 2 0 0 1 0 2.8L8.6 19z"/>',
			'cal' => '<rect x="3.5" y="5" width="17" height="15.5" rx="2.4"/><path d="M3.5 9.5h17M8 3v4M16 3v4"/>',
			'api' => '<path d="M8 7 3 12l5 5M16 7l5 5-5 5"/>',
			'card' => '<rect x="3" y="5" width="18" height="14" rx="2.4"/><path d="M3 10h18M7 15h4"/>',
			'grid' => '<rect x="3.5" y="3.5" width="7" height="7" rx="1.6"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.6"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.6"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.6"/>',
			'type' => '<path d="M5 7V5h14v2M12 5v14M9 19h6"/>',
			'img' => '<rect x="3.5" y="4.5" width="17" height="15" rx="2.4"/><circle cx="9" cy="10" r="1.6"/><path d="m4 17.5 5-4.5 4 3.5 3-2.5 4 3.5"/>',
			'mail' => '<rect x="3" y="5" width="18" height="14" rx="2.4"/><path d="m3.5 6.5 8.5 6.5 8.5-6.5"/>',
			'cycle' => '<path d="M20 12a8 8 0 0 1-14.3 4.9M4 12a8 8 0 0 1 14.3-4.9"/><path d="M18.5 3v4.2h-4.2M5.5 21v-4.2h4.2"/>',
			'palette' => '<path d="M12 3a9 9 0 1 0 0 18c1.2 0 1.8-.8 1.8-1.7 0-1.3-1-1.6-1-2.7 0-1 .8-1.6 1.8-1.6H17a4 4 0 0 0 4-4C21 6.6 17 3 12 3z"/><circle cx="7.5" cy="11" r="1"/><circle cx="10" cy="7.3" r="1"/><circle cx="14.6" cy="7.4" r="1"/>',
			'home' => '<path d="M4 11 12 4l8 7v8.5a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 19.5z"/><path d="M10 21v-6h4v6"/>',
			'inbox' => '<path d="M3.5 13.5 6 5h12l2.5 8.5V19a1.5 1.5 0 0 1-1.5 1.5H5A1.5 1.5 0 0 1 3.5 19z"/><path d="M3.5 13.5H8l1.5 2.5h5l1.5-2.5h4.5"/>',
			'file' => '<path d="M7 3.5h7l4.5 4.5v11a1.5 1.5 0 0 1-1.5 1.5H7A1.5 1.5 0 0 1 5.5 19V5A1.5 1.5 0 0 1 7 3.5z"/><path d="M14 3.5V8h4.5"/>',
			'chart' => '<path d="M4 20V4M4 20h16"/><path d="M8.5 16v-4M12.5 16V8M16.5 16v-6"/>',
			'gauge' => '<path d="M4.2 17a8.5 8.5 0 1 1 15.6 0"/><path d="m12 13 3.8-4.2"/><circle cx="12" cy="13.2" r="1.3"/>',
			'lock' => '<rect x="5" y="10.5" width="14" height="10" rx="2.4"/><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5"/>',
			'globe' => '<circle cx="12" cy="12" r="8.5"/><path d="M3.5 12h17M12 3.5c2.5 2.6 3.6 5.4 3.6 8.5s-1.1 5.9-3.6 8.5c-2.5-2.6-3.6-5.4-3.6-8.5s1.1-5.9 3.6-8.5z"/>',
			'layers' => '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>',
			'spark' => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M18 6l-2.5 2.5M8.5 15.5 6 18"/>',
			'users' => '<circle cx="9" cy="8" r="3.3"/><path d="M2.8 19.5c.7-3.2 3.2-5 6.2-5s5.5 1.8 6.2 5"/><path d="M15.5 4.9a3.3 3.3 0 0 1 0 6.3M17.4 14.7c2 .6 3.4 2.3 3.8 4.8"/>',
			'clock' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
			'pin' => '<path d="M12 21s-6.5-5.7-6.5-11a6.5 6.5 0 0 1 13 0c0 5.3-6.5 11-6.5 11z"/><circle cx="12" cy="10" r="2.3"/>',
			'msg' => '<path d="M4 5h16v11H9l-5 4z"/>',
			'access' => '<circle cx="12" cy="4.5" r="1.8"/><path d="M5 8.5 12 10l7-1.5M12 10v4l-3 6.5M12 14l3 6.5"/>',
			'code' => '<rect x="3" y="4.5" width="18" height="15" rx="2.5"/><path d="M3 8.5h18M9.5 12l-2 2 2 2M14.5 12l2 2-2 2"/>',
			'rocket' => '<path d="M14.5 4.2c2.4-.9 4.6-.9 5.3-.2.7.7.7 2.9-.2 5.3-1 2.6-3.5 5.3-6.6 7.1l-3.4-3.4c1.8-3.1 4.5-5.6 6.9-6.8z"/><path d="M9.6 13 6 12.4l2.6-2.8 3.3-.2M11 14.4l.6 3.6 2.8-2.6.2-3.3"/>',
			'link' => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
			'doc' => '<rect x="5" y="3.5" width="14" height="17" rx="2.2"/><path d="M8.5 8h7M8.5 12h7M8.5 16h4"/>',
			'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
			'linkedin' => '<rect x="3" y="3" width="18" height="18" rx="3.5"/><path d="M7.5 10.5v6M7.5 7.5v.1M11.5 16.5v-6M11.5 13c0-1.6 1-2.6 2.4-2.6s2.3 1 2.3 2.6v3.5"/>',
			'instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="3.8"/><path d="M17 7v.1"/>',
			'xsocial' => '<path d="M4.5 4.5 16 19.5h3.5L8 4.5zM19 4.5l-5.6 6.3M5 19.5l5.7-6.4"/>',
			'youtube' => '<rect x="2.8" y="5.5" width="18.4" height="13" rx="3.6"/><path d="m10.3 9.4 4.4 2.6-4.4 2.6z"/>',
			'alert' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 8v4.5M12 16h0"/>',
			'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
		);
	}
}

/** Print the sprite. Called once, right after <body>. */
if ( ! function_exists( 'dm_sprite' ) ) {
	function dm_sprite() {
		echo '<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false"><defs>';
		foreach ( dm_icon_paths() as $k => $p ) {
			echo '<symbol id="i-' . esc_attr( $k ) . '" viewBox="0 0 24 24">' . $p . '</symbol>'; // phpcs:ignore WordPress.Security.EscapeOutput -- static markup.
		}
		echo '</defs></svg>';
	}
}

/** Return one icon. Decorative: the surrounding element carries the label. */
if ( ! function_exists( 'dm_i' ) ) {
	function dm_i( $name, $class = 'i' ) {
		return '<svg class="' . esc_attr( $class ) . '" aria-hidden="true" focusable="false"><use href="#i-' . esc_attr( $name ) . '"/></svg>';
	}
}
