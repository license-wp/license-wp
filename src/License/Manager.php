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
			$result = $wpdb->get_row( $wpdb->prepare( 'SELECT FROM ' . $wpdb->prefix . 'license_wp_licenses WHERE license_key = %s', $key ) );

		} while ( null !== $result ); // keep generating until we've got a unique key

		// return key
		return $key;
	}

}