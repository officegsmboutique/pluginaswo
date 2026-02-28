<?php
// Uninstall hook: runs when plugin is deleted from WordPress admin.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$options = array(
	'aswo_customer_id',
	'aswo_api_user',
	'aswo_api_password',
	'aswo_api_base_url',
	'aswo_results_per_page',
	'aswo_currency',
	'aswo_country_code',
	'aswo_language',
	'aswo_enable_appliance_search',
	'aswo_search_page_id',
	'aswo_cart_page_id',
	'aswo_show_price_vat',
	'aswo_vat_rate',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

delete_transient( 'aswo_session_token' );
