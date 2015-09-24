<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h2><?php _e( 'Add License', 'license-wp' ); ?></h2>
	<form id="license-add-form" method="post">
		<input type="hidden" name="page" value="license_wp_add_license" />
		<p><?php _e( 'Create a license manually using the form below. The license key will be generated automatically and will be emailed to the customer.', 'license-wp' ); ?></p>
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
										echo '<option value="' . absint( $child->ID ) . '">&nbsp;&nbsp;&mdash;&nbsp;' . $child_product->get_title() . $extra_data . '</option>';
									}
									echo '</optgroup>';
								} else {
									echo '<option value="' . $product->ID . '">' . $product->post_title . '</option>';
								}
							}
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<label for="user_id"><?php _e( 'Customer', 'license-wp' ); ?></label>
				</th>
				<td>
					<input type="text" name="user_id" class="input-text regular-text lwp-select2-customer" placeholder="<?php _e( 'Guest', 'license-wp' ); ?>" data-nonce="<?php echo wp_create_nonce( "search-customers" ); ?>" style="width:30em" />
				</td>
			</tr>
			<tr>
				<th>
					<label for="activation_email"><?php _e( 'Activation email', 'license-wp' ); ?></label>
				</th>
				<td>
					<input type="email" name="activation_email" id="activation_email" class="input-text regular-text" placeholder="<?php _e( 'Email address used to activate product', 'license-wp' ); ?>" style="width:30em" />
				</td>
			</tr>
		</table>

		<p class="submit">
			<input type="submit" class="button button-primary" name="add_license" value="<?php _e( 'Add License', 'license-wp' ); ?>" />
		</p>

		<?php wp_nonce_field( 'add_license', 'license_wp_licensing_nonce' ); ?>
	</form>
</div>
