<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="options_group show_if_is_api_product_license">

	<?php if ( $api_products ) : ?>

		<p class="form-field">
	    	<label><?php _e( 'API Product access', 'wp-plugin-licencing' ); ?></label>
	    	<select id="api_product_permissions" name="api_product_permissions[]" multiple="multiple" data-placeholder="<?php _e( 'Choose API products&hellip;', 'wp-plugin-licencing' ); ?>" class="wc-enhanced-select">
				<?php foreach ( $api_products as $api_product ) : ?>

					<option value="<?php echo esc_attr( $api_product->ID ); ?>" <?php selected( in_array( $api_product->ID, $current_api_products ), true ); ?>><?php echo esc_html( $api_product->post_title ); ?></option>

				<?php endforeach; ?>
	        </select>
        </p>

	<?php endif; ?>

	<?php woocommerce_wp_text_input( array( 
		'id'                => '_license_activation_limit', 
		'label'             => __( 'Licence activation limit', 'wp-plugin-licencing' ),
		'placeholder'       => __( 'Unlimited', 'wp-plugin-licencing' ),
		'description'       => __( 'The maximum number of activations allowed. Leave blank for unlimited.', 'wp-plugin-licencing' ), 
		'value'             => get_post_meta( $post_id, '_license_activation_limit', true ),
		'desc_tip'          => true, 
		'type'              => 'number', 
		'custom_attributes' => array(
			'min'   => '',
			'step' 	=> '1'
		) ) ); ?>

	<?php woocommerce_wp_text_input( array( 
		'id'                => '_license_expiry_days', 
		'label'             => __( 'Licence expiry days', 'wp-plugin-licencing' ), 
		'placeholder'       => __( 'Never expire', 'wp-plugin-licencing' ),
		'description'       => __( 'How many days until the license expires. Leave blank for never.', 'wp-plugin-licencing' ), 
		'value'             => get_post_meta( $post_id, '_license_expiry_days', true ),
		'desc_tip'          => true, 
		'type'              => 'number', 
		'custom_attributes' => array(
			'min'   => '',
			'step' 	=> '1'
		) ) ); ?>

</div>
<script type="text/javascript">
	jQuery(function() {
		jQuery('#_is_api_product_license').change(function() {
			if ( jQuery(this).is( ':checked' ) ) {
				jQuery( '.show_if_is_api_product_license' ).show();
			} else {
				jQuery( '.show_if_is_api_product_license' ).hide();
			}
			jQuery('#api_product_permissions' ).select2({dropdownCssClass : 'license_wp_select2'});
			console.log('test');
		}).change();
	});
</script>