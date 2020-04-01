<?php
/**
 * Class Test_AMP_AdManager for test case post type.
 *
 * @package AMP_AdManager
 */

namespace AMP_AdManager\Tests;

use AMP_AdManager\AMP_AdManager;

/**
 * Unit Test for AMP_AdManager\AMP_AdManager
 *
 * @coversDefaultClass \AMP_AdManager\AMP_AdManager
 *
 * @group AMP_AdManager
 *
 * @package AMP_AdManager
 */
class Test_AMP_AdManager extends \WP_UnitTestCase {

	/**
	 * AMP_AdManager instance
	 *
	 * @var AMP_AdManager
	 */
	protected $_instance;

	/**
	 * Setup instance
	 */
	public function setup() {
		parent::setup();

		$this->_instance = new AMP_AdManager();
	}

	/**
	 * Tests class constructor.
	 *
	 * @covers ::__construct
	 */
	public function test_construct() {

		Utility::invoke_method( $this->_instance, '__construct' );

		$hooks = [
			[
				'type'     => 'action',
				'name'     => 'wp_head',
				'priority' => 0,
				'function' => 'load_amp_resources',
			],
		];

		// Check if hooks loaded.
		foreach ( $hooks as $hook ) {

			$this->assertEquals(
				$hook['priority'],
				call_user_func(
					sprintf( 'has_%s', $hook['type'] ),
					$hook['name'],
					[
						$this->_instance,
						$hook['function'],
					]
				),
				sprintf( 'AMP_AdManager::__construct() failed to register %1$s "%2$s" to %3$s()', $hook['type'], $hook['name'], $hook['function'] )
			);
		}

	}

	/**
	 * Tests `get_dfp_ad_targeting_data` function.
	 *
	 * @covers ::get_dfp_ad_targeting_data
	 *
	 * @return void
	 */
	public function test_get_dfp_ad_targeting_data() {

		if ( ! empty( $GLOBALS['wp_query'] ) ) {
			$old_wp_query = $GLOBALS['wp_query'];
		}

		$post_id = $this->factory->post->create( [ 'post_type' => 'post' ] );

		$conditions = [ 'is_home' => true ];
		Utility::mock_wp_query(
			[
				'post_type'      => 'post',
				'posts_per_page' => 1,
			],
			$conditions
		);

		$attr   = [
			'ad-unit'   => 'AMP_ADTest',
			'sizes'     => '336x280',
			'targeting' => [
				'contentType' => '',
				'siteDomain'  => 'example.com',
				'adId'        => 'AMP_ADTest',
			],
		];
		$output = AMP_AdManager::get_dfp_ad_targeting_data( $attr );

		$expected_output = [
			'targeting' => [
				'contentType' => '',
				'siteDomain'  => 'example.com',
				'adId'        => 'AMP_ADTest',
			],
		];

		$this->assertNotEmpty( $output );
		$this->assertArrayHasKey( 'targeting', $output );
		$this->assertEquals( $expected_output, $output );

		// Test case for is_single() condition.
		$conditions = [
			'is_home'     => false,
			'is_single'   => true,
			'is_singular' => true,
		];
		Utility::mock_wp_query(
			[
				'post_type'      => 'post',
				'posts_per_page' => 1,
			],
			$conditions
		);

		$attr   = [
			'ad-unit' => 'AMP_ADTest',
			'sizes'   => '336x280',
		];
		$output = AMP_AdManager::get_dfp_ad_targeting_data( $attr );

		$this->assertNotEmpty( $output );
		$this->assertArrayHasKey( 'targeting', $output );
		$this->assertArrayHasKey( 'postCategories', $output['targeting'] );
		$this->assertArrayHasKey( 'postName', $output['targeting'] );
		$this->assertArrayHasKey( 'contentType', $output['targeting'] );
		$this->assertArrayHasKey( 'adId', $output['targeting'] );
		$this->assertEquals( $output['targeting']['adId'], $attr['ad-unit'] );
		$this->assertEquals( $output['targeting']['contentType'], 'post' );

		// Test case for is_page() condition.
		$this->factory->post->create( [ 'post_type' => 'page' ] );
		$conditions = [
			'is_home'     => false,
			'is_page'     => true,
			'is_singular' => true,
		];
		Utility::mock_wp_query(
			[
				'post_type'      => 'page',
				'posts_per_page' => 1,
			],
			$conditions
		);

		$output = AMP_AdManager::get_dfp_ad_targeting_data( $attr );

		$this->assertNotEmpty( $output );
		$this->assertArrayHasKey( 'targeting', $output );
		$this->assertArrayHasKey( 'postName', $output['targeting'] );
		$this->assertArrayHasKey( 'contentType', $output['targeting'] );
		$this->assertArrayHasKey( 'adId', $output['targeting'] );
		$this->assertEquals( $output['targeting']['adId'], $attr['ad-unit'] );
		$this->assertEquals( $output['targeting']['contentType'], 'page' );

		// Test case for is_archive() condition.
		$conditions = [ 'is_archive' => true ];
		Utility::mock_wp_query( [ 'post_type' => 'page' ], $conditions );

		$output = AMP_AdManager::get_dfp_ad_targeting_data( $attr );

		$this->assertNotEmpty( $output );
		$this->assertArrayHasKey( 'targeting', $output );
		$this->assertArrayHasKey( 'contentType', $output['targeting'] );
		$this->assertArrayHasKey( 'adId', $output['targeting'] );
		$this->assertEquals( $output['targeting']['adId'], $attr['ad-unit'] );
		$this->assertEquals( $output['targeting']['contentType'], 'listingpage' );

		// Test case for is_category() condition.
		$term       = $this->factory->category->create_and_get(
			[
				'name'   => 'Parent',
				'slug'   => 'parent',
				'parent' => 0,
			]
		);
		$conditions = [ 'is_category' => true ];
		Utility::mock_wp_query(
			[
				'post_type'     => 'page',
				'category_name' => 'Parent',
				'cat'           => $term->term_id,
			],
			$conditions
		);

		$output = AMP_AdManager::get_dfp_ad_targeting_data( $attr );

		$this->assertNotEmpty( $output );
		$this->assertArrayHasKey( 'targeting', $output );
		$this->assertArrayHasKey( 'contentType', $output['targeting'] );
		$this->assertArrayHasKey( 'categoryPage', $output['targeting'] );
		$this->assertArrayHasKey( 'adId', $output['targeting'] );
		$this->assertEquals( $output['targeting']['adId'], $attr['ad-unit'] );
		$this->assertEquals( $output['targeting']['contentType'], 'listingpage' );
		$this->assertEquals( $output['targeting']['categoryPage'], 'parent' );

		// Test case for is_author() condition.
		$user_id    = $this->factory->user->create( [ 'user_login' => 'testuser' ] );
		$conditions = [
			'is_author'  => true,
			'is_home'    => false,
			'is_archive' => true,
		];
		Utility::mock_wp_query(
			[
				'post_type' => 'page',
				'author'    => $user_id,
			],
			$conditions
		);

		$output = AMP_AdManager::get_dfp_ad_targeting_data( $attr );

		$this->assertNotEmpty( $output );
		$this->assertArrayHasKey( 'targeting', $output );
		$this->assertArrayHasKey( 'contentType', $output['targeting'] );
		$this->assertArrayHasKey( 'adId', $output['targeting'] );
		$this->assertEquals( $output['targeting']['adId'], $attr['ad-unit'] );
		$this->assertEquals( $output['targeting']['contentType'], 'listingpage' );

		// Restore global wp_query.
		if ( ! empty( $old_wp_query ) ) {
			$GLOBALS['wp_query'] = $old_wp_query;
		}
	}

	/**
	 * Tests `get_amp_ad` function.
	 *
	 * @covers ::get_amp_ad
	 *
	 * @return void
	 */
	public function test_get_amp_ad() {

		$expected_output = '<amp-ad width="336" height="280" media="(min-width: 500px) and (max-width: 799px)" type="doubleclick" data-slot="/123456789/AMP_ADTest" json=\'{&quot;targeting&quot;:{&quot;contentType&quot;:&quot;&quot;,&quot;siteDomain&quot;:&quot;example.org&quot;,&quot;adId&quot;:&quot;AMP_ADTest&quot;}}\' data-multi-size="336x280" data-multi-size-validation="false" layout="responsive" data-loading-strategy="prefer-viewability-over-views" data-enable-refresh=></amp-ad>';
		$attr            = [
			'ad-unit' => 'AMP_ADTest',
			'max'     => 799,
			'min'     => 500,
			'width'   => 336,
			'height'  => 280,
			'sizes'   => '336x280',
		];
		$output          = Utility::invoke_method( $this->_instance, 'get_amp_ad', [ $attr ] );

		$this->assertNotEmpty( $output );
		$this->assertEquals( $expected_output, $output );

		// Test case for blank attributes.
		$output = Utility::invoke_method( $this->_instance, 'get_amp_ad', [ [] ] );
		$this->assertEmpty( $output );

	}

	/**
	 * Tests `get_ads` function.
	 *
	 * @covers ::get_ads
	 *
	 * @return void
	 */
	public function test_get_ads() {

		// Mobile Ads.
		$expected_output = '<amp-ad width="300" height="250" media="(max-width: 499px)" type="doubleclick" data-slot="/123456789/AMP_ADTest" json=\'{&quot;targeting&quot;:{&quot;contentType&quot;:&quot;&quot;,&quot;siteDomain&quot;:&quot;example.org&quot;,&quot;adId&quot;:&quot;AMP_ADTest&quot;}}\' data-multi-size="300x250" data-multi-size-validation="false" layout="fixed" data-loading-strategy="prefer-viewability-over-views" data-enable-refresh=></amp-ad>';
		$ad_attr         = [
			'ad-unit'      => 'AMP_ADTest',
			'mobile-sizes' => '300x250',
			'layout'       => 'fixed',
		];
		$output          = AMP_AdManager::get_ads( $ad_attr );

		$this->assertNotEmpty( $output );
		$this->assertEquals( $expected_output, $output );

		// Tablet Ads.
		$expected_output = '<amp-ad width="336" height="280" media="(min-width: 500px) and (max-width: 799px)" type="doubleclick" data-slot="/123456789/AMP_ADTest" json=\'{&quot;targeting&quot;:{&quot;contentType&quot;:&quot;&quot;,&quot;siteDomain&quot;:&quot;example.org&quot;,&quot;adId&quot;:&quot;AMP_ADTest&quot;}}\' data-multi-size="336x280" data-multi-size-validation="false" layout="fixed" data-loading-strategy="prefer-viewability-over-views" data-enable-refresh=></amp-ad>';
		$ad_attr         = [
			'ad-unit'      => 'AMP_ADTest',
			'tablet-sizes' => '336x280',
			'layout'       => 'fixed',
		];
		$output          = AMP_AdManager::get_ads( $ad_attr );

		$this->assertNotEmpty( $output );
		$this->assertEquals( $expected_output, $output );

		// Desktop ads.
		$expected_output = '<amp-ad width="970" height="250" media="(min-width: 800px)" type="doubleclick" data-slot="/123456789/AMP_ADTest" json=\'{&quot;targeting&quot;:{&quot;contentType&quot;:&quot;&quot;,&quot;siteDomain&quot;:&quot;example.org&quot;,&quot;adId&quot;:&quot;AMP_ADTest&quot;}}\' data-multi-size="970x250" data-multi-size-validation="false" layout="responsive" data-loading-strategy="prefer-viewability-over-views" data-enable-refresh=></amp-ad>';
		$ad_attr         = [
			'ad-unit' => 'AMP_ADTest',
			'sizes'   => '970x250',
		];
		$output          = AMP_AdManager::get_ads( $ad_attr );

		$this->assertNotEmpty( $output );
		$this->assertEquals( $expected_output, $output );

		// Test echo.
		$output_echo = Utility::buffer_and_return( 'AMP_AdManager\AMP_AdManager::get_ads', [ $ad_attr, true ] );

		$this->assertNotEmpty( $output_echo );
		$this->assertEquals( $expected_output, $output_echo );

	}

	/**
	 * Tests `filter_breakpoints` function.
	 *
	 * @covers ::filter_breakpoints
	 *
	 * @return void
	 */
	public function test_filter_breakpoints() {

		$ad_attr = [
			'ad-unit' => 'AMP_ADTest',
			'sizes'   => '320x50',
			'layout'  => 'fixed',
		];

		$output = Utility::invoke_method( $this->_instance, 'filter_breakpoints', [ $ad_attr['sizes'] ] );

		$this->assertNotEmpty( $output );
		$this->assertTrue( is_array( $output ) );

		$this->assertArrayHasKey( 'desktop', $output );
		$this->assertArrayHasKey( 'tablet', $output );
		$this->assertArrayHasKey( 'mobile', $output );

		$expected_output = [
			'mobile'  => [
				'width'  => '320',
				'height' => '50',
				'sizes'  => [ '320x50' ],
			],
			'tablet'  => [
				'width'  => '320',
				'height' => '50',
				'sizes'  => [ '320x50' ],
			],
			'desktop' => [],
		];

		// Check mobile sizes data.
		$this->assertEquals( $expected_output, $output );

		// Test for desktop size ad.
		$ad_attr = [
			'ad-unit' => 'AMP_ADTest',
			'sizes'   => '728x90',
			'layout'  => 'fixed',
		];

		$output = Utility::invoke_method( $this->_instance, 'filter_breakpoints', [ $ad_attr['sizes'] ] );

		$this->assertNotEmpty( $output );
		$this->assertNotEmpty( $output );
		$this->assertTrue( is_array( $output ) );

		$this->assertArrayHasKey( 'desktop', $output );
		$this->assertArrayHasKey( 'tablet', $output );
		$this->assertArrayHasKey( 'mobile', $output );

		$expected_output = [
			'mobile'  => [],
			'tablet'  => [],
			'desktop' => [
				'width'  => '728',
				'height' => '90',
				'sizes'  => [ '728x90' ],
			],
		];

		// Check mobile sizes data.
		$this->assertEquals( $expected_output, $output );

	}

	/**
	 * Tests `set_max_height_and_width` function.
	 *
	 * @covers ::set_max_height_and_width
	 *
	 * @return void
	 */
	public function test_set_max_height_and_width() {

		$ad_attr    = [
			'ad-unit' => 'AMP_ADTest',
			'sizes'   => '320x50',
			'layout'  => 'fixed',
		];
		$breakpoint = Utility::invoke_method( $this->_instance, 'filter_breakpoints', [ $ad_attr['sizes'] ] );

		$output = Utility::invoke_method( $this->_instance, 'set_max_height_and_width', [ 'mobile', $breakpoint, 300, 50 ] );

		$this->assertNotEmpty( $output );
		$this->assertNotEmpty( $output );
		$this->assertTrue( is_array( $output ) );

		$this->assertArrayHasKey( 'desktop', $output );
		$this->assertArrayHasKey( 'tablet', $output );
		$this->assertArrayHasKey( 'mobile', $output );

		$mobile_output = [
			'width'  => '320',
			'height' => '50',
			'sizes'  => [ '320x50', '300x50' ],
		];

		// Check mobile sizes data.
		$this->assertEquals( $mobile_output, $output['mobile'] );

	}

	/**
	 * Tests `set_custom_sizes` function.
	 *
	 * @covers ::set_custom_sizes
	 *
	 * @return void
	 */
	public function test_set_custom_sizes() {

		$ad_attr    = [
			'ad-unit'          => 'AMP_ADTest',
			'desktop-sizes'    => '970x250',
			'tablet-sizes'     => '336x280',
			'mobile-sizes'     => '300x250',
			'custom-targeting' => 'adPosition:1',
			'layout'           => 'fixed',
		];
		$breakpoint = [];

		$output = Utility::invoke_method( $this->_instance, 'set_custom_sizes', [ $ad_attr, $breakpoint ] );

		$this->assertNotEmpty( $output );
		$this->assertTrue( is_array( $output ) );

		$this->assertArrayHasKey( 'desktop', $output );
		$this->assertArrayHasKey( 'tablet', $output );
		$this->assertArrayHasKey( 'mobile', $output );

		$desktop_output = [
			'width'  => '970',
			'height' => '250',
			'sizes'  => [ '970x250' ],
		];

		// Check output data.
		$this->assertEquals( $desktop_output, $output['desktop'] );

		// Case with breakpoints.
		$ad_attr    = [
			'ad-unit' => 'AMP_ADTest',
			'sizes'   => '320x50',
			'layout'  => 'fixed',
		];
		$breakpoint = Utility::invoke_method( $this->_instance, 'filter_breakpoints', [ $ad_attr['sizes'] ] );

		$output = Utility::invoke_method( $this->_instance, 'set_custom_sizes', [ $ad_attr, $breakpoint ] );

		$this->assertNotEmpty( $output );
		$this->assertTrue( is_array( $output ) );

		$this->assertArrayHasKey( 'desktop', $output );
		$this->assertArrayHasKey( 'tablet', $output );
		$this->assertArrayHasKey( 'mobile', $output );

		$mobile_output = [
			'width'  => '320',
			'height' => '50',
			'sizes'  => [ '320x50' ],
		];

		// Check output data.
		$this->assertEquals( $mobile_output, $output['mobile'] );

	}

	/**
	 * Tests `get_slot_media_query` function.
	 *
	 * @covers ::get_slot_media_query
	 *
	 * @return void
	 */
	public function test_get_slot_media_query() {

		$expected_output = '(min-width: 200px) and (max-width: 50px)';
		$output          = AMP_AdManager::get_slot_media_query( 200, 50 );

		$this->assertEquals( $expected_output, $output );

		$expected_output = '(min-width: 200px)';
		$output          = AMP_AdManager::get_slot_media_query( 200, 0 );

		$this->assertEquals( $expected_output, $output );

	}

	/**
	 * Tests `load_amp_resources` function.
	 *
	 * @covers ::load_amp_resources
	 *
	 * @return void
	 */
	public function test_load_amp_resources() {

		$expected = '<meta name="amp-ad-doubleclick-sra" />';

		update_option(
			'amp-admanager-menu-settings',
			[
				'load-amp-resources' => '1',
				'dfp-network-id'     => '',
			]
		);

		// Update settings after updating option.
		AMP_AdManager::$amp_settings = get_option( 'amp-admanager-menu-settings' );
		$output                      = Utility::buffer_and_return( [ $this->_instance, 'load_amp_resources' ] );

		$this->assertContains( $expected, $output );
		$this->assertContains( '<style amp-boilerplate>', $output );
		$this->assertContains( '<link rel="preload" as="script" href="https://cdn.ampproject.org/v0.js">', $output );
		$this->assertContains( '<script type="text/javascript" src="https://cdn.ampproject.org/v0.js" async></script>', $output ); // phpcs:ignore

		// Test for is_amp_endpoint() condition.
		$user_mock = $this->factory->user->create_and_get( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_mock->ID );
		$_GET['amp_validate'] = true;
		$output_user          = Utility::buffer_and_return( [ $this->_instance, 'load_amp_resources' ] );

		$this->assertEquals( $expected, $output_user );

	}
}
