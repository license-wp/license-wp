<?php

namespace Never5\LicenseWP\ApiProduct;

class Manager {

	/**
	 * Get API license products by WooCommerce product ID
	 *
	 * @param $product_or_variation_id
	 *
	 * @return array
	 */
	public function get_api_product_permissions( $product_or_variation_id ) {
		if ( 'product_variation' === get_post_type( $product_or_variation_id ) ) {
			$variation  = get_post( $product_or_variation_id );
			$product_id = $variation->post_parent;
		} else {
			$product_id = $product_or_variation_id;
		}

		return (array) json_decode( get_post_meta( $product_id, '_api_product_permissions', true ) );
	}

}