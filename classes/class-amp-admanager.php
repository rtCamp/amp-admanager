<?php
/**
 * AMP AdManager main class.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @package AMP_AdManager
 */

namespace AMP_AdManager;

/**
 * Class AMP_AdManager.
 */
class AMP_AdManager {

	/**
	 * DFP Network ID.
	 *
	 * @var string
	 */
	public static $amp_settings;

	/**
	 * Class Constructor.
	 */
	public function __construct() {

		self::$amp_settings = get_option( 'amp-admanager-menu-settings' );

		/**
		 * Actions.
		 */
		add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ] );
		add_action( 'wp_default_scripts', [ $this, 'add_amp_default_scripts' ] );
	}

	/**
	 * Function used to create ads data.
	 *
	 * @return array Dfp setTargeting ad data.
	 */
	public static function get_dfp_ad_targeting_data() {

		$dfp_ad_data  = [];
		$content_type = '';
		$queried      = get_queried_object();

		if ( is_category() || is_tag() || is_archive() ) {

			$content_type = 'Listing Page';

			if ( is_category() ) {

				$dfp_ad_data['categoryPage'] = $queried->name;
			}

			if ( is_author() ) {

				if ( isset( $queried->data->display_name ) && null !== $queried->data->display_name ) {
					$dfp_ad_data['authorPage'] = $queried->data->display_name;
				} elseif ( isset( $queried->display_name ) && null !== $queried->display_name ) {
					$dfp_ad_data['authorPage'] = $queried->display_name;
				}
			}

			if ( is_tag() ) {

				$dfp_ad_data['tagPage'] = $queried->name;
			}
		} elseif ( is_front_page() || is_home() ) {

			$content_type = 'Home Page';
		} elseif ( is_single() ) {

			$content_type = ucwords( $queried->post_type );
			$category     = wp_get_post_terms(
				$queried->ID,
				'category',
				[
					'fields' => 'names',
				]
			);

			$dfp_ad_data['category'] = $category;
			$tag                     = wp_get_post_terms(
				$queried->ID,
				'post_tag',
				[
					'fields' => 'names',
				]
			);

			$dfp_ad_data['tag'] = $tag;
		}

		$dfp_ad_data['contentType'] = $content_type;

		$dfp_ad_data = apply_filters( 'amp_dfp_targeting_data', $dfp_ad_data );

		return $dfp_ad_data;
	}

	/**
	 * To get amp ad html code for all breakpoints.
	 *
	 * @param array   $attr shortcode attributes.
	 * @param boolean $echo whether to echo or return html code.
	 *
	 * @return string
	 */
	public static function get_amp_ad( $attr = [], $echo = false ) {

		if ( empty( $attr ) ) {
			return;
		}

		$ad_html = '';

		foreach ( $attr['breakpoint'] as $breakpoint ) {

			$ad_html .= sprintf(
				'<amp-ad width="%s" layout="fixed" height="%s" media="%s" type="doubleclick" data-slot="%s" json="%s" data-multi-size="%s" data-multi-size-validation="false"></amp-ad>',
				$attr['width'],
				$attr['height'],
				self::get_slot_media_query( $breakpoint ),
				'/' . self::$amp_settings['dfp-network-id'] . '/' . $attr['ad-unit'],
				wp_json_encode( self::get_dfp_ad_targeting_data() ),
				$breakpoint['sizes']
			);
		}

		if ( $echo ) {
			echo $ad_html; // phpcs:ignore
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
	public static function get_slot_media_query( $breakpoint ) {

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

	/**
	 * Registers amp default resources.
	 *
	 * @param WP_Scripts $wp_scripts global WP_Scrips object.
	 *
	 * @return void
	 */
	public function add_amp_default_scripts( $wp_scripts ) {

		// AMP Runtime script registration for wp_enqueue_script.
		$handle = 'amp-runtime';
		$wp_scripts->add(
			$handle,
			'https://cdn.ampproject.org/v0.js',
			array(),
			null
		);
		$wp_scripts->add_data( $handle, 'amp_script_attributes', array(
			'async' => true,
		) );

		// AMP Ad script registration for wp_enqueue_script.
		$handle = 'amp-ad';
		$wp_scripts->add(
			$handle,
			'https://cdn.ampproject.org/v0/amp-ad-0.1.js',
			array(),
			null
		);
		$wp_scripts->add_data( $handle, 'amp_script_attributes', array(
			'async'          => true,
			'custom-element' => 'amp-ad',
		) );

	}

	/**
	 * To load resources for AMP.
	 *
	 * @return void
	 */
	public function load_scripts() {

		$should_load_resources = self::$amp_settings['load-amp-resources'];

		if ( ! empty( $should_load_resources ) && '1' === $should_load_resources ) {
			if ( ! wp_script_is( 'amp-runtime' ) ) {
				wp_enqueue_script( 'amp-runtime' );
				wp_enqueue_script( 'amp-ad' ); // @todo This needs to check, it throws Error: amp-ad is already registered.
			}
		}
	}
}
