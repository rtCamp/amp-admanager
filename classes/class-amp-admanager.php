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

		/**
		 * Filters.
		 */
		if ( ! is_admin() ) {
			add_filter( 'script_loader_tag', [ $this, 'add_script_async_attribute' ], 10, 2 );
		}
	}

	/**
	 * Function used to create ads data.
	 *
	 * @return array Dfp setTargeting ad data.
	 */
	public static function get_dfp_ad_targeting_data( $attr ) {

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
		$dfp_ad_data['siteDomain']  = wp_parse_url( home_url(), PHP_URL_HOST );
		$dfp_ad_data['adId']        = $attr['ad-unit'];
		$dfp_ad_data['adUnit']      = '/' . self::$amp_settings['dfp-network-id'] . '/' . $attr['ad-unit'];

		$final_ad_data['targeting'] = apply_filters( 'amp_dfp_targeting_data', $dfp_ad_data, $attr );

		return $final_ad_data;
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

		$ad_html = sprintf(
			'<amp-ad width="%s" layout="fixed" height="%s" media="%s" type="doubleclick" data-slot="%s" json=%s data-multi-size="%s" data-multi-size-validation="false"></amp-ad>',
			$attr['width'],
			$attr['height'],
			self::get_slot_media_query( $attr['min'], $attr['max'] ),
			'/' . self::$amp_settings['dfp-network-id'] . '/' . $attr['ad-unit'],
			wp_json_encode( self::get_dfp_ad_targeting_data( $attr ) ),
			$attr['sizes']
		);

		if ( $echo ) {
			echo $ad_html; // phpcs:ignore
		}

		return $ad_html;
	}

	/**
	 * To get ad slot media query in proper format.
	 *
	 * @param string $min min size of amp-ad media query.
	 * @param string $max max size of amp-ad media query.
	 *
	 * @return string
	 */
	public static function get_slot_media_query( $min, $max ) {

		$media = '';

		if ( ! empty( $min ) ) {
			$media = '(min-width: ' . $min . 'px)';
		}

		if ( ! empty( $media ) && ! empty( $max ) ) {
			$media .= ' and ';
		}

		if ( ! empty( $max ) ) {
			$media .= '(max-width: ' . $max . 'px)';
		}

		return $media;
	}

	/**
	 * Add async parameter in amp scripts while enqueueing.
	 *
	 * @param string $tag    enqueueing script tag.
	 * @param string $handle script enqueue handle.
	 *
	 * @return string
	 */
	public function add_script_async_attribute( $tag, $handle ) {

		if ( 'amp-runtime' !== $handle || false !== strpos( $tag, 'async' ) ) {
			return $tag;
		}

		$tag = preg_replace(
			':(?=></script>):',
			'async',
			$tag,
			1
		);

		return $tag;
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

				/**
				 * Adding amp-runtime only.
				 * loading amp-ad throws Error: amp-ad is already registered.
				 * This is because custom-element.js loads the amp-ad script.
				 * amp-ad is included in amp-runtime so we don't need to enqueue it explicitly.
				 * https://www.ampproject.org/docs/fundamentals/spec#resourcess
				 */
				wp_enqueue_script(
					'amp-runtime',
					'https://cdn.ampproject.org/v0.js'
				);

				// Load template for amp boilerplate style sheet.
				load_template( AMP_ADMANAGER_ROOT . '/template-parts/amp-boilerplate-css.php' );
			}
		}
	}
}
