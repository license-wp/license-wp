<?php

namespace Never5\LicenseWP\Activation;

class WordPressRepository implements Repository {

	/**
	 * Retrieve activation data from WordPress database
	 *
	 * @param int $id
	 *
	 * @return \stdClass
	 */
	public function retrieve( $id ) {
		global $wpdb;

		$data = new \stdClass();

		// fetch row from DB
		$row = $wpdb->get_row( $wpdb->prepare( "
		SELECT * FROM {$wpdb->lwp_activations}
		WHERE activation_id = %d
	", $id ) );

		// set data if row found
		if ( null !== $row ) {
			$data->id                = $row->activation_id;
			$data->license_key       = $row->license_key;
			$data->api_product_id    = $row->api_product_id;
			$data->instance          = $row->instance;
			$data->activation_date   = new \DateTime( $row->activation_date );
			$data->activation_active = $row->activation_active;
		}

		return $data;
	}

	/**
	 * Persist activation data in WordPress database
	 *
	 * @param Activation $activation
	 *
	 * @return Activation
	 */
	public function persist( $activation ) {
		global $wpdb;

		// dem defaults
		$defaults = array(
			'license_key'       => '',
			'api_product_id'    => 0,
			'instance'          => '',
			'activation_date'   => '',
			'activation_active' => 0,
		);

		// setup array with data
		$data = wp_parse_args( array(
			'license_key'       => $activation->get_license_key(),
			'api_product_id'    => $activation->get_api_product_id(),
			'instance'          => $activation->get_instance(),
			'activation_date'   => $activation->get_activation_date()->format( 'Y-m-d' ),
			'activation_active' => $activation->get_activation_active(),

		), $defaults );

		// check if new license or existing
		if ( 0 === $activation->get_id() ) { // insert

			// insert into WordPress database
			$wpdb->insert( $wpdb->lwp_activations, $data );

			// set activation id
			$activation->set_id( $wpdb->insert_id );
		} else { // update

			// update database
			$wpdb->update( $wpdb->lwp_activations,
				$data,
				array(
					'activation_id' => $activation->get_id()
				)
			);

		}

		return $activation;
	}

}