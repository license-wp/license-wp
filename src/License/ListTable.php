<?php

namespace Never5\LicenseWP\License;

use Never5\LicenseWP\WooCommerce;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ListTable extends \WP_List_Table {

	/**
	 * Constructor
	 */
	public function __construct() {
		//Set parent defaults
		parent::__construct( array(
			'singular' => 'license key',
			'plural'   => 'license keys',
			'ajax'     => false
		) );
	}

	/**
	 * Column default
	 *
	 * @access public
	 *
	 * @param object $item
	 * @param array $column_name
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		global $wpdb;

		switch ( $column_name ) {
			case 'license_key' :
				return '<a href="' . admin_url( 'admin.php?page=license_wp_licenses&amp;edit=' . $item->license_key ) . '"><code>' . $item->license_key . '</code></a>';
			case 'activation_email' :
				return $item->activation_email;
			case 'product_id' :

				$product = WooCommerce\Product::get_product( $item->product_id );

				return ( $product ) ? '<a href="' . admin_url( 'post.php?post=' . absint( $product->ID ) . '&action=edit' ) . '">' . esc_html( $product->post_title ) . '</a>' : __( 'n/a', 'license-wp' );
			case 'user_id' :
				return ( $item->user_id ) ? '<a href="' . admin_url( 'user-edit.php?user_id=' . absint( $item->user_id ) ) . '">#' . esc_html( $item->user_id ) . '&rarr;</a>' : __( 'n/a', 'license-wp' );
			case 'activations' :
				$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( activation_id ) FROM {$wpdb->lwp_activations} WHERE activation_active = 1 AND license_key=%s;", $item->license_key ) );

				return '<a href="' . admin_url( 'admin.php?page=license_wp_activations&amp;license_key=' . $item->license_key ) . '">' . absint( $count ) . ' &rarr;</a>';
			case 'activation_limit' :
				return $item->activation_limit ? sprintf( __( '%d per product', 'license-wp' ), absint( $item->activation_limit ) ) : __( 'n/a', 'license-wp' );
			case 'order_id' :
				return $item->order_id > 0 ? '<a href="' . admin_url( 'post.php?post=' . absint( $item->order_id ) . '&action=edit' ) . '">#' . absint( $item->order_id ) . ' &rarr;</a>' : __( 'n/a', 'license-wp' );
			case 'date_created' :
				return $item->date_created > 0 ? date_i18n( get_option( 'date_format' ), strtotime( $item->date_created ) ) : __( 'n/a', 'license-wp' );
			case 'date_expires' :
				return strtotime( $item->date_expires ) > 0 ? date_i18n( get_option( 'date_format' ), strtotime( $item->date_expires ) ) : __( 'n/a', 'license-wp' );
		}
	}

	/**
	 * Column callback
	 *
	 * @access public
	 *
	 * @param mixed $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'license_key_id',
			$item->license_key
		);
	}

	/**
	 * get_columns function.
	 *
	 * @access public
	 */
	public function get_columns() {
		$columns = array(
			'cb'               => '<input type="checkbox" />',
			'license_key'      => __( 'License key', 'license-wp' ),
			'activation_email' => __( 'Activation email', 'license-wp' ),
			'product_id'       => __( 'Product', 'license-wp' ),
			'order_id'         => __( 'Order ID', 'license-wp' ),
			'user_id'          => __( 'User ID', 'license-wp' ),
			'activation_limit' => __( 'Activation limit', 'license-wp' ),
			'activations'      => __( 'Activations', 'license-wp' ),
			'date_created'     => __( 'Date created', 'license-wp' ),
			'date_expires'     => __( 'Date expires', 'license-wp' )
		);

		return $columns;
	}

	/**
	 * get_sortable_columns function.
	 *
	 * @access public
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'date_created'     => array( 'date_created', true ),     //true means its already sorted
			'date_expires'     => array( 'date_expires', false ),
			'order_id'         => array( 'order_id', false ),
			'user_id'          => array( 'user_id', false ),
			'product_id'       => array( 'product_id', false ),
			'activation_email' => array( 'activation_email', false ),
		);

		return $sortable_columns;
	}

	/**
	 * get_bulk_actions
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'deactivate' => __( 'Deactivate', 'license-wp' ),
			'delete'     => __( 'Delete', 'license-wp' )
		);

		return $actions;
	}

	/**
	 * Process bulk actions
	 */
	public function process_bulk_action() {
		global $wpdb;

		if ( ! isset( $_POST['license_key_id'] ) ) {
			return;
		}

		$items = array_map( 'sanitize_text_field', $_POST['license_key_id'] );

		if ( $items ) {
			switch ( $this->current_action() ) {
				case 'deactivate' :
					foreach ( $items as $id ) {
						$wpdb->update( $wpdb->lwp_activations, array( 'activation_active' => 0 ), array( 'license_key' => $id ) );
					}
					echo '<div class="updated"><p>' . sprintf( __( '%d keys deactivated', 'license-wp' ), sizeof( $items ) ) . '</p></div>';
					break;
				case 'delete' :
					foreach ( $items as $id ) {
						$wpdb->delete( $wpdb->lwp_licenses, array( 'license_key' => $id ) );
						$wpdb->delete( $wpdb->lwp_activations, array( 'license_key' => $id ) );
					}
					echo '<div class="updated"><p>' . sprintf( __( '%d keys deleted', 'license-wp' ), sizeof( $items ) ) . '</p></div>';
					break;
			}
		}
	}

	/**
	 * prepare_items function.
	 *
	 * @access public
	 */
	public function prepare_items() {
		global $wpdb;

		$current_page = $this->get_pagenum();
		$per_page     = 50;
		$orderby      = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'date_created';
		$order        = empty( $_REQUEST['order'] ) || $_REQUEST['order'] === 'asc' ? 'ASC' : 'DESC';
		$order_id     = ! empty( $_REQUEST['order_id'] ) ? absint( $_REQUEST['order_id'] ) : '';
		$license_key  = ! empty( $_REQUEST['license_key'] ) ? sanitize_text_field( $_REQUEST['license_key'] ) : '';

		// column headers
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		// process bulk action
		$this->process_bulk_action();

		$where = array( 'WHERE 1=1' );

		if ( $order_id ) {
			$where[] = 'AND order_id=' . $order_id;
		}

		if ( $license_key ) {
			$where[] = "AND license_key='{$license_key}'";
		}

		$where = implode( ' ', $where );

		// fetch matx
		$max = $wpdb->get_var( "SELECT COUNT(license_key) FROM {$wpdb->lwp_licenses} $where;" );

		// fetch items
		$this->items = $wpdb->get_results( $wpdb->prepare( "
			SELECT * FROM {$wpdb->lwp_licenses}
			$where
			ORDER BY `{$orderby}` {$order} LIMIT %d, %d
		", ( $current_page - 1 ) * $per_page, $per_page ) );

		// pagination
		$this->set_pagination_args( array(
			'total_items' => $max,
			'per_page'    => $per_page,
			'total_pages' => ceil( $max / $per_page )
		) );
	}
}
