<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly ?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<?php echo $body; ?>

<?php do_action( 'woocommerce_email_footer' ); ?>
