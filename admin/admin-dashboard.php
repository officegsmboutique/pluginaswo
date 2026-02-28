<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

$api_login    = get_option( 'aswo_api_login', '' );
$customer_id  = get_option( 'aswo_customer_id', '' );
$search_page  = absint( get_option( 'aswo_search_page_id', 0 ) );
$cart_page    = absint( get_option( 'aswo_cart_page_id', 0 ) );
$configured   = ! empty( $api_login ) && ! empty( $customer_id );
?>
<div class="wrap aswo-settings-wrap">
	<h1><?php esc_html_e( 'ASWO Parts Search & Order', 'pluginaswo' ); ?></h1>
	<p class="description"><?php esc_html_e( 'Plugin WordPress pentru căutarea și comanda pieselor de schimb prin API-ul EURAS/ASWO EED.', 'pluginaswo' ); ?></p>

	<div class="aswo-dashboard-grid">

		<!-- Status Card -->
		<div class="aswo-card">
			<h2><?php esc_html_e( 'Status Plugin', 'pluginaswo' ); ?></h2>
			<table class="widefat striped">
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Versiune', 'pluginaswo' ); ?></td>
						<td><strong><?php echo esc_html( ASWO_VERSION ); ?></strong></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Credențiale API', 'pluginaswo' ); ?></td>
						<td>
							<?php if ( $configured ) : ?>
								<span class="aswo-status aswo-status-ok">&#10003; <?php esc_html_e( 'Configurate', 'pluginaswo' ); ?></span>
							<?php else : ?>
								<span class="aswo-status aswo-status-warn">&#9888; <?php esc_html_e( 'Neconfigurat', 'pluginaswo' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Pagina Căutare', 'pluginaswo' ); ?></td>
						<td>
							<?php if ( $search_page ) : ?>
								<a href="<?php echo esc_url( get_edit_post_link( $search_page ) ); ?>">
									<?php echo esc_html( get_the_title( $search_page ) ); ?>
								</a>
							<?php else : ?>
								<span class="aswo-status aswo-status-warn">&#9888; <?php esc_html_e( 'Nesetată', 'pluginaswo' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Pagina Coș', 'pluginaswo' ); ?></td>
						<td>
							<?php if ( $cart_page ) : ?>
								<a href="<?php echo esc_url( get_edit_post_link( $cart_page ) ); ?>">
									<?php echo esc_html( get_the_title( $cart_page ) ); ?>
								</a>
							<?php else : ?>
								<span class="aswo-status aswo-status-warn">&#9888; <?php esc_html_e( 'Nesetată', 'pluginaswo' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Quick Links Card -->
		<div class="aswo-card">
			<h2><?php esc_html_e( 'Acțiuni Rapide', 'pluginaswo' ); ?></h2>
			<ul class="aswo-quick-links">
				<li>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=aswo-settings' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Configurare Setări', 'pluginaswo' ); ?>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>" class="button button-secondary">
						<?php esc_html_e( 'Creare Pagină Nouă', 'pluginaswo' ); ?>
					</a>
				</li>
			</ul>

			<h3><?php esc_html_e( 'Shortcode-uri disponibile', 'pluginaswo' ); ?></h3>
			<ul class="aswo-shortcodes">
				<li>
					<code>[aswo_parts_search]</code>
					<span><?php esc_html_e( '— Formular căutare piese', 'pluginaswo' ); ?></span>
				</li>
				<li>
					<code>[aswo_cart]</code>
					<span><?php esc_html_e( '— Coș cumpărături și comandă', 'pluginaswo' ); ?></span>
				</li>
			</ul>
		</div>

	</div>
</div>
