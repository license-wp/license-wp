<?php

namespace Never5\LicenseWP\WooCommerce;

class Order {
	const KEY_ACTION_RENEW = 'renew';
	const KEY_ACTION_RECONNECT = 'reconnect';

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

			$order            = wc_get_order( $order_id );
			$license_page_url = 'admin.php?page=license_wp_licenses';
			foreach ( $order->get_items() as $item_id => $item ) {
				$renewing_key = $item->get_meta( '_renewing_key' );
				if ( $renewing_key ) {
					$license_page_url .= '&license_key=' . $renewing_key;
					break;
				}
			}

			if ( empty( $renewing_key ) ) {
				$license_page_url .= '&order_id=' . $order_id;
			}

			?>
			<li class="wide">
				<a href="<?php echo admin_url( $license_page_url ); ?>"><?php _e( 'View license keys &rarr;', 'license-wp' ); ?></a>
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

		$previous_license_keys = array();

		// check for subscription renewal
		$order_types = array( 'renewal' => self::KEY_ACTION_RENEW, 'resubscribe' => self::KEY_ACTION_RECONNECT );
		foreach ( $order_types as $order_type => $key_action ) {
			$subscriptions = self::get_order_subscriptions( $order_id, $order_type );
			if ( ! empty( $subscriptions ) ) {
				foreach ( $subscriptions as $subscription ) {
					// get parent order id
					$parent_order_id = $subscription->get_parent_id();

					$licenses = license_wp()->service( 'license_manager' )->get_licenses_by_order( $parent_order_id );

					if ( ! empty( $licenses ) ) {
						/**
						 * @var \Never5\LicenseWP\License\License $license
						 */
						$license = array_shift( $licenses );
						$previous_license_keys[ $license->get_product_id() ] = array( 'action' => $key_action, 'key' => $license->get_key() );
					}
				}
			}
		}

		// loop items
		if ( sizeof( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item ) {

				// get product
				/**
				 * @var \WC_Product $product
				 */
				$product = $item->get_product();

				// fetch if it's an API license product
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

					// get expiry date
					$expiry_modify_string = "";

					$license_expiry_amount = get_post_meta( $product->get_id(), '_license_expiry_amount', true );
					$license_expiry_type   = get_post_meta( $product->get_id(), '_license_expiry_type', true );

					if ( empty( $license_expiry_amount ) && $product->is_type( 'variation' ) ) {
						$license_expiry_amount = get_post_meta( $product->get_parent_id(), '_license_expiry_amount', true );
					}

					if ( empty( $license_expiry_type ) && $product->is_type( 'variation' ) ) {
						$license_expiry_type = get_post_meta( $product->get_parent_id(), '_license_expiry_type', true );
					}

					if ( ! empty( $license_expiry_amount ) && 0 != $license_expiry_amount ) {
						$expiry_modify_string = "+".$license_expiry_amount." ";
						switch ( $license_expiry_type ) {
							case 'years':
								$expiry_modify_string .= "years";
								break;
							case 'months':
								$expiry_modify_string .= "months";
								break;
							case 'days':
							default:
								$expiry_modify_string .= "days";
								break;
						}
					}

					// search for upgrade key
					$_upgrading_key = false;
					foreach ( $item['item_meta'] as $meta_key => $meta_value ) {
						if ( $meta_key == '_upgrading_key' ) {
							$_upgrading_key = $meta_value[0];
						}
					}

					// Make $_upgrading_key filterable
					$_upgrading_key = apply_filters( 'lwp_order_upgrading_key', $_upgrading_key, $item, $order );

					// check for standard product renewing
					if ( ! isset( $previous_license_keys[ $product->get_id() ] ) && ! empty( $item['item_meta']['_renewing_key'] ) ) {
						$previous_license_keys[ $product->get_id() ] = array( 'key' => $item['item_meta']['_renewing_key'],  'action' => self::KEY_ACTION_RENEW  );
					}

					// check on renewal
					if ( isset( $previous_license_keys[ $product->get_id() ] ) ) {
						$previous_license_record = $previous_license_keys[ $product->get_id() ];
						$previous_license_key = $previous_license_record['key'];
						$previous_license_action = $previous_license_record['action'];

						// get license
						/** @var \Never5\LicenseWP\License\License $license */
						$license = license_wp()->service( 'license_factory' )->make( $previous_license_key );

						// set new expiration date
						if ( $previous_license_action === self::KEY_ACTION_RENEW && ! empty( $expiry_modify_string ) ) {
							$renew_datetime = (  ! $license->is_expired() ) ? $license->get_date_expires() : new \DateTime();
							$license->set_date_expires( $renew_datetime->setTime( 0, 0, 0 )->modify( $expiry_modify_string ) );
						}

						// store license
						license_wp()->service( 'license_repository' )->persist( $license );

					} else if ( $_upgrading_key ) {

						// get license
						/** @var \Never5\LicenseWP\License\License $license */
						$license = license_wp()->service( 'license_factory' )->make( $_upgrading_key );

						// set new expiration date
						if ( apply_filters( 'lwp_upgrade_update_date_expires', true, $license, $order, $item ) ) {
							if ( ! empty( $expiry_modify_string ) ) {
								$current_datetime = new \DateTime();
								$current_datetime->setTime( 0, 0, 0 )->modify( $expiry_modify_string );
								$license->set_date_expires( apply_filters( 'lwp_upgrade_date_expires', $current_datetime, $license, $order, $item )  );
							}
						}

						// set new activation limit
						if ( ! empty( $activation_limit ) ) {
							$license->set_activation_limit( $activation_limit );
						}

						// set new product id
						$license->set_product_id( $product->get_id() );

						// set new order id for license, store old order id with new order
						if ( apply_filters( 'lwp_upgrade_update_order_id', true, $license, $order, $item ) ) {
							update_post_meta( $order_id, 'original_order_id', $license->get_order_id() );
							$license->set_order_id( $order_id );
						}

						// store license
						license_wp()->service( 'license_repository' )->persist( $license );

					} else { // no renewal, no upgrade, new key

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
							if ( ! empty( $expiry_modify_string ) ) {
								$exp_date = new \DateTime();
								$license->set_date_expires( $exp_date->setTime( 0, 0, 0 )->modify( $expiry_modify_string ) );
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

	/**
	 * Returns the previous subscriptions (renewals or resubscriptions) tied to this order.
	 *
	 * @param int               $order_id
	 * @param string|array|null $order_type Order type for subscription query. Default (set to null) is `[ 'renewal', 'resubscribe' ]`.
	 *
	 * @return array|bool
	 */
	public static function get_order_subscriptions( $order_id, $order_type = null ) {
		if ( ! class_exists( 'WC_Subscription' ) || ! function_exists( 'wcs_get_subscriptions_for_order' ) ) {
			return false;
		}
		if ( null === $order_type ) {
			$order_type = array( 'renewal', 'resubscribe' );
		}
		return wcs_get_subscriptions_for_order( $order_id, array( 'order_type' => $order_type ) );
	}
}
