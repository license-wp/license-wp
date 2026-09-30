<?php
/*
    Plugin Name: License WP - WordPress Premium Licensing for WooCommerce
    Plugin URI: https://wordpress.org/plugins/license-wp/
    Description: A simple solution to plugin licencing. Define API Products separately, then sell licenses as products in WooCommerce which grant access to api products.
    Version: 1.0.0
    Author: Mike Jolley & Barry Kooij
    Author URI: http://www.mikeandbarry.com
    License: GPL v2

	Copyright 2015 - Never5

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}

// autoloader
require __DIR__ . '/vendor/autoload.php';

/**
 * @return \Never5\LicenseWP\Plugin
 */
function license_wp() {

	static $instance;
	if ( is_null( $instance ) ) {
		$instance = new \Never5\LicenseWP\Plugin( '1.0.0', __FILE__ );
	}

	return $instance;

}

function __load_license_wp() {
	// fetch instance
	license_wp();
}

// check PHP version
$updatePhp = new WPUpdatePhp( '5.3.0' );
if ( $updatePhp->does_it_meet_required_php_version( PHP_VERSION ) ) {

	// create plugin object
	add_action( 'plugins_loaded', '__load_license_wp', 20 );

	// Activation hook
	register_activation_hook( __FILE__, array( 'Never5\\LicenseWP\\Installer', 'install' ) );

	// Deactivation hook
	register_deactivation_hook( __FILE__, array( 'Never5\\LicenseWP\\Installer', 'uninstall' ) );
}
