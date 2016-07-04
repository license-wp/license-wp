<?php
/**
 * Lost license form
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<?php wc_print_notices(); ?>

<form method="post">

	<p><?php _e( 'Please enter your license key below to locate your license.', 'license-wp' ); ?></p>

	<p class="form-row form-row-first">
		<label for="account_first_name"><?php _e( 'License Key', 'license-wp' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="license_key" id="license_key" value="" placeholder="AAAA-1234-BBBB-5678" />
	</p>
	<div class="clear"></div>
	<p>
		<input type="submit" class="button" name="submit_upgrade_license_find_license" value="<?php _e( 'Find License', 'license-wp' ); ?>" />
	</p>

	<?php do_action( 'license_wp_license_upgrade_find_license_after_fields' ); ?>

</form>
