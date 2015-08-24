<?php
/**
 * New license email
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( $user_first_name ) {
	echo sprintf( __( "Hello %s,", 'license-wp' ), $user_first_name ) . "\n\n";
} else {
	echo __( "Hi there,", 'license-wp' ) . "\n\n";
}
_e( "A license has just been created for you. The details are as follows:", 'license-wp' );
echo "\n";

// license products
$api_products = $license->get_api_products();

// check & loop
if ( isset( $api_products ) && count( $api_products ) > 0 ) {
	foreach ( $api_products as $api_product ) {
		echo "\n====================\n";
		echo esc_html( get_the_title( $api_product->get_id() ) ) . ': ' . $api_product->download_url( $license ) . "\n";
		echo $license->get_key() . "";
		echo "\n====================\n\n";
	}
}

_e( "You can manage your licenses and download your products from your My Account page.", 'license-wp' );
echo "\n";
echo "\n";

// Footer
echo '--' . "\n";
echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );