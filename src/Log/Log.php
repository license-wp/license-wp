<?php

namespace Never5\LicenseWP\Log;

class Log {

	/**
	 * Insert log entry
	 *
	 * @param int $product_id
	 * @param string $license_key
	 * @param string $activation_email
	 *
	 * @returns bool
	 */
	public function insert( $product_id, $license_key, $activation_email ) {
		global $wpdb;

		// clean vars
		$product_id       = absint( $product_id );
		$license_key      = sanitize_text_field( $license_key );
		$activation_email = sanitize_text_field( $activation_email );


		// insert into db
		$wpdb->insert( $wpdb->lwp_download_log, array(
			'licence_key'      => $license_key,
			'activation_email' => $activation_email,
			'api_product_id'   => $product_id,
			'date_downloaded'  => current_time( 'mysql' ),
			'user_ip_address'  => sanitize_text_field( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'] )
		) );

		// success
		return true;
	}

}