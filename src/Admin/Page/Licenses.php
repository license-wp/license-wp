<?php

namespace Never5\LicenseWP\Admin\Page;

use \Never5\LicenseWP\License;

/**
 * Class Licenses
 * @package Never5\LicenseWP\Admin\Pages
 */
class Licenses extends Page {

	/**
	 * __construct
	 */
	public function __construct() {
		parent::__construct( __( 'Licenses', 'license-wp' ), '55.8' );
	}

	/**
	 * Output page content
	 *
	 * @return void
	 */
	public function output() {
		
		// create list table
		$list_table = new License\ListTable(); 
		
		// prepare items in list table
		$list_table->prepare_items();
		?>
		<div class="wrap">
			<h2><?php _e( 'Licenses', 'license-wp' ); ?> <a
					href="<?php echo admin_url( 'admin.php?page=license_wp_add_license' ); ?>"
					class="add-new-h2"><?php _e( 'Add License', 'license-wp' ); ?></a></h2>

			<form id="licence-management" method="post">
				<input type="hidden" name="page" value="license_wp_licenses"/>
				<?php $list_table->display() ?>
				<?php wp_nonce_field( 'save', 'license_wp_licensing_nonce' ); ?>
			</form>
		</div>
		<?php
	}

}