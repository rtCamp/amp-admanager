<?php
namespace AMP_AdManager\Tests;

use AMP_AdManager\Admin;

/**
 * Class Admin
 *
 * @coversDefaultClass Admin
 */
class Test_Admin extends \WP_UnitTestCase {

	/**
	 * Admin Class Instance.
	 *
	 * @var Admin
	 */
	protected $_instance = false;

	/**
	 * This function sets the instance for class \AMP_AdManager\Admin.
	 */
	public function setUp(): void {
		$this->_instance = new Admin();
	}

	/**
	 * @covers \AMP_AdManager\Admin::__construct
	 * @throws \ReflectionException
	 */
	public function test_construct() {
		Utility::invoke_method( $this->_instance, '__construct' );
		$this->assertEquals( 10, has_action( 'admin_menu', [ $this->_instance, 'amp_admanager_menu' ] ) );
		$this->assertEquals( 10, has_action( 'admin_init', [ $this->_instance, 'amp_admanager_menu_init' ] ) );
	}

	/**
	 * @covers \AMP_AdManager\Admin::amp_admanager_menu
	 */
	public function test_amp_admanager_menu() {
		$current_user = get_current_user_id();
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );
		$this->_instance->amp_admanager_menu();
		$admin_page_url = home_url() . '/wp-admin/admin.php?page=amp-admanager-menu';
		$this->assertEquals(
			$admin_page_url,
			menu_page_url( 'amp-admanager-menu', false ), 'AMP AdManager Settings Page was not created'
		);
		wp_set_current_user( $current_user );
	}

	/**
	 * @covers \AMP_AdManager\Admin::amp_admanager_menu_init
	 */
	public function test_amp_admanager_menu_init() {
		global $new_whitelist_options, $wp_settings_sections, $wp_settings_fields;

		$this->_instance->amp_admanager_menu_init();

		$this->assertTrue(
			array_key_exists( 'amp-admanager-menu', $new_whitelist_options ),
			'Option Group amp-admanager-menu has not been created' );

		$settings = $new_whitelist_options['amp-admanager-menu'];

		$this->assertCount( 1, $settings, 'The Settings Group amp-admanager-menu 1 setting' );

		$this->assertContains( 'amp-admanager-menu-settings',
			$settings,
			sprintf( 'Setting "%1$s" has not been created', 'amp-admanager-menu-settings' )
		);

		$this->assertarrayHasKey(
			'amp-admanager-menu-page',
			$wp_settings_sections,
			'amp-admanager-menu-page setting section has not been created'
		);

		$this->assertarrayHasKey(
			'amp-admanager-menu-page',
			$wp_settings_fields,
			'amp-admanager-menu-page settings field has not been created'
		);

		$this->assertarrayHasKey(
			'amp-admanager-general-settings',
			$wp_settings_fields['amp-admanager-menu-page'],
			'amp-admanager-menu-page types section settings fields has not been created'
		);

		$this->assertCount(
			2,
			$wp_settings_fields['amp-admanager-menu-page']['amp-admanager-general-settings'],
			'There are less than 3  sections in the amp-admanager-menu-page settings field'
		);

		$settings_fields = array(
			'dfp-network-id',
			'load-amp-resources',
		);

		foreach ( $settings_fields as $setting_field ) {
			$this->assertarrayHasKey(
				$setting_field,
				$wp_settings_fields['amp-admanager-menu-page']['amp-admanager-general-settings']
			);
		}
	}

	/**
	 * @covers \AMP_AdManager\Admin::amp_admanager_menu_html
	 */
	public function test_amp_admanager_menu_html() {
		// Test access of user with privileges.
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );
		ob_start();
		$this->_instance->amp_admanager_menu_html();
		$amp_ad_manager_settings_page = ob_get_clean();
		$this->assertContains( 'Global Settings', $amp_ad_manager_settings_page );

		// Test access of user without privileges.
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'author' ) ) );
		ob_start();
		$this->_instance->amp_admanager_menu_html();
		$amp_ad_manager_settings_page = ob_get_clean();
		$this->assertContains( 'You do not have sufficient permissions to access this page.', $amp_ad_manager_settings_page );
	}

	/**
	 * @covers \AMP_AdManager\Admin::get_checkbox_field
	 */
	public function test_get_checkbox_field() {
		ob_start();
		$this->_instance->get_checkbox_field();
		$amp_ad_manager_checkbox_field = ob_get_clean();
		$this->assertContains( 'id="load-amp-resources"', $amp_ad_manager_checkbox_field );
	}

	/**
	 * @covers \AMP_AdManager\Admin::get_text_field
	 */
	public function test_get_text_field() {
		ob_start();
		$this->_instance->get_text_field();
		$amp_ad_manager_checkbox_field = ob_get_clean();
		$this->assertContains( 'id="dfp-network-id"', $amp_ad_manager_checkbox_field );
	}
}
