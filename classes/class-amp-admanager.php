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
		 * Actions to load AMP resources and more.
		 */
		add_action( 'wp_head', [ $this, 'load_amp_resources' ], 0 );

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

			$content_type = 'listingpage';

			if ( is_category() ) {

				$dfp_ad_data['categoryPage'] = $queried->slug;
			}

			if ( is_author() ) {

				if ( isset( $queried->data->username ) && null !== $queried->data->username ) {
					$dfp_ad_data['authorPage'] = $queried->data->username;
				} elseif ( isset( $queried->username ) && null !== $queried->username ) {
					$dfp_ad_data['authorPage'] = $queried->username;
				}
			}

			if ( is_tag() ) {

				$dfp_ad_data['tagPage'] = $queried->slug;
			}
		} elseif ( is_front_page() ) {

			$content_type = 'homepage';

		} elseif ( is_single() ) {

			$content_type = $queried->post_type;
			$category     = wp_get_post_terms(
				$queried->ID,
				'category',
				[
					'fields' => 'slugs',
				]
			);

			$dfp_ad_data['postCategories'] = $category;
			$tag                     = wp_get_post_terms(
				$queried->ID,
				'post_tag',
				[
					'fields' => 'slugs',
				]
			);

			$dfp_ad_data['postTags'] = $tag;

			// Add post_name and postid as targeting variable for single posts/pages.
			$dfp_ad_data['postName'] = $queried->post_name;
			$dfp_ad_data['postId']   = $queried->ID;
		}

		$dfp_ad_data['contentType'] = sanitize_title( $content_type );
		$dfp_ad_data['siteDomain']  = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$dfp_ad_data['adId']        = trim( $attr['ad-unit'] ); // Remove trailing spaces.

		$final_ad_data['targeting'] = $dfp_ad_data;

		if ( isset( $attr['targeting'] ) ) {
			$final_ad_data['targeting'] = array_unique( array_merge( $dfp_ad_data, $attr['targeting'] ) );
		}

		$final_ad_data['targeting'] = apply_filters( 'amp_dfp_targeting_data', $final_ad_data['targeting'], $attr );

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

		/**
		 * Use network-id attribute for allowing other networks ads such as AdX.
		 * Example:
		 * data-slot="/10000/another-adunit-code"
		 *
		 * By default, network id will be taken from amp-admanager plugin options page if network-id attribute is not provided.
		 *
		 * @since 0.3
		 */
		$network_id = ( ! empty( $attr['network-id'] ) ) ? $attr['network-id'] : self::$amp_settings['dfp-network-id'];
		$data_slot  = sprintf( '/%s/%s', $network_id, $attr['ad-unit'] );

		$media_query = self::get_slot_media_query( $attr['min'], $attr['max'] );

		/**
		 * Encode targeting data for json attribute in amp-ad.
		 */
		$targeting_data_json = wp_json_encode( self::get_dfp_ad_targeting_data( $attr ) );

		/**
		 * Add given layout attribute in the amp-ad. Use `responsive` by default.
		 * Supported layout attributes: fill, fixed, fixed-height, flex-item, intrinsic, nodisplay, responsive.
		 * Ref - https://amp.dev/documentation/components/amp-ad doc.
		 *
		 * @since 0.2
		 */
		$layout = ( empty( $attr['layout'] ) ? 'responsive' : esc_attr( $attr['layout'] ) );

		/**
		 * amp-ad markup.
		 */
		$ad_html = sprintf(
			'<amp-ad width="%s" height="%s" media="%s" type="doubleclick" data-slot="%s" json=\'%s\' data-multi-size="%s" data-multi-size-validation="false" layout="%s"></amp-ad>',
			esc_attr( $attr['width'] ),
			esc_attr( $attr['height'] ),
			esc_attr( $media_query ),
			esc_attr( $data_slot ),
			esc_attr( $targeting_data_json ),
			esc_attr( $attr['sizes'] ),
			esc_attr( $layout )
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
	 * Load amp boilerplate css only on Non-AMP pages for better elements loading.
	 * Also loads `amp-runtime` script at the top of wp_head due to script sequencing issues.
	 *
	 * @return void
	 */
	public function load_amp_resources() {

		// Check if current page is amp page. 
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			return;
		}

		$should_load_resources = self::$amp_settings['load-amp-resources'];

		if ( ! empty( $should_load_resources ) && '1' === $should_load_resources ) {

			/**
			 * Loads amp-boilerplate CSS and amp-runtime script at the top of wp_head.
			 *
			 * Only if `load amp resources` is enabled.
			 *
			 * @since 0.2
			 */
			load_template( AMP_ADMANAGER_ROOT . '/template-parts/amp-resources.php' );

		}
	}
}
