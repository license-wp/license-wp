<?php

namespace Never5\LicenseWP;

use Never5\LicenseWP\License;

class Installer {

	/**
	 * Install plugin
	 */
	public static function install() {
		self::db();

		// set renewal email cron
		$cron = new License\Cron();
		$cron->schedule();
	}

	/**
	 * Uninstall plugin
	 */
	public static function uninstall() {
		// unset renewal email cron
		$cron = new License\Cron();
		$cron->unschedule();
	}

	/**
	 * Create database tables
	 */
	private static function db() {
		global $wpdb;

		$wpdb->hide_errors();

		// needed for dbDelta
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "
CREATE TABLE " . $wpdb->prefix . "license_wp_licenses (
license_key varchar(200) NOT NULL,
order_id bigint(20) NOT NULL DEFAULT 0,
user_id bigint(20) NOT NULL DEFAULT 0,
activation_email varchar(200) NOT NULL,
product_id int(20) NOT NULL,
activation_limit int(20) NOT NULL DEFAULT 0,
date_created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
date_expires datetime NULL,
PRIMARY KEY  (license_key)
);
CREATE TABLE " . $wpdb->prefix . "license_wp_activations (
activation_id bigint(20) NOT NULL auto_increment,
license_key varchar(200) NOT NULL,
api_product_id varchar(200) NOT NULL,
instance varchar(200) NOT NULL,
activation_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
activation_active int(1) NOT NULL DEFAULT 1,
PRIMARY KEY  (activation_id)
);
CREATE TABLE " . $wpdb->prefix . "license_wp_download_log (
log_id bigint(20) NOT NULL auto_increment,
date_downloaded datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
license_key varchar(200) NOT NULL,
activation_email varchar(200) NOT NULL,
api_product_id varchar(200) NOT NULL,
user_ip_address varchar(200) NOT NULL,
PRIMARY KEY  (log_id)
);
		";

		dbDelta( $sql );
	}

}