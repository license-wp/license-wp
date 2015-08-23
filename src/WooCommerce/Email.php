<?php

namespace Never5\LicenseWP\WooCommerce;

class Email {

	/**
	 * Setup hooks and filters
	 */
	public function setup() {
		add_action( 'woocommerce_email_before_order_table', array( $this, 'add_keys' ) );
		add_action( 'woocommerce_thankyou', array( $this, 'add_keys' ), 5 );
	}

	/**
	 * Add license keys to WooCommerce emails
	 *
	 * @param $order
	 */
	public function add_keys( $order ) {
		global $wpdb;

		// check and get order
		if ( ! is_object( $order ) ) {
			$order = new WC_Order( $order );
		}

		// fetch license keys
		$licence_keys = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->lwp_licenses . " WHERE order_id = %d", $order->id ) );

		// check if we've found license keys
		if ( null != $licence_keys ) {

			// load our template file
			wc_get_template( 'email-keys.php', array( 'keys' => $licence_keys ), 'license-wp', license_wp()->service( 'file' )->plugin_path() . '/templates/' );
		}
	}

}