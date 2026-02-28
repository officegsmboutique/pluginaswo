<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ASWO_Shortcodes - Registers [aswo_parts_search] and [aswo_cart] shortcodes.
 */
class ASWO_Shortcodes {

	public function init() {
		add_shortcode( 'aswo_parts_search', array( $this, 'render_search' ) );
		add_shortcode( 'aswo_cart', array( $this, 'render_cart' ) );
	}

	/**
	 * Render the parts search shortcode.
	 *
	 * @return string
	 */
	public function render_search( $atts ) {
		ob_start();
		include ASWO_PLUGIN_DIR . 'public/views/search-page.php';
		return ob_get_clean();
	}

	/**
	 * Render the cart shortcode.
	 *
	 * @return string
	 */
	public function render_cart( $atts ) {
		ob_start();
		include ASWO_PLUGIN_DIR . 'public/views/cart-page.php';
		return ob_get_clean();
	}
}
