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
			$order = new \WC_Order( $order );
		}

		// fetch license keys
		$licenses = license_wp()->service( 'license_manager' )->get_licenses_by_order( $order->id );

		// check if we've found license keys
		if ( is_array( $licenses ) && count( $licenses ) > 0 ) {

			// load our template file
			wc_get_template( 'email-keys.php', array( 'licenses' => $licenses ), 'license-wp', license_wp()->service( 'file' )->plugin_path() . '/templates/' );
		}
	}

}