<?php
namespace Never5\LicenseWP;

abstract class Assets {

	/**
	 * Enqueue frontend assets
	 */
	public static function enqueue_frontend() {
		// frontend CSS
		wp_enqueue_style(
			'license_wp_style_frontend',
			license_wp()->service( 'file' )->plugin_url( '/assets/css/frontend.css' ),
			array(),
			license_wp()->get_version()
		);
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
			'license_wp_style_admin',
			license_wp()->service( 'file' )->plugin_url( '/assets/css/admin.css' ),
			array(),
			license_wp()->get_version()
		);

	}

	/**
	 * Enqueue upgrade license JS
	 */
	public static function enqueue_shortcode_upgrade_license() {
		wp_enqueue_script(
			'lwp_js_upgrade_license',
			license_wp()->service( 'file' )->plugin_url( '/assets/js/upgrade-license' . ( ( ! SCRIPT_DEBUG ) ? '.min' : '' ) . '.js' ),
			array( 'jquery' ),
			license_wp()->get_version(),
			true
		);
	}

}