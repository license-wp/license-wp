<?php

namespace Never5\LicenseWP\Admin\Page;

use \Never5\LicenseWP\Activation;

/**
 * Class AddLicenses
 * @package Never5\LicenseWP\Admin\Pages
 */
class Activations extends SubPage {

	/**
	 * __construct
	 */
	public function __construct() {
		parent::__construct( 'license_wp_licenses', __( 'Activations', 'license-wp' ) );
	}

	/**
	 * Output page content
	 *
	 * @return void
	 */
	public function output() {
		// create list table
		$list_table = new Activation\ListTable();

		// prepare items in list table
		$list_table->prepare_items();
		?>
		<div class="wrap">
			<h2><?php _e( 'Activations', 'license-wp' ); ?></h2>

			<form id="license-management" method="post">
				<input type="hidden" name="page" value="license_wp_licenses"/>
				<?php $list_table->display() ?>
				<?php wp_nonce_field( 'save', 'license_wp_licensing_nonce' ); ?>
			</form>
		</div>
		<?php
	}

}
