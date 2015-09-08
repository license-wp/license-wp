<?php

namespace Never5\LicenseWP\Activation;

class Manager {

	/**
	 * Get activated activations by license
	 *
	 * @param \Never5\LicenseWP\License\License $license
	 * @param \Never5\LicenseWP\ApiProduct\ApiProduct $api_product
	 * @param bool $only_active
	 *
	 * @return array
	 */
	public function get_activations( $license, $api_product = null, $only_active = true ) {
		global $wpdb;

		// dat SQL
		$sql = "SELECT `activation_id` FROM {$wpdb->lwp_activations} WHERE `license_key`=%s";

		// add API product ID if !== null
		if ( null !== $api_product ) {
			$sql .= $wpdb->prepare( " AND `api_product_id`=%s ", $api_product->get_slug() );
		}

		// add if only_active is true
		if ( $only_active ) {
			$sql .= " AND activation_active = 1";
		}

		$sql .= ";";

		// get activation rows
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $license->get_key() ) );

		// array that stores the api products
		$activations = array();

		// check and loop
		if ( is_array( $rows ) && count( $rows ) > 0 ) {
			foreach ( $rows as $row ) {

				// create ApiProduct objects and store them in array
				$activations[] = license_wp()->service( 'activation_factory' )->make( $row->activation_id );
			}
		}

		// return array
		return $activations;
	}

	/**
	 * Deactivate an activation
	 *
	 * @param Activation $activation
	 *
	 * @return bool
	 */
	public function deactivate( $activation ) {
		// set active to 0
		$activation->set_activation_active( 0 );


		// persists activation
		$activation = license_wp()->service( 'activation_repository' )->persist( $activation );

		// return true of active is set to 0
		return ( $activation->get_activation_active() == 0 );
	}

}