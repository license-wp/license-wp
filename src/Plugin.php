<?php
namespace Never5\LicenseWP;

class Plugin extends Pimple\Container {

	/** @var string */
	private $version = '1.0';

	/**
	 * Constructor
	 *
	 * @param string $version
	 * @param string $file
	 */
	public function __construct( $version, $file ) {

		// set version
		$this->version = $version;

		// Pimple Container construct
		parent::__construct();

		// setup custom database tables
		$this->setup_db_tables();

		// register file service
		$this['file'] = function () use ( $file ) {
			return new File( $file );
		};

		// register services early since some add-ons need 'm
		$this->register_services();

		// load the plugin
		$this->load();
	}

	/**
	 * Setup custom tables to $wpdb object
	 */
	private function setup_db_tables() {
		global $wpdb;

		$wpdb->lwp_licenses     = $wpdb->prefix . 'license_wp_licenses';
		$wpdb->lwp_activations  = $wpdb->prefix . 'license_wp_activations';
		$wpdb->lwp_download_log = $wpdb->prefix . 'license_wp_download_log';

	}

	/**
	 * Register services
	 */
	private function register_services() {
		$provider = new PluginServiceProvider();
		$provider->register( $this );
	}

	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get service
	 *
	 * @param String $key
	 *
	 * @return mixed
	 */
	public function service( $key ) {
		return $this[ $key ];
	}

	/**
	 * Start loading classes on `plugins_loaded`, priority 20.
	 */
	private function load() {
		$container = $this;

		// Backend & Frontend
		$api_product = new ApiProduct\PostType();
		$api_product->setup();

		// WooCommerce product
		$wc_product = new WooCommerce\Product();
		$wc_product->setup();

		// WooCommerce order
		$wc_order = new WooCommerce\Order();
		$wc_order->setup();

		// WooCommerce email
		$wc_email = new WooCommerce\Email();
		$wc_email->setup();

		// WooCommerce my account
		$wc_my_account = new WooCommerce\MyAccount();
		$wc_my_account->setup();

		if ( is_admin() ) { // Backend

			// meta box
			$mb_api_product_data = new Admin\MetaBox\ApiProductData();
			$mb_api_product_data->register();

			// setup pages
			$page_manager = new Admin\Page\Manager();
			$page_manager->setup();

			// admin assets
			add_action( 'admin_enqueue_scripts', array( 'Never5\\LicenseWP\\Assets', 'enqueue_backend' ) );

		} else { // Frontend

			// setup lost license form shortcode
			new Shortcode\LostLicenseForm();

			// frontend assets
			add_action( 'wp_enqueue_scripts', array( 'Never5\\LicenseWP\\Assets', 'enqueue_frontend' ) );

		}

	}

}


class WP_Plugin_Licencing {

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'woocommerce_api_wp_plugin_licencing_activation_api', array(
			$this,
			'handle_activation_api_request'
		) );
		add_action( 'woocommerce_api_wp_plugin_licencing_update_api', array( $this, 'handle_update_api_request' ) );
	}


	/**
	 * Activation
	 */
	public function handle_activation_api_request() {
		include_once( 'includes/class-wp-plugin-licencing-activation-api.php' );
		new WP_Plugin_Licencing_Activation_API( $_REQUEST );
	}

	/**
	 * Plugin updates
	 */
	public function handle_update_api_request() {
		include_once( 'includes/class-wp-plugin-licencing-update-api.php' );
		new WP_Plugin_Licencing_Update_API( $_REQUEST );
	}
}