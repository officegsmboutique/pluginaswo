<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// This template is included inside #aswo-product-detail (hidden by default).
// Content is populated dynamically via JavaScript.
?>
<div class="aswo-detail-inner">
	<button class="aswo-back-btn aswo-btn aswo-btn-secondary">
		&#8592; <?php esc_html_e( 'Înapoi la căutare', 'pluginaswo' ); ?>
	</button>
	<div class="aswo-detail-body">
		<div class="aswo-detail-img">
			<img id="aswo-detail-image" src="" alt="" />
		</div>
		<div class="aswo-detail-info">
			<p id="aswo-detail-article-no" class="aswo-article-no"></p>
			<h2 id="aswo-detail-name"></h2>
			<p id="aswo-detail-description" class="aswo-description"></p>
			<div class="aswo-price-row">
				<span id="aswo-detail-price" class="aswo-price"></span>
				<span id="aswo-detail-availability" class="aswo-availability"></span>
			</div>
			<div id="aswo-detail-compatibles" class="aswo-compatibles"></div>
			<div class="aswo-card-actions">
				<input type="number" id="aswo-detail-qty" class="aswo-qty-input" value="1" min="1" />
				<button id="aswo-detail-add-cart" class="aswo-btn aswo-btn-primary">
					<?php esc_html_e( 'Adaugă în coș', 'pluginaswo' ); ?>
				</button>
			</div>
		</div>
	</div>
</div>
