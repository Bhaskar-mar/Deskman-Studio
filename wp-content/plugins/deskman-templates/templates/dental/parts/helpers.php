<?php
/**
 * Rendering helpers for the dental template.
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'dmt_dental_svg' ) ) {
	/**
	 * Inline a bundled SVG from the template's svg folder.
	 *
	 * @param string $name Relative name without extension, e.g. 'hero' or 'icons/tooth'.
	 * @return string Bundled, trusted SVG markup.
	 */
	function dmt_dental_svg( $name ) {
		static $cache = array();
		$name = preg_replace( '#[^a-z0-9/_-]#', '', strtolower( $name ) );
		if ( ! isset( $cache[ $name ] ) ) {
			$file           = DMT_DIR . 'templates/dental/svg/' . $name . '.svg';
			$cache[ $name ] = is_readable( $file ) ? trim( (string) file_get_contents( $file ) ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- bundled asset.
		}
		return $cache[ $name ];
	}
}

if ( ! function_exists( 'dmt_dental_tel' ) ) {
	/**
	 * tel: URI for a phone number.
	 *
	 * @param string $phone Phone.
	 * @return string
	 */
	function dmt_dental_tel( $phone ) {
		return 'tel:' . preg_replace( '/[^0-9+]/', '', (string) $phone );
	}
}

if ( ! function_exists( 'dmt_dental_hours' ) ) {
	/**
	 * Opening hours as list items ("Day: time" per line).
	 *
	 * @param string $hours Hours text.
	 * @return string Escaped HTML.
	 */
	function dmt_dental_hours( $hours ) {
		$out = '';
		foreach ( preg_split( '/\r\n|\r|\n/', (string) $hours ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$parts = explode( ': ', $line, 2 );
			if ( 2 === count( $parts ) ) {
				$out .= '<li><span>' . esc_html( $parts[0] ) . '</span><span>' . esc_html( $parts[1] ) . '</span></li>';
			} else {
				$out .= '<li><span>' . esc_html( $line ) . '</span></li>';
			}
		}
		return $out;
	}
}

if ( ! function_exists( 'dmt_dental_portrait' ) ) {
	/**
	 * Illustrated, clearly placeholder team portrait.
	 *
	 * @param array $p {skin, hair, style: short|bun|long|curly, outfit: primary|secondary}.
	 * @return string SVG markup built from fixed, trusted parameters.
	 */
	function dmt_dental_portrait( array $p ) {
		$skin   = esc_attr( $p['skin'] );
		$hair   = esc_attr( $p['hair'] );
		$outfit = 'secondary' === $p['outfit'] ? 'var(--c-secondary)' : 'var(--c-primary)';
		$id     = 'dtP' . substr( md5( wp_json_encode( $p ) ), 0, 6 );

		$back  = '';
		$front = '';
		switch ( $p['style'] ) {
			case 'long':
				$back  = '<path d="M98 168c0-52 26-92 62-92s62 40 62 92v96c0 12-10 20-22 20H120c-12 0-22-8-22-20Z" fill="' . $hair . '"/>';
				$front = '<path d="M106 156c4-44 26-70 54-70 30 0 50 24 54 62-22-4-44-18-56-40-10 24-30 40-52 48Z" fill="' . $hair . '"/>';
				break;
			case 'bun':
				$back  = '<circle cx="160" cy="84" r="26" fill="' . $hair . '"/>';
				$front = '<path d="M106 162c-2-48 22-80 54-80s56 30 54 80c-8-26-28-46-54-48-26 2-46 22-54 48Z" fill="' . $hair . '"/>';
				break;
			case 'curly':
				$front = '<g fill="' . $hair . '"><circle cx="120" cy="128" r="22"/><circle cx="140" cy="104" r="24"/><circle cx="168" cy="98" r="25"/><circle cx="194" cy="112" r="22"/><circle cx="206" cy="138" r="18"/><circle cx="112" cy="152" r="14"/></g>';
				break;
			default:
				$front = '<path d="M106 158c-4-46 20-78 56-78 34 0 56 28 52 72-6-18-20-30-40-34-22-4-46 6-68 40Z" fill="' . $hair . '"/>';
		}

		return '<svg class="dt-portrait" viewBox="0 0 320 340" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">'
			. '<defs><linearGradient id="' . $id . '" x1="0" y1="0" x2="1" y2="1"><stop offset="0" style="stop-color:var(--c-accent);stop-opacity:.45"/><stop offset="1" style="stop-color:var(--c-primary);stop-opacity:.16"/></linearGradient></defs>'
			. '<rect width="320" height="340" fill="url(#' . $id . ')"/>'
			. '<circle cx="250" cy="70" r="46" fill="#FFFFFF" fill-opacity=".35"/>'
			. $back
			. '<path d="M40 340c0-62 50-96 120-96s120 34 120 96Z" fill="#FFFFFF"/>'
			. '<path d="M132 246l28 46 28-46c-9-2-18-3-28-3s-19 1-28 3Z" style="fill:' . $outfit . '"/>'
			. '<path d="M122 249l38 91-40 0-24-74c8-8 16-13 26-17Z" fill="#EEF3F7"/><path d="M198 249l-38 91 40 0 24-74c-8-8-16-13-26-17Z" fill="#EEF3F7"/>'
			. '<rect x="210" y="292" width="34" height="8" rx="4" style="fill:' . $outfit . '" opacity=".5"/>'
			. '<rect x="144" y="196" width="32" height="54" rx="14" fill="' . $skin . '"/><rect x="144" y="196" width="32" height="30" rx="12" fill="#000" fill-opacity=".08"/>'
			. '<ellipse cx="160" cy="156" rx="50" ry="58" fill="' . $skin . '"/>'
			. '<ellipse cx="110" cy="162" rx="8" ry="12" fill="' . $skin . '"/><ellipse cx="210" cy="162" rx="8" ry="12" fill="' . $skin . '"/>'
			. $front
			. '</svg>';
	}
}
