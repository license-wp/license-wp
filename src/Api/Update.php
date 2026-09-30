<?php

namespace Never5\LicenseWP\Api;

use Never5\LicenseWP\WooCommerce;

class Update {

	/**
	 * Setup API Activation endpoint
	 */
	public function setup() {
		add_action( 'woocommerce_api_wp_plugin_licencing_update_api', array( $this, 'handle' ) );
		add_action( 'woocommerce_api_license_wp_api_update', array( $this, 'handle' ) );
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

		// set request, from the query string or a POST body
		$request = array_map( 'sanitize_text_field', apply_filters( 'license_wp_api_update_request', wp_unslash( array_merge( $_GET, $_POST ) ) ) );
		$request = wp_parse_args( $request, array( 'request' => '', 'license_key' => '', 'api_product_id' => '', 'instance' => '', 'plugin_name' => '', 'version' => '', 'wp_version' => '', 'php_version' => '' ) );

		// check for required things
		try {

			$purchase_url = get_permalink( wc_get_page_id( 'shop' ) );

			// check for request var
			if ( ! isset( $request['request'] ) || empty( $request['request'] ) ) {
				throw new UpdateException( __( '<strong>Update error:</strong> No API Request set.', 'license-wp' ), 'invalid_request' );
			}

			// check for license var
			if ( ! isset( $request['license_key'] ) || empty( $request['license_key'] ) ) {
				throw new UpdateException( sprintf( __( '<strong>Update error:</strong> No license key set. <a href="%s" target="_blank">Purchase a valid license</a> to receive updates and support.', 'license-wp' ), $purchase_url ), 'no_key' );
			}

			// check for api product ID var
			if ( ! isset( $request['api_product_id'] ) || empty( $request['api_product_id'] ) ) {
				throw new UpdateException( __( '<strong>Update error:</strong> No API Product ID set.', 'license-wp' ), 'no_api_product_id' );
			}

			// get license
			/** @var \Never5\LicenseWP\License\License $license */
			$license = license_wp()->service( 'license_factory' )->make( $request['license_key'] );

			// check if license exists
			if ( '' == $license->get_key() ) {
				throw new UpdateException( sprintf( __( '<strong>Update error:</strong> The provided license is invalid. <a href="%s" target="_blank">Purchase a valid license</a> to receive updates and support.', 'license-wp' ), $purchase_url ), 'invalid_key' );
			}

			// check if license is linked to order and if so, if the order is not refunded
			if ( ! $license->has_valid_order_status() ) {
				throw new UpdateException( sprintf( __( '<strong>Update error:</strong> The order used to purchase this license has an invalid status. <a href="%s" target="_blank">Purchase a valid license</a> to receive updates and support.', 'license-wp' ), $purchase_url ), 'invalid_order_status' );
			}

			// get api product by given api product id (slug)
			$api_product = $license->get_api_product_by_slug( $request['api_product_id'] );

			// check if license grants access to request api product
			if ( null === $api_product ) {
				throw new UpdateException( sprintf( __( '<strong>Update error:</strong> This license does not allow access to the requested product. <a href="%s" target="_blank">Purchase a valid license</a> to receive updates and support.', 'license-wp' ), $purchase_url), 'no_api_product_access' );
			}

			// check if license expired
			if ( $license->is_expired() ) {
				throw new UpdateException( sprintf( __( '<strong>Update error:</strong> License of <strong>%s</strong> has expired. You must <a href="%s" target="_blank">renew your license</a> if you want to use it again.', 'license-wp' ), $api_product->get_name(), $license->get_renewal_url() ), 'expired_key' );
			}

			// get activations
			$activations = $license->get_activations( $api_product );

			// store if activation is found
			$is_activated = false;

			// check if instance is activated
			if ( count( $activations ) > 0 ) {
				/** @var \Never5\LicenseWP\Activation\Activation $activation */
				foreach ( $activations as $activation ) {
					if ( $activation->get_instance() === $activation->format_instance( $request['instance'] ) ) {
						$is_activated = true;
						break;
					}
				}
			}

			// throw exception if given instance is not activated
			if ( false === $is_activated ) {
				throw new UpdateException( sprintf( __( '<strong>Update error:</strong> License of <strong>%s</strong> is not activated on this website. Manage your activations on your <a href="%s" target="_blank">My Account page</a>.', 'license-wp' ), $api_product->get_name(), get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ), 'no_activation' );
			}

			// do given request
			switch ( $request['request'] ) {
				case 'pluginupdatecheck' :
					$this->plugin_update_check( $license, $api_product, $request );
					break;
				case 'plugininformation' :
					$this->plugin_information( $license, $api_product, $request );
					break;
			}


		} catch ( UpdateException $e ) {

			$response = new \stdClass();

			switch ( $request['request'] ) {
				case 'pluginupdatecheck' :
					$response->slug        = '';
					$response->plugin      = '';
					$response->new_version = '';
					$response->url         = '';
					$response->package     = '';
					break;
				case 'plugininformation' :
					$response->name          = '';
					$response->slug          = '';
					$response->plugin        = '';
					$response->version       = '';
					$response->last_updated  = '';
					$response->download_link = '';
					$response->author        = '';
					$response->requires      = '';
					$response->tested        = '';
					$response->homepage      = '';
					$response->sections      = '';
					break;
			}

			$response->errors = $e->__toArray();
			$this->send_data( $response );
		}

	}

	/**
	 * WordPress update check
	 *
	 * @param \Never5\LicenseWP\License\License $license
	 * @param \Never5\LicenseWP\ApiProduct\ApiProduct $api_product
	 * @param array $request
	 *
	 * @throws ApiException
	 */
	private function plugin_update_check( $license, $api_product, $request ) {

		$release = $this->get_release( $api_product, $request );

		$data         = new \stdClass();
		$data->plugin = $request['plugin_name'];
		$data->slug   = $request['api_product_id'];
		$data->url    = $api_product->get_uri();

		if ( null === $release ) {
			// the installed version, so the client sees no update
			$data->new_version = $request['version'];
			$data->package     = '';
		} else {
			$data->new_version  = $release['version'];
			$data->package      = $api_product->get_download_url( $license, $release['legacy'] );
			$data->requires     = $release['requires'];
			$data->requires_php = $release['requires_php'];
			$data->tested       = $release['tested'];
		}

		// send data
		$this->send_data( $data );

	}

	/**
	 * WordPress update check
	 *
	 * @param \Never5\LicenseWP\License\License $license
	 * @param \Never5\LicenseWP\ApiProduct\ApiProduct $api_product
	 * @param array $request
	 *
	 * @throws ApiException
	 */
	private function plugin_information( $license, $api_product, $request ) {

		// a site that can run no release sees the current one, without a download
		$release = $this->get_release( $api_product, $request );
		$shown   = ( null === $release ) ? $api_product->get_release() : $release;

		// transient name
		$transient_name = 'plugininfo_' . md5( $request['api_product_id'] . $shown['version'] );

		// check if transient exists
		if ( false === ( $data = get_transient( $transient_name ) ) ) {

			// set data properties
			$data               = new \stdClass();
			$data->name         = $api_product->get_name();
			$data->plugin       = $request['plugin_name'];
			$data->slug         = $request['api_product_id'];
			$data->version      = $shown['version'];
			$data->last_updated = $api_product->get_date();

			// set author
			if ( '' != $api_product->get_author_uri() ) {
				$data->author = '<a href="' . esc_url( $api_product->get_author_uri() ) . '">' . esc_html( $api_product->get_author() ) . '</a>';
			} else {
				$data->author = esc_html( $api_product->get_author() );
			}

			// set properties
			$data->requires     = $shown['requires'];
			$data->requires_php = $shown['requires_php'];
			$data->tested       = $shown['tested'];
			$data->homepage     = $api_product->get_uri();

			// set sections
			$data->sections = array(
				'description' => wpautop( \Parsedown::instance()->text( $api_product->get_description() ) ),
				'changelog'   => \Parsedown::instance()->text( $api_product->get_changelog() )
			);

			set_transient( $transient_name, $data, DAY_IN_SECONDS );
		}

		// download link
		$data->download_link = ( null === $release ) ? '' : $api_product->get_download_url( $license, $release['legacy'] );

		// send data
		$this->send_data( $data );
	}

	/**
	 * The release of the API product the requesting site can run
	 *
	 * The WordPress version comes from the wp_version parameter, else from the User-Agent WordPress sends
	 * ("WordPress/6.5.2; https://example.com"). The PHP version only comes from the php_version parameter.
	 *
	 * @param \Never5\LicenseWP\ApiProduct\ApiProduct $api_product
	 * @param array $request
	 *
	 * @return array|null See ApiProduct::get_release().
	 */
	private function get_release( $api_product, $request ) {
		$wp_version = $request['wp_version'];
		if ( '' === $wp_version && isset( $_SERVER['HTTP_USER_AGENT'] ) && preg_match( '#^WordPress/(\d+(?:\.\d+)*)#', wp_unslash( $_SERVER['HTTP_USER_AGENT'] ), $matches ) ) {
			$wp_version = $matches[1];
		}

		$wp_version  = preg_match( '/^\d+(\.\d+)*/', $wp_version, $matches ) ? $matches[0] : '';
		$php_version = preg_match( '/^\d+(\.\d+)*/', $request['php_version'], $matches ) ? $matches[0] : '';

		return $api_product->get_release( $wp_version, $php_version );
	}

	/**
	 * Send API response back to client.
	 *
	 * @param array|object $data
	 */
	private function send_data( $data ) {
		if ( isset( $_SERVER['HTTP_ACCEPT'] ) && 'application/json' === $_SERVER['HTTP_ACCEPT'] ) {
			wp_send_json( $data );
			exit;
		}

		header( 'Content-type: text/plain' );
		echo serialize( $data );
		exit;
	}
}
