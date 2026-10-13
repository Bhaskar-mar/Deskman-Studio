<?php
/**
 * Template field schema helpers: loading, defaults and sanitisation.
 *
 * @package DeskmanTemplates
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field schema helpers.
 */
class DMT_Fields {

	/**
	 * Supported field types.
	 */
	const TYPES = array( 'text', 'textarea', 'tel', 'email', 'url', 'color', 'logo' );

	/**
	 * Cached schemas, keyed by template id.
	 *
	 * @var array
	 */
	private static $schemas = array();

	/**
	 * Grouped field schema for a template.
	 *
	 * @param string $template_id Template id.
	 * @return array
	 */
	public static function groups( $template_id ) {
		if ( ! isset( self::$schemas[ $template_id ] ) ) {
			$template = DMT_Registry::template( $template_id );
			$groups   = array();
			if ( $template && is_readable( $template['dir'] . 'fields.php' ) ) {
				$groups = require $template['dir'] . 'fields.php';
			}
			$groups = apply_filters( 'dmt_template_fields', (array) $groups, $template_id );

			foreach ( $groups as $group_id => $group ) {
				foreach ( (array) $group['fields'] as $key => $field ) {
					$field = wp_parse_args(
						$field,
						array(
							'type'     => 'text',
							'label'    => $key,
							'default'  => '',
							'max'      => 200,
							'help'     => '',
							'quick'    => false,
							'required' => false,
							'rows'     => 3,
						)
					);
					if ( ! in_array( $field['type'], self::TYPES, true ) ) {
						$field['type'] = 'text';
					}
					$groups[ $group_id ]['fields'][ $key ] = $field;
				}
			}
			self::$schemas[ $template_id ] = $groups;
		}
		return self::$schemas[ $template_id ];
	}

	/**
	 * Flat map of field key => definition.
	 *
	 * @param string $template_id Template id.
	 * @return array
	 */
	public static function flat( $template_id ) {
		$flat = array();
		foreach ( self::groups( $template_id ) as $group ) {
			foreach ( $group['fields'] as $key => $field ) {
				$flat[ $key ] = $field;
			}
		}
		return $flat;
	}

	/**
	 * Default (demo) values for a template.
	 *
	 * @param string $template_id Template id.
	 * @return array
	 */
	public static function defaults( $template_id ) {
		return wp_list_pluck( self::flat( $template_id ), 'default' );
	}

	/**
	 * Sanitise a single value according to its field definition.
	 *
	 * @param array $field Field definition.
	 * @param mixed $value Raw value.
	 * @return string Sanitised value ('' when invalid).
	 */
	public static function sanitize_value( array $field, $value ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}
		$value = (string) $value;
		$max   = max( 1, (int) $field['max'] );

		switch ( $field['type'] ) {
			case 'textarea':
				$value = sanitize_textarea_field( $value );
				break;
			case 'email':
				$value = sanitize_email( $value );
				$value = is_email( $value ) ? $value : '';
				break;
			case 'tel':
				$value = trim( preg_replace( '/[^0-9+()\-.\s]/', '', $value ) );
				$value = preg_replace( '/\s+/', ' ', $value );
				break;
			case 'url':
				$value = esc_url_raw( trim( $value ), array( 'http', 'https' ) );
				break;
			case 'color':
				$value = sanitize_hex_color( trim( $value ) );
				$value = $value ? strtoupper( $value ) : '';
				break;
			case 'logo':
				// Logos are uploaded as files, never accepted as field values.
				return '';
			default:
				$value = sanitize_text_field( $value );
		}

		return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $max ) : substr( $value, 0, $max );
	}

	/**
	 * Sanitise a full customisation payload against the schema.
	 *
	 * Unknown keys are discarded. Empty or invalid required values fall back
	 * to the template default, matching what the visitor saw in the preview.
	 *
	 * @param string $template_id Template id.
	 * @param array  $raw         Raw key => value data.
	 * @return array
	 */
	public static function sanitize_customisation( $template_id, $raw ) {
		$raw   = is_array( $raw ) ? $raw : array();
		$clean = array();
		foreach ( self::flat( $template_id ) as $key => $field ) {
			if ( 'logo' === $field['type'] ) {
				continue;
			}
			$value = isset( $raw[ $key ] ) ? self::sanitize_value( $field, $raw[ $key ] ) : '';
			if ( '' === $value && $field['required'] ) {
				$value = (string) $field['default'];
			}
			$clean[ $key ] = $value;
		}
		return $clean;
	}

	/**
	 * Schema prepared for the customiser JavaScript.
	 *
	 * @param string $template_id Template id.
	 * @return array
	 */
	public static function for_js( $template_id ) {
		$out = array();
		foreach ( self::flat( $template_id ) as $key => $field ) {
			$out[ $key ] = array(
				'type'     => $field['type'],
				'label'    => $field['label'],
				'default'  => $field['default'],
				'max'      => (int) $field['max'],
				'required' => (bool) $field['required'],
			);
		}
		return $out;
	}
}
