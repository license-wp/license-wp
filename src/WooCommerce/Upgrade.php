<?php

namespace Never5\LicenseWP\WooCommerce;

class Upgrade {

	/**
	 * Setup renewal actions/hooks
	 */
	public function setup() {
		add_action( 'wp', function () {

			// check if we need to renew a license
			if ( isset( $_GET['upgrade_license'] ) ) {

				// add renewal to cart
				$this->add_upgrade_to_cart( $_GET['upgrade_license'], $_GET['new_license'] );
			}

		} );

		// WooCommerce filters to make the renewal work
		add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 10, 1 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 10, 2 );
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'order_item_meta' ), 10, 2 );
	}

	/**
	 * Add license renewal to cart
	 *
	 * @param string $license_key
	 * @param string $new_license
	 */
	private function add_upgrade_to_cart( $license_key, $new_license ) {

		// sanitize license key & activation email
		$license_key = sanitize_text_field( $license_key );
		$new_license = absint( $new_license );

		// get license
		/** @var \Never5\LicenseWP\License\License $license */
		$license = license_wp()->service( 'license_factory' )->make( $license_key );

		// get the new WooCommerce Product (license)
		$new_product = wc_get_product( $new_license );

		// check if license exists
		if ( '' == $license->get_key() ) {
			wc_add_notice( __( 'Invalid license key.', 'license-wp' ) );
		}

		// check if this license is owned by logged in user
		if ( is_user_logged_in() && $license->get_user_id() != get_current_user_id() ) {
			wc_add_notice( __( 'This license does not appear to be yours.', 'license-wp' ) );
		}

		// check if WooCommerce product exists
		if ( false === $new_product ) {
			wc_add_notice( __( "This product can't be found.", 'license-wp' ), 'error' );

			return;
		}

		// check if product is purchasable
		if ( ! $new_product->is_purchasable() ) {
			wc_add_notice( __( 'This product can no longer be purchased', 'license-wp' ), 'error' );

			return;
		}

		// get WP term object of license
		$new_product_license_term = Product::get_license_term_of_product( $new_product );

		// Add to cart
		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( $new_product->parent->get_id(), 1, $new_product->get_id(), array( 'License' => $new_product_license_term->name ), array(
			'upgrading_key' => $license->get_key()
		) );

		//renewing_key

		// Message
		wc_add_notice( __( 'The product upgrade has been added to your cart.', 'license-wp' ), 'success' );

		// Redirect to checkout
		wp_redirect( get_permalink( wc_get_page_id( 'checkout' ) ) );

		// bye
		exit;
	}

	/**
	 * Generate new cart item with upgrading discount
	 *
	 * @param array $cart_item
	 *
	 * @return array
	 */
	private function generate_new_cart_item( $cart_item ) {

		// get license
		/** @var \Never5\LicenseWP\License\License $license */
		$license = license_wp()->service( 'license_factory' )->make( $cart_item['upgrading_key'] );

		// check if license is found and matches
		if ( $cart_item['upgrading_key'] == $license->get_key() ) {
			$price            = $cart_item['data']->get_price();
			$discounted_price = $price - $license->calculate_worth();

			$cart_item['data']->set_price( $discounted_price );
			$cart_item['data']->get_post_data();
			$cart_item['data']->post->post_title .= ' (' . __( 'Upgrade', 'license-wp' ) . ')';
		}

		return $cart_item;
	}

	/**
	 * Change price in cart to discount the upgrade
	 *
	 * @param array $cart_item
	 *
	 * @return array
	 */
	public function add_cart_item( $cart_item ) {
		if ( isset( $cart_item['upgrading_key'] ) ) {

			// generate new cart item
			$cart_item = $this->generate_new_cart_item( $cart_item );

		}

		return $cart_item;
	}

	/**
	 * get_cart_item_from_session function.
	 *
	 * @param array $cart_item
	 * @param array $values
	 *
	 * @return array
	 */
	public function get_cart_item_from_session( $cart_item, $values ) {
		if ( isset( $cart_item['upgrading_key'] ) ) {

			// generate new cart item
			$cart_item = $this->generate_new_cart_item( $cart_item );

		}

		return $cart_item;
	}

	/**
	 * order_item_meta function for storing the meta in the order line items
	 *
	 * @param int $item_id
	 * @param array $values
	 */
	public function order_item_meta( $item_id, $values ) {
		if ( isset( $values['upgrading_key'] ) ) {
			wc_add_order_item_meta( $item_id, __( '_upgrading_key', 'license-wp' ), $values['upgrading_key'] );
		}
	}

}