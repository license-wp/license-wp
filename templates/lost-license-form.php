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

	<p class="form-row form-row-first">
		<label for="account_first_name"><?php _e( 'Activation email', 'license-wp' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="activation_email" id="activation_email" value="" />
	</p>
	<div class="clear"></div>
	<p>
		<input type="submit" class="button" name="submit_lost_license_form" value="<?php _e( 'Email my keys', 'license-wp' ); ?>" />
	</p>

</form>
