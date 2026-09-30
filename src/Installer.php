<?php

namespace Never5\LicenseWP;

use Never5\LicenseWP\License;

class Installer {

	/** @var string Version of the table definitions below */
	const DB_VERSION = '1.1.0';

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
	 * Update the tables when their definitions changed since the last install
	 */
	public static function maybe_upgrade() {
		if ( version_compare( get_option( 'license_wp_db_version', '1.0.0' ), self::DB_VERSION, '<' ) ) {
			self::db();
		}
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

		$charset_collate = $wpdb->get_charset_collate();

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
PRIMARY KEY  (license_key),
KEY order_id (order_id),
KEY user_id (user_id),
KEY activation_email (activation_email(191)),
KEY date_expires (date_expires)
) $charset_collate;
CREATE TABLE " . $wpdb->prefix . "license_wp_activations (
activation_id bigint(20) NOT NULL auto_increment,
license_key varchar(200) NOT NULL,
api_product_id varchar(200) NOT NULL,
instance varchar(200) NOT NULL,
activation_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
activation_active int(1) NOT NULL DEFAULT 1,
PRIMARY KEY  (activation_id),
KEY license_key (license_key(191))
) $charset_collate;
CREATE TABLE " . $wpdb->prefix . "license_wp_download_log (
log_id bigint(20) NOT NULL auto_increment,
date_downloaded datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
license_key varchar(200) NOT NULL,
activation_email varchar(200) NOT NULL,
api_product_id varchar(200) NOT NULL,
user_ip_address varchar(200) NOT NULL,
PRIMARY KEY  (log_id),
KEY license_key (license_key(191))
) $charset_collate;
		";

		dbDelta( $sql );

		update_option( 'license_wp_db_version', self::DB_VERSION );
	}

}