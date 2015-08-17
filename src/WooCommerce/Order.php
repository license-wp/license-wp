<?php

namespace Never5\LicenseWP\WooCommerce;

class Order {

	public function setup() {

		// display keys in order edit screen
		add_action( 'woocommerce_order_actions_end', array( $this, 'display_keys' ) );

		// hook into WooCommerce order completed status
		add_action( 'woocommerce_order_status_completed', array( $this, 'order_completed' ) );
	}

	/**
	 * Dislay lincense keys
	 *
	 * @param int $order_id
	 */
	public function display_keys( $order_id ) {
		if ( get_post_meta( $order_id, 'has_api_product_license_keys', true ) ) {
			?>
			<li class="wide">
				<a href="<?php echo admin_url( 'admin.php?page=license_wp_licenses&order_id=' . $order_id ); ?>"><?php _e( 'View licence keys &rarr;', 'license-wp' ); ?></a>
			</li>
			<?php
		}
	}

	/**
	 * Generate codes
	 *
	 * @param int $order_id
	 */
	public function order_completed( $order_id ) {
		global $wpdb;

		// only continue of this order doesn't have license keys yet
		if ( get_post_meta( $order_id, 'has_api_product_license_keys', true ) ) {
			return;
		}

		// create \WC_Order
		$order   = new \WC_Order( $order_id );
		$has_key = false;

		// loop items
		if ( sizeof( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item ) {

				// get product
				$product = $order->get_product_from_item( $item );

				// check if this is an API license product
				if ( 'yes' === get_post_meta( $product->id, '_is_api_product_license', true ) ) {

					// get activation limit
					if ( ! $product->variation_id || ( ! $activation_limit = get_post_meta( $product->variation_id, '_license_activation_limit', true ) ) ) {
						$activation_limit = get_post_meta( $product->id, '_license_activation_limit', true );
					}

					// get expiry days
					if ( ! $product->variation_id || ( ! $license_expiry_days = get_post_meta( $product->variation_id, '_license_expiry_days', true ) ) ) {
						$license_expiry_days = get_post_meta( $product->id, '_license_expiry_days', true );
					}

					// search for renewal key
					$_renewing_key = false;
					foreach ( $item['item_meta'] as $meta_key => $meta_value ) {
						if ( $meta_key == '_renewing_key' ) {
							$_renewing_key = $meta_value[0];
						}
					}

					// check on renewal
					if ( $_renewing_key ) {

						// get license
						/** @var \Never5\LicenseWP\License\License $license */
						$license = license_wp()->service( 'license_factory' )->make( $_renewing_key );

						// set new expiration date
						if ( ! empty( $licence_expiry_days ) ) {
							$exp_date = new \DateTime();
							$license->set_date_expires( $exp_date->setTime( 0, 0, 0 )->modify( "+{$license_expiry_days} days" ) );
						}

						// store license
						license_wp()->service( 'license_repository' )->persist( $license );

					} else { // no renewal, new key

						// Generate new keys
						for ( $i = 0; $i < absint( $item['qty'] ); $i ++ ) {

							// create license
							/** @var \Never5\LicenseWP\License\License $license */
							$license = license_wp()->service( 'license_factory' )->make();

							// set license data, key is generated when persisting license
							$license->set_order_id( $order_id );
							$license->set_activation_email( $order->billing_email );
							$license->set_user_id( $order->customer_user );
							$license->set_product_id( ( $product->variation_id ? $product->variation_id : $product->id ) );
							$license->set_activation_limit( $activation_limit );

							// set correct expiry days
							if ( ! empty( $licence_expiry_days ) ) {
								$exp_date = new \DateTime();
								$license->set_date_expires( $exp_date->setTime( 0, 0, 0 )->modify( "+{$license_expiry_days} days" ) );
							}

							// store license
							license_wp()->service( 'license_repository' )->persist( $license );

						}

					}

					$has_key = true;
				}
			}
		}

		// set post meta if we created at least 1 key
		if ( $has_key ) {
			update_post_meta( $order_id, 'has_api_product_license_keys', 1 );
		}
	}

}