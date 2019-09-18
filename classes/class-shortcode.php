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
	 * @param array  $attr    shortcode attributes.
	 * @param string $content current post content.
	 *
	 * @return string
	 */
	public function render_amp_ad( $attr = [], $content = '' ) {

		// defaut sizes.
		$sizes = '300x250,300x100';

		if ( isset( $attr['sizes'] ) ) {
			// Pass user defined sizes if defined.
			$sizes = $attr['sizes'];
		} elseif ( isset( $attr['mobile-sizes'] ) ||
			isset( $attr['tablet-sizes'] ) ||
			isset( $attr['desktop-sizes'] ) ) {
			// Pass blank if any of custom sizes defined and sizes not defined.
			$sizes = '';
		}

		$default_attr = [
			'network-id'       => '',
			'ad-unit'          => '',
			'desktop-sizes'    => '',
			'tablet-sizes'     => '',
			'mobile-sizes'     => '',
			'sizes'            => $sizes,
			'layout'           => 'fixed',
			'custom-targeting' => '',
			'ad-refresh'       => false,
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
		if ( ! empty( $attr['custom-targeting'] ) ) {

			// Separate out all key values in array.
			$custom_targeting = explode( ',', trim( $attr['custom-targeting'] ) );

			if ( ! empty( $custom_targeting ) ) {

				foreach ( $custom_targeting as $value ) {

					// Separate out individual targeting key values as $key => $value pair.
					$new_key_value = explode( ':', trim( $value ) );

					if ( ! empty( $new_key_value ) ) {
						$attr['targeting'][ trim( $new_key_value[0] ) ] = trim( $new_key_value[1] );
					}
				}
			}
		}

		$ad_html = AMP_AdManager::get_ads( $attr );

		if ( empty( $ad_html ) ) {
			return $content;
		}

		return $ad_html . $content;

	}
}
