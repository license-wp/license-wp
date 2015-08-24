<?php
namespace Never5\LicenseWP;

abstract class Assets {

	/**
	 * Enqueue frontend assets
	 */
	public static function enqueue_frontend() {
	}

	/**
	 * Enqueue backend(admin) assets
	 */
	public static function enqueue_backend() {
		global $pagenow, $post;

		// only load WP media assets on correct page
		if ( ( $pagenow == 'post.php' && isset( $post ) && ApiProduct\PostType::KEY === $post->post_type ) || ( $pagenow == 'post-new.php' && isset( $_GET['post_type'] ) && ApiProduct\PostType::KEY == $_GET['post_type'] ) ) {
			wp_enqueue_media();
		}

		// admin CSS
		wp_enqueue_style(
			'license_wp_style',
			license_wp()->service( 'file' )->plugin_url( '/assets/css/admin.css' ),
			array(),
			license_wp()->get_version()
		);

	}

}