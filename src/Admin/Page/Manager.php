<?php

namespace Never5\LicenseWP\Admin\Page;

class Manager {

	/**
	 * Setup pages and page related filters/actions
	 */
	public function setup() {

		// setup pages
		$this->setup_pages();

		// filter WooCommerce screen ID's
		add_filter( 'woocommerce_screen_ids', function ( $ids ) {
			$screen_id = strtolower( __( 'Licenses', 'woocommerce' ) );
			$ids[]     = $screen_id . '_page_license_wp_add_license';
			$ids[]     = 'toplevel_page_license_wp_licenses';
			return $ids;
		} );

		// move our CPT menu item to our custom pages menu items
		add_filter( 'menu_order', array( $this, 'menu_order' ) );
	}

	/**
	 * Setup pages
	 */
	private function setup_pages() {
		// Admin License Page
		$page_license = new Licenses();
		$page_license->setup();

		// Admin Activations Page
		$page_activations = new Activations();
		$page_activations->setup();

		// Admin Add License Page
		$page_add_license = new AddLicense();
		$page_add_license->setup();
	}

	/**
	 * Reorder the menu items in admin.
	 *
	 * @param array $menu_order
	 * @return array
	 */
	public function menu_order( $menu_order ) {
		// Initialize our custom order array
		$modified_menu_order = array();

		// Get index of product menu
		$api_products = array_search( 'edit.php?post_type=api_product', $menu_order );

		// Loop through menu order and do some rearranging
		foreach ( $menu_order as $index => $item ) {

			if ( ( ( 'license_wp_licenses' ) == $item ) ) {
				$modified_menu_order[] = 'edit.php?post_type=api_product';
				$modified_menu_order[] = $item;
				unset( $menu_order[ $api_products ] );
			} else {
				$modified_menu_order[] = $item;
			}
		}

		return $modified_menu_order;
	}
}
