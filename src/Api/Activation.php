<?php

namespace Never5\LicenseWP\Api;

/**
 * Class Activation
 * @package Never5\LicenseWP\Api
 */
class Activation {

	/**
	 * Setup API Activation endpoint
	 */
	public function setup() {
		add_action( 'woocommerce_api_wp_plugin_licencing_activation_api', array( $this, 'handle' ) );
		add_action( 'woocommerce_api_license_wp_api_activation', array( $this, 'handle' ) );
	}

	/**
	 * Handle API request
	 */
	public function handle() {
		global $wpdb;

		// hide DB errors
		$wpdb->hide_errors();

		// send no-cache header
		nocache_headers();

		// set request
		$request = array_map( 'sanitize_text_field', apply_filters( 'license_wp_api_activation_request', $_GET ) );

		try {

			// check for request var
			if ( ! isset( $request['request'] ) || empty( $request['request'] ) ) {
				throw new ApiException( __( 'Invalid API Request.', 'license-wp' ), 100 );
			}

			// check for license var
			if ( ! isset( $request['license_key'] ) || empty( $request['license_key'] ) ) {
				throw new ApiException( __( 'Activation error: The provided licence is invalid.', 'license-wp' ), 101 );
			}

			// check for api product ID var
			if ( ! isset( $request['api_product_id'] ) || empty( $request['api_product_id'] ) ) {
				throw new ApiException( __( 'Activation error: Invalid API Product ID.', 'license-wp' ), 102 );
			}

			// get license
			/** @var \Never5\LicenseWP\License\License $license */
			$license = license_wp()->service( 'license_factory' )->make( $request['license_key'] );

			// check if license exists
			if ( empty( $license->get_key() ) ) {
				throw new ApiException( __( 'Activation error: The provided licence is invalid.', 'license-wp' ), 101 );
			}

			// check if license expired
			if ( $license->is_expired() ) {
				throw new ApiException( __( 'Activation error: The provided licence has expired.', 'license-wp' ), 110 ); // @todo add renew link
			}

			// get api products linked to license
			$api_products = $license->get_api_products();

			// store api product ids in array
			$api_products_ids = array();
			if ( count( $api_products ) > 0 ) {
				foreach ( $api_products as $api_product ) {
					$api_products_ids[] = $api_product->get_slug();
				}
			}

			// check if license grants access to request api product
			if ( ! in_array( $request['api_product_id'], $api_products_ids ) ) {
				throw new ApiException( sprintf( __( 'This license does not allow access to the requested product.', 'license-wp' ), $request['email'] ), 104 );
			}

			switch ( $request['request'] ) {
				case 'activate' :

					// we do the email check here because email var is not passed for deactivations

					// check for email var
					if ( ! isset( $request['email'] ) || empty( $request['email'] ) ) {
						throw new ApiException( sprintf( __( 'Activation error: The email provided (%s) is invalid.', 'license-wp' ), $request['email'] ), 103 );
					}

					// check if activation email is correct
					if ( ! is_email( $request['email'] ) || $request['email'] != $license->get_activation_email() ) {
						throw new ApiException( sprintf( __( 'Activation error: The email provided (%s) is invalid.', 'license-wp' ), $request['email'] ), 103 );
					}

					// activate the license
					$this->activate( $license, $request );
					break;
				case 'deactivate' :
					$this->deactivate();
					break;
				default :
					throw new ApiException( __( 'Invalid API Request.', 'license-wp' ), 100 );
					break;
			}


		} catch ( ApiException $e ) {
			header( 'Content-Type: application/json' );
			echo $e->__toString();
			exit;
		}

		// bye
		exit;

	}

	/**
	 * Activate an instance to a license
	 *
	 * @param \Never5\LicenseWP\License\License $license
	 * @param array $request
	 *
	 * @throws ApiException
	 */
	private function activate( $license, $request ) {

		// get all activation, including deactivated activations
		$existing_activations = license_wp()->service( 'activation_manager' )->get_activations( $license, false );

		// existing active activation instances
		$existing_active_activation_instances = array();

		// check & loop
		if ( count( $existing_activations ) > 0 ) {
			foreach ( $existing_activations as $existing_activation ) {

				// check if activation is active
				if ( $existing_activation->is_active() ) {

					// add instance to array
					$existing_active_activation_instances[] = $existing_activation->get_instance();

				}

			}
		}

		// check if activation limit is reached and the requested instance isn't already activated
		if ( count( $license->get_activations() ) >= $license->get_activation_limit() && ! in_array( $request['instance'], $existing_active_activation_instances ) ) {
			throw new ApiException( sprintf( __( 'Activation error: Activation limit reached. Please deactivate an install first at your My Account page: %s.', 'license-wp' ), get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ), 105 );
		}

		// the activation
		$activation = null;

		// check if request instance already exists in an activation
		if ( count( $existing_activations ) > 0 ) {
			foreach ( $existing_activations as $existing_activation ) {

				// check if request instance equals activation instance
				if ( $request['instance'] == $existing_activation->get_instance() ) {
					$activation = $existing_activation;
					break;
				}

			}
		}

		// check if we got an activation for requested instance
		if ( null === $activation ) {

			// make new activation
			/** @var \Never5\LicenseWP\Activation\Activation $activation */
			$activation = license_wp()->service( 'activation_factory' )->make();

			// set props
			$activation->set_license_key( $license->get_key() );
			$activation->set_api_product_id( $request['api_product_id'] );
			$activation->set_instance( $request['instance'] );
			$activation->set_activation_date( new \DateTime() );
			$activation->set_activation_active( 1 );

		} else {
			$activation->set_activation_date( new \DateTime() );
			$activation->set_activation_active( 1 );
		}


		// persist activation
		$activation = license_wp()->service( 'activation_repository' )->persist( $activation );

		// check if activation was saved
		if ( $activation->get_id() == 0 ) {
			throw new ApiException( __( 'Activation error: Could not activate license key. Please contact support.', 'license-wp' ), 107 );
		}

		// calculate activations left
		error_log(count( $license->get_activations() ), 0);
		$activations_left = ( ( $license->get_activation_limit() > 0 ) ? $license->get_activation_limit() - count( $license->get_activations() ) : - 1 );

		// response
		$response = apply_filters( 'license_wp_api_activation_response', array(
			'activated' => true,
			'remaining' => $activations_left
		) );

		// send JSON the WP way
		wp_send_json( $response );
		exit;

	}

}