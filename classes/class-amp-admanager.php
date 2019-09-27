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
		 * Actions to load AMP resources - amp-runtime and amp-boilerplate css.
		 * It is important to use 0 priority to always load amp resources at the top of wp_head scripts.
		 */
		add_action( 'wp_head', [ $this, 'load_amp_resources' ], 0 );

	}

	/**
	 * Function used to create ads data.
	 *
	 * @param	array $attr Array of attributes supplied in ampad shortcode.
	 *
	 * @return	array Dfp setTargeting ad data.
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

				if ( ! empty( $queried->data->username ) ) {
					$dfp_ad_data['authorPage'] = $queried->data->username;
				} elseif ( ! empty( $queried->username ) ) {
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

			// Add post_name and postid as targeting variable for single posts.
			$dfp_ad_data['postName'] = $queried->post_name;
			$dfp_ad_data['postId']   = $queried->ID;

		} elseif ( is_page() ) {

			$content_type = $queried->post_type;
			// Add post_name and postid as targeting variable for single pages.
			$dfp_ad_data['postName'] = $queried->post_name;
			$dfp_ad_data['postId']   = $queried->ID;
		}

		$dfp_ad_data['contentType'] = sanitize_title( $content_type );
		$dfp_ad_data['siteDomain']  = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$dfp_ad_data['adId']        = trim( $attr['ad-unit'] ); // Remove trailing spaces.

		$final_ad_data = [];
		$final_ad_data['targeting'] = $dfp_ad_data;

		if ( ! empty( $attr['targeting'] ) ) {
			$final_ad_data['targeting'] = array_unique( array_merge( $dfp_ad_data, $attr['targeting'] ) );
		}

		/**
		 * amp_dfp_targeting_data filter to customize targeting variable.
		 */
		$final_ad_data['targeting'] = apply_filters( 'amp_dfp_targeting_data', $final_ad_data['targeting'], $attr );

		return $final_ad_data;
	}

	/**
	 * Get amp ad html code.
	 *
	 * @param array $attr shortcode attributes.
	 *
	 * @return string
	 */
	private static function get_amp_ad( $attr ) {

		if ( empty( $attr ) ) {
			return '';
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
		$layout = empty( $attr['layout'] ) ? 'responsive' : $attr['layout'];

		/**
		 * Add data-loading-strategy attribute.
		 * `prefer-viewability-over-views` will be default value.
		 * Supported values: float value in the range of [0, 3]
		 */
		$data_loading_strategy = empty( $attr['data-loading-strategy'] ) ? 'prefer-viewability-over-views' : $attr['data-loading-strategy'];

		/**
		 * Add data-enable-refresh attribute.
		 * `false` will be default value or attribute value is less than 30.
		 * Supported values: Integer value 30 or above.
		 */
		$ad_arefresh_rate = ( isset( $attr['ad-refresh'] ) && (int) $attr['ad-refresh'] >= 30 ) ? (int) $attr['ad-refresh'] : false;

		/**
		 * amp-ad markup.
		 */
		$ad_html = sprintf(
			'<amp-ad width="%s" height="%s" media="%s" type="doubleclick" data-slot="%s" json=\'%s\' data-multi-size="%s" data-multi-size-validation="false" layout="%s" data-loading-strategy="%s" data-enable-refresh=%s></amp-ad>',
			esc_attr( $attr['width'] ),
			esc_attr( $attr['height'] ),
			esc_attr( $media_query ),
			esc_attr( $data_slot ),
			esc_attr( $targeting_data_json ),
			esc_attr( $attr['sizes'] ),
			esc_attr( $layout ),
			esc_attr( $data_loading_strategy ),
			esc_attr( $ad_arefresh_rate )
		);

		return $ad_html;
	}

	/**
	 * Get amp ad html for all the sizes.
	 *
	 * @param array   $attr shortcode attributes.
	 * @param boolean $echo whether to echo or return html code.
	 *
	 * @return string
	 */
	public static function get_ads( $attr = [], $echo = false ) {
		$ad_html     = '';
		$breakpoints = [];

		// filter breakpoints for mobile , tablet, and desktop.
		if ( isset( $attr['sizes'] ) && ! empty( $attr['sizes'] ) ) {
			$breakpoints = self::filter_breakpoints( $attr['sizes'] );
		}

		// set priority for custom sizes for mobile, tablet, and desktop.
		$breakpoints = self::set_custom_sizes( $attr, $breakpoints );

		foreach ( $breakpoints as $device_type => $breakpoint ) {

			if ( ! isset( $breakpoint['sizes'] ) || empty( $breakpoint['sizes'] ) ) {
				continue;
			}

			// get height and width to set attribute value.
			$width  = $breakpoint['width'];
			$height = $breakpoint['height'];

			$sizes = implode( ',', $breakpoint['sizes'] );

			$attr['width']  = $width;
			$attr['height'] = $height;
			$attr['sizes']  = $sizes;

			// set max and min media query as per device type.
			switch ( $device_type ) {
				case 'desktop':
					$attr['max'] = '';
					$attr['min'] = 800;
					break;

				case 'tablet':
					$attr['max'] = 500;
					$attr['min'] = 799;
					break;
				case 'mobile':
					$attr['max'] = 499;
					$attr['min'] = '';
					break;
			}

			$ad_html .= self::get_amp_ad( $attr );
		}

		if ( $echo ) {
			echo $ad_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $ad_html;
	}

	/**
	 * Filter sizes for all 3 device type breakpoints.
	 *
	 * @param string $sizes coma separated size dimensions.
	 *
	 * @return array of breakpoints.
	 */
	private static function filter_breakpoints( $sizes ) {

		$breakpoints['mobile']  = [];
		$breakpoints['tablet']  = [];
		$breakpoints['desktop'] = [];

		$dimensions = explode( ',', $sizes );

		foreach ( $dimensions as $dimension ) {

			list( $width, $height ) = explode( 'x', $dimension );

			// filter ads from width of the dimensions.
			if ( 728 <= (int) $width ) {
				$breakpoints = self::set_max_height_and_width( 'desktop', $breakpoints, $width, $height );
			} elseif ( 300 <= $width && 600 >= $width ) {
				$breakpoints = self::set_max_height_and_width( 'tablet', $breakpoints, $width, $height );
			} else {
				$breakpoints = self::set_max_height_and_width( 'mobile', $breakpoints, $width, $height );
			}
		}

		return $breakpoints;

	}

	/**
	 * Validate and set maximum width and height needed for ad container.
	 *
	 * @param string $device_type desktop, mobile, or tablet.
	 * @param array  $breakpoints contains breakpoints.
	 * @param string $width       width.
	 * @param string $height      height.
	 *
	 * @return array of updated width, height and sizes.
	 */
	private static function set_max_height_and_width( $device_type, $breakpoints, $width, $height ) {

		if ( ! isset( $breakpoints[ $device_type ]['width'] ) ||
			(int) $width > (int) $breakpoints[ $device_type ]['width'] ) {
			$breakpoints[ $device_type ]['width'] = $width;
		}

		if ( ! isset( $breakpoints[ $device_type ]['height'] ) ||
			(int) $height > (int) $breakpoints[ $device_type ]['height'] ) {
			$breakpoints[ $device_type ]['height'] = $height;
		}

		$breakpoints[ $device_type ]['sizes'][] = sprintf( '%sx%s', $width, $height );

		return $breakpoints;
	}

	/**
	 * Set custom sizes for different device type.
	 *
	 * @param array $attr        attributes containing custom size.
	 * @param array $breakpoints default dimensions.
	 *
	 * @return array of new breakpoint custom sizes
	 */
	private static function set_custom_sizes( $attr, $breakpoints ) {

		// set custom desktop size if passed.
		if ( isset( $attr['desktop-sizes'] ) && ! empty( $attr['desktop-sizes'] ) ) {
			// set blank array to overwrite sizes attribute.
			$breakpoints['desktop'] = [];
			foreach ( explode( ',', $attr['desktop-sizes'] ) as $size ) {
				list( $width, $height ) = explode( 'x', $size );

				$breakpoints = self::set_max_height_and_width( 'desktop', $breakpoints, $width, $height );
			}
		}

		// set custom tablet size if passed.
		if ( isset( $attr['tablet-sizes'] ) && ! empty( $attr['tablet-sizes'] ) ) {
			$breakpoints['tablet'] = [];
			foreach ( explode( ',', $attr['tablet-sizes'] ) as $size ) {
				list( $width, $height ) = explode( 'x', $size );

				$breakpoints = self::set_max_height_and_width( 'tablet', $breakpoints, $width, $height );
			}
		}

		// set custom mobile size if passed.
		if ( isset( $attr['mobile-sizes'] ) && ! empty( $attr['mobile-sizes'] ) ) {
			$breakpoints['mobile'] = [];
			foreach ( explode( ',', $attr['mobile-sizes'] ) as $size ) {
				list( $width, $height ) = explode( 'x', $size );

				$breakpoints = self::set_max_height_and_width( 'mobile', $breakpoints, $width, $height );
			}
		}

		return $breakpoints;
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

		// Add DFP single request mode for ad rendering ref - https://github.com/ampproject/amphtml/blob/master/extensions/amp-ad-network-doubleclick-impl/sra.md for more info.
		?>
		<meta name="amp-ad-doubleclick-sra" />
		<?php

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
