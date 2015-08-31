<?php if ( count( $licenses ) > 0 ) : ?>

	<h2><?php _e( 'License Keys', 'license-wp' ); ?></h2>
	<ul>
		<?php
		foreach ( $licenses as $license ) :
			$wc_product = \Never5\LicenseWP\WooCommerce\Product::get_product( $license->get_product_id() );
			?>
			<li>
				<?php echo esc_html( $wc_product->post_title ); ?>: <strong><?php echo $license->get_key(); ?></strong>
				<?php
				// license products
				$api_products = $license->get_api_products();
				if ( count( $api_products ) > 0 ) {
					echo '<ul class="digital-downloads">';
					foreach ( $api_products as $api_product ) {
						echo '<li><a class="download-button" href="' . $api_product->get_download_url( $license ) . '">' . sprintf( __( 'Download %s', 'license-wp' ), $api_product->get_name() ) . '</a></li>';
					}
					echo '</ul>';
				}
				?>
			</li>
		<?php endforeach; ?>
	</ul>

<?php endif; ?>