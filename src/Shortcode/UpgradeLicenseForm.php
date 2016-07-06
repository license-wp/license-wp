<?php
namespace Never5\LicenseWP\Shortcode;

use Never5\LicenseWP;

class UpgradeLicenseForm {

	private $license_key = '';

	private $license = null;

	/**
	 * __constructor
	 */
	public function __construct() {

		// add shortcode
		add_shortcode( 'upgrade_license_key_form', array( $this, 'callback' ) );
	}

	/**
	 * Check if license key is set in URL, if so set in var
	 */
	private function set_license_key() {
		if ( ! empty( $_GET['license_key'] ) ) {
			$this->license_key = trim( $_GET['license_key'] );
		}

		if ( ! empty( $_POST['license_key'] ) ) {
			$this->license_key = trim( $_POST['license_key'] );
		}
	}

	/**
	 * Load license data based on set $this->license_key
	 */
	private function load_license() {

		// check
		if ( ! empty( $this->license_key ) ) {

			/** @var \Never5\LicenseWP\License\License $license */
			$license = license_wp()->service( 'license_factory' )->make( $this->license_key );

			// check if license is found
			if ( $this->license_key == $license->get_key() ) {

				// check if license is expired
				if ( $license->is_expired() ) {
					wc_add_notice( sprintf( __( 'License with key %s has expired, please %srenew license%s before upgrading.', 'license-wp' ), '<strong>' . esc_attr( $this->license_key ) . '</strong>', '<a href="' . $license->get_renewal_url() . '">', '</a>' ), 'notice' );
				}

				$this->license = $license;
			} else {
				wc_add_notice( sprintf( __( 'License key %s could not be found, please try again.', 'license-wp' ), '<strong>' . esc_attr( $this->license_key ) . '</strong>' ), 'error' );
			}

		}

	}

	/**
	 * Callback
	 */
	public function callback() {

		// set license key
		$this->set_license_key();

		// load license
		$this->load_license();


		// process the post
//		if ( ! empty( $_POST['submit_lost_license_form'] ) ) {
//			$this->process_post();
//		}

		// load view
		return $this->view();
	}

	/**
	 * Process the post
	 */
	private function process_post() {

		echo 'todo';
		exit;

		/*
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
		*/

	}

	/**
	 * Shortcode view
	 *
	 * @return string
	 */
	private function view() {
		ob_start();

		if ( ! empty( $this->license_key ) && ! is_null( $this->license ) ) {

			// enqueue JS
			LicenseWP\Assets::enqueue_shortcode_upgrade_license();

			// get product
			$product = wc_get_product( $this->license->get_product_id() );

			// load template file via WooCommerce template function
			wc_get_template( 'upgrade-license-form.php', array(
				'license' => $this->license,
				'product' => $product
			), 'license-wp', license_wp()->service( 'file' )->plugin_path() . '/templates/' );
		} else {

			// load template file via WooCommerce template function
			wc_get_template( 'upgrade-license-form-find-license.php', array(), 'license-wp', license_wp()->service( 'file' )->plugin_path() . '/templates/' );
		}


		return ob_get_clean();
	}

}