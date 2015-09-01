<?php

namespace Never5\LicenseWP\License;

class WordPressRepository implements Repository {

	/**
	 * Retrieve license data from WordPress database
	 *
	 * @param string $key
	 *
	 * @return \stdClass
	 */
	public function retrieve( $key ) {
		global $wpdb;

		$data = new \stdClass();

		$row = $wpdb->get_row( $wpdb->prepare( "
		SELECT * FROM {$wpdb->lwp_licenses}
		WHERE license_key = %s
	", $key ) );
		
		// set data if row found
		if ( null !== $row ) {
			$data->key              = $row->license_key;
			$data->order_id         = $row->order_id;
			$data->user_id          = $row->user_id;
			$data->activation_email = $row->activation_email;
			$data->product_id       = $row->product_id;
			$data->activation_limit = $row->activation_limit;
			$data->date_created     = new \DateTime( $row->date_created );
			$data->date_expires     = new \DateTime( $row->date_expires );
		}

		return $data;
	}

	/**
	 * Persist license data in WordPress database
	 *
	 * @param License $license
	 *
	 * @return License
	 */
	public function persist( $license ) {
		global $wpdb;

		// dem defaults
		$defaults = array(
			'order_id'         => '',
			'activation_email' => '',
			'user_id'          => '',
			'license_key'      => '',
			'product_id'       => '',
			'activation_limit' => '',
			'date_expires'     => '',
			'date_created'     => ''
		);

		// setup array with data
		$data = wp_parse_args( array(
			'license_key'      => $license->get_key(),
			'order_id'         => $license->get_order_id(),
			'user_id'          => $license->get_user_id(),
			'activation_email' => $license->get_activation_email(),
			'product_id'       => $license->get_product_id(),
			'activation_limit' => $license->get_activation_limit(),
			'date_created'     => $license->get_date_created()->format( 'Y-m-d' ),
			'date_expires'     => $license->get_date_expires()->format( 'Y-m-d' )
		), $defaults );

		// check if new license or existing
		if ( '' == $license->get_key() ) { // insert

			// generate new license
			$license->set_key( license_wp()->service( 'license_manager' )->generate_license_key() );

			// set key in data
			$data['license_key'] = $license->get_key();

			// insert into WordPress database
			$wpdb->insert( $wpdb->lwp_licenses, $data );
		} else { // update

			// unset license from data
			unset( $data['license_key'] );

			// update database
			$wpdb->update( $wpdb->lwp_licenses,
				$data,
				array(
					'license_key' => $license->get_key()
				)
			);

		}

		return $license;
	}

}