<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ASWO_Orders - Handles storing and retrieving orders in the WordPress database.
 */
class ASWO_Orders {

	/** @var string Unprefixed table name (full name = $wpdb->prefix . self::$table) */
	private static $table = 'aswo_orders';

	/**
	 * Return the fully-qualified table name.
	 *
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . self::$table;
	}

	/**
	 * Create the orders table (called on plugin activation).
	 *
	 * @return void
	 */
	public static function create_table() {
		global $wpdb;

		$table      = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			aswo_order_id VARCHAR(100) NOT NULL DEFAULT '',
			status      VARCHAR(50)  NOT NULL DEFAULT 'pending',
			company     VARCHAR(255) NOT NULL DEFAULT '',
			first_name  VARCHAR(100) NOT NULL DEFAULT '',
			last_name   VARCHAR(100) NOT NULL DEFAULT '',
			email       VARCHAR(255) NOT NULL DEFAULT '',
			phone       VARCHAR(50)  NOT NULL DEFAULT '',
			address     TEXT         NOT NULL,
			city        VARCHAR(100) NOT NULL DEFAULT '',
			postcode    VARCHAR(20)  NOT NULL DEFAULT '',
			country     VARCHAR(10)  NOT NULL DEFAULT '',
			notes       TEXT         NOT NULL,
			items       LONGTEXT     NOT NULL,
			total       DECIMAL(10,2) NOT NULL DEFAULT '0.00',
			created_at  DATETIME     NOT NULL,
			PRIMARY KEY  (id),
			KEY aswo_order_id (aswo_order_id),
			KEY created_at (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Save a new order to the database.
	 *
	 * @param array  $order_data  Customer / address fields.
	 * @param array  $items       Cart items array.
	 * @param float  $total       Order total.
	 * @param string $aswo_order_id  The order ID returned by the ASWO API.
	 * @param string $status      Order status (default 'completed').
	 * @return int|false  Inserted row ID, or false on failure.
	 */
	public static function save( array $order_data, array $items, $total, $aswo_order_id = '', $status = 'completed' ) {
		global $wpdb;

		$result = $wpdb->insert(
			self::table_name(),
			array(
				'aswo_order_id' => sanitize_text_field( $aswo_order_id ),
				'status'        => sanitize_text_field( $status ),
				'company'       => sanitize_text_field( isset( $order_data['company'] ) ? $order_data['company'] : '' ),
				'first_name'    => sanitize_text_field( isset( $order_data['first_name'] ) ? $order_data['first_name'] : '' ),
				'last_name'     => sanitize_text_field( isset( $order_data['last_name'] ) ? $order_data['last_name'] : '' ),
				'email'         => sanitize_email( isset( $order_data['email'] ) ? $order_data['email'] : '' ),
				'phone'         => sanitize_text_field( isset( $order_data['phone'] ) ? $order_data['phone'] : '' ),
				'address'       => sanitize_text_field( isset( $order_data['address'] ) ? $order_data['address'] : '' ),
				'city'          => sanitize_text_field( isset( $order_data['city'] ) ? $order_data['city'] : '' ),
				'postcode'      => sanitize_text_field( isset( $order_data['postcode'] ) ? $order_data['postcode'] : '' ),
				'country'       => sanitize_text_field( isset( $order_data['country'] ) ? $order_data['country'] : '' ),
				'notes'         => sanitize_textarea_field( isset( $order_data['notes'] ) ? $order_data['notes'] : '' ),
				'items'         => wp_json_encode( $items ),
				'total'         => floatval( $total ),
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Retrieve orders with optional pagination.
	 *
	 * @param int $per_page
	 * @param int $paged
	 * @return array  { items: array, total: int }
	 */
	public static function get_orders( $per_page = 20, $paged = 1 ) {
		global $wpdb;

		$table  = self::table_name();
		$offset = absint( ( $paged - 1 ) * $per_page );
		$limit  = absint( $per_page );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$limit,
				$offset
			)
		);

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		// phpcs:enable

		return array(
			'items' => $items ? $items : array(),
			'total' => $total,
		);
	}

	/**
	 * Retrieve a single order by its row ID.
	 *
	 * @param int $id
	 * @return object|null
	 */
	public static function get_order( $id ) {
		global $wpdb;

		$table = self::table_name();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", absint( $id ) )
		);
		// phpcs:enable
	}
}
