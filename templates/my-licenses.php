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

			// get the WooCommere product
			$wc_product = \Never5\LicenseWP\WooCommerce\Product::get_product( $license->get_product_id() );

			// get activations
			$activations = $license->get_activations();
			?>
			<tr>
				<td rowspan="<?php echo( ( ! $license->is_expired() ) ? sizeof( $activations ) + 1 : 1 ); ?>" class="lwp_licenses_name"><?php echo esc_html( $wc_product->post_title ); ?></td>
				<td class="lwp_licenses_code">
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
				<td class="lwp_licenses_activation_limit"><?php
					if ( $license->get_activation_limit() > 0 ) {
						printf( __( '%d per product', 'license-wp' ), absint( $license->get_activation_limit() ) );

						// only show upgrade for non-expired
						if( ! $license->is_expired() ) {
							// get available upgrade license options
							$license_options = \Never5\LicenseWP\WooCommerce\Product::get_available_upgrade_options( wc_get_product( $license->get_product_id() ), $license );

							// check if there are upgrade options available
							if ( count( $license_options ) > 0 ) {
								echo '<br/><a class="button lwp_button_upgrade" href="' . $license->get_upgrade_url() . '">' . __( 'Upgrade License', 'license-wp' ) . '</a>';
							}
						}

					} else {
						_e( 'Unlimited', 'license-wp' );
					}
					?></td>
				<td class="lwp_licenses_download"><?php
					if ( $license->is_expired() ) {
						echo '<a class="button lwp_button_renew" href="' . $license->get_renewal_url() . '">' . __( 'Renew License', 'license-wp' ) . '</a>';
					} else {

						// get API products
						$api_products = $license->get_api_products();

						if ( count( $api_products ) > 0 ) {
							echo '<ul class="digital-downloads">';
							foreach ( $api_products as $api_product ) {
								echo '<li><a class="lwp-download-button" href="' . $api_product->get_download_url( $license ) . '">' . $api_product->get_name() . ' (v' . $api_product->get_version() . ')</a></li>';
							}
							echo '</ul>';
						}
					}
					?></td>
			</tr>
			<?php if( ! $license->is_expired() ) : ?>
			<?php foreach ( $activations as $activation ) : ?>
			<?php
			/** @var \Never5\LicenseWP\Activation\Activation $activation */
			?>
			<tr>
				<td colspan="3" class="lwp_licenses_activation">
					<?php echo get_the_title(  $activation->get_api_product_post_id() ); ?> &mdash; <a href="<?php echo esc_attr( $activation->get_instance() ); ?>" target="_blank"><?php echo esc_html( $activation->get_instance() ); ?></a> <a class="button" style="float:right" href="<?php echo $activation->get_deactivate_url($license); ?>"><?php _e( 'Deactivate', 'license-wp' ); ?></a>
				</td>
			</tr>
		<?php endforeach; ?>
		<?php endif; ?>
		<?php endforeach; ?>
		</tbody>
	</table>

<?php endif; ?>
