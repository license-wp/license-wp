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

}