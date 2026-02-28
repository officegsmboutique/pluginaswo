<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
?>
<div class="wrap aswo-settings-wrap">
	<h1><?php esc_html_e( 'ASWO Settings', 'pluginaswo' ); ?></h1>

	<form method="post" action="options.php">
		<?php settings_fields( 'aswo_settings_group' ); ?>

		<!-- ===== API CREDENTIALS ===== -->
		<h2><?php esc_html_e( 'ASWO API Credentials', 'pluginaswo' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="aswo_customer_id"><?php esc_html_e( 'ASWO Customer ID / Kunden-Nr', 'pluginaswo' ); ?></label></th>
				<td><input type="text" id="aswo_customer_id" name="aswo_customer_id" value="<?php echo esc_attr( get_option( 'aswo_customer_id' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="aswo_api_login"><?php esc_html_e( 'ASWO API Login / Username', 'pluginaswo' ); ?></label></th>
				<td><input type="text" id="aswo_api_login" name="aswo_api_login" value="<?php echo esc_attr( get_option( 'aswo_api_login' ) ); ?>" class="regular-text" autocomplete="off" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="aswo_api_password"><?php esc_html_e( 'ASWO API Password', 'pluginaswo' ); ?></label></th>
				<td><input type="password" id="aswo_api_password" name="aswo_api_password" value="<?php echo esc_attr( get_option( 'aswo_api_password' ) ); ?>" class="regular-text" autocomplete="new-password" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="aswo_api_base_url"><?php esc_html_e( 'API Base URL', 'pluginaswo' ); ?></label></th>
				<td><input type="text" id="aswo_api_base_url" name="aswo_api_base_url" value="<?php echo esc_attr( get_option( 'aswo_api_base_url', 'https://shop.euras.com/eed/' ) ); ?>" class="regular-text" /></td>
			</tr>
		</table>

		<hr />

		<!-- ===== SHOP SETTINGS ===== -->
		<h2><?php esc_html_e( 'Shop Settings', 'pluginaswo' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="aswo_results_per_page"><?php esc_html_e( 'Results per page', 'pluginaswo' ); ?></label></th>
				<td><input type="number" id="aswo_results_per_page" name="aswo_results_per_page" value="<?php echo esc_attr( get_option( 'aswo_results_per_page', 20 ) ); ?>" min="1" max="100" class="small-text" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="aswo_currency"><?php esc_html_e( 'Currency', 'pluginaswo' ); ?></label></th>
				<td>
					<select id="aswo_currency" name="aswo_currency">
						<?php
						$current_currency = get_option( 'aswo_currency', 'EUR' );
						foreach ( array( 'EUR', 'RON', 'USD', 'GBP' ) as $c ) {
							printf(
								'<option value="%s" %s>%s</option>',
								esc_attr( $c ),
								selected( $current_currency, $c, false ),
								esc_html( $c )
							);
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="aswo_country_code"><?php esc_html_e( 'Country Code', 'pluginaswo' ); ?></label></th>
				<td><input type="text" id="aswo_country_code" name="aswo_country_code" value="<?php echo esc_attr( get_option( 'aswo_country_code', 'RO' ) ); ?>" class="small-text" maxlength="2" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="aswo_language"><?php esc_html_e( 'Language', 'pluginaswo' ); ?></label></th>
				<td>
					<select id="aswo_language" name="aswo_language">
						<?php
						$current_lang = get_option( 'aswo_language', 'en' );
						foreach ( array( 'en', 'de', 'ro', 'fr', 'it', 'es' ) as $l ) {
							printf(
								'<option value="%s" %s>%s</option>',
								esc_attr( $l ),
								selected( $current_lang, $l, false ),
								esc_html( $l )
							);
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable Appliance Search', 'pluginaswo' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="aswo_enable_appliance_search" value="1" <?php checked( 1, get_option( 'aswo_enable_appliance_search', 0 ) ); ?> />
						<?php esc_html_e( 'Activate appliance-based part search', 'pluginaswo' ); ?>
					</label>
				</td>
			</tr>
		</table>

		<hr />

		<!-- ===== DISPLAY SETTINGS ===== -->
		<h2><?php esc_html_e( 'Display Settings', 'pluginaswo' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="aswo_search_page_id"><?php esc_html_e( 'Search Page ID', 'pluginaswo' ); ?></label></th>
				<td>
					<?php
					wp_dropdown_pages(
						array(
							'name'              => 'aswo_search_page_id',
							'id'                => 'aswo_search_page_id',
							'selected'          => get_option( 'aswo_search_page_id', 0 ),
							'show_option_none'  => __( '— Select —', 'pluginaswo' ),
							'option_none_value' => '0',
						)
					);
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="aswo_cart_page_id"><?php esc_html_e( 'Cart Page ID', 'pluginaswo' ); ?></label></th>
				<td>
					<?php
					wp_dropdown_pages(
						array(
							'name'              => 'aswo_cart_page_id',
							'id'                => 'aswo_cart_page_id',
							'selected'          => get_option( 'aswo_cart_page_id', 0 ),
							'show_option_none'  => __( '— Select —', 'pluginaswo' ),
							'option_none_value' => '0',
						)
					);
					?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Show prices including VAT', 'pluginaswo' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="aswo_show_price_vat" value="1" <?php checked( 1, get_option( 'aswo_show_price_vat', 1 ) ); ?> />
						<?php esc_html_e( 'Display prices with VAT included', 'pluginaswo' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="aswo_vat_rate"><?php esc_html_e( 'VAT Rate %', 'pluginaswo' ); ?></label></th>
				<td><input type="number" id="aswo_vat_rate" name="aswo_vat_rate" value="<?php echo esc_attr( get_option( 'aswo_vat_rate', 19 ) ); ?>" min="0" max="100" class="small-text" /></td>
			</tr>
		</table>

		<hr />

		<?php submit_button( __( 'Save Settings', 'pluginaswo' ) ); ?>
	</form>

	<!-- Test Connection -->
	<h2><?php esc_html_e( 'Test Connection', 'pluginaswo' ); ?></h2>
	<p><?php esc_html_e( 'Save your credentials first, then test the API connection.', 'pluginaswo' ); ?></p>
	<button id="aswo-test-connection" class="button button-secondary">
		<?php esc_html_e( 'Test Connection', 'pluginaswo' ); ?>
	</button>
	<span id="aswo-test-result" style="margin-left:12px;font-weight:600;"></span>
</div>
