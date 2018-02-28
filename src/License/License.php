<?php

namespace Never5\LicenseWP\License;

/**
 * Class License
 * @package Never5\LicenseWP\License
 */
class License {

	/** @var string */
	private $key = '';

	/** @var int */
	private $order_id = 0;

	/** @var int */
	private $user_id = 0;

	/** @var string */
	private $activation_email = '';

	/** @var int */
	private $product_id = 0;

	/** @var int */
	private $activation_limit = 0;

	/** @var \DateTime */
	private $date_created;

	/** @var \DateTimeImmutable|bool */
	private $date_expires = false;

	/**
	 * @return string
	 */
	public function get_key() {
		return $this->key;
	}

	/**
	 * @param string $key
	 */
	public function set_key( $key ) {
		$this->key = $key;
	}

	/**
	 * @return int
	 */
	public function get_order_id() {
		return $this->order_id;
	}

	/**
	 * @param int $order_id
	 */
	public function set_order_id( $order_id ) {
		$this->order_id = $order_id;
	}

	/**
	 * @return int
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * @param int $user_id
	 */
	public function set_user_id( $user_id ) {
		$this->user_id = $user_id;
	}

	/**
	 * @return string
	 */
	public function get_activation_email() {
		return $this->activation_email;
	}

	/**
	 * @param string $activation_email
	 */
	public function set_activation_email( $activation_email ) {
		$this->activation_email = $activation_email;
	}

	/**
	 * @return int
	 */
	public function get_product_id() {
		return $this->product_id;
	}

	/**
	 * @param int $product_id
	 */
	public function set_product_id( $product_id ) {
		$this->product_id = $product_id;
	}

	/**
	 * @return int
	 */
	public function get_activation_limit() {
		return $this->activation_limit;
	}

	/**
	 * @param int $activation_limit
	 */
	public function set_activation_limit( $activation_limit ) {
		$this->activation_limit = $activation_limit;
	}

	/**
	 * @return \DateTime
	 */
	public function get_date_created() {
		return $this->date_created;
	}

	/**
	 * @param \DateTime $date_created
	 */
	public function set_date_created( $date_created ) {
		$this->date_created = $date_created;
	}

	/**
	 * @return \DateTime
	 */
	public function get_date_expires() {
		return $this->date_expires;
	}

	/**
	 * @param \DateTime|bool $date_expires or false if never expires
	 */
	public function set_date_expires( $date_expires ) {
		$this->date_expires = $date_expires;
	}

	/**
	 * Return if a license has expired
	 *
	 * @return bool
	 */
	public function is_expired() {

		// check if license expired
		if ( $this->get_date_expires() && $this->get_date_expires() < new \DateTime() ) {
			return true;
		}

		return false;
	}

	/**
	 * First we check if there is an order attached to this license.
	 * If there is an order attached, we check if it has an 'allowed' status
	 *
	 * @return bool
	 */
	public function has_valid_order_status() {

		if ( $this->get_order_id() > 0 ) {

			// get order
			$order = wc_get_order( $this->get_order_id() );

			if ( false !== $order ) {

				if ( ! in_array( $order->get_status(), apply_filters( 'license_wp_license_valid_order_statuses', array( 'completed' ) ) ) ) {
					return false;
				}

			}

		}

		return true;
	}

	/**
	 * Get API products this license gives access to
	 *
	 * @return array<\Never5\LicenseWP\ApiProduct\ApiProduct>
	 */
	public function get_api_products() {

		// get correct product id (variations etc.)
		if ( 'product_variation' === get_post_type( $this->get_product_id() ) ) {
			$variation  = get_post( $this->get_product_id() );
			$product_id = $variation->post_parent;
		} else {
			$product_id = $this->get_product_id();
		}

		// get the api product ids
		$api_product_ids = (array) json_decode( get_post_meta( $product_id, '_api_product_permissions', true ) );

		// array that stores the api products
		$api_products = array();

		// check and loop
		if ( is_array( $api_product_ids ) && count( $api_product_ids ) > 0 ) {
			foreach ( $api_product_ids as $api_product_id ) {

				// create ApiProduct objects and store them in array
				$api_products[] = license_wp()->service( 'api_product_factory' )->make( $api_product_id );
			}
		}

		// return array
		return array_filter( $api_products );
	}

	/**
	 * Get API product of license by slug
	 *
	 * @param $slug
	 *
	 * @return \Never5\LicenseWP\ApiProduct\ApiProduct
	 */
	public function get_api_product_by_slug( $slug ) {
		$api_products = $this->get_api_products();

		if ( count( $api_products ) > 0 ) {
			foreach ( $api_products as $api_product ) {
				if ( $api_product->get_slug() == $slug ) {
					return $api_product;
				}
			}
		}

		return null;
	}

	/**
	 * Get activations of license
	 * Uses Activations\Manager:get_activations
	 *
	 * @param \Never5\LicenseWP\ApiProduct\ApiProduct $api_product
	 *
	 * @return array
	 */
	public function get_activations( $api_product = null ) {
		return license_wp()->service( 'activation_manager' )->get_activations( $this, $api_product );
	}

	/**
	 * Return renewal URL
	 *
	 * @return string
	 */
	public function get_renewal_url() {
		return apply_filters( 'license_wp_license_renewal_url', add_query_arg( array(
			'renew_license'    => $this->get_key(),
			'activation_email' => $this->get_activation_email()
		), apply_filters( 'woocommerce_get_cart_url', wc_get_page_permalink( 'cart' ) ) ), $this );
	}

	/**
	 * Return upgrade URL
	 *
	 * @return string
	 */
	public function get_upgrade_url() {
		$page = get_page_by_title( apply_filters( 'license_wp_license_upgrade_page_title', 'upgrade license' ) );

		return apply_filters( 'license_wp_license_upgrade_url', add_query_arg( array(
			'license_key' => $this->get_key()
		), get_permalink( $page->ID ) ) );
	}

	/**
	 * Calculate the $ worth of the
	 *
	 * @return int
	 */
	public function calculate_worth() {

		/** @var \WC_Order $order */
		$order = wc_get_order( $this->get_order_id() );

		// worth is 0 if there is no order
		if ( false === $order ) {
			return 0;
		}

		/** @var \WC_Product_Variable $product */
		$product = wc_get_product( $this->get_product_id() ); // most likely a variable product

		// original price
		$price = 0;

		// search for the WooCommerce product that this license is attached to
		$line_items = $order->get_items( 'line_item' );
		if ( ! empty( $line_items ) ) {
			foreach ( $line_items as $line_item ) {

				// check if products match
				if ( $line_item['product_id'] == $product->get_parent_id() ) {

					// check if the WooCommerce product the license is linked to is a variation
					if ( 'variation' == $product->get_type() ) {

						// if license is linked to variation, the variation_id must also match
						if ( $line_item['variation_id'] != $product->get_id() ) {
							continue;
						}
					}

					// set price
					$price = floatval( $line_item['line_total'] );

					// and done
					break;

				}
			}
		}

		// if price is 0 (or for some extremely odd reason below 0), return a worth of 0
		if ( $price <= 0 ) {
			return 0;
		}

		// license is worth full price if it never expires
		if ( false === $this->get_date_expires() ) {
			return $price;
		}

		// now
		$now = new \DateTime();

		// datetime difference between today and creation date
		$diff_used = $now->diff( $this->get_date_created() );

		// datetime difference between creation date and expiration date
		$diff_exp = $this->get_date_created()->diff( $this->get_date_expires() );

		// amount of days used = $diff_used->days
		// amount of days used = $diff_exp->days

		/**
		 * calculate worth
		 *
		 * amount of days used = $diff_used->days
		 * amount of days license is valid from creation to expiry date = $diff_exp->days
		 */
		return apply_filters( 'license_wp_license_worth', round( $price - ( ( $price / $diff_exp->days ) * $diff_used->days ), 2 ), $this );
	}

}
