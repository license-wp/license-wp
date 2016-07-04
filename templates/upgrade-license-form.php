<?php
/**
 * Lost license form
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

var_dump($license);

var_dump($product);

/** @var $license \Never5\LicenseWP\License\License */
/** @var $product WC_Product */
//$license;
?>

<?php wc_print_notices(); ?>

<form method="post" id="lwp-upgrade-license-form">

	<p><?php _e( "Please review your license data below and select the license you'd like to upgrade to.", 'license-wp' ); ?></p>

	<div class="lwp-upgrade-license-block lwp-upgrade-license-current-license">
		<h3><?php _e( 'Current License', 'license-wp' ); ?></h3>
		<p class="form-row form-row-wide">
			<label for="license-key"><?php _e( 'License Key', 'license-wp' ); ?></label>
			<span><?php echo $license->get_key(); ?></span>
		</p>

		<p class="form-row form-row-wide">
			<label for="license-product"><?php _e( 'Product', 'license-wp' ); ?></label>
			<span><?php echo $product->get_title(); ?></span>
		</p>

		<p class="form-row form-row-wide">
			<label for="license-product"><?php _e( 'Current License', 'license-wp' ); ?></label>
			<span><?php echo $product->get_title(); ?> (<?php echo( ( $license->get_activation_limit() > 0 ) ? sprintf( __( '%d websites per product', 'license-wp' ), absint( $license->get_activation_limit() ) ) : __( 'Unlimited', 'license-wp' ) ); ?>)</span>
		</p>

		<p class="form-row form-row-wide">
			<label for="license-product"><?php _e( 'Expiration Date', 'license-wp' ); ?></label>
			<span><?php echo $license->get_date_expires()->format( get_option( 'date_format' ) ); ?></span>
		</p>
	</div>

	<div class="lwp-upgrade-license-block lwp-upgrade-license-new-license">
		<h3><?php _e( 'Upgrade Options', 'license-wp' ); ?></h3>
		<p class="form-row form-row-wide">
			<label for="license-key"><?php _e( 'Select New License', 'license-wp' ); ?></label>
			<select name="new_license">
				<option value="0">Business License - 5 websites</option>
				<option value="0">Test Option 2</option>
			</select>
		</p>

		<p class="form-row form-row-wide">
			<label for="license-product"><?php _e( 'Expiration Date', 'license-wp' ); ?></label>
			<span><?php echo $license->get_date_expires()->format( get_option( 'date_format' ) ); ?></span>
		</p>

	</div>

	<div class="lwp-upgrade-license-block lwp-upgrade-license-new-license">
		<h3><?php _e( 'Price', 'license-wp' ); ?></h3>

		<p class="form-row form-row-wide">
			<label for="license-product"><?php _e( 'License Price', 'license-wp' ); ?></label>
			<span class="lwp-upgrade-license-price">$59.00</span>
		</p>

		<p class="form-row form-row-wide">
			<label for="license-product"><?php _e( 'Upgrade Discount', 'license-wp' ); ?></label>
			<span class="lwp-upgrade-license-price">$20.00</span>
		</p>

		<p class="form-row form-row-wide">
			<label for="license-product"><?php _e( 'Price to Upgrade', 'license-wp' ); ?></label>
			<span class="lwp-upgrade-license-price">$39.00</span>
		</p>

		<?php do_action( 'license_wp_license_upgrade_fields_after_price' ); ?>

	</div>





	<div class="clear"></div>
	<p>
		<input type="submit" class="button" name="submit_upgrade_license" value="<?php _e( 'Upgrade License', 'license-wp' ); ?>" />
	</p>

	<?php do_action( 'license_wp_license_upgrade_after_fields' ); ?>

</form>
