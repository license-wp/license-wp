<?php
/**
 * Lost license form
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//var_dump($license);

//var_dump($product);

/** @var $license \Never5\LicenseWP\License\License */
/** @var $product WC_Product_Variable */

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


// get variation related data
$available_variations = $product->parent->get_available_variations();
//$attributes           = $product->parent->get_variation_attributes();

//var_dump($available_variations);
//var_dump($attributes);

// we need available variations
if ( empty( $available_variations ) ) {
	return;
}

// fetch and store license options in variable
$license_options = array();

// loop and check a bunch of license variation required props
foreach ( $available_variations as $variation ) {
	if ( is_array( $variation ) ) {

		// get variation product
		$variation_product = wc_get_product( $variation['variation_id'] );

		// check
		if ( ! empty( $variation_product ) && $variation_product->is_purchasable() && absint( $variation_product->license_activation_limit ) > $license->get_activation_limit() ) {

			// take first variation attribute of an API licensed product
			foreach ( $variation_product->get_variation_attributes() as $vp_key => $vp_val ) {

				// get attr tax slug from attr name
				$attr_slug = sanitize_title( substr( $vp_key, 10 ) );

				// check if exists
				if ( taxonomy_exists( $attr_slug ) ) {

					// get term
					$term = get_term_by( 'slug', $vp_val, $attr_slug );

					// finally add to array
					$license_options[ esc_attr( $term->slug ) ] = array(
						'title' => $term->name . ' - ' . sprintf( __( 'Up to %d Websites', 'license-wp' ), absint( $variation_product->license_activation_limit ) ),
						'price' => $variation_product->get_price()
					);

					// got term, break
					break;
				}
			}
		}
	}
}

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
			<label for="license-product"><?php _e( 'Current License', 'license-wp' ); ?></label>
			<span><?php echo $product->get_title(); ?>
				(<?php echo( ( $license->get_activation_limit() > 0 ) ? sprintf( __( '%d websites per product', 'license-wp' ), absint( $license->get_activation_limit() ) ) : __( 'Unlimited', 'license-wp' ) ); ?>
				)</span>
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
				<?php if ( ! empty( $license_options ) ) : ?>
					<?php foreach ( $license_options as $lk => $lv ) : ?>
						<option value="<?php echo $lk; ?>" data-price="<?php absint($lv['price']); ?>"><?php echo $lv['title']; ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</p>

		<p class="form-row form-row-wide">
			<label for="license-product"><?php _e( 'Price to Upgrade', 'license-wp' ); ?></label>
			<span class="lwp-upgrade-license-price">$39.00</span>
		</p>


		<?php
		/*
		<p class="form-row form-row-wide">
			<label for="license-product"><?php _e( 'Expiration Date', 'license-wp' ); ?></label>
			<span><?php echo $license->get_date_expires()->format( get_option( 'date_format' ) ); ?></span>
		</p>
		*/
		?>

	</div>

	<?php /*
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
*/ ?>

	<div class="clear"></div>
	<p>
		<input type="submit" class="button" name="submit_upgrade_license"
		       value="<?php _e( 'Upgrade License', 'license-wp' ); ?>"/>
	</p>

	<?php do_action( 'license_wp_license_upgrade_after_fields' ); ?>

</form>
