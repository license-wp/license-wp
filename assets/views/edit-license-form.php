<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h2><?php printf( __( 'Edit License "%s"', 'license-wp' ), esc_html( $license_key ) ); ?></h2>
	<form id="license-add-form" method="post">
		<input type="hidden" name="page" value="license_wp_edit_license" />
		<table class="form-table">
			<tr>
				<th>
					<label for="product_id"><?php _e( 'Product', 'license-wp' ); ?></label>
				</th>
				<td>
					<select name="product_id" class="lwp-select2" data-placeholder="<?php _e( 'Choose a product&hellip;', 'license-wp' ) ?>" style="width:30em">
						<?php
						echo '<option value=""></option>';

						$args = array(
							'post_type'      => 'product',
							'posts_per_page' => -1,
							'post_status'    => 'publish',
							'order'          => 'ASC',
							'orderby'        => 'title',
							'meta_query'     => array(
								array(
									'key'   => '_is_api_product_license',
									'value' => 'yes'
								)
							)
						);

						$products = get_posts( $args );

						if ( $products ) {
							foreach ( $products as $product ) {
								$args_get_children = array(
									'post_type'      => array( 'product_variation', 'product' ),
									'posts_per_page' => -1,
									'order'          => 'ASC',
									'orderby'        => 'title',
									'post_parent'    => $product->ID
								);

								if ( $children_products = get_children( $args_get_children ) ) {
									echo '<optgroup label="' . esc_attr( $product->post_title ) . '">';
									foreach ( $children_products as $child ) {
										$child_product = wc_get_product( $child );
										$attributes    = $child_product->get_variation_attributes();
										$extra_data    = ' &ndash; ' . implode( ', ', $attributes ) . ' &ndash; ' . wc_price( $child_product->get_price() );
										echo '<option value="' . absint( $child->ID ) . '" ' . selected( absint( $child->ID ), $license->get_product_id(), false ) . '>&nbsp;&nbsp;&mdash;&nbsp;' . $child_product->get_title() . $extra_data . '</option>';
									}
									echo '</optgroup>';
								} else {
									echo '<option value="' . $product->ID . '" ' . selected( absint( $product->ID ), $license->get_product_id(), false ) . '>' . $product->post_title . '</option>';
								}
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label for="user_id"><?php _e( 'Customer ID', 'license-wp' ); ?></label>
				</th>
				<td>
					<input type="text" name="user_id" value="<?php echo esc_attr( $license->get_user_id() ); ?>" class="input-text regular-text" placeholder="<?php _e( 'Guest', 'license-wp' ); ?>" style="width:30em" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="activation_email"><?php _e( 'Activation email', 'license-wp' ); ?></label>
				</th>
				<td>
					<input type="email" name="activation_email" value="<?php echo esc_attr( $license->get_activation_email() ); ?>" id="activation_email" class="input-text regular-text" placeholder="<?php _e( 'Email address used to activate product', 'license-wp' ); ?>" style="width:30em" />
				</td>
			</tr>
            <tr>
				<th>
					<label for="order_id"><?php _e( 'Order ID', 'license-wp' ); ?></label>
				</th>
				<td>
					<input type="text" name="order_id" value="<?php echo esc_attr( $license->get_order_id() ); ?>" id="order_id" class="input-text regular-text" placeholder="<?php _e( 'Order ID associated with this license', 'license-wp' ); ?>" style="width:30em" />
				</td>
			</tr>
            <tr>
				<th>
					<label for="activation_limit"><?php _e( 'Activation limit', 'license-wp' ); ?></label>
				</th>
				<td>
					<input type="text" name="activation_limit" value="<?php echo esc_attr( $license->get_activation_limit() ); ?>" id="activation_limit" class="input-text regular-text" placeholder="<?php _e( 'Activation limit for this license (default is unlimited)', 'license-wp' ); ?>" style="width:30em" />
				</td>
			</tr>
            <tr>
				<th>
					<label for="date_expires"><?php _e( 'Expires', 'license-wp' ); ?></label>
				</th>
				<td>
					<input type="text" name="date_expires" value="<?php echo esc_attr( $license->get_date_expires() ? $license->get_date_expires()->format( 'Y-m-d' ) : '' ); ?>" id="date_expires" class="input-text regular-text license-wp-datepicker" placeholder="<?php _e( 'Date expires (default is never)', 'license-wp' ); ?>" style="width:30em" />
				</td>
			</tr>
		</table>

		<p class="submit">
			<input type="submit" class="button button-primary" name="edit_license" value="<?php _e( 'Save License', 'license-wp' ); ?>" />
		</p>

		<?php wp_nonce_field( 'edit_license', 'license_wp_licensing_nonce' ); ?>
	</form>
</div>
