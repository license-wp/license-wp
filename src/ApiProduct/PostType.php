<?php

namespace Never5\LicenseWP\ApiProduct;

class PostType {

	const KEY = 'api_product';

	public function setup() {
		self::register();

		// filter title placeholder
		add_filter( 'enter_title_here', function ( $text, $post ) {
			if ( self::KEY === $post->post_type ) {
				return __( 'Plugin name', 'license-wp' );
			}

			return $text;
		}, 1, 2 );

		// add custom columns
		add_filter( 'manage_edit-api_product_columns', array( $this, 'columns' ) );

		// custom column data callback
		add_action( 'manage_api_product_posts_custom_column', array( $this, 'column_data' ), 2 );
	}

	/**
	 * Register post type
	 */
	private function register() {

		if ( post_type_exists( self::KEY ) ) {
			return;
		}

		/**
		 * Post types
		 */
		$singular = __( 'API Product', 'license-wp' );
		$plural   = __( 'API Products', 'license-wp' );

		register_post_type( self::KEY, array(
			'labels'              => array(
				'name'               => $plural,
				'singular_name'      => $singular,
				'menu_name'          => $plural,
				'all_items'          => sprintf( __( 'All %s', 'license-wp' ), $plural ),
				'add_new'            => __( 'Add New', 'license-wp' ),
				'add_new_item'       => sprintf( __( 'Add %s', 'license-wp' ), $singular ),
				'edit'               => __( 'Edit', 'license-wp' ),
				'edit_item'          => sprintf( __( 'Edit %s', 'license-wp' ), $singular ),
				'new_item'           => sprintf( __( 'New %s', 'license-wp' ), $singular ),
				'view'               => sprintf( __( 'View %s', 'license-wp' ), $singular ),
				'view_item'          => sprintf( __( 'View %s', 'license-wp' ), $singular ),
				'search_items'       => sprintf( __( 'Search %s', 'license-wp' ), $plural ),
				'not_found'          => sprintf( __( 'No %s found', 'license-wp' ), $plural ),
				'not_found_in_trash' => sprintf( __( 'No %s found in trash', 'license-wp' ), $plural ),
				'parent'             => sprintf( __( 'Parent %s', 'license-wp' ), $singular )
			),
			'description'         => __( 'This is where you can create and manage api products.', 'license-wp' ),
			'public'              => false,
			'show_ui'             => true,
			'capability_type'     => 'post',
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'hierarchical'        => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => array( 'title' ),
			'has_archive'         => false,
			'show_in_nav_menus'   => false
		) );
	}

	/**
	 * Add custom columns to PT overview
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function columns( $columns ) {
		if ( ! is_array( $columns ) ) {
			$columns = array();
		}

		unset( $columns['date'] );

		$columns['api_product']  = __( 'API Product ID', 'license-wp' );
		$columns['version']      = __( 'Version', 'license-wp' );
		$columns['last_updated'] = __( 'Last updated', 'license-wp' );
		$columns['package']      = __( 'Package name', 'license-wp' );

		return $columns;
	}

	/**
	 * Custom column data
	 *
	 * @param string $column
	 */
	public function column_data( $column ) {
		global $post;

		switch ( $column ) {
			case 'api_product' :
				echo '<code>' . $post->post_name . '</code>';
				break;
			case 'version' :
			case 'last_updated' :
				$data = get_post_meta( $post->ID, '_' . $column, true );
				echo esc_html( $data );
				break;
			case 'package' :
				$data = get_post_meta( $post->ID, '_package', true );
				echo '<code>' . esc_html( basename( $data ) ) . '</code>';
				break;
		}
	}
}