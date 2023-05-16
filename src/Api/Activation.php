<?php

namespace Never5\LicenseWP\Api;

/**
 * Class Activation
 *
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

		// Log to file also.
		global $wp_filesystem;
		require_once( ABSPATH . '/wp-admin/includes/file.php' );
		WP_Filesystem();
		$txt      = urldecode( http_build_query( $_REQUEST, '', ', ' ) );
		$txt      = '[' . date( 'Y-m-d H:i:s' ) . '] - ' . $txt;
		$old_text = $wp_filesystem->get_contents( License_WP_Dir . '/log_file.txt' );
		$text     = $old_text ? $old_text . "\n" . $txt : $txt;
		// Need double quotes around the \n to make it work.
		$wp_filesystem->put_contents( License_WP_Dir . '/log_file.txt', $text );

		// print_r($request);

		try {

			$purchase_url = get_permalink( wc_get_page_id( 'shop' ) );

			// check for request var.
			if ( ! isset( $request['request'] ) || empty( $request['request'] ) ) {
				throw new ApiException( __( 'Invalid API Request.', 'license-wp' ), 100 );
			}
			// check for license var.
			if ( ! isset( $request['license_key'] ) || empty( $request['license_key'] ) ) {
				if ( empty( $request['license_key'] ) ) {
					$set_api = isset( $request['api_product_id'] );
					if ( $set_api && false !== strpos( $request['api_product_id'], ',' ) ) {
						$installed_extensions = explode( ',', $request['api_product_id'] );

						foreach ( $installed_extensions as $extension ) {
							$object                           = wc_get_product_id_by_sku( $request['api_product_id'] );
							$single_request                   = $request;
							$single_request['extension_id']   = isset( $object ) ? $object : 0;
							$single_request['license_id']     = '-1';
							$single_request['api_product_id'] = $extension;
							$single_request['site']           = $request['instance'];
							$this->log_api_call( $single_request );
						}
					} else {
						$args                 = array(
							'action' => $request['request'] . $request['action_trigger'],
							'site'   => $request['instance'],
						);
						$args['license_id']   = '-1';
						$object               = $set_api ? wc_get_product_id_by_sku( $request['api_product_id'] ) : null;
						$args['extension_id'] = isset( $object ) ? $object : 0;
						$this->log_api_call( $args );
					}
				}
				throw new ApiException( __( '<strong>Activation error:</strong> The provided license is invalid.', 'license-wp' ), 101 );
			}

			// check for api product ID var
			if ( ! isset( $request['api_product_id'] ) || empty( $request['api_product_id'] ) ) {
				throw new ApiException( __( '<strong>Activation error:</strong> Invalid API Product ID.', 'license-wp' ), 102 );
			}

			// get license
			/** @var \Never5\LicenseWP\License\License $license */
			$license = license_wp()->service( 'license_factory' )->make( $request['license_key'] );

			// check if license exists
			if ( '' == $license->get_key() ) {
				if ( false !== strpos( $request['api_product_id'], ',' ) ) {
					$installed_extensions = explode( ',', $request['api_product_id'] );
					foreach ( $installed_extensions as $extension ) {
						$single_request                 = array(
							'license_id' => 0,
							'action'     => $request['request'] . $request['action_trigger'],
							'site'       => $request['instance'],
						);
						$object                         = wc_get_product_id_by_sku( $request['api_product_id'] );
						$single_request['extension_id'] = isset( $object ) ? $object : 0;
						$this->log_api_call( $single_request );
					}
				} else {
					$args                 = array(
						'license_id' => 0,
						'action'     => $request['request'] . $request['action_trigger'],
						'site'       => $request['instance'],
					);
					$object               = wc_get_product_id_by_sku( $request['api_product_id'] );
					$args['extension_id'] = isset( $object ) ? $object : 0;
					$this->log_api_call( $args );
				}

				throw new ApiException( sprintf( __( '<strong>Activation error:</strong> The provided license is invalid. <a href="%s" target="_blank">Purchase a valid license</a> to receive updates and support.', 'license-wp' ), $purchase_url ), 101 );
			}

			// check if license expired
			if ( $license->is_expired() ) {
				if ( false !== strpos( $request['api_product_id'], ',' ) ) {
					$installed_extensions = explode( ',', $request['api_product_id'] );
					foreach ( $installed_extensions as $extension ) {
						$single_request                 = array(
							'license_id' => 0,
							'action'     => $request['request'] . $request['action_trigger'],
							'site'       => $request['instance'],
						);
						$object               = wc_get_product_id_by_sku( $request['api_product_id'] );
						$single_request['extension_id'] = isset( $object ) ? $object : 0;
						$this->log_api_call( $single_request );
					}
				} else {
					$args                 = array(
						'license_id' => 0,
						'action'     => $request['request'] . $request['action_trigger'],
						'site'       => $request['instance'],
					);
					$object               = wc_get_product_id_by_sku( $request['api_product_id'] );
					$args['extension_id'] = isset( $object ) ? $object : 0;
					$this->log_api_call( $args );
				}

				throw new ApiException( sprintf( __( '<strong>Activation error:</strong> Your license has expired. You must <a href="%s" target="_blank">renew your license</a> if you want to use it again.', 'license-wp' ), $license->get_renewal_url() ), 110 ); // @todo add renew link
			}
			// check if license is linked to order and if so, if the order is not refunded
			if ( ! $license->has_valid_order_status() ) {
				if ( false !== strpos( $request['api_product_id'], ',' ) ) {
					$installed_extensions = explode( ',', $request['api_product_id'] );
					foreach ( $installed_extensions as $extension ) {
						$args                 = array(
							'license_id' => 0,
							'action'     => $request['request'] . $request['action_trigger'],
							'site'       => $request['instance'],
						);
						$object               = wc_get_product_id_by_sku( $request['api_product_id'] );
						$args['extension_id'] = isset( $object ) ? $object : 0;
						$this->log_api_call( $args );
					}
				} else {
					$single_request                 = array(
						'license_id' => 0,
						'action'     => $request['request'] . $request['action_trigger'],
						'site'       => $request['instance'],
					);
					$object               = wc_get_product_id_by_sku( $request['api_product_id'] );
					$single_request['extension_id'] = isset( $object ) ? $object : 0;
					$this->log_api_call( $single_request );
				}
				throw new ApiException( sprintf( __( '<strong>Update error:</strong> The order used to purchase this license has an invalid status. <a href="%s" target="_blank">Purchase a valid license</a> to receive updates and support.', 'license-wp' ), $purchase_url ), 111 );
			}

			// If comma is in string means it is a multi extension license.
			if ( false !== strpos( $request['api_product_id'], ',' ) ) {
				$extensions           = $license->get_api_products();
				$available_extensions = array();
				$licensed_extensions  = array();
				foreach ( $extensions as $extension ) {
					$available_extensions[] = $extension->get_slug();
				}
				$installed_extensions = explode( ',', $request['api_product_id'] );

				foreach ( $installed_extensions as $extension ) {
					$api_product                      = $license->get_api_product_by_slug( $extension );
					$single_request                   = $request;
					$single_request['api_product_id'] = $extension;

					if ( ! empty( $api_product ) && in_array( $extension, $available_extensions ) ) {
						$licensed_extensions[$api_product->get_slug()] = $api_product->get_name();

						switch ( $single_request['request'] ) {
							case 'activate':
								// we do the email check here because email var is not passed for deactivations.

								$email_err_message = __( '<strong>Activation error:</strong> The email provided (%1$s) is invalid. Please enter the correct email address or <a href="%2$s" target="_blank">purchase a valid license</a> to receive updates and support.', 'license-wp' );

								// check for email var.
								if ( ! isset( $request['email'] ) || empty( $request['email'] ) ) {
									throw new ApiException( sprintf( $email_err_message, $request['email'], $purchase_url ), 103 );
								}

								// check if activation email is correct.
								if ( ! is_email( $request['email'] ) || $request['email'] != $license->get_activation_email() ) {
									throw new ApiException( sprintf( $email_err_message, $request['email'], $purchase_url ), 103 );
								}

								// activate the license.
								$this->activate( $license, $api_product, $single_request, false );
								break;
							case 'deactivate':
								$this->deactivate( $license, $api_product, $single_request, false );
								break;
							default:
								throw new ApiException( __( 'Invalid API Request.', 'license-wp' ), 100 );
								break;
						}
						$args = array(
							'license_id'   => $license->get_product_id(),
							'extension_id' => $api_product->get_id(),
							'action'       => $single_request['request'] . $single_request['action_trigger'],
							'site'         => $single_request['instance'],
						);
						$this->log_api_call( $args );
					} else {
						$args = array(
							'license_id'   => $license->get_product_id(),
							'extension_id' => $api_product->get_id(),
							'action'       => 'failed attempt' . $single_request['action_trigger'],
							'site'         => $single_request['instance'],
						);
						$this->log_api_call( $args );
					}
				}
				wp_send_json( $licensed_extensions );
			} else {
				// get api product by given api product id (slug)
				$api_product = $license->get_api_product_by_slug( $request['api_product_id'] );

				// check if license grants access to request api product
				if ( null === $api_product ) {
					throw new ApiException( sprintf( __( '<strong>Activation error:</strong> This license does not allow access to the requested product. <a href="%s" target="_blank">Purchase a valid license</a> to receive updates and support.', 'license-wp' ), $purchase_url ), 104 );
				}
				$args = array(
					'license_id'   => $license->get_product_id(),
					'extension_id' => $api_product->get_id(),
					'action'       => $request['request'] . $request['action_trigger'],
					'site'         => $request['instance'],
				);
				$this->log_api_call( $args );
				switch ( $request['request'] ) {
					case 'activate':
						// we do the email check here because email var is not passed for deactivations

						$email_err_message = __( '<strong>Activation error:</strong> The email provided (%1$s) is invalid. Please enter the correct email address or <a href="%2$s" target="_blank">purchase a valid license</a> to receive updates and support.', 'license-wp' );

						// check for email var
						if ( ! isset( $request['email'] ) || empty( $request['email'] ) ) {
							throw new ApiException( sprintf( $email_err_message, $request['email'], $purchase_url ), 103 );
						}

						// check if activation email is correct
						if ( ! is_email( $request['email'] ) || $request['email'] != $license->get_activation_email() ) {
							throw new ApiException( sprintf( $email_err_message, $request['email'], $purchase_url ), 103 );
						}

						// activate the license
						$this->activate( $license, $api_product, $request );
						break;
					case 'deactivate':
						$this->deactivate( $license, $api_product, $request );
						break;
					default:
						throw new ApiException( __( 'Invalid API Request.', 'license-wp' ), 100 );
						break;
				}
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
	 * Activate an instance of a license
	 *
	 * @param \Never5\LicenseWP\License\License       $license
	 * @param \Never5\LicenseWP\ApiProduct\ApiProduct $api_product
	 * @param array                                   $request
	 *
	 * @throws ApiException
	 */
	private function activate( $license, $api_product, $request, $end_activation = true ) {

		// Format the instance
		$request['instance'] = str_replace( array( 'http://', 'https://' ), '', trim( $request['instance'] ) );

		// get all activation, including deactivated activations
		$existing_activations = license_wp()->service( 'activation_manager' )->get_activations( $license, $api_product, false );

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
		if ( $license->get_activation_limit() > 0 && count( $license->get_activations( $api_product ) ) >= $license->get_activation_limit() && ! in_array( $request['instance'], $existing_active_activation_instances ) ) {
			throw new ApiException( sprintf( __( '<strong>Activation error:</strong> Activation limit reached. Please deactivate another website or upgrade your license at your <a href="%s" target="_blank">My Account page</a>.', 'license-wp' ), get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ), 105 );
		}

		// the activation
		$activation = null;

		// check if request instance already exists in an activation
		if ( count( $existing_activations ) > 0 ) {
			foreach ( $existing_activations as $existing_activation ) {

				// check if request instance equals activation instance
				if ( $request['instance'] === $existing_activation->get_instance() ) {
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
			throw new ApiException( __( '<strong>Activation error:</strong> Could not activate license key. Please contact support.', 'license-wp' ), 107 );
		}

		// calculate activations left
		$activations_left = ( ( $license->get_activation_limit() > 0 ) ? $license->get_activation_limit() - count( $license->get_activations( $api_product ) ) : - 1 );

		// response
		$response = apply_filters(
			'license_wp_api_activation_response',
			array(
				'success'   => true,
				'activated' => true,
				'remaining' => $activations_left,
			)
		);

		if ( $end_activation ) {
			// send JSON the WP way
			wp_send_json( $response );
			exit;
		} else {
			return;
		}
	}

	/**
	 * Deactivates an instance of a license
	 *
	 * @param \Never5\LicenseWP\License\License       $license
	 * @param \Never5\LicenseWP\ApiProduct\ApiProduct $api_product
	 * @param array                                   $request
	 *
	 * @throws ApiException
	 */
	private function deactivate( $license, $api_product, $request, $end_activation = true ) {

		// get activations
		$activations = $license->get_activations( $api_product );

		// check & loop
		if ( count( $activations ) > 0 ) {

			/** @var \Never5\LicenseWP\Activation\Activation $activation */
			foreach ( $activations as $activation ) {

				// check if given instance equals activation instance
				if ( $activation->format_instance( $request['instance'] ) === $activation->get_instance() ) {

					// set activation to not active
					$activation->set_activation_active( 0 );

					// set activation date to now
					$activation->set_activation_date( new \DateTime() );

					// persist activation
					$activation = license_wp()->service( 'activation_repository' )->persist( $activation );

					// check if deactivation was successful
					if ( $activation->is_active() ) {
						throw new ApiException( __( 'Deactivation error: Could not deactivate license key. Please contact support.', 'license-wp' ), 108 );
					}

					// response
					$response = apply_filters(
						'license_wp_api_activation_response',
						array(
							'success' => true,
						)
					);
					if ( $end_activation ) {
						// send JSON the WP way
						wp_send_json( $response );
						exit;
					}
				}
			}
			return;
		}

		//throw new ApiException( __( 'Deactivation error: instance not found.', 'license-wp' ), 109 );

	}

	/**
	 * Add API call to log.
	 *
	 * @param array $args Arguments.
	 *
	 * @return void
	 */
	private function log_api_call( $args ) {
		global $wpdb;

		$response = $wpdb->insert(
			$wpdb->dlm_api_log,
			array(
				'license_id'   => $args['license_id'],
				'extension_id' => $args['extension_id'],
				'action'       => $args['action'],
				'site'         => $args['site'],
				'date'         => current_time( 'mysql', 0 ),
			)
		);
	}
}
