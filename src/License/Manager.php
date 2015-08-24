<?php

namespace Never5\LicenseWP\License;

class Manager {

	/**
	 * Generate a new unique license key
	 *
	 * @return string
	 */
	public function generate_license_key() {
		global $wpdb;

		do {
			// generate key
			$key = apply_filters( 'license_wp_generate_license_key', strtoupper( sprintf(
				'%04x-%04x-%04x-%04x',
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
				mt_rand( 0, 0x0fff ) | 0x4000,
				mt_rand( 0, 0x3fff ) | 0x8000,
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
			) ) );

			// check if exists
			$result = $wpdb->get_row( $wpdb->prepare( 'SELECT `license_key` FROM ' . $wpdb->lwp_licenses . ' WHERE license_key = %s', $key ) );

		} while ( null !== $result ); // keep generating until we've got a unique key

		// return key
		return $key;
	}

	/**
	 * Get licenses by order ID
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function get_licenses_by_order( $order_id ) {
		global $wpdb;

		// keys
		$licenses = array();

		// fetch keys
		$results = $wpdb->get_results( $wpdb->prepare( 'SELECT `license_key` FROM ' . $wpdb->lwp_licenses . ' WHERE order_id = %d', $order_id ) );

		// count & loop
		if ( count( $results ) > 0 ) {
			foreach ( $results as $result ) {
				// add to array
				$licenses[] = license_wp()->service( 'license_factory' )->make( $result->license_key );
			}
		}

		// return license keys
		return $licenses;
	}

	/**
	 * Remove license by order ID
	 *
	 * @param $order_id
	 */
	public function remove_license_data_by_order( $order_id ) {
		global $wpdb;

		// get license keys
		$licenses = $this->get_licenses_by_order( $order_id );

		// count and loop
		if ( count( $licenses ) > 0 ) {
			foreach ( $licenses as $license ) {
				// delete all data connected to license key
				$wpdb->delete( $wpdb->lwp_licenses, array( 'license_key' => $license->get_key() ) );
				$wpdb->delete( $wpdb->lwp_activations, array( 'license_key' => $license->get_key() ) );
				$wpdb->delete( $wpdb->lwp_download_log, array( 'license_key' => $license->get_key() ) );
			}
		}

	}

}