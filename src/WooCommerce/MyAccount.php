<?php

namespace Never5\LicenseWP\WooCommerce;

/**
 * Class MyAccount
 * @package Never5\LicenseWP\WooCommerce
 */
class MyAccount {

	/**
	 * Setup hooks
	 */
	public function setup() {
		add_action( 'woocommerce_before_my_account', array( $this, 'print_licenses' ) );
		add_action( 'init', array( $this, 'catch_deactivate_activation' ) );
	}

	/**
	 * Print licenses
	 */
	public function print_licenses() {

		// get licenses of current user
		$licenses = license_wp()->service( 'license_manager' )->get_licenses_by_user( get_current_user_id() );

		if ( count( $licenses ) > 0 ) {
			wc_get_template( 'my-licenses.php', array( 'licenses' => $licenses ), 'license-wp', license_wp()->service( 'file' )->plugin_path() . '/templates/' );
		}

	}

	/**
	 * Catch the deactivate activation request from My Account page
	 */
	public function catch_deactivate_activation() {
		if ( is_user_logged_in() && isset( $_GET['deactivate_license'] ) && isset( $_GET['license_key'] ) && isset( $_GET['activation_email'] ) ) {

			// clean vars
			$activation_id    = absint( $_GET['deactivate_license'] );
			$license_key      = sanitize_text_field( $_GET['license_key'] );
			$activation_email = sanitize_text_field( $_GET['activation_email'] );

			// get license
			/** @var \Never5\LicenseWP\License\License $license */
			$license = license_wp()->service( 'license_factory' )->make( $license_key );

			// check if license exists
			if ( '' == $license->get_key() ) {
				wp_die( __( 'Invalid or expired license key.', 'license-wp' ) );
			}

			// check if license expired
			if ( $license->is_expired() ) {
				wp_die( sprintf( __( 'License has expires. You can renew it here: %s', 'license-wp' ), $license->get_renewal_url() ) );
			}

			// check if this license is owned by logged in user
			if ( is_user_logged_in() && $license->get_user_id() != get_current_user_id() ) {
				wp_die( __( 'This license does not appear to be yours.', 'license-wp' ) );
			}

			// check if activation email is correct
			if ( ! is_email( $activation_email ) || $activation_email != $license->get_activation_email() ) {
				wp_die( __( 'Invalid activation email address.', 'license-wp' ) );
			}

			// get activation
			/** @var \Never5\LicenseWP\Activation\Activation $activation */
			$activation = license_wp()->service( 'activation_factory' )->make( $activation_id );

			// check if lincense key in activation equals given license key
			if ( $activation->get_license_key() != $license_key ) {
				wp_die( __( 'This appears to be not your activation.', 'license-wp' ) );
			}

			// deactivate activation
			if ( license_wp()->service( 'activation_manager' )->deactivate( $activation ) ) {
				wc_add_notice( __( 'Licence successfully deactivated.', 'license-wp' ), 'success' );
			}

		}
	}

}