<?php
namespace Never5\LicenseWP\Shortcode;

class LostLicenseForm {

	/**
	 * __constructor
	 */
	public function __construct() {
		add_shortcode( 'lost_license_key_form', array( $this, 'view' ) );
	}

	/**
	 * Shortcode view callback
	 *
	 * @return string
	 */
	public function view() {
		ob_start();

		// load template file via WooCommerce template function
		wc_get_template( 'lost-license-form.php', array(), 'license-wp', license_wp()->service( 'file' )->plugin_path() . '/templates/' );

		return ob_get_clean();
	}

}