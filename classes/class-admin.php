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
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'amp_admanager_menu' ] );
	}

	/**
	 * To add AMP AdManager menu tabs.
	 *
	 * @return void
	 */
	public function amp_admanager_menu() {
		add_menu_page( 'AMP AdManager Settings', 'AMP AdManager', 'manage_options', 'amp-admanager-menu', [ $this, 'amp_admanager_menu_html' ] );
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

		if ( isset( $_POST['dfp-network-id'] ) ) { // phpcs:ignore
			update_option( 'dfp-network-id', sanitize_text_field( wp_unslash( $_POST['dfp-network-id'] ) ) ); // phpcs:ignore
		} else {
			delete_option( 'dfp-network-id' );
		}

		if ( isset( $_POST['load-amp-resources'] ) ) { // phpcs:ignore
			update_option( 'load-amp-resources', sanitize_text_field( wp_unslash( $_POST['load-amp-resources']  ) ) ); // phpcs:ignore
		} else {
			delete_option( 'load-amp-resources' );
		}

		load_template( AMP_ADMANAGER_ROOT . '/template-parts/admin-settings.php' );
	}
}
