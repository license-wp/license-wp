<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/** @var $license \Never5\LicenseWP\License\License */
/** @var $product WC_Product_Variable */

// find current license variation term (for name in table)
$current_license_term = \Never5\LicenseWP\WooCommerce\Product::get_license_term_of_product( $product );

// we need a license at this point
if ( empty( $license ) ) {
	return;
}

// we need a product
if ( empty( $product ) ) {
	return;
}

// our product needs to be a variation
if ( 'variation' != $product->product_type ) {
	return;
}

// our product parent must be a variable
if ( 'variable' != $product->parent->product_type ) {
	return;
}

// get available upgrade license options
$license_options = \Never5\LicenseWP\WooCommerce\Product::get_available_upgrade_options( $product, $license );

?>

<form method="post" id="lwp-upgrade-license-form">

	<p><?php _e( "Please review your license data below and select the license you'd like to upgrade to.", 'license-wp' ); ?></p>

	<div class="lwp-upgrade-license-block lwp-upgrade-license-current-license">
		<h3><?php _e( 'Current License', 'license-wp' ); ?></h3>
		<p class="form-row form-row-wide">
			<label><?php _e( 'License Key', 'license-wp' ); ?></label>
			<span><?php echo $license->get_key(); ?></span>
		</p>

		<p class="form-row form-row-wide">
			<label><?php _e( 'Current License', 'license-wp' ); ?></label>
			<span><?php echo $product->get_title(); ?> - <?php echo $current_license_term->name; ?>
				<small>(<?php echo( ( $license->get_activation_limit() > 0 ) ? sprintf( __( '%d websites per product', 'license-wp' ), absint( $license->get_activation_limit() ) ) : __( 'Unlimited', 'license-wp' ) ); ?>)</small></span>
		</p>

		<p class="form-row form-row-wide">
			<label><?php _e( 'Expiration Date', 'license-wp' ); ?></label>
			<span><?php echo $license->get_date_expires()->format( get_option( 'date_format' ) ); ?></span>
		</p>
	</div>

	<div class="lwp-upgrade-license-block lwp-upgrade-license-new-license">
		<h3><?php _e( 'Upgrade Options', 'license-wp' ); ?></h3>
		<p class="form-row form-row-wide">
			<label for="lwp_new_license"><?php _e( 'Select New License', 'license-wp' ); ?></label>
			<select name="new_license" id="lwp_new_license">
				<?php if ( ! empty( $license_options ) ) : ?>
					<?php foreach ( $license_options as $license_option ) : ?>
						<option value="<?php echo esc_attr( $license_option['id'] ); ?>" data-upgrade_price="<?php echo esc_attr( $license_option['upgrade_price'] ); ?>"><?php echo $license_option['title']; ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</p>

		<p class="form-row form-row-wide">
			<label><?php _e( 'Price to Upgrade', 'license-wp' ); ?></label>
			<span class="lwp-upgrade-license-price" id="lwp-upgrade-license-price">&nbsp;</span>
		</p>
		<?php do_action( 'license_wp_license_upgrade_fields_after_price' ); ?>


	</div>

	<div class="clear"></div>
	<p>
		<input type="hidden" name="license_key" value="<?php echo esc_attr( $license->get_key() ); ?>" />
		<input type="submit" class="button" name="submit_upgrade_license" value="<?php _e( 'Upgrade License', 'license-wp' ); ?>"/>
	</p>

	<?php do_action( 'license_wp_license_upgrade_after_fields' ); ?>

</form>
