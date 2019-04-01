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

		$ad_breakpoint = [
			[
				'min'   => '',
				'max'   => '499',
				'sizes' => '320x50,300x100',
			],
			[
				'min'   => '500',
				'max'   => '799',
				'sizes' => '468x60',
			],
			[
				'min'   => '800',
				'max'   => '',
				'sizes' => '728x90,600x90',
			],
		];

		$default_attr = [
			'network-id' => '104683778',
			'width'      => '300',
			'height'     => '250',
			'ad-unit'    => '',
			'breakpoint' => wp_json_encode( $ad_breakpoint ),
		];

		$atts = shortcode_atts( $default_attr, $atts );

		$atts['breakpoint'] = json_decode( $atts['breakpoint'], true );

		$ad_html = AMP_AdManager::get_adm_ad( $atts );

		if ( empty( $ad_html ) ) {
			return $content;
		}

		return $ad_html . $content;

	}
}
