<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<tr class="show_if_is_api_product_license">
	<td>
		<div>
			<?php woocommerce_wp_text_input( array(
				'id'                => '_variation_license_activation_limit_' . $loop,
				'name'              => '_variation_license_activation_limit[' . $loop . ']',
				'label'             => __( 'License activation limit', 'wp-plugin-licencing' ) . ':',
				'placeholder'       => __( 'Inherit from parent', 'wp-plugin-licencing' ),
				'value'             => get_post_meta( $variation->ID, '_license_activation_limit', true ),
				'type'              => 'number',
				'class'             => '',
				'wrapper_class'     => 'form-row form-row-full',
				'custom_attributes' => array(
					'min'  => '',
					'step' => '1'
				)
			) ); ?>
		</div>
	</td>
	<td>
		<div>
			<?php woocommerce_wp_text_input( array(
				'id'                => '_variation_license_expiry_amount_'.$loop,
				'name'              => '_variation_license_expiry_amount[' . $loop . ']',
				'label'             => __( 'License expiry amount', 'license-wp' ),
				'placeholder'       => __( 'Never expire', 'license-wp' ),
				'description'       => __( 'How many days/months/years until the license expires. Leave blank for never.', 'license-wp' ),
				'value'             => get_post_meta( $variation->ID, '_license_expiry_amount', true ),
				'desc_tip'          => true,
				'type'              => 'number',
				'wrapper_class'     => 'form-row form-row-full',
				'custom_attributes' => array(
					'min'   => '',
					'step' 	=> '1'
				) ) ); ?>
		</div>
	</td>
    <td>
        <div>
			<?php woocommerce_wp_select( array(
				'id'                => '_variation_license_expiry_type_'.$loop,
				'name'              => '_variation_license_expiry_type[' . $loop . ']',
				'label'             => __( 'License expiry type', 'license-wp' ),
				'placeholder'       => __( 'Days', 'license-wp' ),
				'description'       => __( 'The date type used for license expiry amount.', 'license-wp' ),
				'value'             => get_post_meta( $variation->ID, '_license_expiry_type', true ),
				'desc_tip'          => true,
				'type'              => 'number',
				'wrapper_class'     => 'form-row form-row-full',
				'options'           => array(
					'days'   => 'Days',
					'months' => 'Months',
					'years'  => 'Years',
				) ) ); ?>
        </div>
    </td>
</tr>
