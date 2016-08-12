<?php

namespace Never5\LicenseWP\ApiProduct;

/**
 * Class DownloadHandler
 * @package Never5\LicenseWP\ApiProduct
 */
class DownloadHandler {

	/**
	 * Listen to download requests
	 */
	public function listen() {
		add_action( 'init', function () {

			// check if get variables are set
			if ( isset( $_GET['download_api_product'] ) && isset( $_GET['license_key'] ) && isset( $_GET['activation_email'] ) ) {

				// trigger
				$this->trigger( $_GET['download_api_product'], $_GET['license_key'], $_GET['activation_email'] );

			}

		} );
	}

	/**
	 * Trigger download
	 *
	 * @param int $product_id
	 * @param string $license_key
	 * @param string $activation_email
	 */
	private function trigger( $product_id, $license_key, $activation_email ) {

		// clean vars
		$product_id       = absint( $product_id );
		$license_key      = sanitize_text_field( $license_key );
		$activation_email = sanitize_text_field( $activation_email );

		// get license
		/** @var \Never5\LicenseWP\License\License $license */
		$license = license_wp()->service( 'license_factory' )->make( $license_key );

		// check if license exists
		if ( '' == $license->get_key() ) {
			wp_die( __( 'Invalid license key.', 'license-wp' ) );
		}

		// check if license expired
		if ( $license->is_expired() ) {
			wp_die( sprintf( __( 'License has expired. You can renew it here: %s', 'license-wp' ), $license->get_renewal_url() ) );
		}

		// check if this license is owned by logged in user
		if ( is_user_logged_in() && $license->get_user_id() != get_current_user_id() ) {
			wp_die( __( 'This license does not appear to be yours.', 'license-wp' ) );
		}

		// check if activation email is correct
		if ( ! is_email( $activation_email ) || $activation_email != $license->get_activation_email() ) {
			wp_die( __( 'Invalid activation email address.', 'license-wp' ) );
		}

		// get api products linked to license
		$api_products = $license->get_api_products();

		// store api product ids in array
		$api_products_ids = array();
		if ( count( $api_products ) > 0 ) {
			foreach ( $api_products as $api_product ) {
				$api_products_ids[] = $api_product->get_id();
			}
		}

		// check if license grants access to request api product
		if ( ! in_array( $product_id, $api_products_ids ) ) {
			wp_die( __( 'This license does not allow access to the requested product.', 'license-wp' ) );
		}

		// get actual API product
		/** @var \Never5\LicenseWP\ApiProduct\ApiProduct $api_product */
		$api_product = license_wp()->service( 'api_product_factory' )->make( $product_id );

		// check if there's a package defined
		if ( $api_product->get_package() == '' ) {
			wp_die( __( 'Download package is missing.', 'license-wp' ) );
		}

		// log request before we start download
		license_wp()->service( 'log' )->insert( $product_id, $license_key, $activation_email );

		// download file
		$this->download( $api_product->get_package() );

	}

	/**
	 * Send the actual file to the requester
	 *
	 * @param string $file_path
	 */
	private function download( $file_path ) {
		global $is_IE;

		if ( ! $file_path ) {
			wp_die( __( 'No file defined', 'license-wp' ) . ' <a href="' . esc_url( home_url() ) . '" class="wc-forward">' . __( 'Go to homepage', 'license-wp' ) . '</a>' );
		}

		$remote_file      = true;
		$parsed_file_path = parse_url( $file_path );

		$wp_uploads     = wp_upload_dir();
		$wp_uploads_dir = $wp_uploads['basedir'];
		$wp_uploads_url = $wp_uploads['baseurl'];

		if ( ( ! isset( $parsed_file_path['scheme'] ) || ! in_array( $parsed_file_path['scheme'], array(
					'http',
					'https',
					'ftp'
				) ) ) && isset( $parsed_file_path['path'] ) && file_exists( $parsed_file_path['path'] )
		) {

			/** This is an absolute path */
			$remote_file = false;

		} elseif ( strpos( $file_path, $wp_uploads_url ) !== false ) {

			/** This is a local file given by URL so we need to figure out the path */
			$remote_file = false;
			$file_path   = str_replace( $wp_uploads_url, $wp_uploads_dir, $file_path );

		} elseif ( is_multisite() && ( strpos( $file_path, network_site_url( '/', 'http' ) ) !== false || strpos( $file_path, network_site_url( '/', 'https' ) ) !== false ) ) {

			/** This is a local file outside of wp-content so figure out the path */
			$remote_file = false;
			// Try to replace network url
			$file_path = str_replace( network_site_url( '/', 'https' ), ABSPATH, $file_path );
			$file_path = str_replace( network_site_url( '/', 'http' ), ABSPATH, $file_path );
			// Try to replace upload URL
			$file_path = str_replace( $wp_uploads_url, $wp_uploads_dir, $file_path );

		} elseif ( strpos( $file_path, site_url( '/', 'http' ) ) !== false || strpos( $file_path, site_url( '/', 'https' ) ) !== false ) {

			/** This is a local file outside of wp-content so figure out the path */
			$remote_file = false;
			$file_path   = str_replace( site_url( '/', 'https' ), ABSPATH, $file_path );
			$file_path   = str_replace( site_url( '/', 'http' ), ABSPATH, $file_path );

		} elseif ( file_exists( ABSPATH . $file_path ) ) {

			/** Path needs an abspath to work */
			$remote_file = false;
			$file_path   = ABSPATH . $file_path;
		}

		if ( ! $remote_file ) {
			// Remove Query String
			if ( strstr( $file_path, '?' ) ) {
				$file_path = current( explode( '?', $file_path ) );
			}

			// Run realpath
			$file_path = realpath( $file_path );
		}

		// Get extension and type
		$file_extension = strtolower( substr( strrchr( $file_path, "." ), 1 ) );
		$ctype          = "application/force-download";

		foreach ( get_allowed_mime_types() as $mime => $type ) {
			$mimes = explode( '|', $mime );
			if ( in_array( $file_extension, $mimes ) ) {
				$ctype = $type;
				break;
			}
		}

		// Start setting headers
		if ( ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 0 );
		}

		if ( function_exists( 'get_magic_quotes_runtime' ) && get_magic_quotes_runtime() ) {
			@set_magic_quotes_runtime( 0 );
		}

		if ( function_exists( 'apache_setenv' ) ) {
			@apache_setenv( 'no-gzip', 1 );
		}

		@session_write_close();
		@ini_set( 'zlib.output_compression', 'Off' );

		/**
		 * Prevents errors, for example: transfer closed with 3 bytes remaining to read
		 */
		@ob_end_clean(); // Clear the output buffer

		if ( ob_get_level() ) {

			$levels = ob_get_level();

			for ( $i = 0; $i < $levels; $i ++ ) {
				@ob_end_clean(); // Zip corruption fix
			}

		}

		if ( $is_IE && is_ssl() ) {
			// IE bug prevents download via SSL when Cache Control and Pragma no-cache headers set.
			header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
			header( 'Cache-Control: private' );
		} else {
			nocache_headers();
		}

		$filename = basename( $file_path );

		if ( strstr( $filename, '?' ) ) {
			$filename = current( explode( '?', $filename ) );
		}

		header( "X-Robots-Tag: noindex, nofollow", true );
		header( "Content-Type: " . $ctype );
		header( "Content-Description: File Transfer" );
		header( "Content-Disposition: attachment; filename=\"" . $filename . "\";" );
		header( "Content-Transfer-Encoding: binary" );

		if ( $size = @filesize( $file_path ) ) {
			header( "Content-Length: " . $size );
		}

		if ( $remote_file ) {
			$this->readfile_chunked( $file_path ) or header( 'Location: ' . $file_path );
		} else {
			$this->readfile_chunked( $file_path ) or wp_die( __( 'File not found', 'license-wp' ) . ' <a href="' . esc_url( home_url() ) . '" class="wc-forward">' . __( 'Go to homepage', 'license-wp' ) . '</a>' );
		}

		exit;
	}

	/**
	 * readfile_chunked
	 * Reads file in chunks so big downloads are possible without changing PHP.INI - http://codeigniter.com/wiki/Download_helper_for_large_files/
	 *
	 * @param    string $file
	 * @param    bool $retbytes return bytes of file
	 *
	 * @return bool|int
	 */
	private function readfile_chunked( $file, $retbytes = true ) {
		$chunksize = 1 * ( 1024 * 1024 );
		$buffer    = '';
		$cnt       = 0;

		$handle = @fopen( $file, 'r' );
		if ( $handle === false ) {
			return false;
		}

		while ( ! feof( $handle ) ) {
			$buffer = fread( $handle, $chunksize );
			echo $buffer;
			@ob_flush();
			@flush();

			if ( $retbytes ) {
				$cnt += strlen( $buffer );
			}
		}

		$status = fclose( $handle );

		if ( $retbytes && $status ) {
			return $cnt;
		}

		return $status;
	}

}