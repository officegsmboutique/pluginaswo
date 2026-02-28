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
	 * Add top-level ASWO admin menu with Dashboard and Settings submenus.
	 */
	public function add_settings_page() {
		add_menu_page(
			__( 'ASWO', 'pluginaswo' ),
			__( 'ASWO', 'pluginaswo' ),
			'manage_options',
			'aswo-dashboard',
			array( $this, 'render_dashboard_page' ),
			'dashicons-cart',
			56
		);

		add_submenu_page(
			'aswo-dashboard',
			__( 'ASWO Dashboard', 'pluginaswo' ),
			__( 'Dashboard', 'pluginaswo' ),
			'manage_options',
			'aswo-dashboard',
			array( $this, 'render_dashboard_page' )
		);

		add_submenu_page(
			'aswo-dashboard',
			__( 'ASWO Settings', 'pluginaswo' ),
			__( 'Settings', 'pluginaswo' ),
			'manage_options',
			'aswo-settings',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			'aswo-dashboard',
			__( 'ASWO Comenzi', 'pluginaswo' ),
			__( 'Comenzi', 'pluginaswo' ),
			'manage_options',
			'aswo-orders',
			array( $this, 'render_orders_page' )
		);
	}

	/**
	 * Render the dashboard overview page.
	 */
	public function render_dashboard_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		include ASWO_PLUGIN_DIR . 'admin/admin-dashboard.php';
	}

	/**
	 * Register all plugin settings.
	 */
	public function register_settings() {
		$fields = array(
			'aswo_customer_id'             => 'sanitize_text_field',
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
		if ( ! in_array( $hook, array( 'toplevel_page_aswo-dashboard', 'aswo_page_aswo-settings', 'aswo_page_aswo-orders' ), true ) ) {
			return;
		}
		wp_enqueue_style(
			'aswo-admin',
			ASWO_PLUGIN_URL . 'assets/css/aswo-admin.css',
			array(),
			ASWO_VERSION
		);
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
	 * Render the orders list page.
	 */
	public function render_orders_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		include ASWO_PLUGIN_DIR . 'admin/admin-orders.php';
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
