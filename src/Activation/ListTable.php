<?php

namespace Never5\LicenseWP\Activation;

if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ListTable extends \WP_List_Table {

	/**
	 * __construct
	 */
	public function __construct(){
		//Set parent defaults
		parent::__construct( array(
			'singular' => 'activation',
			'plural'   => 'activations',
			'ajax'     => false
		) );
	}

	/**
	 * column_default function.
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case 'license_key' :
				return '<a href="' . admin_url( 'admin.php?page=license_wp_licenses&amp;license_key=' . $item->license_key ) . '">' . '<code>' . $item->license_key . '</code>' . '</a>';
			case 'api_product_id' :
				return esc_html( $item->api_product_id );
			case 'instance' :
				return $item->instance ? esc_html( $item->instance ) : __( 'n/a', 'license-wp' );
			case 'activation_date' :
				return ( $item->activation_date ) ? date_i18n( get_option( 'date_format' ), strtotime( $item->activation_date ) ) : __( 'n/a', 'license-wp' );
			case 'activation_active' :
				return $item->activation_active ? '&#10004;' : '-';
		}
	}

	/**
	 * column_cb function.
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_cb( $item ){
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'activation_id',
			$item->activation_id
		);
	}

	/**
	 * get_columns function.
	 *
	 * @access public
	 */
	public function get_columns(){
		$columns = array(
			'cb'                => '<input type="checkbox" />',
			'license_key'       => __( 'License key', 'license-wp' ),
			'api_product_id'    => __( 'API Product ID', 'license-wp' ),
			'activation_date'   => __( 'Activation date', 'license-wp' ),
			'instance'          => __( 'Instance', 'license-wp' ),
			'activation_active' => __( 'Active?', 'license-wp' ),
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
			'activation_date'  => array( 'activation_date', true ),     //true means its already sorted
			'date_expires'     => array( 'date_expires', false ),
			'order_id'         => array( 'order_id', false ),
			'api_product_id'   => array( 'api_product_id', false ),
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
			'activate'   => __( 'Activate', 'license-wp' ),
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

		if ( ! isset( $_POST['activation_id'] ) ) {
			return;
		}

		$items = array_map( 'absint', $_POST['activation_id'] );

		if ( $items ) {
			switch ( $this->current_action() ) {
				case 'activate' :
					foreach ( $items as $id ) {
						$wpdb->update( "{$wpdb->prefix}wp_plugin_licencing_activations", array( 'activation_active' => 1 ), array( 'activation_id' => $id ) );
					}
					echo '<div class="updated"><p>' . sprintf( __( '%d activations activated', 'license-wp' ), sizeof( $items ) ) . '</p></div>';
					break;
				case 'deactivate' :
					foreach ( $items as $id ) {
						$wpdb->update( "{$wpdb->prefix}wp_plugin_licencing_activations", array( 'activation_active' => 0 ), array( 'activation_id' => $id ) );
					}
					echo '<div class="updated"><p>' . sprintf( __( '%d activations deactivated', 'license-wp' ), sizeof( $items ) ) . '</p></div>';
					break;
				case 'delete' :
					foreach ( $items as $id ) {
						$wpdb->delete( "{$wpdb->prefix}wp_plugin_licencing_activations", array( 'activation_id' => $id ) );
					}
					echo '<div class="updated"><p>' . sprintf( __( '%d activations deleted', 'license-wp' ), sizeof( $items ) ) . '</p></div>';
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
		$orderby      = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'activation_date';
		$order        = empty( $_REQUEST['order'] ) || $_REQUEST['order'] === 'asc' ? 'ASC' : 'DESC';
		$license_key  = ! empty( $_REQUEST['license_key'] ) ? sanitize_text_field( $_REQUEST['license_key'] ) : '';

		/**
		 * Init column headers
		 */
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		/**
		 * Process bulk actions
		 */
		$this->process_bulk_action();

		$where = array( 'WHERE 1=1' );
		if ( $license_key ) {
			$where[] = "AND license_key='{$license_key}'";
		}
		$where = implode( ' ', $where );

		/**
		 * Get items
		 */
		$max = $wpdb->get_var( "SELECT COUNT( activation_id ) FROM {$wpdb->lwp_activations} $where;" );

		$this->items = $wpdb->get_results( $wpdb->prepare( "
			SELECT * FROM {$wpdb->lwp_activations}
			$where
			ORDER BY `{$orderby}` {$order} LIMIT %d, %d
		", ( $current_page - 1 ) * $per_page, $per_page ) );

		/**
		 * Pagination
		 */
		$this->set_pagination_args( array(
			'total_items' => $max,
			'per_page'    => $per_page,
			'total_pages' => ceil( $max / $per_page )
		) );
	}

}