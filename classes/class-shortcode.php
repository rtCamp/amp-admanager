<?php
/**
 * AMP AdManager Shortcode Class.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
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
	public function render_amp_ad( $atts = [], $content = '' ) {

		$default_attr = [
			'width'   => '300',
			'height'  => '250',
			'ad-unit' => '',
			'min'     => '',
			'max'     => '',
			'sizes'   => '300x250,300x100',
		];

		$atts = shortcode_atts( $default_attr, $atts );

		$ad_html = AMP_AdManager::get_amp_ad( $atts );

		if ( empty( $ad_html ) ) {
			return $content;
		}

		return $ad_html . $content;

	}
}
