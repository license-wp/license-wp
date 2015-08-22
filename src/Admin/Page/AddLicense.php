<?php

namespace Never5\LicenseWP\Admin\Page;

/**
 * Class AddLicenses
 * @package Never5\LicenseWP\Admin\Pages
 */
class AddLicense extends SubPage {

	/**
	 * __construct
	 */
	public function __construct() {
		parent::__construct( 'license_wp_licenses', __( 'Add License', 'license-wp' ) );
	}

	/**
	 * Method to enqueue page specific styles & scripts
	 */
	public function page_enqueue() {
		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'woocommerce_admin' );
		wp_enqueue_script(
			'lwp_add_license',
			license_wp()->service( 'file' )->plugin_url( '/assets/js/add-license' . ( ( ! SCRIPT_DEBUG ) ? '.min' : '' ) . '.js' ),
			array( 'jquery' ),
			license_wp()->get_version()
		);

	}

	/**
	 * Output page content
	 *
	 * @return void
	 */
	public function output() {
		wc_get_template( 'add-license-form.php', array(), 'license-wp', license_wp()->service( 'file' )->plugin_path() . '/assets/views/' );
	}

}