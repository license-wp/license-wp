<?php
namespace Never5\LicenseWP\License;

class Cron {

	/**
	 * Schedule License cron
	 */
	public function schedule() {
		if ( ! wp_next_scheduled( 'license_wp_license_expiring_email' ) ) {
			// schedule
			wp_schedule_event( ( time() + 60 ), 'daily', 'license_wp_license_expiring_email' );
		}
	}

	/**
	 * Unschedule License cron
	 */
	public function unschedule() {
		$timestamp = wp_next_scheduled( 'license_wp_license_expiring_email' );

		// unschedule
		if ( false !== $timestamp ) {
			wp_unschedule_event( $timestamp, 'license_wp_license_expiring_email' );
		}
	}

}