<?php

namespace Never5\LicenseWP\WooCommerce;

class Renewal {

	/**
	 * Setup renewal actions/hooks
	 */
	public function setup() {
		add_action( 'wp', function () {

			// check if we need to renew a license
			if ( isset( $_GET['renew_license'] ) && isset( $_GET['activation_email'] ) ) {

				// add renewal to cart
				$this->add_renewal_to_cart( wp_unslash( $_GET['renew_license'] ), wp_unslash( $_GET['activation_email'] ) );
			}

		} );

		// WooCommerce filters to make the renewal work
		add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 10, 1 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'order_item_meta' ), 10, 4 );
	}

	/**
	 * Add license renewal to cart
	 *
	 * @param string $license_key
	 * @param string $activation_email
	 */
	private function add_renewal_to_cart( $license_key, $activation_email ) {

		// sanitize license key & activation email
		$license_key      = sanitize_text_field( $license_key );
		$activation_email = sanitize_text_field( $activation_email );

		// get license
		/** @var \Never5\LicenseWP\License\License $license */
		$license = license_wp()->service( 'license_factory' )->make( $license_key );

		// check if license exists
		if ( '' == $license->get_key() ) {
			wc_add_notice( __( 'Invalid license key.', 'license-wp' ), 'error' );

			return;
		}

		// check if this license is owned by logged in user
		if ( is_user_logged_in() && $license->get_user_id() != get_current_user_id() ) {
			wc_add_notice( __( 'This license does not appear to be yours.', 'license-wp' ), 'error' );

			return;
		}

		// check if activation email is correct
		if ( ! is_email( $activation_email ) || $activation_email != $license->get_activation_email() ) {
			wc_add_notice( __( 'Invalid activation email address.', 'license-wp' ), 'error' );

			return;
		}

		// get WooCommerce product
		$product = wc_get_product( $license->get_product_id() );

		// check if product is purchasable
		if ( ! $product || ! $product->is_purchasable() ) {
			wc_add_notice( __( 'This product can no longer be purchased', 'license-wp' ), 'error' );

			return;
		}

		// Add to cart
		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( $license->get_product_id(), 1, '', '', array(
			'renewing_key' => $license->get_key()
		) );

		// Message
		$discount = $this->get_discount( $license );
		if ( $discount > 0 ) {
			wc_add_notice( sprintf( __( 'The product has been added to your cart with a %d%% discount.', 'license-wp' ), $discount ), 'success' );
		} else {
			wc_add_notice( __( 'The product has been added to your cart.', 'license-wp' ), 'success' );
		}

		// Redirect to checkout
		wp_redirect( get_permalink( wc_get_page_id( 'checkout' ) ) );

		// bye
		exit;
	}

	/**
	 * The renewal discount in percent: only for licenses that have not expired yet
	 *
	 * @param \Never5\LicenseWP\License\License $license
	 *
	 * @return int
	 */
	private function get_discount( $license ) {
		if ( '' === $license->get_key() || $license->is_expired() ) {
			return 0;
		}

		return absint( apply_filters( 'license_wp_renewal_discount', 30, $license ) );
	}

	/**
	 * Set the renewal price and name of a cart item
	 *
	 * @param array $cart_item
	 * @param string $license_key
	 *
	 * @return array
	 */
	private function apply_renewal( $cart_item, $license_key ) {
		$discount = $this->get_discount( license_wp()->service( 'license_factory' )->make( $license_key ) );

		if ( $discount > 0 ) {
			$price = $cart_item['data']->get_price();
			$cart_item['data']->set_price( $price - ( ( $price / 100 ) * $discount ) );
		}

		$cart_item['data']->set_name( $cart_item['data']->get_name() . ' (' . __( 'Renewal', 'license-wp' ) . ')' );

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
		if ( isset( $cart_item['renewing_key'] ) ) {
			$cart_item = $this->apply_renewal( $cart_item, $cart_item['renewing_key'] );
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
		if ( isset( $values['renewing_key'] ) ) {
			$cart_item                 = $this->apply_renewal( $cart_item, $values['renewing_key'] );
			$cart_item['renewing_key'] = $values['renewing_key'];
		}
		return $cart_item;
	}

	/**
	 * order_item_meta function for storing the meta in the order line items
	 *
	 * @param \WC_Order_Item $item
	 * @param string        $cart_item_key
	 * @param array         $values
	 * @param \WC_Order      $order
	 */
	public function order_item_meta( $item, $cart_item_key, $values, $order ) {
		if ( isset( $values['renewing_key'] ) ) {
			$item->add_meta_data( '_renewing_key', $values['renewing_key'], true );
		}
	}

}