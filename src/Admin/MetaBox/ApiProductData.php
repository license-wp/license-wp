<?php

namespace Never5\LicenseWP\Admin\MetaBox;

class ApiProductData {

	/**
	 * Register meta box
	 */
	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'add' ) );
		add_action( 'save_post', array( $this, 'save' ), 1, 2 );
		add_action( 'license_wp_save_api_product', array( $this, 'save_api_product_data' ), 20, 2 );
		add_action( 'admin_menu', array( $this, 'remove_meta_box' ) );
	}

	/**
	 * Add meta box
	 */
	public function add() {
		add_meta_box( 'api_product_data', __( 'API Product Data', 'license-wp' ), array(
			$this,
			'view'
		), 'api_product', 'normal', 'high' );
	}

	/**
	 * Remove slug meta box
	 */
	public function remove_meta_box() {
		remove_meta_box( 'slugdiv', 'api_product', 'normal' );
	}

	/**
	 * Get fields used in meta box
	 *
	 * @return array
	 */
	private function get_fields() {
		global $post;

		return apply_filters( 'license_wp_api_product_data_fields', array(
			'post_name'            => array(
				'label'       => __( 'API Product ID', 'license-wp' ),
				'placeholder' => __( 'your-plugin-name', 'license-wp' ),
				'description' => __( 'A unique identifier for the API Product. Stored as the post_name.', 'license-wp' ),
				'type'        => 'text',
				'value'       => $post->post_name
			),
			'_version'             => array(
				'label'       => __( 'Version', 'license-wp' ),
				'placeholder' => __( 'x.x.x', 'license-wp' ),
				'description' => __( 'The current version number of the plugin.', 'license-wp' )
			),
			'_last_updated'        => array(
				'label'       => __( 'Date', 'license-wp' ),
				'placeholder' => __( 'yyyy-mm-dd', 'license-wp' ),
				'description' => __( 'The date of the last update.', 'license-wp' )
			),
			'_package'             => array(
				'label'       => __( 'Package', 'license-wp' ),
				'type'        => 'file',
				'description' => __( 'The plugin package zip file.', 'license-wp' )
			),
			'_plugin_uri'          => array(
				'label' => __( 'Plugin URI', 'license-wp' )
			),
			'_author'              => array(
				'label'       => __( 'Author', 'license-wp' ),
				'placeholder' => ''
			),
			'_author_uri'          => array(
				'label'       => __( 'Author URI', 'license-wp' ),
				'placeholder' => ''
			),
			'_requires_wp_version' => array(
				'label'       => __( 'Requries at least', 'license-wp' ),
				'placeholder' => __( 'e.g. 3.8', 'license-wp' )
			),
			'_tested_wp_version'   => array(
				'label'       => __( 'Tested up to', 'license-wp' ),
				'placeholder' => __( 'e.g. 3.9', 'license-wp' )
			),
			'content'              => array(
				'label'       => __( 'Description', 'license-wp' ),
				'placeholder' => __( 'Content describing the plugin', 'license-wp' ),
				'type'        => 'textarea',
				'value'       => $post->post_content
			),
			'_changelog'           => array(
				'label' => __( 'Changelog', 'license-wp' ),
				'type'  => 'textarea'
			)
		) );
	}

	/**
	 * Meta box view
	 */
	public function view() {
		global $post, $thepostid;

		$thepostid = $post->ID;

		echo '<div class="license_wp_meta_data">';

		wp_nonce_field( 'save_meta_data', 'license_wp_nonce' );

		do_action( 'license_wp_api_product_data_start', $thepostid );

		foreach ( $this->get_fields() as $key => $field ) {
			$type = ! empty( $field['type'] ) ? $field['type'] : 'text';

			$class_name = 'Never5\\LicenseWP\\Admin\\MetaBox\\Input\\' . ucfirst( ( ( ! empty( $field['type'] ) ) ? $field['type'] : 'text' ) );
			if ( class_exists( $class_name ) ) {
				$input = new $class_name( $key, $field );
				$input->view();
			} else {
				do_action( 'license_wp_api_product_data_field_input_' . $type, $key, $field );
			}
		}

		do_action( 'license_wp_api_product_data_end', $thepostid );

		echo '</div>';
	}

	/**
	 * Save meta data
	 *
	 * @param int $post_id
	 * @param \WP_Post $post
	 */
	public function save( $post_id, $post ) {
		if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( is_int( wp_is_post_revision( $post ) ) ) {
			return;
		}
		if ( is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}
		if ( empty( $_POST['license_wp_nonce'] ) || ! wp_verify_nonce( $_POST['license_wp_nonce'], 'save_meta_data' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( $post->post_type != 'api_product' ) {
			return;
		}

		do_action( 'license_wp_save_api_product', $post_id, $post );
	}

	/**
	 * save_api_product_data function.
	 *
	 * @access public
	 *
	 * @param int $post_id
	 * @param \WP_Post $post
	 */
	public function save_api_product_data( $post_id, $post ) {

		// Save fields
		foreach ( $this->get_fields() as $key => $field ) {
			// last updated date
			if ( '_last_updated' === $key ) {
				if ( ! empty( $_POST[ $key ] ) ) {
					update_post_meta( $post_id, $key, date( 'Y-m-d', strtotime( sanitize_text_field( $_POST[ $key ] ) ) ) );
				} else {
					update_post_meta( $post_id, $key, date( 'Y-m-d' ) );
				}
			} elseif ( 'content' === $key ) {
				continue;
			} elseif ( 'post_name' === $key ) {
				continue;
			} // Everything else
			else {
				$type = ! empty( $field['type'] ) ? $field['type'] : '';

				switch ( $type ) {
					case 'textarea' :
						update_post_meta( $post_id, $key, wp_kses_post( stripslashes( $_POST[ $key ] ) ) );
						break;
					default :
						if ( is_array( $_POST[ $key ] ) ) {
							update_post_meta( $post_id, $key, array_map( 'sanitize_text_field', $_POST[ $key ] ) );
						} else {
							update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) );
						}
						break;
				}
			}
		}

		// Get the plugin version
		$plugin_version = get_post_meta( $post_id, '_version', true );

		delete_transient( 'plugininfo_' . md5( $post->post_name . $plugin_version ) );
	}

}