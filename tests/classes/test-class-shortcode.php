<?php
namespace AMP_AdManager\Tests;

use AMP_AdManager\AMP_AdManager;
use AMP_AdManager\Shortcode;

/**
 * Class Shortcode
 *
 * @coversDefaultClass Shortcode
 */
class Test_Shortcode extends \WP_UnitTestCase {

	/**
	 * Shortcode Class Instance.
	 *
	 * @var Shortcode
	 */
	protected $_instance = false;

	/**
	 * This function sets the instance for class \AMP_AdManager\Shortcode.
	 */
	public function setUp(): void {

		// Set demo data for settings.
		update_option(
			'amp-admanager-menu-settings',
			[
				'dfp-network-id'     => '123456789',
				'load-amp-resources' => '1',
			]
		);
		new AMP_AdManager();
		$this->_instance = new Shortcode();

	}

	/**
	 * Tests construct.
	 *
	 * @covers \AMP_AdManager\Shortcode::__construct
	 * @throws \ReflectionException Throws ReflectionException exception.
	 */
	public function test_construct() {
		Utility::invoke_method( $this->_instance, '__construct' );
		// Check existence of shortcode.
		$this->assertTrue( shortcode_exists( 'ampad' ) );
	}

	/**
	 * Tests render_amp_ad.
	 *
	 * @covers \AMP_AdManager\Shortcode::render_amp_ad
	 */
	public function test_render_amp_ad() {
		// Test with sizes and custom targeting for site domain.
		$post_content = '<p>post content start [ampad ad-unit="my-ad-unit" sizes="320x50" custom-targeting="siteDomain:mysite.com" ad-refresh="30"] post content ends</p>';
		$response = do_shortcode( $post_content );
		$this->assertContains( '<amp-ad width="320" height="50"', $response );
		$this->assertContains( 'mysite.com', $response );

		// Test with desktop sizes.
		$post_content = '[ampad ad-unit="my-ad-unit" desktop-sizes="320x100,300x100"]Ad content[/ampad]';
		$response = do_shortcode( $post_content );
		$this->assertContains( 'data-multi-size="320x100,300x100"', $response );

		// Test with tablet sizes.
		$post_content = '[ampad ad-unit="my-ad-unit" tablet-sizes="468x60,300x100"]Ad content[/ampad]';
		$response = do_shortcode( $post_content );
		$this->assertContains( 'data-multi-size="468x60,300x100"', $response );

		// Test with mobile sizes.
		$post_content = '[ampad ad-unit="my-ad-unit" mobile-sizes="300x100,320x50"]Ad content[/ampad]';
		$response = do_shortcode( $post_content );
		$this->assertContains( 'data-multi-size="300x100,320x50"', $response );

		// Test without custom targeting for domain.
		$post_content = '[ampad ad-unit="my-ad-unit" sizes="300x50" ad-refresh="30"]Ad content[/ampad]';
		$response = do_shortcode( $post_content );
		$this->assertContains( '<amp-ad width="300" height="50"', $response );
		$this->assertNotContains( 'mysite.com', $response );

		// Test with content.
		$post_content = '[ampad ad-unit="my-ad-unit" sizes="300x50"]Ad content[/ampad]';
		$response = do_shortcode( $post_content );
		$this->assertContains( '<amp-ad width="300" height="50"', $response );
		$this->assertContains( 'Ad content', $response );

		// Test with empty sizes and content.
		$post_content = '[ampad ad-unit="my-ad-unit" sizes=""]Ad content[/ampad]';
		$response = do_shortcode( $post_content );
		$this->assertNotContains( '<amp-ad', $response );
		$this->assertEquals( 'Ad content', $response );
	}
}
