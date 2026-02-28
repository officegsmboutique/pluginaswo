<?php
/**
 * Plugin Name: ASWO Parts Search & Order
 * Plugin URI: https://github.com/officegsmboutique/pluginaswo
 * Description: Plugin WordPress pentru căutarea și comanda pieselor de schimb prin API-ul EURAS/ASWO EED.
 * Version: 1.0.0
 * Author: GSM Boutique
 * Author URI: https://github.com/officegsmboutique
 * Text Domain: pluginaswo
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ASWO_VERSION', '1.0.0' );
define( 'ASWO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ASWO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once ASWO_PLUGIN_DIR . 'includes/class-aswo-api.php';
require_once ASWO_PLUGIN_DIR . 'includes/class-aswo-cart.php';
require_once ASWO_PLUGIN_DIR . 'includes/class-aswo-orders.php';
require_once ASWO_PLUGIN_DIR . 'includes/class-aswo-shortcodes.php';
require_once ASWO_PLUGIN_DIR . 'admin/class-aswo-admin.php';
require_once ASWO_PLUGIN_DIR . 'public/class-aswo-public.php';

// Activation
register_activation_hook( __FILE__, 'aswo_activate' );
function aswo_activate() {
	$defaults = array(
		'aswo_customer_id'     => '',
		'aswo_api_base_url'    => 'https://shop.euras.com/eed/',
		'aswo_results_per_page' => 20,
		'aswo_currency'        => 'EUR',
		'aswo_country_code'    => 'RO',
		'aswo_language'        => 'en',
		'aswo_enable_appliance_search' => 0,
		'aswo_search_page_id'  => 0,
		'aswo_cart_page_id'    => 0,
		'aswo_show_price_vat'  => 1,
		'aswo_vat_rate'        => 19,
	);
	foreach ( $defaults as $key => $value ) {
		if ( false === get_option( $key ) ) {
			add_option( $key, $value );
		}
	}
	ASWO_Orders::create_table();
	flush_rewrite_rules();
}

// Deactivation
register_deactivation_hook( __FILE__, 'aswo_deactivate' );
function aswo_deactivate() {
	delete_transient( 'aswo_session_token' );
	flush_rewrite_rules();
}

// Init plugin components
add_action( 'plugins_loaded', 'aswo_init' );
function aswo_init() {
	$shortcodes = new ASWO_Shortcodes();
	$shortcodes->init();

	if ( is_admin() ) {
		$admin = new ASWO_Admin();
		$admin->init();
	}

	$public = new ASWO_Public();
	$public->init();
}

// AJAX handlers - both logged in and not logged in
$ajax_actions = array(
	'aswo_search',
	'aswo_article_detail',
	'aswo_suggestions',
	'aswo_add_to_cart',
	'aswo_update_cart',
	'aswo_remove_from_cart',
	'aswo_get_cart',
	'aswo_place_order',
	'aswo_appliance_search',
);

foreach ( $ajax_actions as $action ) {
	add_action( 'wp_ajax_' . $action, array( 'ASWO_Public', 'handle_ajax_' . $action ) );
	add_action( 'wp_ajax_nopriv_' . $action, array( 'ASWO_Public', 'handle_ajax_' . $action ) );
}

// Admin-only AJAX
add_action( 'wp_ajax_aswo_test_connection', array( 'ASWO_Admin', 'handle_ajax_test_connection' ) );
