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
	 * Output page content
	 *
	 * @return void
	 */
	public function output() {
		echo 'dat overview';
	}

}