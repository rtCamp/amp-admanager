<?php
/**
 * AMP AdManager Shortcode Class.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @package AMP_AdManager
 */

namespace AMP_AdManager\Shortcode;

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
			'json'       => '',
			'breakpoint' => wp_json_encode( $ad_breakpoint ),
		];

		$atts = shortcode_atts( $default_attr, $atts );

		$atts['breakpoint'] = json_decode( $atts['breakpoint'], true );

		$ad_html = $this->get_amp_ad_html( $atts );

		if ( empty( $ad_html ) ) {
			return $content;
		}

		return $ad_html . $content;

	}

	/**
	 * To get amp ad html code for all breakpoints.
	 *
	 * @param array $atts shortcode attributes.
	 *
	 * @return string
	 */
	public function get_amp_ad_html( $atts = [] ) {

		if ( empty( $atts ) ) {
			return;
		}

		$ad_html = '';

		foreach ( $atts['breakpoint'] as $breakpoint ) {
			$ad_html .= sprintf(
				'<amp-ad width="%s" layout="fixed" height="%s" media="%s" type="doubleclick" data-slot="%s" json="" data-multi-size="" data-multi-size-validation="false"></amp-ad>',
				$atts['width'],
				$atts['height'],
				$this->get_slot_media_query( $breakpoint ),
				'/' . $atts['network-id'] . '/' . $atts['ad-unit'],
				$atts['json'],
				$breakpoint['sizes']
			);
		}

		return $ad_html;
	}

	/**
	 * To get ad slot media query in proper format.
	 *
	 * @param array $breakpoint ad-slot brekpoint data.
	 *
	 * @return string
	 */
	public function get_slot_media_query( $breakpoint ) {

		$media = '';

		if ( ! empty( $breakpoint['min'] ) ) {
			$media = '(min-width: ' . $breakpoint['min'] . 'px)';
		}

		if ( ! empty( $breakpoint['min'] ) && ! empty( $breakpoint['max'] ) ) {
			$media .= ' and ';
		}

		if ( $breakpoint['max'] ) {
			$media .= '(max-width: ' . $breakpoint['max'] . 'px)';
		}

		return $media;
	}

}
