<?php

namespace Never5\LicenseWP\Admin\Page;

use \Never5\LicenseWP\License;

/**
 * Class Licenses
 * @package Never5\LicenseWP\Admin\Pages
 */
class Licenses extends Page {

	/**
	 * __construct
	 */
	public function __construct() {
		parent::__construct( __( 'Licenses', 'license-wp' ), '55.8' );

		// handle save
		add_action( 'init', function () {
			if ( isset( $_POST['edit_license'] ) ) {
				$this->save();
			}
		} );
	}

	/**
	 * Method to enqueue page specific styles & scripts
	 */
	public function page_enqueue() {
		wp_enqueue_script(
			'lwp_add_license',
			license_wp()->service( 'file' )->plugin_url( '/assets/js/add-license' . ( ( ! SCRIPT_DEBUG ) ? '.min' : '' ) . '.js' ),
			array( 'jquery', 'jquery-ui-datepicker', 'select2' ),
			license_wp()->get_version()
		);

	}

	/**
	 * Output page content
	 *
	 * @return void
	 */
	public function output() {
		if ( ! empty( $_GET['edit'] ) ) {
			$this->edit_license();
		} else {
			$this->list_licenses();
		}
	}

	/**
	 * List licences
	 */
	public function list_licenses() {
		// create list table
		$list_table = new License\ListTable();

		// prepare items in list table
		$list_table->prepare_items();
		?>
		<div class="wrap">
			<h2><?php _e( 'Licenses', 'license-wp' ); ?> <a
					href="<?php echo admin_url( 'admin.php?page=license_wp_add_license' ); ?>"
					class="add-new-h2"><?php _e( 'Add License', 'license-wp' ); ?></a></h2>

			<form id="license-management" method="post">
				<input type="hidden" name="page" value="license_wp_licenses"/>
				<?php $list_table->display() ?>
				<?php wp_nonce_field( 'save', 'license_wp_licensing_nonce' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Edit a single license
	 */
	public function edit_license() {
		wc_get_template( 'edit-license-form.php', array(
			'license_key' => wc_clean( $_GET['edit'] ),
			'license'     => license_wp()->service( 'license_factory' )->make( wc_clean( $_GET['edit'] ) )
		), 'license-wp', license_wp()->service( 'file' )->plugin_path() . '/assets/views/' );
	}

	/**
	 * Save the new license
	 */
	public function save() {

		// vars
		$license_key      = wc_clean( $_GET['edit'] );
		$activation_email = wc_clean( $_POST['activation_email'] );
		$product_id       = absint( $_POST['product_id'] );
		$user_id          = absint( $_POST['user_id'] );
		$order_id         = absint( $_POST['order_id'] );
		$activation_limit = absint( $_POST['activation_limit'] );
		$date_expires     = wc_clean( $_POST['date_expires'] );

		try {

			// check nonce
			if ( empty( $_POST['license_wp_licensing_nonce'] ) || ! wp_verify_nonce( $_POST['license_wp_licensing_nonce'], 'edit_license' ) ) {
				throw new \Exception( __( 'Nonce check failed', 'license-wp' ) );
			}

			// check product ID
			if ( empty( $product_id ) ) {
				throw new \Exception( __( 'You must choose a product for this license', 'license-wp' ) );
			}

			// check activation email
			if ( empty( $activation_email ) && empty( $user_id ) ) {
				throw new \Exception( __( 'Either an activation email or user ID is required', 'license-wp' ) );
			}

			// get WooCommerce product
			$product = \wc_get_product( $product_id );

			// product must be an API license product
			if ( 'yes' !== get_post_meta( $product->is_type( 'variable' ) ? $product->get_parent_id() : $product->get_id(), '_is_api_product_license', true ) ) {
				throw new \Exception( __( 'Invalid product', 'license-wp' ) );
			}

			// set activation email to user email if no activation email is set
			if ( ! $activation_email && $user_id ) {
				$user             = get_user_by( 'id', $user_id );
				$activation_email = $user->user_email;
			}

			// exit if we still have no valid email address at this point
			if ( empty( $activation_email ) || ! is_email( $activation_email ) ) {
				throw new \Exception( __( 'A valid activation email is required', 'license-wp' ) );
			}

			// create license object
			/** @var \Never5\LicenseWP\License\License $license */
			$license = license_wp()->service( 'license_factory' )->make( $license_key );

			// set license data, key is generated when persisting license
			$license->set_activation_email( $activation_email );
			$license->set_user_id( $user_id );
			$license->set_product_id( $product_id );
			$license->set_activation_limit( $activation_limit );
			$license->set_order_id( $order_id );

			if ( $date_expires ) {
				$exp_date = new \DateTime( $date_expires );
				$license->set_date_expires( $exp_date );
			} else {
				$license->set_date_expires( false );
			}

			// save license
			$license = license_wp()->service( 'license_repository' )->persist( $license );

		} catch ( \Exception $e ) {
			echo sprintf( '<div class="error"><p>%s</p></div>', $e->getMessage() );
		}
	}
}
