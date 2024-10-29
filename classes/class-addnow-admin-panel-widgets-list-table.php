<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/classes/class-wp-list-table.php' );
}

if ( ! class_exists( 'AddNow_Admin_Panel_Widgets_View_List_Table' ) ) {
	class AddNow_Admin_Panel_Widgets_View_List_Table extends WP_List_Table {

		var $filters 	= array();
		var $per_page 	= 20;

		function __construct() {
			global $status, $page;

			//Set parent defaults
			parent::__construct( array(
				'singular'  => 'widget',    //singular name of the listed records
				'plural'    => 'widgets',   //plural name of the listed records
				'ajax'      => true,				//does this table support ajax?
			) );
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function check_table_filters() {
			global $addnow_plugin;
			$this->filters = array();
			if ( isset( $_POST['s'] ) && ! empty( $_POST['s'] ) ) {
				$this->filters['filters-search-widgets'] = esc_attr( $_POST['s'] );
			} else {
				$this->filters['filters-search-widgets'] = '';
			}
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function search_box( $text, $input_id ) {
			if ( empty( $_REQUEST['s'] ) && ! $this->has_items() )
				return;

			$input_id = $input_id . '-search-input';

			if ( ! empty( $_REQUEST['orderby'] ) )
				echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
			if ( ! empty( $_REQUEST['order'] ) )
				echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
			echo '<input type="hidden" name="paged" value="1" />';

			?>
			<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label><input type="search" id="<?php echo $input_id ?>" name="s" value="<?php echo $this->filters['filters-search-widgets'] ?>" /><?php submit_button( $text, 'button', 'addnow-search-widgets-button', false, array('id' => 'search-submit') ); ?></p><?php
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function get_bulk_actions() {
			$bulk_actions = array(
				'delete-widget' => __( 'Delete Widgets', 'addnow' ),
			);

			return $bulk_actions;
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function get_columns() {
			$columns = array();
			$columns['cb'] = '<input type="checkbox" />';
			$columns['widget_name'] = __( 'Widget Name', 'addnow' );
			$columns['widget_active'] = __( 'Active', 'addnow' );
			$columns['widget_shortcode'] = __( 'Shortcode', 'addnow' );
			$columns['widget_placement'] = __( 'Placement', 'addnow' );

			return $columns;
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function column_cb( $item ) {
			?><input type="checkbox" id="addnow-widgets-bulk-items-<?php echo $item['id'] ?>" name="addnow-widgets-bulk-items[]" value="<?php echo $item['id'] ?>" /><?php
		}

		function column_widget_name( $item ) {

			$action_url = add_query_arg( 'widget_id', $item['id'], remove_query_arg( 'paged' ) );

			$edit_url = add_query_arg( 'action', 'edit', $action_url );
			echo '<a href="'. $edit_url .'">'. $item['name'] .'</a>';

			$actions = array();

			$actions['edit'] = '<a title="'. __( 'Edit this Widget', 'addnow' ) .'" href="'. $edit_url .'">'. __( 'edit', 'addnow' ) .'</a>';

			$delete_nonce = wp_create_nonce( 'addnow-plugin-delete-widget-nonce-'. $item['id'] );
			$delete_url = add_query_arg(
				array(
					'action' 		=> 'delete',
					'addnow-nonce'	=> $delete_nonce,
				),
				$action_url
			);
			$actions['delete'] = '<a title="'. __( 'Delete this Widget', 'addnow' ) .'" href="'. $delete_url .'">'. __( 'delete', 'addnow' ) .'</a>';

			if ( ! empty( $actions ) ) {
				echo $this->row_actions( $actions );
			}

		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function column_widget_active( $item ) {
			if ( empty( $item['active'] ) ) $item['active'] = __( 'No', 'addnow' );
			echo ucfirst( stripslashes( $item['active'] ) );
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function column_widget_code( $item ) {
			echo htmlentities( stripslashes( $item['code'] ) );
		}

		function column_widget_shortcode( $item ) {
			echo '<code>[addnow id="'. stripslashes( $item['id'] ) .'"]</code>';
		}

		function column_widget_placement( $item ) {
			echo str_replace( ';', '<br />', htmlentities( stripslashes( $item['placement_labels'] ) ) );
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function prepare_items() {
			global $wpdb, $addnow_plugin;

			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );

			$total_items 	= count( $addnow_plugin->settings['widgets'] );
			$current_page 	= $this->get_pagenum();
			$per_page 		= $this->per_page;
			$offset			= ($current_page - 1) * $per_page;

			if ( $total_items > $per_page ) {
				$this->items	= array_slice( $addnow_plugin->settings['widgets'], $offset, $per_page );
			} else {
				$this->items 	= $addnow_plugin->settings['widgets'];
			}

			$this->set_pagination_args( array(
					'total_items' => intval( $total_items ),	//WE have to calculate the total number of items
					'per_page'    => intval( $per_page ),		//WE have to determine how many items to show on a page
					'total_pages' => ceil( intval( $total_items ) / intval( $per_page ) )   //WE have to calculate the total number of pages
				)
			);
		}

		// End of Functions
	}
}
