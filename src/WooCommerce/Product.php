<?php

namespace Never5\LicenseWP\WooCommerce;

class Product {

	/**
	 * Setup WooCommerce Product class
	 */
	public function setup() {

		// add product type
		add_filter( 'product_type_options', function ( $options ) {
			$options['is_api_product_license'] = array(
				'id'            => '_is_api_product_license',
				'wrapper_class' => 'show_if_simple show_if_variable',
				'label'         => __( 'API Product License', 'license-wp' ),
				'description'   => __( 'Enable this option if this is a license for an API Product', 'license-wp' )
			);

			return $options;
		} );

		// license data
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'license_data' ) );
		add_filter( 'woocommerce_process_product_meta', array( $this, 'save_license_data' ) );

		// variable license data
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'variable_license_data' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_variable_license_data' ), 10, 2 );

	}

	/**
	 * License data view
	 */
	public function license_data() {
		global $post;
		$post_id              = $post->ID;
		$current_api_products = (array) json_decode( get_post_meta( $post->ID, '_api_product_permissions', true ) );
		$api_products         = get_posts( array(
			'numberposts' => - 1,
			'orderby'     => 'title',
			'post_type'   => 'api_product',
			'post_status' => array( 'publish' ),
		) );

		// include view
		include( license_wp()->service( 'file' )->plugin_path() . '/assets/views/html-license-data.php' );
	}

	/**
	 * Save the license data
	 */
	public function save_license_data() {
		global $post;

		error_log( 'save_license_data triggered', 0 );
		error_log( 'Post ID: '. $post->ID, 0 );
		error_log( print_r($_POST, 1), 0 );

		if ( ! empty( $_POST['_is_api_product_license'] ) ) {
			update_post_meta( $post->ID, '_is_api_product_license', 'yes' );
		} else {
			update_post_meta( $post->ID, '_is_api_product_license', 'no' );
		}

		update_post_meta( $post->ID, '_api_product_permissions', json_encode( array_map( 'absint', (array) ( isset( $_POST['api_product_permissions'] ) ? $_POST['api_product_permissions'] : array() ) ) ) );
		update_post_meta( $post->ID, '_license_activation_limit', sanitize_text_field( $_POST['_license_activation_limit'] ) );
		update_post_meta( $post->ID, '_license_expiry_amount', sanitize_text_field( $_POST['_license_expiry_amount'] ) );
		update_post_meta( $post->ID, '_license_expiry_type', sanitize_text_field( $_POST['_license_expiry_type'] ) );
	}

	/**
	 * Variable product license data
	 *
	 * @param $loop
	 * @param $variation_data
	 * @param $variation
	 */
	public function variable_license_data( $loop, $variation_data, $variation ) {
		global $post, $thepostid;
		include( license_wp()->service( 'file' )->plugin_path() . '/assets/views/html-variation-license-data.php' );
	}

	/**
	 * Save variable product license data
	 *
	 * @param $variation_id
	 * @param $i
	 */
	public function save_variable_license_data( $variation_id, $i ) {
		$variation_license_activation_limit     = $_POST['_variation_license_activation_limit'];
		$variation_license_expiry_amount        = $_POST['_variation_license_expiry_amount'];
		$variation_license_expiry_type          = $_POST['_variation_license_expiry_type'];

		update_post_meta( $variation_id, '_license_activation_limit', sanitize_text_field( $variation_license_activation_limit[ $i ] ) );
		update_post_meta( $variation_id, '_license_expiry_amount', sanitize_text_field( $variation_license_expiry_amount[ $i ] ) );
		update_post_meta( $variation_id, '_license_expiry_type', sanitize_text_field( $variation_license_expiry_type[ $i ] ) );
	}

	/**
	 * Get WooCommerce product, returns parent if product is variable product
	 *
	 * @param $id
	 *
	 * @return \WP_Post
	 */
	public static function get_product( $id ) {
		if ( 'product_variation' === get_post_type( $id ) ) {
			$variation  = get_post( $id );
			$product_id = $variation->post_parent;
		} else {
			$product_id = $id;
		}

		return get_post( $product_id );
	}

	/**
	 * Get WordPress term object of license of WC_Product_Variable
	 *
	 * @param \WC_Product_Variable $product
	 *
	 * @return object
	 */
	public static function get_license_term_of_product( $product ) {
		$pa = $product->get_variation_attributes();

		return get_term_by( 'slug', reset( $pa ), sanitize_title( substr( key( $pa ), 10 ) ) );
	}

	/**
	 * Get available upgrade options
	 *
	 * @param \WC_Product_Variable $product
	 * @param \Never5\LicenseWP\License\License $license
	 *
	 * @return array
	 */
	public static function get_available_upgrade_options( $product, $license ) {
		// fetch and store license options in variable
		$license_options = array();

		// our product needs to be a variation
		if ( 'variation' != $product->get_type() ) {
			return $license_options;
		}

		// our product parent must be a variable
		$parent = wc_get_product( $product->get_parent_id() );
		if ( 'variable' != $parent->get_type() ) {
			return $license_options;
		}

		// get variation related data
		$available_variations = $parent->get_available_variations();

		// we need available variations
		if ( empty( $available_variations ) ) {
			return $license_options;
		}

		// store license worth in var
		$license_worth = $license->calculate_worth();

		// loop and check a bunch of license variation required props
		foreach ( $available_variations as $variation ) {
			if ( is_array( $variation ) ) {

				// get variation product
				$variation_product = wc_get_product( $variation['variation_id'] );

				$license_activation_limit = absint ( get_post_meta( $variation_product->get_id(), '_license_activation_limit', true ) );

				// check
				if ( ! empty( $variation_product ) && $variation_product->is_purchasable() && $license_activation_limit > $license->get_activation_limit() ) {

					// take first variation attribute of an API licensed product
					foreach ( $variation_product->get_variation_attributes() as $vp_key => $vp_val ) {

						// get attr tax slug from attr name
						$attr_slug = sanitize_title( substr( $vp_key, 10 ) );

						// check if exists
						if ( taxonomy_exists( $attr_slug ) ) {

							// get term
							$term = get_term_by( 'slug', $vp_val, $attr_slug );

							// finally add to array
							$license_options[] = array(
								'id'            => $variation_product->get_id(),
								'slug'          => $term->slug,
								'title'         => $term->name . ' - ' . sprintf( __( 'Up to %d Websites', 'license-wp' ), $license_activation_limit ),
								'price'         => $variation_product->get_price(),
								'upgrade_price' => $variation_product->get_price() - $license_worth
							);

							// got term, break
							break;
						}
					}
				}
			}
		}

		return $license_options;
	}

}
