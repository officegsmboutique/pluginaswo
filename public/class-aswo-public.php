<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ASWO_Public - Handles frontend enqueue and AJAX actions.
 */
class ASWO_Public {

	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'maybe_start_session' ) );
	}

	/**
	 * Start PHP session early if not already started (for cart).
	 */
	public function maybe_start_session() {
		// Session is started in ASWO_Cart constructor; this is a safety call.
		if ( ! session_id() ) {
			session_start();
		}
	}

	/**
	 * Enqueue CSS and JS on the front end.
	 */
	public function enqueue_scripts() {
		wp_enqueue_style(
			'aswo-public',
			ASWO_PLUGIN_URL . 'assets/css/aswo-public.css',
			array(),
			ASWO_VERSION
		);

		wp_enqueue_script(
			'aswo-public',
			ASWO_PLUGIN_URL . 'assets/js/aswo-public.js',
			array( 'jquery' ),
			ASWO_VERSION,
			true
		);

		$cart     = new ASWO_Cart();
		$currency = get_option( 'aswo_currency', 'EUR' );
		$cart_url = '';
		$cart_page_id = absint( get_option( 'aswo_cart_page_id', 0 ) );
		if ( $cart_page_id ) {
			$cart_url = get_permalink( $cart_page_id );
		}

		wp_localize_script(
			'aswo-public',
			'aswoPublic',
			array(
				'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( 'aswo_public' ),
				'currency'          => esc_js( $currency ),
				'cartUrl'           => esc_url( $cart_url ),
				'cartCount'         => $cart->get_count(),
				'enableAppliance'   => (bool) get_option( 'aswo_enable_appliance_search', 0 ),
				'i18n'              => array(
					'searching'      => __( 'Se caută...', 'pluginaswo' ),
					'noResults'      => __( 'Nu s-au găsit rezultate.', 'pluginaswo' ),
					'addedToCart'    => __( 'Produs adăugat în coș!', 'pluginaswo' ),
					'removedFromCart'=> __( 'Produs eliminat din coș.', 'pluginaswo' ),
					'cartUpdated'    => __( 'Coșul a fost actualizat.', 'pluginaswo' ),
					'orderPlaced'    => __( 'Comanda a fost plasată cu succes!', 'pluginaswo' ),
					'errorGeneric'   => __( 'A apărut o eroare. Vă rugăm să încercați din nou.', 'pluginaswo' ),
					'confirmRemove'  => __( 'Sigur doriți să eliminați acest produs?', 'pluginaswo' ),
					'inStock'        => __( 'In stock', 'pluginaswo' ),
					'outOfStock'     => __( 'Indisponibil', 'pluginaswo' ),
					'requiredFields' => __( 'Completați toate câmpurile obligatorii.', 'pluginaswo' ),
					'invalidEmail'   => __( 'Adresa de email nu este validă.', 'pluginaswo' ),
					'processing'     => __( 'Se procesează...', 'pluginaswo' ),
					'placeOrder'     => __( 'Plasează comanda', 'pluginaswo' ),
					'compatTitle'    => __( 'Aparate compatibile:', 'pluginaswo' ),
					'orderIdLabel'   => __( 'ID Comandă: ', 'pluginaswo' ),
				),
			)
		);
	}

	// =========================================================================
	// AJAX Handlers
	// =========================================================================

	/** Verify public nonce and return error on failure. */
	private static function verify_nonce() {
		if ( ! check_ajax_referer( 'aswo_public', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Nonce invalid.', 'pluginaswo' ) ) );
		}
	}

	/** aswo_search */
	public static function handle_ajax_aswo_search() {
		self::verify_nonce();
		$query = sanitize_text_field( wp_unslash( isset( $_GET['query'] ) ? $_GET['query'] : '' ) );
		$page  = absint( isset( $_GET['page'] ) ? $_GET['page'] : 1 );
		$limit = absint( get_option( 'aswo_results_per_page', 20 ) );

		if ( empty( $query ) ) {
			wp_send_json_error( array( 'message' => __( 'Introduceți un termen de căutare.', 'pluginaswo' ) ) );
		}

		$api    = new ASWO_API();
		$result = $api->search_articles( $query, $limit, $page );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	/** aswo_article_detail */
	public static function handle_ajax_aswo_article_detail() {
		self::verify_nonce();
		$article_no = sanitize_text_field( wp_unslash( isset( $_GET['article_no'] ) ? $_GET['article_no'] : '' ) );

		if ( empty( $article_no ) ) {
			wp_send_json_error( array( 'message' => __( 'Nr. articol lipsă.', 'pluginaswo' ) ) );
		}

		$api    = new ASWO_API();
		$result = $api->get_article_detail( $article_no );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	/** aswo_suggestions */
	public static function handle_ajax_aswo_suggestions() {
		self::verify_nonce();
		$query = sanitize_text_field( wp_unslash( isset( $_GET['query'] ) ? $_GET['query'] : '' ) );

		if ( empty( $query ) ) {
			wp_send_json_success( array( 'suggestions' => array() ) );
		}

		$api    = new ASWO_API();
		$result = $api->get_suggestions( $query );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}

	/** aswo_add_to_cart */
	public static function handle_ajax_aswo_add_to_cart() {
		self::verify_nonce();
		$article_no = sanitize_text_field( wp_unslash( isset( $_POST['article_no'] ) ? $_POST['article_no'] : '' ) );
		$quantity   = absint( isset( $_POST['quantity'] ) ? $_POST['quantity'] : 1 );
		$name       = sanitize_text_field( wp_unslash( isset( $_POST['name'] ) ? $_POST['name'] : '' ) );
		$price      = floatval( isset( $_POST['price'] ) ? $_POST['price'] : 0 );
		$image_url  = esc_url_raw( isset( $_POST['image_url'] ) ? $_POST['image_url'] : '' );

		if ( empty( $article_no ) ) {
			wp_send_json_error( array( 'message' => __( 'Nr. articol lipsă.', 'pluginaswo' ) ) );
		}

		$cart = new ASWO_Cart();
		$cart->add_item(
			$article_no,
			$quantity,
			array(
				'name'      => $name,
				'price'     => $price,
				'image_url' => $image_url,
			)
		);

		wp_send_json_success(
			array(
				'count'   => $cart->get_count(),
				'total'   => $cart->get_total(),
				'message' => __( 'Produs adăugat în coș!', 'pluginaswo' ),
			)
		);
	}

	/** aswo_update_cart */
	public static function handle_ajax_aswo_update_cart() {
		self::verify_nonce();
		$article_no = sanitize_text_field( wp_unslash( isset( $_POST['article_no'] ) ? $_POST['article_no'] : '' ) );
		$quantity   = absint( isset( $_POST['quantity'] ) ? $_POST['quantity'] : 0 );

		if ( empty( $article_no ) ) {
			wp_send_json_error( array( 'message' => __( 'Nr. articol lipsă.', 'pluginaswo' ) ) );
		}

		$cart = new ASWO_Cart();
		$cart->update_item( $article_no, $quantity );

		wp_send_json_success(
			array(
				'count' => $cart->get_count(),
				'total' => $cart->get_total(),
				'items' => $cart->get_items(),
			)
		);
	}

	/** aswo_remove_from_cart */
	public static function handle_ajax_aswo_remove_from_cart() {
		self::verify_nonce();
		$article_no = sanitize_text_field( wp_unslash( isset( $_POST['article_no'] ) ? $_POST['article_no'] : '' ) );

		if ( empty( $article_no ) ) {
			wp_send_json_error( array( 'message' => __( 'Nr. articol lipsă.', 'pluginaswo' ) ) );
		}

		$cart = new ASWO_Cart();
		$cart->remove_item( $article_no );

		wp_send_json_success(
			array(
				'count' => $cart->get_count(),
				'total' => $cart->get_total(),
				'items' => $cart->get_items(),
			)
		);
	}

	/** aswo_get_cart */
	public static function handle_ajax_aswo_get_cart() {
		self::verify_nonce();
		$cart = new ASWO_Cart();

		wp_send_json_success(
			array(
				'items' => $cart->get_items(),
				'count' => $cart->get_count(),
				'total' => $cart->get_total(),
			)
		);
	}

	/** aswo_place_order */
	public static function handle_ajax_aswo_place_order() {
		self::verify_nonce();

		$order_data = array(
			'company'    => sanitize_text_field( wp_unslash( isset( $_POST['company'] ) ? $_POST['company'] : '' ) ),
			'first_name' => sanitize_text_field( wp_unslash( isset( $_POST['first_name'] ) ? $_POST['first_name'] : '' ) ),
			'last_name'  => sanitize_text_field( wp_unslash( isset( $_POST['last_name'] ) ? $_POST['last_name'] : '' ) ),
			'email'      => sanitize_email( wp_unslash( isset( $_POST['email'] ) ? $_POST['email'] : '' ) ),
			'phone'      => sanitize_text_field( wp_unslash( isset( $_POST['phone'] ) ? $_POST['phone'] : '' ) ),
			'address'    => sanitize_text_field( wp_unslash( isset( $_POST['address'] ) ? $_POST['address'] : '' ) ),
			'city'       => sanitize_text_field( wp_unslash( isset( $_POST['city'] ) ? $_POST['city'] : '' ) ),
			'postcode'   => sanitize_text_field( wp_unslash( isset( $_POST['postcode'] ) ? $_POST['postcode'] : '' ) ),
			'country'    => sanitize_text_field( wp_unslash( isset( $_POST['country'] ) ? $_POST['country'] : '' ) ),
			'notes'      => sanitize_textarea_field( wp_unslash( isset( $_POST['notes'] ) ? $_POST['notes'] : '' ) ),
		);

		// Basic validation.
		$required = array( 'first_name', 'last_name', 'email', 'address', 'city', 'postcode', 'country' );
		foreach ( $required as $field ) {
			if ( empty( $order_data[ $field ] ) ) {
				/* translators: %s: field name */
				wp_send_json_error( array( 'message' => sprintf( __( 'Câmpul "%s" este obligatoriu.', 'pluginaswo' ), esc_html( $field ) ) ) );
			}
		}

		if ( ! is_email( $order_data['email'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Adresa de email nu este validă.', 'pluginaswo' ) ) );
		}

		$cart = new ASWO_Cart();
		if ( 0 === $cart->get_count() ) {
			wp_send_json_error( array( 'message' => __( 'Coșul este gol.', 'pluginaswo' ) ) );
		}

		$api  = new ASWO_API();
		$sync = $cart->sync_to_api( $api );
		if ( is_wp_error( $sync ) ) {
			wp_send_json_error( array( 'message' => $sync->get_error_message() ) );
		}

		$result = $api->place_order( $order_data );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		$cart->clear_cart();
		wp_send_json_success(
			array(
				'message'  => __( 'Comanda a fost plasată cu succes!', 'pluginaswo' ),
				'order_id' => isset( $result['order_id'] ) ? $result['order_id'] : '',
			)
		);
	}

	/** aswo_appliance_search */
	public static function handle_ajax_aswo_appliance_search() {
		self::verify_nonce();
		$query = sanitize_text_field( wp_unslash( isset( $_GET['query'] ) ? $_GET['query'] : '' ) );

		if ( empty( $query ) ) {
			wp_send_json_error( array( 'message' => __( 'Introduceți un model de aparat.', 'pluginaswo' ) ) );
		}

		$api    = new ASWO_API();
		$result = $api->search_appliances( $query );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success( $result );
	}
}
