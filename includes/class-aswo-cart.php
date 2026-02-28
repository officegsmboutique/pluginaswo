<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ASWO_Cart - Manages the local shopping cart stored in PHP session.
 */
class ASWO_Cart {

	/** @var string */
	private $session_key = 'aswo_cart';

	public function __construct() {
		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}
	}

	/**
	 * Get all items in the cart.
	 *
	 * @return array
	 */
	public function get_items() {
		return isset( $_SESSION[ $this->session_key ] ) ? $_SESSION[ $this->session_key ] : array();
	}

	/**
	 * Add an item to the cart (or increase quantity if already present).
	 *
	 * @param string $article_no
	 * @param int    $quantity
	 * @param array  $item_data  Extra data: name, price, image_url
	 * @return void
	 */
	public function add_item( $article_no, $quantity = 1, $item_data = array() ) {
		$article_no = sanitize_text_field( $article_no );
		$quantity   = absint( $quantity );

		if ( ! isset( $_SESSION[ $this->session_key ] ) ) {
			$_SESSION[ $this->session_key ] = array();
		}

		if ( isset( $_SESSION[ $this->session_key ][ $article_no ] ) ) {
			$_SESSION[ $this->session_key ][ $article_no ]['quantity'] += $quantity;
		} else {
			$_SESSION[ $this->session_key ][ $article_no ] = array_merge(
				array(
					'article_no' => $article_no,
					'quantity'   => $quantity,
					'name'       => '',
					'price'      => 0,
					'image_url'  => '',
				),
				$item_data
			);
		}
	}

	/**
	 * Update the quantity of a cart item.
	 *
	 * @param string $article_no
	 * @param int    $quantity
	 * @return void
	 */
	public function update_item( $article_no, $quantity ) {
		$article_no = sanitize_text_field( $article_no );
		$quantity   = absint( $quantity );

		if ( isset( $_SESSION[ $this->session_key ][ $article_no ] ) ) {
			if ( $quantity <= 0 ) {
				$this->remove_item( $article_no );
			} else {
				$_SESSION[ $this->session_key ][ $article_no ]['quantity'] = $quantity;
			}
		}
	}

	/**
	 * Remove an item from the cart.
	 *
	 * @param string $article_no
	 * @return void
	 */
	public function remove_item( $article_no ) {
		$article_no = sanitize_text_field( $article_no );
		unset( $_SESSION[ $this->session_key ][ $article_no ] );
	}

	/**
	 * Get the total price of all items in the cart.
	 *
	 * @return float
	 */
	public function get_total() {
		$total = 0.0;
		foreach ( $this->get_items() as $item ) {
			$total += floatval( $item['price'] ) * absint( $item['quantity'] );
		}
		return $total;
	}

	/**
	 * Get the total number of items (sum of quantities) in the cart.
	 *
	 * @return int
	 */
	public function get_count() {
		$count = 0;
		foreach ( $this->get_items() as $item ) {
			$count += absint( $item['quantity'] );
		}
		return $count;
	}

	/**
	 * Clear all items from the cart.
	 *
	 * @return void
	 */
	public function clear_cart() {
		$_SESSION[ $this->session_key ] = array();
	}

	/**
	 * Sync local cart items to the EURAS API basket.
	 *
	 * @param ASWO_API $api
	 * @return bool|WP_Error
	 */
	public function sync_to_api( ASWO_API $api ) {
		$api->clear_basket();
		foreach ( $this->get_items() as $article_no => $item ) {
			$result = $api->add_to_basket( $article_no, $item['quantity'] );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}
		return true;
	}
}
