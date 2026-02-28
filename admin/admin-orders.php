<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

$per_page = 20;
$paged    = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
$data     = ASWO_Orders::get_orders( $per_page, $paged );
$orders   = $data['items'];
$total    = $data['total'];
$pages    = (int) ceil( $total / $per_page );

// Single-order detail view.
$view_id  = isset( $_GET['view'] ) ? absint( $_GET['view'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
$single   = $view_id ? ASWO_Orders::get_order( $view_id ) : null;
?>
<div class="wrap aswo-settings-wrap">
	<h1><?php esc_html_e( 'ASWO Comenzi', 'pluginaswo' ); ?></h1>

	<?php if ( $single ) : ?>
		<!-- ======= Single Order Detail ======= -->
		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=aswo-orders' ) ); ?>" class="button button-secondary">
				&larr; <?php esc_html_e( 'Înapoi la comenzi', 'pluginaswo' ); ?>
			</a>
		</p>

		<div class="aswo-dashboard-grid">

			<div class="aswo-card">
				<h2>
					<?php
					/* translators: %d: order row ID */
					printf( esc_html__( 'Comanda #%d', 'pluginaswo' ), (int) $single->id );
					if ( ! empty( $single->aswo_order_id ) ) {
						echo ' &mdash; <span style="color:#646970;">' . esc_html( $single->aswo_order_id ) . '</span>';
					}
					?>
				</h2>
				<table class="widefat striped">
					<tbody>
						<tr><td><?php esc_html_e( 'Status', 'pluginaswo' ); ?></td><td><strong><?php echo esc_html( $single->status ); ?></strong></td></tr>
						<tr><td><?php esc_html_e( 'Data', 'pluginaswo' ); ?></td><td><?php echo esc_html( $single->created_at ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Companie', 'pluginaswo' ); ?></td><td><?php echo esc_html( $single->company ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Nume', 'pluginaswo' ); ?></td><td><?php echo esc_html( $single->first_name . ' ' . $single->last_name ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Email', 'pluginaswo' ); ?></td><td><?php echo esc_html( $single->email ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Telefon', 'pluginaswo' ); ?></td><td><?php echo esc_html( $single->phone ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Adresă', 'pluginaswo' ); ?></td><td><?php echo esc_html( $single->address . ', ' . $single->postcode . ' ' . $single->city . ', ' . $single->country ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Observații', 'pluginaswo' ); ?></td><td><?php echo esc_html( $single->notes ); ?></td></tr>
						<tr><td><?php esc_html_e( 'Total', 'pluginaswo' ); ?></td><td><strong><?php echo esc_html( number_format( floatval( $single->total ), 2 ) . ' ' . esc_html( get_option( 'aswo_currency', 'EUR' ) ) ); ?></strong></td></tr>
					</tbody>
				</table>
			</div>

			<div class="aswo-card">
				<h2><?php esc_html_e( 'Produse comandate', 'pluginaswo' ); ?></h2>
				<?php
				$items = json_decode( $single->items, true );
				if ( ! empty( $items ) ) :
				?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Articol Nr.', 'pluginaswo' ); ?></th>
							<th><?php esc_html_e( 'Denumire', 'pluginaswo' ); ?></th>
							<th><?php esc_html_e( 'Cantitate', 'pluginaswo' ); ?></th>
							<th><?php esc_html_e( 'Preț', 'pluginaswo' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $items as $item ) : ?>
						<tr>
							<td><?php echo esc_html( isset( $item['article_no'] ) ? $item['article_no'] : '' ); ?></td>
							<td><?php echo esc_html( isset( $item['name'] ) ? $item['name'] : '' ); ?></td>
							<td><?php echo esc_html( isset( $item['quantity'] ) ? $item['quantity'] : 1 ); ?></td>
							<td><?php echo esc_html( number_format( floatval( isset( $item['price'] ) ? $item['price'] : 0 ), 2 ) ); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php else : ?>
				<p><?php esc_html_e( 'Nu există produse.', 'pluginaswo' ); ?></p>
				<?php endif; ?>
			</div>

		</div>

	<?php else : ?>
		<!-- ======= Orders List ======= -->
		<p class="description">
			<?php
			/* translators: %d: total orders count */
			printf( esc_html__( 'Total comenzi: %d', 'pluginaswo' ), (int) $total );
			?>
		</p>

		<?php if ( empty( $orders ) ) : ?>
			<p><?php esc_html_e( 'Nu există comenzi înregistrate.', 'pluginaswo' ); ?></p>
		<?php else : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th style="width:50px;">#</th>
					<th><?php esc_html_e( 'ID ASWO', 'pluginaswo' ); ?></th>
					<th><?php esc_html_e( 'Client', 'pluginaswo' ); ?></th>
					<th><?php esc_html_e( 'Email', 'pluginaswo' ); ?></th>
					<th><?php esc_html_e( 'Total', 'pluginaswo' ); ?></th>
					<th><?php esc_html_e( 'Status', 'pluginaswo' ); ?></th>
					<th><?php esc_html_e( 'Data', 'pluginaswo' ); ?></th>
					<th><?php esc_html_e( 'Acțiuni', 'pluginaswo' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $orders as $order ) : ?>
				<tr>
					<td><?php echo (int) $order->id; ?></td>
					<td><?php echo esc_html( $order->aswo_order_id ); ?></td>
					<td><?php echo esc_html( $order->first_name . ' ' . $order->last_name ); ?></td>
					<td><?php echo esc_html( $order->email ); ?></td>
					<td><?php echo esc_html( number_format( floatval( $order->total ), 2 ) . ' ' . get_option( 'aswo_currency', 'EUR' ) ); ?></td>
					<td><span class="aswo-status <?php echo 'completed' === $order->status ? 'aswo-status-ok' : 'aswo-status-warn'; ?>">
						<?php echo esc_html( $order->status ); ?>
					</span></td>
					<td><?php echo esc_html( $order->created_at ); ?></td>
					<td>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=aswo-orders&view=' . absint( $order->id ) ) ); ?>">
							<?php esc_html_e( 'Detalii', 'pluginaswo' ); ?>
						</a>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( $pages > 1 ) : ?>
		<div class="tablenav bottom">
			<div class="tablenav-pages">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'current'   => $paged,
							'total'     => $pages,
							'prev_text' => '&laquo;',
							'next_text' => '&raquo;',
						)
					)
				);
				?>
			</div>
		</div>
		<?php endif; ?>
		<?php endif; ?>

	<?php endif; ?>
</div>
