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

}