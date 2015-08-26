<?php
namespace Never5\LicenseWP\Shortcode;

use \Never5\LicenseWP\Email;

class LostLicenseForm {

	/**
	 * __constructor
	 */
	public function __construct() {
		add_shortcode( 'lost_license_key_form', array( $this, 'callback' ) );
	}

	/**
	 * Callback
	 */
	public function callback() {

		// process the post
		if ( ! empty( $_POST['submit_lost_license_form'] ) ) {
			$this->process_post();
		}

		// load view
		return $this->view();
	}

	/**
	 * Process the post
	 */
	private function process_post() {

		// sanitize text field
		$activation_email = sanitize_text_field( $_POST['activation_email'] );

		// check email address
		if ( ! is_email( $activation_email ) ) {
			wc_add_notice( __( 'Invalid email address.', 'license-wp' ), 'error' );

			return;
		}

		// get license by email address
		$licenses = license_wp()->service( 'license_manager' )->get_licenses_by_email( $activation_email );

		// loop
		foreach ( $licenses as $license_key => $license ) {

			// unset when license has expired
			if ( $license->is_expired() ) {
				unset( $licenses[ $license_key ] );
			}

		}

		// check if we found licenses
		if ( count( $licenses ) > 0 ) {

			// get user email address
			$user = get_user_by( 'email', $activation_email );

			// try to get a first name
			if ( ! empty( $user ) && ! empty( $user->first_name ) ) {
				$user_first_name = $user->first_name;
			} else {
				$user_first_name = false;
			}

			// send email to activation email
			$sent = license_wp()->service( 'email_manager' )->send( new Email\LostLicense(
				$licenses,
				$user_first_name
			), $activation_email );

			// correct notice
			if ( $sent ) {
				wc_add_notice( sprintf( __( 'Your licenses have been emailed to %s.', 'license-wp' ), $activation_email ), 'success' );
			} else {
				wc_add_notice( __( 'Your licenses could not be sent. Please contact us for support.', 'license-wp' ), 'error' );
			}

		} else {
			wc_add_notice( __( 'No active licenses found.', 'license-wp' ), 'error' );
		}

	}

	/**
	 * Shortcode view
	 *
	 * @return string
	 */
	private function view() {
		ob_start();

		// load template file via WooCommerce template function
		wc_get_template( 'lost-license-form.php', array(), 'license-wp', license_wp()->service( 'file' )->plugin_path() . '/templates/' );

		return ob_get_clean();
	}

}