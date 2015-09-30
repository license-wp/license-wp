<?php
/**
 * New license email
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

do_action( 'woocommerce_email_header', $email_heading );

if ( $user_first_name ) {
	echo sprintf( __( "Hello %s,", 'license-wp' ), $user_first_name ) . "<br/><br/>";
} else {
	echo __( "Hi there,", 'license-wp' ) . "<br/><br/>";
}
_e( "A license has just been created for you. The details are as follows:", 'license-wp' );
echo "<br/>";

// license products
$api_products = $license->get_api_products();

// check & loop
if ( isset( $api_products ) && count( $api_products ) > 0 ) {
	foreach ( $api_products as $api_product ) {
		?>
		<table cellspacing="0" cellpadding="0" border="0" style="padding: 0;margin:0;">
			<tr>
				<td style="padding-left:0 !important;"><?php echo esc_html( get_the_title( $api_product->get_id() ) ); ?></td>
				<td><?php echo $license->get_key(); ?></td>
				<td>
					<a href="<?php echo $api_product->get_download_url( $license ); ?>"><?php _e( 'Download', 'license-wp' ); ?></a>
				</td>
			</tr>
		</table>
		<?php
	}
}

_e( "You can manage your licenses and download your products from your My Account page.", 'license-wp' );
echo "<br/>";
echo "<br/>";

_e( "Best regards,", 'license-wp' );
echo '<br/>';
printf( __( "The %s team", 'license-wp' ), get_bloginfo( 'name' ) );

do_action( 'woocommerce_email_footer' );