<?php

namespace Never5\LicenseWP\Activation;

class Manager {

	/**
	 * Get activated activations by license
	 *
	 * @param \Never5\LicenseWP\License\License $license
	 *
	 * @return array
	 */
	public function get_activations( $license ) {
		global $wpdb;

		// get activation rows
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT `activation_id` FROM {$wpdb->lwp_activations} WHERE activation_active = 1 AND license_key=%s;", $license->get_key() ) );

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