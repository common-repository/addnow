<?php
/*
Plugin Name: 	AddNow
Plugin URI:  	http://gravity4.com/
Description: 	Adds the AddNow Sharing Buttons Widgets on your website.
Version: 			1.0
Author:      	Gravity4.com
Author URI:  	gravity4.com
Text Domain: 	addnow
Domain Path: 	/languages
*/

if ( ! class_exists( 'AddNow_Plugin' ) ) {
	class AddNow_Plugin {

		var $version	= '1.0';

		// Contains the reference path to the plugin root directory. Used when other included plugin files
		// need to include files relative to the plugin root.
		var $plugin_dir;

		// Contains the reference url to the plugin root directory. Used when other included plugin files
		// need to refernece CSS/JS via URL
		var $plugin_url;

		var $settings_key = 'addnow-plugin-settings';
		var $settings 	= array();

		var $add_js = false;

		CONST API_CDN = 'cdn.addnow.com/';

		function __construct() {

			$this->plugin_dir = trailingslashit( plugin_dir_path( __FILE__ ) );
			$this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );

			add_action( 'init', array( $this, 'init' ) );
			add_action( 'wp_footer', array( $this, 'wp_footer' ), 1000 );

			if ( is_admin() ) {

				include $this->plugin_dir . 'classes/class-addnow-admin-menu.php';
				$this->admin_menu = new AddNow_Admin_Menu;

				add_filter( 'plugin_action_links_'. basename( dirname( __FILE__ ) ) .'/'. basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			}

			include $this->plugin_dir .'classes/class-addnow-buttons-handler.php';
			$this->buttons_handler = new AddNow_Buttons_Handler;

			include $this->plugin_dir .'classes/class-addnow-shortcode-handler.php';
			$this->shortcode_handler = new AddNow_Shortcode_Handler;

			register_activation_hook( __FILE__, array( $this, 'install' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function init() {

			load_plugin_textdomain( 'addnow', false, dirname( plugin_basename( __FILE__ ) ) .'/languages/' );

			$this->load_settings();
		}


		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function wp_footer() {
			if ( $this->add_js === true ) {
				if ( ( isset( $this->settings['widgets'] ) ) && ( ! empty( $this->settings['widgets'] ) ) ) {
					echo stripslashes( '<script type="text/javascript">' );
					echo stripslashes( 'var _addnow = _addnow || [];' );
					foreach ($this->settings['widgets'] as $widget) {
						if ($widget['entity']->is_active) {
							echo stripslashes( ' _addnow.push(["set", "hash_id", "' . $widget['entity']->hash_id . '"]);' );
						}
					}
					echo stripslashes( '(function() {var addnow = document.createElement("script"); addnow.type = "text/javascript"; addnow.async = true; addnow.src = ("https:" === document.location.protocol ? "https://" : "http://") + "' . AddNow_Plugin::API_CDN . 'widget/addnow.js"; var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(addnow, s);})();' );
					echo stripslashes( '</script>' );
				}
			}

		}

		/**
		 * Add frontend css
		 */
		function enqueue_assets() {
			wp_enqueue_style( 'addnow', plugin_dir_url( __FILE__ ) . '/css/addnow.css' );
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function plugin_action_links( $links ) {
			$settings_link = '<a href="'. admin_url( 'options-general.php?page=addnow-plugin' ) .'">'
				. __( 'Settings', 'addnow' ) .'</a>';
			array_unshift( $links, $settings_link );

			return $links;
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function load_settings() {
			if ( ! empty( $this->settings ) )
				unset( $this->settings );

			$this->settings = get_option( $this->settings_key );

			$this->convert_settings();
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function update_settings() {
			$this->settings['version'] = $this->version;
			delete_option( $this->settings_key );
			return update_option( $this->settings_key, $this->settings );
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function get_setting( $key = '', $section = 'settings' ) {
			if ( isset( $this->settings[ $section ][ $key ] ) ) {

				if ( is_string( $this->settings[ $section ][ $key ] ) ) {
					return html_entity_decode( stripslashes( $this->settings[ $section ][ $key ] ) );
				} else {
					return $this->settings[ $section ][ $key ];
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
		function convert_settings() {

			if ( ! isset( $this->settings['settings'] ) )
				$this->settings['settings'] = array();

			if ( ! isset( $this->settings['settings']['tracking-code'] ) )
				$this->settings['settings']['tracking-code'] = '';

			if ( ! isset( $this->settings['widgets'] ) )
				$this->settings['widgets'] = array();

		}

		/**
		 * Preprocess widget code before inserting onto a page
		 * @param string $code
		 * @return string
		 */
		public static function wrapEmbedCode( $code ) {
			return '<div class="addnow-wrapper">' . html_entity_decode( stripslashes( $code ) ) . '</div>';
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function install() {
		}

		// End of Functions
	}
	$addnow_plugin = new AddNow_Plugin();
}
