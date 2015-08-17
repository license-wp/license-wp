<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<tr class="show_if_is_api_product_licence">
	<td>
		<div>
			<?php woocommerce_wp_text_input( array(
				'id'                => '_variation_licence_activation_limit_' . $loop,
				'name'              => '_variation_licence_activation_limit[' . $loop . ']',
				'label'             => __( 'Licence activation limit', 'wp-plugin-licencing' ) . ':',
				'placeholder'       => __( 'Inherit from parent', 'wp-plugin-licencing' ),
				'value'             => get_post_meta( $variation->ID, '_licence_activation_limit', true ),
				'type'              => 'number',
				'class'             => '',
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
				'id'                => '_variation_licence_expiry_days_' . $loop,
				'name'              => '_variation_licence_expiry_days[' . $loop . ']',
				'label'             => __( 'Licence expiry days', 'wp-plugin-licencing' ) . ':',
				'placeholder'       => __( 'Inherit from parent', 'wp-plugin-licencing' ),
				'value'             => get_post_meta( $variation->ID, '_licence_expiry_days', true ),
				'type'              => 'number',
				'class'             => '',
				'custom_attributes' => array(
					'min'  => '',
					'step' => '1'
				)
			) ); ?>
		</div>
	</td>
</tr>