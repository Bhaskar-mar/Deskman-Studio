<?php
/**
 * Colour helpers shared by templates (mirrored in the templates' JS so the
 * server-rendered defaults and live updates produce identical results).
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

/**
 * Colour utilities.
 */
class DMT_Color {

	/**
	 * Hex to RGB.
	 *
	 * @param string $hex #RGB or #RRGGBB.
	 * @return int[]
	 */
	public static function rgb( $hex ) {
		$hex = ltrim( (string) sanitize_hex_color( $hex ), '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( 6 !== strlen( $hex ) ) {
			return array( 0, 0, 0 );
		}
		return array_map( 'hexdec', str_split( $hex, 2 ) );
	}

	/**
	 * WCAG relative luminance.
	 *
	 * @param string $hex Colour.
	 * @return float
	 */
	public static function luminance( $hex ) {
		$c = array_map(
			function ( $v ) {
				$v /= 255;
				return $v <= 0.03928 ? $v / 12.92 : pow( ( $v + 0.055 ) / 1.055, 2.4 );
			},
			self::rgb( $hex )
		);
		return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
	}

	/**
	 * Readable text colour (white or deep ink) on a background.
	 *
	 * @param string $hex Background.
	 * @return string
	 */
	public static function on( $hex ) {
		$l = self::luminance( $hex );
		// Contrast with white vs. with #0B1B2B (luminance ~0.011).
		return ( 1.05 / ( $l + 0.05 ) ) >= ( ( $l + 0.05 ) / 0.061 ) ? '#FFFFFF' : '#0B1B2B';
	}

	/**
	 * A version of the colour dark enough to use as text on white.
	 *
	 * @param string $hex Colour.
	 * @return string
	 */
	public static function ink( $hex ) {
		$rgb = self::rgb( $hex );
		for ( $i = 0; $i < 12 && self::luminance( self::hex( $rgb ) ) > 0.18; $i++ ) {
			$rgb = array_map(
				function ( $v ) {
					return (int) round( $v * 0.85 );
				},
				$rgb
			);
		}
		return self::hex( $rgb );
	}

	/**
	 * RGB to hex.
	 *
	 * @param int[] $rgb RGB.
	 * @return string
	 */
	public static function hex( $rgb ) {
		return sprintf( '#%02X%02X%02X', $rgb[0], $rgb[1], $rgb[2] );
	}

	/**
	 * CSS custom properties for a brand palette.
	 *
	 * @param string $primary   Primary.
	 * @param string $secondary Secondary.
	 * @param string $accent    Accent.
	 * @return string CSS declarations.
	 */
	public static function css_vars( $primary, $secondary, $accent ) {
		$vars = array(
			'--c-primary'      => $primary,
			'--c-secondary'    => $secondary,
			'--c-accent'       => $accent,
			'--c-on-primary'   => self::on( $primary ),
			'--c-on-secondary' => self::on( $secondary ),
			'--c-on-accent'    => self::on( $accent ),
			'--c-primary-ink'  => self::ink( $primary ),
			'--c-secondary-ink' => self::ink( $secondary ),
		);
		$out = '';
		foreach ( $vars as $name => $value ) {
			$out .= $name . ':' . $value . ';';
		}
		return $out;
	}
}
