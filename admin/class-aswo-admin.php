<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ASWO_Admin - Admin settings page handler.
 */
class ASWO_Admin {

	public function init() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Add Settings > ASWO Settings menu item.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'ASWO Settings', 'pluginaswo' ),
			__( 'ASWO Settings', 'pluginaswo' ),
			'manage_options',
			'aswo-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register all plugin settings.
	 */
	public function register_settings() {
		$fields = array(
			'aswo_customer_id'             => 'sanitize_text_field',
			'aswo_api_login'               => 'sanitize_text_field',
			'aswo_api_password'            => 'sanitize_text_field',
			'aswo_api_base_url'            => 'esc_url_raw',
			'aswo_results_per_page'        => 'absint',
			'aswo_currency'                => 'sanitize_text_field',
			'aswo_country_code'            => 'sanitize_text_field',
			'aswo_language'                => 'sanitize_text_field',
			'aswo_enable_appliance_search' => 'absint',
			'aswo_search_page_id'          => 'absint',
			'aswo_cart_page_id'            => 'absint',
			'aswo_show_price_vat'          => 'absint',
			'aswo_vat_rate'                => 'absint',
		);

		foreach ( $fields as $option => $sanitize_callback ) {
			register_setting( 'aswo_settings_group', $option, array( 'sanitize_callback' => $sanitize_callback ) );
		}
	}

	/**
	 * Enqueue admin JS for test connection button.
	 *
	 * @param string $hook
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'settings_page_aswo-settings' !== $hook ) {
			return;
		}
		wp_enqueue_script(
			'aswo-admin',
			ASWO_PLUGIN_URL . 'assets/js/aswo-admin.js',
			array( 'jquery' ),
			ASWO_VERSION,
			true
		);
		wp_localize_script(
			'aswo-admin',
			'aswoAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'aswo_test_connection' ),
				'testing' => __( 'Se testează...', 'pluginaswo' ),
				'success' => __( 'Conexiune reușită!', 'pluginaswo' ),
				'error'   => __( 'Conexiune eșuată: ', 'pluginaswo' ),
			)
		);
	}

	/**
	 * Render the settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		include ASWO_PLUGIN_DIR . 'admin/admin-settings.php';
	}

	/**
	 * AJAX handler: Test API connection.
	 */
	public static function handle_ajax_test_connection() {
		check_ajax_referer( 'aswo_test_connection', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permisiuni insuficiente.', 'pluginaswo' ) ) );
		}

		delete_transient( 'aswo_session_token' );
		$api    = new ASWO_API();
		$result = $api->start_session();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( array( 'message' => __( 'Conexiune API reușită! Token obținut.', 'pluginaswo' ) ) );
	}
}
