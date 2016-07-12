<?php
namespace Never5\LicenseWP\Shortcode;

use Never5\LicenseWP;

class UpgradeLicenseForm {

	private $license_key = '';

	private $license = null;

	private $is_upgradable = true;

	/**
	 * __constructor
	 */
	public function __construct() {

		// add shortcode
		add_shortcode( 'upgrade_license_key_form', array( $this, 'callback' ) );

		// set license key
		$this->set_license_key();

		// load license
		$this->load_license();

		// bail if not upgradable
		if ( ! $this->is_upgradable ) {
			return;
		}

		// process the post
		if ( ! empty( $_POST['new_license'] ) ) {
			$this->process_post();
		}

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
					$this->is_upgradable = false;
				}

				$this->license = $license;
			} else {
				wc_add_notice( sprintf( __( 'License key %s could not be found, please try again.', 'license-wp' ), '<strong>' . esc_attr( $this->license_key ) . '</strong>' ), 'error' );
				$this->is_upgradable = false;
			}

		}

	}

	/**
	 * Callback
	 */
	public function callback() {
		// load view
		return $this->view();
	}

	/**
	 * Process the post
	 */
	private function process_post() {

		if ( ! empty( $this->license_key ) && ! is_null( $this->license ) && isset( $_POST['new_license'] ) && ! empty( $_POST['new_license'] ) ) {

			// new license
			$new_license = absint( $_POST['new_license'] );

			// setup add-to-cart upgrade URL
			$redirect_url = apply_filters( 'license_wp_license_upgrade_url_cart', add_query_arg( array(
				'upgrade_license' => $this->license->get_key(),
				'new_license'     => $new_license
			), apply_filters( 'woocommerce_get_cart_url', wc_get_page_permalink( 'cart' ) ) ), $this->license );

			// redirect to cart
			wp_redirect( $redirect_url );
			exit;

		}

	}

	/**
	 * Shortcode view
	 *
	 * @return string
	 */
	private function view() {
		ob_start();

		// print WC notices
		wc_print_notices();

		// only load view if is upgrable
		if ( $this->is_upgradable ) {

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

		}

		return ob_get_clean();
	}

}