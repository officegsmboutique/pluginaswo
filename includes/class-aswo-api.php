<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ASWO_API - Handles all communication with the EURAS EED API.
 */
class ASWO_API {

	/** @var string */
	private $base_url;

	/** @var string */
	private $customer_id;

	/** @var string|null */
	private $session_token = null;

	public function __construct() {
		$this->base_url    = get_option( 'aswo_api_base_url', 'https://shop.euras.com/eed/' );
		$this->customer_id = get_option( 'aswo_customer_id', '' );
	}

	/**
	 * Obtain or refresh a session token (cached in transients for 30 minutes).
	 *
	 * @return string|WP_Error
	 */
	public function start_session() {
		$cached = get_transient( 'aswo_session_token' );
		if ( $cached ) {
			$this->session_token = $cached;
			return $cached;
		}

		$url = add_query_arg(
			array(
				'do'    => 'session',
				'user'  => rawurlencode( $this->customer_id ),
				'pwd'   => rawurlencode( $this->customer_id ),
				'kid'   => rawurlencode( $this->customer_id ),
			),
			$this->base_url
		);

		$response = $this->do_request( $url );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$token = isset( $response['session'] ) ? sanitize_text_field( $response['session'] ) : '';
		if ( empty( $token ) ) {
			return new WP_Error( 'aswo_session_error', __( 'Nu s-a putut obține token-ul de sesiune.', 'pluginaswo' ) );
		}

		set_transient( 'aswo_session_token', $token, 30 * MINUTE_IN_SECONDS );
		$this->session_token = $token;
		return $token;
	}

	/**
	 * Ensure we have a valid session, refreshing if necessary.
	 *
	 * @return string|WP_Error
	 */
	private function get_session() {
		if ( $this->session_token ) {
			return $this->session_token;
		}
		return $this->start_session();
	}

	/**
	 * Search articles.
	 *
	 * @param string $query
	 * @param int    $limit
	 * @param int    $page
	 * @return array|WP_Error
	 */
	public function search_articles( $query, $limit = 20, $page = 1 ) {
		$session = $this->get_session();
		if ( is_wp_error( $session ) ) {
			return $session;
		}

		$url = add_query_arg(
			array(
				'do'      => 'search',
				'session' => $session,
				'q'       => rawurlencode( $query ),
				'limit'   => absint( $limit ),
				'page'    => absint( $page ),
				'lang'    => rawurlencode( get_option( 'aswo_language', 'en' ) ),
				'country' => rawurlencode( get_option( 'aswo_country_code', 'RO' ) ),
			),
			$this->base_url
		);

		return $this->do_request( $url );
	}

	/**
	 * Extended article search.
	 *
	 * @param array $params
	 * @return array|WP_Error
	 */
	public function search_articles_extended( $params ) {
		$session = $this->get_session();
		if ( is_wp_error( $session ) ) {
			return $session;
		}

		$defaults = array(
			'do'      => 'search_ext',
			'session' => $session,
			'lang'    => get_option( 'aswo_language', 'en' ),
			'country' => get_option( 'aswo_country_code', 'RO' ),
		);
		$args = array_merge( $defaults, $params );

		$url = add_query_arg( array_map( 'rawurlencode', $args ), $this->base_url );
		return $this->do_request( $url );
	}

	/**
	 * Get article detail.
	 *
	 * @param string $article_number
	 * @return array|WP_Error
	 */
	public function get_article_detail( $article_number ) {
		$session = $this->get_session();
		if ( is_wp_error( $session ) ) {
			return $session;
		}

		$url = add_query_arg(
			array(
				'do'         => 'article',
				'session'    => $session,
				'article_no' => rawurlencode( $article_number ),
				'lang'       => rawurlencode( get_option( 'aswo_language', 'en' ) ),
				'country'    => rawurlencode( get_option( 'aswo_country_code', 'RO' ) ),
			),
			$this->base_url
		);

		return $this->do_request( $url );
	}

	/**
	 * Get article picture URL.
	 *
	 * @param string $article_number
	 * @param string $size  e.g. 'S', 'M', 'L'
	 * @return string|WP_Error
	 */
	public function get_article_picture( $article_number, $size = 'M' ) {
		$session = $this->get_session();
		if ( is_wp_error( $session ) ) {
			return $session;
		}

		return add_query_arg(
			array(
				'do'         => 'picture',
				'session'    => $session,
				'article_no' => rawurlencode( $article_number ),
				'size'       => rawurlencode( $size ),
			),
			$this->base_url
		);
	}

	/**
	 * Get autocomplete suggestions.
	 *
	 * @param string $query
	 * @return array|WP_Error
	 */
	public function get_suggestions( $query ) {
		$session = $this->get_session();
		if ( is_wp_error( $session ) ) {
			return $session;
		}

		$url = add_query_arg(
			array(
				'do'      => 'suggest',
				'session' => $session,
				'q'       => rawurlencode( $query ),
				'lang'    => rawurlencode( get_option( 'aswo_language', 'en' ) ),
			),
			$this->base_url
		);

		return $this->do_request( $url );
	}

	/**
	 * Search appliances.
	 *
	 * @param string $query
	 * @return array|WP_Error
	 */
	public function search_appliances( $query ) {
		$session = $this->get_session();
		if ( is_wp_error( $session ) ) {
			return $session;
		}

		$url = add_query_arg(
			array(
				'do'      => 'appliance_search',
				'session' => $session,
				'q'       => rawurlencode( $query ),
				'lang'    => rawurlencode( get_option( 'aswo_language', 'en' ) ),
			),
			$this->base_url
		);

		return $this->do_request( $url );
	}

	/**
	 * Get articles for a specific appliance.
	 *
	 * @param string $appliance_id
	 * @return array|WP_Error
	 */
	public function get_appliance_articles( $appliance_id ) {
		$session = $this->get_session();
		if ( is_wp_error( $session ) ) {
			return $session;
		}

		$url = add_query_arg(
			array(
				'do'           => 'appliance_articles',
				'session'      => $session,
				'appliance_id' => rawurlencode( $appliance_id ),
				'lang'         => rawurlencode( get_option( 'aswo_language', 'en' ) ),
				'country'      => rawurlencode( get_option( 'aswo_country_code', 'RO' ) ),
			),
			$this->base_url
		);

		return $this->do_request( $url );
	}

	/**
	 * Add item to API basket.
	 *
	 * @param string $article_no
	 * @param int    $quantity
	 * @return array|WP_Error
	 */
	public function add_to_basket( $article_no, $quantity = 1 ) {
		$session = $this->get_session();
		if ( is_wp_error( $session ) ) {
			return $session;
		}

		$url = add_query_arg(
			array(
				'do'         => 'basket_add',
				'session'    => $session,
				'article_no' => rawurlencode( $article_no ),
				'qty'        => absint( $quantity ),
			),
			$this->base_url
		);

		return $this->do_request( $url );
	}

	/**
	 * Get current API basket.
	 *
	 * @return array|WP_Error
	 */
	public function get_basket() {
		$session = $this->get_session();
		if ( is_wp_error( $session ) ) {
			return $session;
		}

		$url = add_query_arg(
			array(
				'do'      => 'basket',
				'session' => $session,
			),
			$this->base_url
		);

		return $this->do_request( $url );
	}

	/**
	 * Update basket item quantity.
	 *
	 * @param string $article_no
	 * @param int    $quantity
	 * @return array|WP_Error
	 */
	public function update_basket( $article_no, $quantity ) {
		$session = $this->get_session();
		if ( is_wp_error( $session ) ) {
			return $session;
		}

		$url = add_query_arg(
			array(
				'do'         => 'basket_update',
				'session'    => $session,
				'article_no' => rawurlencode( $article_no ),
				'qty'        => absint( $quantity ),
			),
			$this->base_url
		);

		return $this->do_request( $url );
	}

	/**
	 * Remove item from basket.
	 *
	 * @param string $article_no
	 * @return array|WP_Error
	 */
	public function remove_from_basket( $article_no ) {
		$session = $this->get_session();
		if ( is_wp_error( $session ) ) {
			return $session;
		}

		$url = add_query_arg(
			array(
				'do'         => 'basket_remove',
				'session'    => $session,
				'article_no' => rawurlencode( $article_no ),
			),
			$this->base_url
		);

		return $this->do_request( $url );
	}

	/**
	 * Clear the API basket.
	 *
	 * @return array|WP_Error
	 */
	public function clear_basket() {
		$session = $this->get_session();
		if ( is_wp_error( $session ) ) {
			return $session;
		}

		$url = add_query_arg(
			array(
				'do'      => 'basket_clear',
				'session' => $session,
			),
			$this->base_url
		);

		return $this->do_request( $url );
	}

	/**
	 * Place an order.
	 *
	 * @param array $order_data
	 * @return array|WP_Error
	 */
	public function place_order( $order_data ) {
		$session = $this->get_session();
		if ( is_wp_error( $session ) ) {
			return $session;
		}

		$args = array_merge(
			array(
				'do'      => 'order',
				'session' => $session,
			),
			$order_data
		);

		$url = add_query_arg( $args, $this->base_url );
		return $this->do_request( $url );
	}

	/**
	 * Perform an HTTP GET request and decode the JSON response.
	 *
	 * @param string $url
	 * @return array|WP_Error
	 */
	private function do_request( $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 30,
				'headers' => array(
					'Accept' => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'ASWO API error: ' . $response->get_error_message() );
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( 200 !== (int) $code ) {
			error_log( 'ASWO API HTTP error: ' . $code . ' body: ' . $body );
			return new WP_Error( 'aswo_http_error', sprintf( __( 'API HTTP error: %d', 'pluginaswo' ), $code ) );
		}

		$data = json_decode( $body, true );
		if ( null === $data ) {
			error_log( 'ASWO API JSON decode error for body: ' . $body );
			return new WP_Error( 'aswo_json_error', __( 'Răspuns API invalid (JSON).', 'pluginaswo' ) );
		}

		// If the API returns an error field, surface it.
		if ( isset( $data['error'] ) && ! empty( $data['error'] ) ) {
			error_log( 'ASWO API returned error: ' . $data['error'] );
			// Session expired – clear cached token so next call refreshes.
			if ( false !== strpos( $data['error'], 'session' ) ) {
				delete_transient( 'aswo_session_token' );
				$this->session_token = null;
			}
			return new WP_Error( 'aswo_api_error', sanitize_text_field( $data['error'] ) );
		}

		return $data;
	}
}
