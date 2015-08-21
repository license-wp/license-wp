<?php

namespace Never5\LicenseWP\Admin\Page;

/**
 * Class Licenses
 * @package Never5\LicenseWP\Admin\Pages
 */
class Licenses extends Page {

	/**
	 * __construct
	 */
	public function __construct() {
		parent::__construct( __( 'Licenses', 'license-wp' ), '55.8' );
	}

	/**
	 * Output page content
	 *
	 * @return void
	 */
	public function output() {
		echo 'licenses';
	}

}