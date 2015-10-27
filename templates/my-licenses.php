<?php

if ( sizeof( $licenses ) > 0 ) : ?>

	<h2><?php _e( 'Licenses', 'license-wp' ); ?></h2>
	<table class="shop_table my_account_orders my_account_api_license_keys">
		<thead>
		<tr>
			<th><?php _e( 'Product name', 'license-wp' ); ?></th>
			<th><?php _e( 'License key', 'license-wp' ); ?></th>
			<th><?php _e( 'Activation limit', 'license-wp' ); ?></th>
			<th><?php _e( 'Download/Renew', 'license-wp' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $licenses as $license ) :

			/** @var \Never5\LicenseWP\License\License $license */
			$license     = $license;

			// get the WooCommere product
			$wc_product = \Never5\LicenseWP\WooCommerce\Product::get_product( $license->get_product_id() );

			// get activations
			$activations = $license->get_activations();

			?>
			<tr>
				<td rowspan="<?php echo sizeof( $activations ) + 1; ?>"><?php echo esc_html( $wc_product->post_title ); ?></td>
				<td>
					<code style="display:block;"><?php echo $license->get_key(); ?></code>
					<small>
						<?php printf( __( 'Activation email: %s', 'license-wp' ), $license->get_activation_email() ); ?><br/>
						<?php if ( $license->get_date_expires() ) : ?>
							<?php if ( ! $license->is_expired() ) : ?>
								<?php printf( __( 'Expiry date: %s.', 'license-wp' ), $license->get_date_expires()->format( get_option( 'date_format' ) ) ); ?>
							<?php else: ?>
								<?php echo '<span style="color:#ff0000;font-weight:bold;">' . sprintf( __( 'Expired on %s', 'license-wp' ), $license->get_date_expires()->format( get_option( 'date_format' ) ) ) . '</span>'; ?>
							<?php endif; ?>
						<?php endif; ?>
					</small>
				</td>
				<td><?php echo( ( $license->get_activation_limit() > 0 ) ? sprintf( __( '%d per product', 'license-wp' ), absint( $license->get_activation_limit() ) ) : __( 'Unlimited', 'license-wp' ) ); ?></td>
				<td><?php
					if ( $license->is_expired() ) {
						echo '<a class="button" href="' . $license->get_renewal_url() . '">' . __( 'Renew License', 'license-wp' ) . '</a>';
					} else {

						// get API products
						$api_products = $license->get_api_products();

						if ( count( $api_products ) > 0 ) {
							echo '<ul class="digital-downloads">';
							foreach ( $api_products as $api_product ) {
								echo '<li><a class="download-button" href="' . $api_product->get_download_url( $license ) . '">' . $api_product->get_name() . ' (v' . $api_product->get_version() . ')</a></li>';
							}
							echo '</ul>';
						}
					}
					?></td>
			</tr>
			<?php foreach ( $activations as $activation ) : ?>
			<?php
			/** @var \Never5\LicenseWP\Activation\Activation $activation */
			$activation = $activation;
			?>
			<tr>
				<td colspan="3">
					<?php echo get_the_title(  $activation->get_api_product_post_id() ); ?> &mdash; <a href="<?php echo esc_attr( $activation->get_instance() ); ?>" target="_blank"><?php echo esc_html( $activation->get_instance() ); ?></a> <a class="button" style="float:right" href="<?php echo $activation->get_deactivate_url($license); ?>"><?php _e( 'Deactivate', 'license-wp' ); ?></a>
				</td>
			</tr>
		<?php endforeach; ?>
		<?php endforeach; ?>
		</tbody>
	</table>

<?php endif; ?>
