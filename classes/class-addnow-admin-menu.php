<?php
if ( ! class_exists( 'AddNow_Admin_Menu' ) ) {
	class AddNow_Admin_Menu {

		var $pagehooks	= array();
		var $panels		= array();

		function __construct() {
			add_action( 'admin_menu', 	array( $this, 'admin_menu' ) );
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function admin_menu() {
			global $addnow_plugin;

			include $addnow_plugin->plugin_dir . '/classes/class-addnow-admin-panel-settings-view.php';
			$this->panels['AddNow_Admin_Panel_Settings_View'] = new AddNow_Admin_Panel_Settings_View;

			$this->pagehooks['addnow-admin-panel-settings-view'] = add_options_page(
				__( 'AddNow', 'addnow' ),
				__( 'AddNow', 'addnow' ),
				'manage_options',
				'addnow-plugin',
				array( $this->panels['AddNow_Admin_Panel_Settings_View'], 'on_show_panel' )
			);

			add_action( 'load-'. $this->pagehooks['addnow-admin-panel-settings-view'], array( $this->panels['AddNow_Admin_Panel_Settings_View'], 'on_load_panel' ) );
		}
	}
}
