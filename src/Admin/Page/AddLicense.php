<?php

namespace Never5\LicenseWP\Admin\Page;

use Never5\LicenseWP\Email;

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

		// handle save
		add_action( 'init', function () {
			if ( isset( $_POST['add_license'] ) ) {
				$this->save();
			}
		} );

		// add custom ajax endpoint
		add_action( 'wp_ajax_wpl_add_license_get_email', array( $this, 'ajax_get_email' ) );
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
	 * AJAX wpl_add_license_get_email callback
	 */
	public function ajax_get_email() {

		// check AJAX nonce
		check_ajax_referer( 'search-customers', 'nonce' );

		// check if user is allowed to do this
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			die( - 1 );
		}

		// id
		$id = absint( $_POST['id'] );

		// get user
		$user = get_user_by( 'id', $id );

		// user email address
		echo $user->user_email;

		// bye
		exit;
	}

	/**
	 * Output page content
	 *
	 * @return void
	 */
	public function output() {
		wc_get_template( 'add-license-form.php', array(), 'license-wp', license_wp()->service( 'file' )->plugin_path() . '/assets/views/' );
	}

	/**
	 * Save the new license
	 */
	public function save() {

		// vars
		$activation_email = wc_clean( $_POST['activation_email'] );
		$product_id       = absint( $_POST['product_id'] );
		$user_id          = absint( $_POST['user_id'] );

		try {

			// check nonce
			if ( empty( $_POST['license_wp_licensing_nonce'] ) || ! wp_verify_nonce( $_POST['license_wp_licensing_nonce'], 'add_license' ) ) {
				throw new \Exception( __( 'Nonce check failed', 'license-wp' ) );
			}

			// check product ID
			if ( empty( $product_id ) ) {
				throw new \Exception( __( 'You must choose a product for this license', 'license-wp' ) );
			}

			// check activation email
			if ( empty( $activation_email ) && empty( $user_id ) ) {
				throw new \Exception( __( 'Either an activation email or user ID is required', 'license-wp' ) );
			}

			// get WooCommerce product
			$product = \wc_get_product( $product_id );

			// product must be an API license product
			if ( 'yes' !== get_post_meta( $product->id, '_is_api_product_license', true ) ) {
				throw new \Exception( __( 'Invalid product', 'license-wp' ) );
			}

			// set activation email to user email if no activation email is set
			if ( ! $activation_email && $user_id ) {
				$user             = get_user_by( 'id', $user_id );
				$activation_email = $user->user_email;
			}

			// exit if we still have no valid email address at this point
			if ( empty( $activation_email ) || ! is_email( $activation_email ) ) {
				throw new \Exception( __( 'A valid activation email is required', 'license-wp' ) );
			}

			// get activation limit
			if ( ! $product->variation_id || ( ! $activation_limit = get_post_meta( $product->variation_id, '_license_activation_limit', true ) ) ) {
				$activation_limit = get_post_meta( $product->id, '_license_activation_limit', true );
			}

			// get expiry days
			if ( ! $product->variation_id || ( ! $license_expiry_days = get_post_meta( $product->variation_id, '_license_expiry_days', true ) ) ) {
				$license_expiry_days = get_post_meta( $product->id, '_license_expiry_days', true );
			}

			// create license object
			/** @var \Never5\LicenseWP\License\License $license */
			$license = license_wp()->service( 'license_factory' )->make();

			// set license data, key is generated when persisting license
			$license->set_activation_email( $activation_email );
			$license->set_user_id( $user_id );
			$license->set_product_id( $product_id );
			$license->set_activation_limit( $activation_limit );

			// set date created
			$date_created = new \DateTime();
			$license->set_date_created( $date_created->setTime( 0, 0, 0 ) );

			// set correct expiry days
			if ( ! empty( $license_expiry_days ) ) {
				$exp_date = new \DateTime();
				$license->set_date_expires( $exp_date->setTime( 0, 0, 0 )->modify( "+{$license_expiry_days} days" ) );
			}

			// store license
			$license = license_wp()->service( 'license_repository' )->persist( $license );

			// check if license was stored
			if ( '' != $license->get_key() ) {

				// try to get a first name
				if ( ! empty( $user ) && ! empty( $user->first_name ) ) {
					$user_first_name = $user->first_name;
				} else {
					$user_first_name = false;
				}

				// send email to activation email
				license_wp()->service( 'email_manager' )->send( new Email\NewLicense(
					$license,
					$user_first_name
				), $activation_email );

				$admin_message = sprintf( __( 'License key has been emailed to %s.', 'license-wp' ), $activation_email );
				echo sprintf( '<div class="updated"><p>%s</p></div>', $admin_message );

			} else {
				throw new \Exception( __( 'Could not create license!', 'license-wp' ) );
			}


		} catch ( \Exception $e ) {
			echo sprintf( '<div class="error"><p>%s</p></div>', $e->getMessage() );
		}
	}

}