<?php
/**
 * Class to include all admin side modules.
 *
 * @author Vishal Dodiya <vishal.dodiya@rtcamp.com>
 *
 * @package AMP_AdManager
 */

namespace AMP_AdManager;

/**
 * Class Admin.
 */
class Admin {

	/**
	 * AMP settings array.
	 *
	 * @var array
	 */
	private $amp_settings;

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->amp_settings = get_option( 'amp-admanager-menu-settings' );

		/**
		 * Actions.
		 */
		add_action( 'admin_menu', [ $this, 'amp_admanager_menu' ] );
		add_action( 'admin_init', [ $this, 'amp_admanager_menu_init' ] );
	}

	/**
	 * To add AMP AdManager menu tabs.
	 *
	 * @return void
	 */
	public function amp_admanager_menu() {
		add_menu_page(
			__( 'AMP AdManager Settings', 'amp-admanager' ),
			__( 'AMP AdManager', 'amp-admanager' ),
			'manage_options',
			'amp-admanager-menu',
			[ $this, 'amp_admanager_menu_html' ]
		);
	}

	/**
	 * AMP AdManager general setting page.
	 *
	 * @return void
	 */
	public function amp_admanager_menu_html() {

		// User Require Capability to edit page.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'amp-admanager' ) );
		}

		load_template( AMP_ADMANAGER_ROOT . '/template-parts/admin-settings.php' );
	}

	/**
	 * Register AMP Admanager menu setting section and fields.
	 *
	 * @return void
	 */
	public function amp_admanager_menu_init() {

		register_setting(
			'amp-admanager-menu',
			'amp-admanager-menu-settings'
		);

		add_settings_section(
			'amp-admanager-general-settings',
			__( 'Global Settings', 'amp-admanager' ),
			'__return_empty_string',
			'amp-admanager-menu-page'
		);

		add_settings_field(
			'dfp-network-id',
			esc_html__( 'DFP Network ID', 'amp-admanager' ),
			[ $this, 'get_text_field' ],
			'amp-admanager-menu-page',
			'amp-admanager-general-settings'
		);

		add_settings_field(
			'load-amp-resources',
			esc_html__( 'Load AMP Resources For Non AMP Site', 'amp-admanager' ),
			[ $this, 'get_checkbox_field' ],
			'amp-admanager-menu-page',
			'amp-admanager-general-settings'
		);

		add_settings_field(
			'amp_admanager_enable_sticky_ads',
			esc_html__( 'Enable Sticky Ads', 'amp-admanager' ),
			[ $this, 'get_enable_sticky_ad_checkbox_field' ],
			'amp-admanager-menu-page',
			'amp-admanager-general-settings'
		);

		add_settings_field(
			'amp_admanager_sticky_ad_unit',
			esc_html__( 'Sticky adunit name', 'amp-admanager' ),
			[ $this, 'get_sticky_adunit_name_field' ],
			'amp-admanager-menu-page',
			'amp-admanager-general-settings'
		);
	}

	/**
	 * Prints checkbox field.
	 *
	 * @return void
	 */
	public function get_checkbox_field() {
		printf( '<input name="amp-admanager-menu-settings[load-amp-resources]" type="checkbox" id="load-amp-resources" value="1" %s>', checked( $this->amp_settings['load-amp-resources'], '1', false ) );
	}

	/**
	 * Prints text field.
	 *
	 * @return void
	 */
	public function get_text_field() {
		printf( '<input name="amp-admanager-menu-settings[dfp-network-id]" type="text" id="dfp-network-id" value="%s" class="regular-text">', esc_attr( $this->amp_settings['dfp-network-id'] ) );
	}

	/**
	 * Prints markup for enable sticky ad checkbox.
	 *
	 * @return void
	 */
	public function get_enable_sticky_ad_checkbox_field() {
		printf( '<input name="amp-admanager-menu-settings[amp_admanager_enable_sticky_ads]" type="checkbox" id="amp_admanager_enable_sticky_ads" value="1" %s>', checked( $this->amp_settings['amp_admanager_enable_sticky_ads'], '1', false ) );
	}

	/**
	 * Prints markup for adunit name text field.
	 *
	 * @return void
	 */
	public function get_sticky_adunit_name_field() {
		printf( '<input name="amp-admanager-menu-settings[amp_admanager_sticky_ad_unit]" type="text" id="amp_admanager_sticky_ad_unit" value="%s" class="regular-text">', esc_attr( $this->amp_settings['amp_admanager_sticky_ad_unit'] ) );
	}
}
