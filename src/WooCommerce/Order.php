<?php

namespace Never5\LicenseWP\WooCommerce;

class Order {

	/**
	 * Setup hooks and filters
	 */
	public function setup() {

		// display keys in order edit screen
		add_action( 'woocommerce_order_actions_end', array( $this, 'display_keys' ) );

		// hook into WooCommerce order completed status
		add_action( 'woocommerce_order_status_completed', array( $this, 'order_completed' ) );

		// delete license related data on order delete
		add_action( 'delete_post', array( $this, 'order_delete' ) );
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
				<a href="<?php echo admin_url( 'admin.php?page=license_wp_licenses&order_id=' . $order_id ); ?>"><?php _e( 'View license keys &rarr;', 'license-wp' ); ?></a>
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
				/**
				 * @var \WC_Product $product
				 */
				$product = $item->get_product();


				// fetch if it's an API license product
				$is_api_product = false;
				if ( $product->is_type( 'variation' ) ) {
					$is_api_product = ( 'yes' === get_post_meta( $product->get_parent_id(), '_is_api_product_license', true ) );
				} else {
					$is_api_product = ( 'yes' === get_post_meta( $product->get_id(), '_is_api_product_license', true ) );
				}

				// check if this is an API license product
				if ( $is_api_product ) {

					// get activation limit
					if ( ! $product->get_id() || ( ! $activation_limit = get_post_meta( $product->get_id(), '_license_activation_limit', true ) ) ) {
						$activation_limit = get_post_meta( $product->get_id(), '_license_activation_limit', true );

						if ( empty( $activation_limit ) && $product->is_type( 'variation' ) ) {
							$activation_limit = get_post_meta( $product->get_parent_id(), '_license_activation_limit', true );
						}
					}

					// get expiry days
					if ( ! $product->get_id() || ( ! $license_expiry_days = get_post_meta( $product->get_id(), '_license_expiry_days', true ) ) ) {
						$license_expiry_days = get_post_meta( $product->get_id(), '_license_expiry_days', true );

						if ( empty( $license_expiry_days ) && $product->is_type( 'variation' ) ) {
							$license_expiry_days = get_post_meta( $product->get_parent_id(), '_license_expiry_days', true );
						}
					}

					// search for upgrade key
					$_upgrading_key = false;
					foreach ( $item['item_meta'] as $meta_key => $meta_value ) {
						if ( $meta_key == '_upgrading_key' ) {
							$_upgrading_key = $meta_value[0];
						}
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
						if ( ! empty( $license_expiry_days ) ) {
							$renew_datetime = $license->get_date_expires() && ! $license->is_expired() ? $license->get_date_expires() : new \DateTime();
							$license->set_date_expires( $renew_datetime->modify( "+{$license_expiry_days} days" ) );
						}

						// set new order id for license, store old order id with new order
						update_post_meta( $order_id, 'original_order_id', $license->get_order_id() );
						$license->set_order_id( $order_id );

						// store license
						license_wp()->service( 'license_repository' )->persist( $license );

					} else if ( $_upgrading_key ) {

						// get license
						/** @var \Never5\LicenseWP\License\License $license */
						$license = license_wp()->service( 'license_factory' )->make( $_upgrading_key );

						// set new expiration date
						if ( ! empty( $license_expiry_days ) ) {
							$current_datetime = new \DateTime();
							$license->set_date_expires( $current_datetime->modify( "+{$license_expiry_days} days" ) );
						}

						// set new activation limit
						if ( ! empty( $activation_limit ) ) {
							$license->set_activation_limit( $activation_limit );
						}

						// set new order id for license, store old order id with new order
						update_post_meta( $order_id, 'original_order_id', $license->get_order_id() );
						$license->set_order_id( $order_id );

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
							$license->set_activation_email( $order->get_billing_email() );
							$license->set_user_id( $order->get_customer_id() );
							$license->set_product_id( $product->get_id() );
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

	/**
	 * On delete post
	 *
	 * @param int $order_id
	 */
	public function order_delete( $order_id ) {
		// check if allowed
		if ( ! current_user_can( 'delete_posts' ) ) {
			return;
		}

		// check id
		if ( $order_id > 0 ) {

			// check post type
			$post_type = get_post_type( $order_id );

			// only continue on WC shop order
			if ( 'shop_order' === $post_type ) {
				license_wp()->service( 'license_manager' )->remove_license_data_by_order( $order_id );
			}
		}
	}
}
