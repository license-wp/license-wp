<?php

namespace Never5\LicenseWP\ApiProduct;

class WordPressRepository implements Repository {

	/**
	 * Retrieve product data from WordPress database
	 *
	 * @param int $id
	 *
	 * @return \stdClass
	 */
	public function retrieve( $id ) {
		$data = new \stdClass();

		// get product post
		$post = get_post( $id );

		// set data if post found
		if ( null !== $post ) {
			$data->id                = $post->ID;
			$data->name              = $post->post_title;
			$data->slug              = $post->post_name;
			$data->version           = get_post_meta( $post->ID, '_version', true );
			$data->date              = get_post_meta( $post->ID, '_date', true );
			$data->package           = get_post_meta( $post->ID, '_package', true );
			$data->uri               = get_post_meta( $post->ID, '_plugin_uri', true );
			$data->author            = get_post_meta( $post->ID, '_author', true );
			$data->author_uri        = get_post_meta( $post->ID, '_author_uri ', true );
			$data->requires_at_least = get_post_meta( $post->ID, '_requires_wp_version ', true );
			$data->tested_up_to      = get_post_meta( $post->ID, '_tested_wp_version ', true );
			$data->description       = $post->post_content;
			$data->changelog         = get_post_meta( $post->ID, '_changelog ', true );
		}

		return $data;
	}

	/**
	 * Persist license data in WordPress database
	 *
	 * @param ApiProduct $product
	 *
	 * @return ApiProduct
	 */
	public function persist( $product ) {

		// check if new license or existing
		if ( 0 === $product->get_id() ) { // insert

			// insert WP post
			$product_id = wp_insert_post( array(
				'post_title'   => $product->get_name(),
				'post_name'    => $product->get_slug(),
				'post_content' => $product->get_description(),
				'post_status'  => 'publish',
				'post_type'    => PostType::KEY,
			) );

			// set product id
			$product->set_id( $product_id );
		} else {

			// update post
			wp_update_post( array(
				'ID'           => $product->get_id(),
				'post_title'   => $product->get_name(),
				'post_name'    => $product->get_slug(),
				'post_content' => $product->get_description(),
				'post_status'  => 'publish',
				'post_type'    => PostType::KEY,
			) );
		}

		// update meta data
		update_post_meta( $product->get_id(), '_version', $product->get_version() );
		update_post_meta( $product->get_id(), '_date', $product->get_date() );
		update_post_meta( $product->get_id(), '_package', $product->get_package() );
		update_post_meta( $product->get_id(), '_plugin_uri', $product->get_uri() );
		update_post_meta( $product->get_id(), '_author', $product->get_author() );
		update_post_meta( $product->get_id(), '_author_uri', $product->get_author_uri() );
		update_post_meta( $product->get_id(), '_requires_wp_version', $product->get_requires_at_least() );
		update_post_meta( $product->get_id(), '_tested_wp_version', $product->get_tested_up_to() );
		update_post_meta( $product->get_id(), '_changelog', $product->get_changelog() );

		return $product;
	}

}