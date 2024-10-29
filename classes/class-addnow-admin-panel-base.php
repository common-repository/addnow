<?php
if ( ! class_exists( 'AddNow_Admin_Panel_View_Base' ) ) {
	class AddNow_Admin_Panel_View_Base {

		var $tabs 				= array();
		var $tab_current 		= '';

		var $form_field_help = array();
		var $admin_screen_help_texts = array();

		function __construct() {
			global $addnow_plugin;

			// Add our tool tips.
			if ( ! class_exists( 'WpmuDev_HelpTooltips' ) )
				require_once $addnow_plugin->plugin_dir . '/classes/class-wd-help-tooltips.php';
			$this->tips = new WpmuDev_HelpTooltips();
			$this->tips->set_icon_url( $addnow_plugin->plugin_url . '/images/information.png' );

			$help_form_fields_texts_files = $addnow_plugin->plugin_dir .'help_texts/'. $this->settings_page .'-form-fields-help-texts.php';
			if ( file_exists( $help_form_fields_texts_files ) ) {
				include $help_form_fields_texts_files;
			}

			$help_admin_texts_file = $addnow_plugin->plugin_dir .'help_texts/'. $this->settings_page .'-admin-help-texts.php';
			if ( file_exists( $help_admin_texts_file ) ) {
				include $help_admin_texts_file;
			}
		}

		function set_default_tab() {
			if ( ! empty( $this->tabs) && is_array( $this->tabs ) ) {
				foreach ( $this->tabs as $tab_key => $tab_item ) {
					$this->tab_current = $tab_key;
					break;
				}
			}
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function get_help_item( $key, $section ) {

			if ($section === "tip") {
				if ( (isset($this->form_field_help[$key][$section])) && (strlen($this->form_field_help[$key][$section])) ) {
					return $this->tips->add_tip($this->form_field_help[$key][$section]);
				}
			} else if ( $section === 'description') {
				if ( (isset($this->form_field_help[$key][$section])) && (strlen($this->form_field_help[$key][$section])) ) {
					return '<br /><span class="description">' . $this->form_field_help[$key][$section] .'</span>';
				}
			} else if ( (isset($this->form_field_help[$key][$section])) && (strlen($this->form_field_help[$key][$section])) ) {
				return $this->form_field_help[$key][$section];
			}
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function admin_help() {
			$screen = get_current_screen();
			if ((isset($this->admin_screen_help_texts)) && (!empty($this->admin_screen_help_texts))) {
				foreach($this->admin_screen_help_texts as $admin_screen_help_key => $admin_screen_help_text) {
					if ($admin_screen_help_key != 'sidebar') {
						$screen->add_help_tab( $admin_screen_help_text );
					} else {
						$screen->set_help_sidebar( $admin_screen_help_text );
					}
				}
			}
		}
	}
}
