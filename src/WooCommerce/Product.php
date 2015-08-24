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

		if ( ! empty( $_POST['_is_api_product_license'] ) ) {
			update_post_meta( $post->ID, '_is_api_product_license', 'yes' );
		} else {
			update_post_meta( $post->ID, '_is_api_product_license', 'no' );
		}

		update_post_meta( $post->ID, '_api_product_permissions', json_encode( array_map( 'absint', (array) ( isset( $_POST['api_product_permissions'] ) ? $_POST['api_product_permissions'] : array() ) ) ) );
		update_post_meta( $post->ID, '_license_activation_limit', sanitize_text_field( $_POST['_license_activation_limit'] ) );
		update_post_meta( $post->ID, '_license_expiry_days', sanitize_text_field( $_POST['_license_expiry_days'] ) );
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
		$variation_license_activation_limit = $_POST['_variation_license_activation_limit'];
		$variation_license_expiry_days      = $_POST['_variation_license_expiry_days'];

		update_post_meta( $variation_id, '_license_activation_limit', sanitize_text_field( $variation_license_activation_limit[ $i ] ) );
		update_post_meta( $variation_id, '_license_expiry_days', sanitize_text_field( $variation_license_expiry_days[ $i ] ) );
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

}