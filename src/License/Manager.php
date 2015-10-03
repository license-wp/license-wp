<?php

namespace Never5\LicenseWP\License;

use Never5\LicenseWP\Email;

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
	 * @param bool $active Whether or not to return only active licenses. Default false.
	 *
	 * @return array
	 */
	public function get_licenses_by_order( $order_id, $active = false ) {
		global $wpdb;

		// keys
		$licenses = array();

		// generate query
		$sql = $wpdb->prepare( 'SELECT `license_key` FROM ' . $wpdb->lwp_licenses . ' WHERE order_id = %d', $order_id );

		if ( $active ) {
			$sql .= " AND (
				date_expires IS NULL
				OR date_expires = '0000-00-00 00:00:00'
				OR date_expires > NOW()
			)";
		}

		// fetch keys
		$results = $wpdb->get_results( $sql );

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
	 * Get licenses by user ID
	 *
	 * @param int $user_id
	 * @param bool $active Whether or not to return only active licenses. Default false.
	 *
	 * @return array
	 */
	public function get_licenses_by_user( $user_id, $active = false ) {
		global $wpdb;

		// keys
		$licenses = array();

		// generate query
		$sql = $wpdb->prepare( 'SELECT `license_key` FROM ' . $wpdb->lwp_licenses . ' WHERE user_id = %d', $user_id );

		if ( $active ) {
			$sql .= " AND (
				date_expires IS NULL
				OR date_expires = '0000-00-00 00:00:00'
				OR date_expires > NOW()
			)";
		}

		// fetch keys
		$results = $wpdb->get_results( $sql );

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
	 * Get licenses by email addresses
	 *
	 * @param string $email
	 * @param bool $active Whether or not to return only active licenses. Default false.
	 *
	 * @return array
	 */
	public function get_licenses_by_email( $email, $active = false ) {
		global $wpdb;

		// keys
		$licenses = array();

		// generate query
		$sql = $wpdb->prepare( 'SELECT `license_key` FROM ' . $wpdb->lwp_licenses . ' WHERE activation_email = %s', $email );

		if ( $active ) {
			$sql .= " AND (
				date_expires IS NULL
				OR date_expires = '0000-00-00 00:00:00'
				OR date_expires > NOW()
			)";
		}

		// fetch keys
		$results = $wpdb->get_results( $sql );

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

	/**
	 * Get License object that expire on given date
	 *
	 * @param \DateTime $date
	 *
	 * @return array<License>
	 */
	public function get_licenses_that_expire_on( $date ) {
		global $wpdb;

		// keys
		$licenses = array();

		// generate query
		$sql = $wpdb->prepare( "SELECT `license_key` FROM " . $wpdb->lwp_licenses . " WHERE DATE_FORMAT( `date_expires`, '%%Y-%%c-%%d' ) = '%s' ", $date->format( 'Y-m-d' ) );
		
		// fetch keys
		$results = $wpdb->get_results( $sql );

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
	 * Send all expiration emails
	 */
	public function send_expiration_emails() {

		// load emails
		$emails = apply_filters( 'license_wp_renewal_emails', array() );

		// check if we have at least 1 email
		if ( is_array( $emails ) && count( $emails ) > 0 ) {

			// loop through emails
			foreach ( $emails as $email_data ) {

				// create \DateTime of today
				$date = new \DateTime();
				$date->setTime( 0, 0, 0 );

				// try to modify object with $email_data['date_modify']
				if ( false !== $date->modify( $email_data['date_modify'] ) ) {

					// get licenses that expire on modified date object
					$licenses = $this->get_licenses_that_expire_on( $date );

					// check if there are licenses
					if ( count( $licenses ) > 0 ) {

						/** @var License $license */
						foreach ( $licenses as $license ) {

							// prep body, replace vars for correct content
							$body = $this->replace_expiration_vars( $email_data['body'], $license );

							// setup email object
							$email = new Email\ExpiringLicense( $email_data['subject'], $body, $license );

							// send email
							license_wp()->service( 'email_manager' )->send( $email, $license->get_activation_email() );
						}
					}

				}

			}

		}

	}

	/**
	 * @param string $content
	 * @param License $license
	 *
	 * @return string
	 */
	private function replace_expiration_vars( $content, $license ) {

		// get user
		$user = get_user_by( 'id', $license->get_user_id() );

		// get first name
		$fname = 'there';
		if ( ! empty( $user ) && ! empty( $user->first_name ) ) {
			$fname = $user->first_name;
		}

		// get WooCommerce product object
		$wc_product = new \WC_Product( $license->get_product_id() );

		// get parent product if the product has one
		if ( 0 != $wc_product->get_parent() ) {
			$wc_product = new \WC_Product( $wc_product->get_parent() );
		}

		$content = str_ireplace( ':fname:', $fname, $content );
		$content = str_ireplace( ':product:', $wc_product->get_title(), $content );
		$content = str_ireplace( ':license-key:', $license->get_key(), $content );
		$content = str_ireplace( ':license-expiration-date:', $license->get_date_expires()->format( 'M d Y' ), $content );
		$content = str_ireplace( ':renewal-link:', $license->get_renewal_url(), $content );

		return $content;
	}

}
