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
		wp_enqueue_script( 'chosen' ); // @todo this should be replaced with select2
		wp_enqueue_script( 'woocommerce_admin' );
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