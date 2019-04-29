<?php
/**
 * AMP AdManager Shortcode Class.
 *
 * @author  Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @package AMP_AdManager
 */

namespace AMP_AdManager;

/**
 * Class Shortcode.
 */
class Shortcode {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'ampad', [ $this, 'render_amp_ad' ] );
	}

	/**
	 * AMP Ad rendering handler.
	 *
	 * @param array  $atts    shortcode attributes.
	 * @param string $content current post content.
	 *
	 * @return string
	 */
	public function render_amp_ad( $attr = [], $content = '' ) {

		$default_attr = [
			'width'            => '300',
			'height'           => '250',
			'network-id'       => '',
			'ad-unit'          => '',
			'min'              => '',
			'max'              => '',
			'sizes'            => '300x250,300x100',
			'layout'           => 'fixed',
			'custom-targeting' => ''
		];

		$attr = shortcode_atts( $default_attr, $attr );

		/**
		 * Custom targeting for a specific amp-ad tag.
		 *
		 * Custom/User provided targeting added in shortcode to merged with default targeting array.
		 * Ex. custom-targeting="key1:value1, key2:value2".
		 *
		 * @since 0.2
		 */
		if ( isset( $attr['custom-targeting'] ) && ! empty ( $attr['custom-targeting'] ) ) {

			// Separate out all key values in array.
			$custom_targeting = explode( ',', trim( $attr['custom-targeting'] ) );

			if ( is_array( $custom_targeting ) && ! empty ( $custom_targeting ) ) {

				foreach ( $custom_targeting as $value ) {

					// Separate out individual targeting key values as $key => $value pair.
					$new_key_value = explode( ':', trim( $value ) );

					if ( ! empty ( $new_key_value ) ) {
						$attr['targeting'][ trim( $new_key_value[0] ) ] = trim( $new_key_value[1] );
					}
				}
			}
		}

		$ad_html = AMP_AdManager::get_amp_ad( $attr );

		if ( empty( $ad_html ) ) {
			return $content;
		}

		return $ad_html . $content;

	}
}
