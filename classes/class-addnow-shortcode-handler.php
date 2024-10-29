<?php

if ( ! class_exists( 'AddNow_Shortcode_Handler' ) ) {

	class AddNow_Shortcode_Handler {

		function __construct() {
			add_shortcode( 'addnow', array( $this, 'shortcode_handler' ) );
		}

		function shortcode_handler( $atts = array(), $content = '' ) {
			global $addnow_plugin;

			$atts = shortcode_atts(
				array(
					'id' => '',
				),
				$atts
			);

			if ( empty( $atts['id'] ) ) {
				return $content;
			}

			if ( ! isset( $addnow_plugin->settings['widgets'][ $atts['id'] ] ) ) {
				return $content;
			}

			$widget = $addnow_plugin->settings['widgets'][ $atts['id'] ];
			$content .= AddNow_Plugin::wrapEmbedCode( $widget['code'] ) . $content;

			$addnow_plugin->add_js = true;

			return $content;

		}
	}

}
