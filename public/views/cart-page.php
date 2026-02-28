<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cart     = new ASWO_Cart();
$items    = $cart->get_items();
$currency = esc_html( get_option( 'aswo_currency', 'EUR' ) );

$search_page_id = absint( get_option( 'aswo_search_page_id', 0 ) );
$search_url     = $search_page_id ? get_permalink( $search_page_id ) : home_url();
?>
<div id="aswo-cart-wrapper" class="aswo-wrapper">

	<h2><?php esc_html_e( 'Coșul meu', 'pluginaswo' ); ?></h2>

	<?php if ( empty( $items ) ) : ?>
		<div class="aswo-empty-cart">
			<p><?php esc_html_e( 'Coșul tău este gol.', 'pluginaswo' ); ?></p>
			<a href="<?php echo esc_url( $search_url ); ?>" class="aswo-btn aswo-btn-primary">
				<?php esc_html_e( 'Caută piese', 'pluginaswo' ); ?>
			</a>
		</div>
	<?php else : ?>

		<form id="aswo-cart-form">
			<table class="aswo-cart-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Produs', 'pluginaswo' ); ?></th>
						<th><?php esc_html_e( 'Nr. Articol', 'pluginaswo' ); ?></th>
						<th><?php esc_html_e( 'Preț', 'pluginaswo' ); ?></th>
						<th><?php esc_html_e( 'Cantitate', 'pluginaswo' ); ?></th>
						<th><?php esc_html_e( 'Total', 'pluginaswo' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody id="aswo-cart-body">
					<?php foreach ( $items as $article_no => $item ) :
						$line_total = floatval( $item['price'] ) * absint( $item['quantity'] );
					?>
					<tr class="aswo-cart-row" data-article="<?php echo esc_attr( $article_no ); ?>">
						<td>
							<?php if ( ! empty( $item['image_url'] ) ) : ?>
								<img src="<?php echo esc_url( $item['image_url'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" class="aswo-cart-thumb" />
							<?php endif; ?>
							<span><?php echo esc_html( $item['name'] ); ?></span>
						</td>
						<td><?php echo esc_html( $article_no ); ?></td>
						<td><?php echo esc_html( number_format( floatval( $item['price'] ), 2 ) . ' ' . $currency ); ?></td>
						<td>
							<input
								type="number"
								class="aswo-qty-input aswo-cart-qty"
								value="<?php echo esc_attr( $item['quantity'] ); ?>"
								min="1"
								data-article="<?php echo esc_attr( $article_no ); ?>"
							/>
						</td>
						<td class="aswo-line-total"><?php echo esc_html( number_format( $line_total, 2 ) . ' ' . $currency ); ?></td>
						<td>
							<button type="button" class="aswo-remove-btn" data-article="<?php echo esc_attr( $article_no ); ?>" title="<?php esc_attr_e( 'Șterge', 'pluginaswo' ); ?>">&#10005;</button>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="4" class="aswo-cart-total-label"><strong><?php esc_html_e( 'Total', 'pluginaswo' ); ?></strong></td>
						<td id="aswo-cart-total" colspan="2"><strong><?php echo esc_html( number_format( $cart->get_total(), 2 ) . ' ' . $currency ); ?></strong></td>
					</tr>
				</tfoot>
			</table>

			<div class="aswo-cart-actions">
				<button type="button" id="aswo-update-cart" class="aswo-btn aswo-btn-secondary">
					<?php esc_html_e( 'Actualizează coșul', 'pluginaswo' ); ?>
				</button>
				<a href="<?php echo esc_url( $search_url ); ?>" class="aswo-btn aswo-btn-secondary">
					<?php esc_html_e( 'Continuă cumpărăturile', 'pluginaswo' ); ?>
				</a>
				<button type="button" id="aswo-checkout-btn" class="aswo-btn aswo-btn-primary">
					<?php esc_html_e( 'Finalizează comanda', 'pluginaswo' ); ?>
				</button>
			</div>
		</form>

		<!-- ===== Order Form ===== -->
		<div id="aswo-order-form-wrap" style="display:none;">
			<h2><?php esc_html_e( 'Date comandă', 'pluginaswo' ); ?></h2>
			<form id="aswo-order-form" novalidate>
				<div class="aswo-form-grid">
					<div class="aswo-form-group">
						<label for="aswo-company"><?php esc_html_e( 'Firmă (opțional)', 'pluginaswo' ); ?></label>
						<input type="text" id="aswo-company" name="company" />
					</div>
					<div class="aswo-form-group">
						<label for="aswo-first-name"><?php esc_html_e( 'Prenume *', 'pluginaswo' ); ?></label>
						<input type="text" id="aswo-first-name" name="first_name" required />
					</div>
					<div class="aswo-form-group">
						<label for="aswo-last-name"><?php esc_html_e( 'Nume *', 'pluginaswo' ); ?></label>
						<input type="text" id="aswo-last-name" name="last_name" required />
					</div>
					<div class="aswo-form-group">
						<label for="aswo-email"><?php esc_html_e( 'Email *', 'pluginaswo' ); ?></label>
						<input type="email" id="aswo-email" name="email" required />
					</div>
					<div class="aswo-form-group">
						<label for="aswo-phone"><?php esc_html_e( 'Telefon', 'pluginaswo' ); ?></label>
						<input type="tel" id="aswo-phone" name="phone" />
					</div>
					<div class="aswo-form-group aswo-full-width">
						<label for="aswo-address"><?php esc_html_e( 'Adresă *', 'pluginaswo' ); ?></label>
						<input type="text" id="aswo-address" name="address" required />
					</div>
					<div class="aswo-form-group">
						<label for="aswo-city"><?php esc_html_e( 'Oraș *', 'pluginaswo' ); ?></label>
						<input type="text" id="aswo-city" name="city" required />
					</div>
					<div class="aswo-form-group">
						<label for="aswo-postcode"><?php esc_html_e( 'Cod poștal *', 'pluginaswo' ); ?></label>
						<input type="text" id="aswo-postcode" name="postcode" required />
					</div>
					<div class="aswo-form-group">
						<label for="aswo-country"><?php esc_html_e( 'Țară *', 'pluginaswo' ); ?></label>
						<input type="text" id="aswo-country" name="country" value="<?php echo esc_attr( get_option( 'aswo_country_code', 'RO' ) ); ?>" required />
					</div>
					<div class="aswo-form-group aswo-full-width">
						<label for="aswo-notes"><?php esc_html_e( 'Note comandă (opțional)', 'pluginaswo' ); ?></label>
						<textarea id="aswo-notes" name="notes" rows="3"></textarea>
					</div>
				</div>

				<div class="aswo-order-summary">
					<strong><?php esc_html_e( 'Total comandă:', 'pluginaswo' ); ?></strong>
					<span><?php echo esc_html( number_format( $cart->get_total(), 2 ) . ' ' . $currency ); ?></span>
				</div>

				<div class="aswo-form-actions">
					<button type="button" id="aswo-back-to-cart" class="aswo-btn aswo-btn-secondary">
						&#8592; <?php esc_html_e( 'Înapoi la coș', 'pluginaswo' ); ?>
					</button>
					<button type="submit" id="aswo-place-order-btn" class="aswo-btn aswo-btn-primary">
						<?php esc_html_e( 'Plasează comanda', 'pluginaswo' ); ?>
					</button>
				</div>
			</form>
		</div>

		<!-- Order success message -->
		<div id="aswo-order-success" class="aswo-order-success" style="display:none;">
			<h3><?php esc_html_e( 'Comanda a fost plasată cu succes!', 'pluginaswo' ); ?></h3>
			<p id="aswo-order-id"></p>
			<a href="<?php echo esc_url( $search_url ); ?>" class="aswo-btn aswo-btn-primary">
				<?php esc_html_e( 'Continuă cumpărăturile', 'pluginaswo' ); ?>
			</a>
		</div>

	<?php endif; ?>

	<div id="aswo-toast" class="aswo-toast"></div>
</div>
