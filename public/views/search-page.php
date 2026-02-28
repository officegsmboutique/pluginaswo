<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$enable_appliance = (bool) get_option( 'aswo_enable_appliance_search', 0 );
?>
<div id="aswo-search-wrapper" class="aswo-wrapper">

	<!-- ===== Search Tabs ===== -->
	<div class="aswo-tabs">
		<button class="aswo-tab active" data-tab="parts"><?php esc_html_e( 'Căutare Piese', 'pluginaswo' ); ?></button>
		<?php if ( $enable_appliance ) : ?>
		<button class="aswo-tab" data-tab="appliance"><?php esc_html_e( 'Căutare după Aparat', 'pluginaswo' ); ?></button>
		<?php endif; ?>
	</div>

	<!-- ===== Parts Search Tab ===== -->
	<div class="aswo-tab-content active" id="aswo-tab-parts">
		<div class="aswo-search-box">
			<div class="aswo-autocomplete-wrap">
				<input
					type="text"
					id="aswo-search-input"
					class="aswo-search-input"
					placeholder="<?php esc_attr_e( 'Caută piese de schimb (nr. articol, denumire, model aparat...)', 'pluginaswo' ); ?>"
					autocomplete="off"
				/>
				<div id="aswo-suggestions" class="aswo-suggestions"></div>
			</div>
			<button id="aswo-search-btn" class="aswo-btn aswo-btn-primary">
				<span class="aswo-icon-search">&#128269;</span>
				<?php esc_html_e( 'Caută', 'pluginaswo' ); ?>
			</button>
		</div>
	</div>

	<?php if ( $enable_appliance ) : ?>
	<!-- ===== Appliance Search Tab ===== -->
	<div class="aswo-tab-content" id="aswo-tab-appliance">
		<div class="aswo-search-box">
			<input
				type="text"
				id="aswo-appliance-input"
				class="aswo-search-input"
				placeholder="<?php esc_attr_e( 'Caută model aparat...', 'pluginaswo' ); ?>"
				autocomplete="off"
			/>
			<button id="aswo-appliance-btn" class="aswo-btn aswo-btn-primary">
				<span class="aswo-icon-search">&#128269;</span>
				<?php esc_html_e( 'Caută', 'pluginaswo' ); ?>
			</button>
		</div>
		<div id="aswo-appliance-results" class="aswo-appliance-results"></div>
	</div>
	<?php endif; ?>

	<!-- ===== Results Area ===== -->
	<div id="aswo-results-area" class="aswo-results-area">
		<div id="aswo-loading" class="aswo-loading" style="display:none;">
			<div class="aswo-spinner"></div>
			<p><?php esc_html_e( 'Se caută...', 'pluginaswo' ); ?></p>
		</div>
		<div id="aswo-no-results" class="aswo-no-results" style="display:none;">
			<p><?php esc_html_e( 'Nu s-au găsit rezultate.', 'pluginaswo' ); ?></p>
		</div>
		<div id="aswo-results-grid" class="aswo-results-grid"></div>
		<div id="aswo-pagination" class="aswo-pagination"></div>
	</div>

	<!-- ===== Product Detail (inline modal) ===== -->
	<div id="aswo-product-detail" class="aswo-product-detail" style="display:none;">
		<?php include ASWO_PLUGIN_DIR . 'public/views/product-detail.php'; ?>
	</div>

	<!-- ===== Toast Notifications ===== -->
	<div id="aswo-toast" class="aswo-toast"></div>

</div>

<!-- Article card template (populated by JS) -->
<template id="aswo-card-template">
	<div class="aswo-card">
		<div class="aswo-card-img">
			<img src="" alt="" loading="lazy" />
		</div>
		<div class="aswo-card-body">
			<p class="aswo-article-no"></p>
			<h3 class="aswo-article-name"></h3>
			<div class="aswo-price-row">
				<span class="aswo-price"></span>
				<span class="aswo-availability"></span>
			</div>
			<div class="aswo-card-actions">
				<input type="number" class="aswo-qty-input" value="1" min="1" />
				<button class="aswo-btn aswo-btn-primary aswo-add-to-cart-btn"><?php esc_html_e( 'Adaugă în coș', 'pluginaswo' ); ?></button>
				<button class="aswo-btn aswo-btn-secondary aswo-detail-btn"><?php esc_html_e( 'Detalii', 'pluginaswo' ); ?></button>
			</div>
		</div>
	</div>
</template>
