<?php
if ( ! class_exists( 'AddNow_Buttons_Handler' ) ) {
	class AddNow_Buttons_Handler {

		var $current_actions = false;
		var $current_widgets = array();

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function __construct() {
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
			add_filter( 'the_content', array( $this, 'the_content' ) );
			add_filter( 'the_title', array( $this, 'the_title' ) );
			add_action( 'loop_start', array( $this, 'loop_start' ) );
			add_action( 'loop_end', array( $this, 'loop_end' ) );
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function template_redirect() {
			global $addnow_plugin;

			if ( is_home() || is_front_page() ) {

				$show_on_front = get_option( 'show_on_front' );
				if ( $show_on_front === 'posts' ) {
					$widgets = $this->get_widgets_for_current_action( 'is_home' );
					if ( !empty( $widgets ) ) {
						$this->current_actions['is_home'] = 'is_home';
						if ( empty( $this->current_widgets ) ) {
							$this->current_widgets = $widgets;
						} else {
							$this->current_widgets = array_merge( $this->current_widgets, $widgets );
						}
					}
				} else if ( $show_on_front === 'page' ) {
					$page_on_front = get_option( 'page_on_front' );
					$page_for_posts = get_option( 'page_for_posts' );

					$queried_object = get_queried_object();
					if ( isset( $queried_object->ID ) ) {

						if ( $queried_object->ID == $page_on_front ) {

							$widgets = $this->get_widgets_for_current_action( 'is_home', 'page_on_front' );
							if ( ! empty( $widgets ) ) {
								$this->current_actions['is_home'] = 'page_on_front';
								if ( empty( $this->current_widgets ) ) {
									$this->current_widgets = $widgets;
								} else {
									$this->current_widgets = array_merge( $this->current_widgets, $widgets );
								}
							}
						} else if ( $queried_object->ID == $page_for_posts ) {
							$this->current_actions['is_home'] = 'page_for_posts';
							$widgets = $this->get_widgets_for_current_action( 'is_home', 'page_for_posts' );
							if ( ! empty( $widgets ) ) {
								$this->current_actions['is_home'] = 'page_for_posts';
								if ( empty( $this->current_widgets ) ) {
									$this->current_widgets = $widgets;
								} else {
									$this->current_widgets = array_merge( $this->current_widgets, $widgets );
								}
							}
						}
					}
				}
			}
			elseif ( is_post_type_archive() ) {
				global $wp_query;

				if ( isset( $wp_query->query['post_type'] ) ) {
					$action_type = $wp_query->query['post_type'];

					$widgets = $this->get_widgets_for_current_action( 'is_archive', $action_type );
					if ( ! empty( $widgets ) ) {
						$this->current_actions['is_archive'] = $action_type;
						if ( empty( $this->current_widgets ) ) {
							$this->current_widgets = $widgets;
						} else {
							$this->current_widgets = array_merge( $this->current_widgets, $widgets );
						}
					}
				}
			}
			elseif ( is_date() ) {
				$action_type = 'date';

				$widgets = $this->get_widgets_for_current_action( 'is_archive', $action_type );
				if ( ! empty( $widgets ) ) {
					$this->current_actions['is_archive'] = $action_type;
					if ( empty( $this->current_widgets ) ) {
						$this->current_widgets = $widgets;
					} else {
						$this->current_widgets = array_merge( $this->current_widgets, $widgets );
					}
				}
			}
			elseif ( is_tag() || is_category() ) {
				$queried_object = get_queried_object();

				if ( isset( $queried_object->taxonomy ) ) {
					$action_type = $queried_object->taxonomy;

					$widgets = $this->get_widgets_for_current_action( 'is_archive', $action_type );
					if ( ! empty( $widgets ) ) {
						$this->current_actions['is_archive'] = $action_type;
						if ( empty( $this->current_widgets ) ) {
							$this->current_widgets = $widgets;
						} else {
							$this->current_widgets = array_merge( $this->current_widgets, $widgets );
						}
					}
				}
			}
			elseif ( is_archive() ) {
				$action_type = 'is_archive';

				$widgets = $this->get_widgets_for_current_action( 'is_archive', $action_type );
				if ( ! empty( $widgets ) ) {
					$this->current_actions['is_archive'] = $action_type;
					if ( empty( $this->current_widgets ) ) {
						$this->current_widgets = $widgets;
					} else {
						$this->current_widgets = array_merge( $this->current_widgets, $widgets );
					}
				}
			}
			elseif ( is_single() ) {
				$queried_object = get_queried_object();

				$widgets = $this->get_widgets_for_current_action( 'is_single', $queried_object->post_type );
				if ( ! empty( $widgets ) ) {
					$this->current_actions['is_single'] = $queried_object->post_type;
					if ( empty( $this->current_widgets ) ) {
						$this->current_widgets = $widgets;
					} else {
						$this->current_widgets = array_merge( $this->current_widgets, $widgets );
					}
				}
			}
			elseif ( is_page() ) {
				$queried_object = get_queried_object();
				$widgets = $this->get_widgets_for_current_action( 'is_single', $queried_object->post_type );
				if ( ! empty( $widgets ) ) {
					$this->current_actions['is_single'] = $queried_object->post_type;
					if ( empty( $this->current_widgets ) ) {
						$this->current_widgets = $widgets;
					} else {
						$this->current_widgets = array_merge( $this->current_widgets, $widgets );
					}
				}
			}
			elseif ( is_search() ) {
				$widgets = $this->get_widgets_for_current_action( 'is_search' );
				if ( ! empty( $widgets ) ) {
					$this->current_actions['is_search'] = 'is_search';
					if ( empty( $this->current_widgets ) ) {
						$this->current_widgets = $widgets;
					} else {
						$this->current_widgets = array_merge( $this->current_widgets, $widgets );
					}
				}
			}

			if ( ( ! empty( $this->current_actions ) ) && ( ! empty( $this->current_widgets ) ) ) {
				$addnow_plugin->add_js = true;
			}
		}

		/**
		 * Inject share widget into title
		 * @param string $title
		 * @return string
		 */
		function the_title( $title = '' ) {
			if ( is_singular() && in_the_loop() ) {
				// Title must be rendered via the_title() function
				$bt = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
				$the_title_present = false;
				foreach ($bt as $step) {
					if ( isset( $step['function'] )
						&& ( ( 'the_title' === $step['function'] && empty( $step['class'] ) ) || ( 'tc_post_page_title_callback' === $step['function'] ) )
					) {
						$the_title_present = true;
						break;
					}
				}

				if ( $the_title_present ) {
					$post_type = get_post_type();
					foreach( $this->current_widgets as $widget ) {
						if (
							! isset( $widget['active'] )
							|| 'yes' !== $widget['active']
							|| empty( $widget['placement']['is_single'][$post_type] )
						) {
							continue;
						}

						$placement_opts = $widget['placement']['is_single'][$post_type];
						if ( ! empty( $placement_opts['before_title'] ) ) {
							$title = AddNow_Plugin::wrapEmbedCode( $widget['code'] ) . $title;
						}
						if ( ! empty( $placement_opts['after_title'] ) ) {
							$title .= AddNow_Plugin::wrapEmbedCode( $widget['code'] );
						}
					}
				}
			}
			return $title;
		}

		/**
		 * Simpler handler for Single Posts and single Pages.
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function the_content( $content = '' ) {
			global $wp_query;

			if ( ! $wp_query->is_main_query() ) {
				return $content;
			}

			// IF this is the home page (is_home) we need to do a secondary check.
			// If both the is_home && is_front_page are set then we are in (default) settings
			// and means we are displaying the latest blog posts.

			// In this secondary check IF the site settings are to display the blog posts
			// on the front page (default) then then the is_single will NOY be set.
			// But if the
			if ( isset( $this->current_actions['is_home'] ) ) {
				if ( isset( $this->current_actions['is_single'] ) ) {
					unset( $this->current_actions['is_single'] );

				} else if ( ! isset( $this->current_actions['is_front_page'] ) ) {
					return $content;
				}
			} else if ( ( ! isset( $this->current_actions['is_single'] ) ) && ( ! isset( $this->current_actions['is_page'] ) ) ) {
				return $content;
			}

			if ( empty( $this->current_widgets ) ) {
				return $content;
			}

			foreach ( $this->current_widgets as $widget ) {
				if ( ( ! isset( $widget['active'] ) ) || ( $widget['active'] !== 'yes' ) ) {
					continue;
				}

				foreach ( $this->current_actions as $current_action => $current_type ) {
					if ( $current_action == $current_type ) {
						if ( ( isset( $widget['placement'][ $current_action ]['top'] ) )
							&& ( ! empty( $widget['placement'][ $current_action ]['top'] ) ) ) {
							$content = AddNow_Plugin::wrapEmbedCode( $widget['code'] ) . $content;
						}
						if ( ( isset( $widget['placement'][ $current_action ]['bottom'] ) )
							&& ( ! empty( $widget['placement'][ $current_action ]['bottom'] ) ) ) {
							$content .= AddNow_Plugin::wrapEmbedCode( $widget['code'] );
						}
					} else {
						if ( ( isset( $widget['placement'][ $current_action ][ $current_type ]['top'] ) )
							&& ( ! empty( $widget['placement'][ $current_action ][ $current_type ]['top'] ) ) ) {
							$content = AddNow_Plugin::wrapEmbedCode( $widget['code'] ) . $content;
						}

						if ( ( isset( $widget['placement'][ $current_action ][ $current_type ]['bottom'] ) )
							&& ( ! empty( $widget['placement'][ $current_action ][ $current_type ]['bottom'] ) ) ) {
							$content .= AddNow_Plugin::wrapEmbedCode( $widget['code'] );
						}
					}
				}
			}

			return $content;
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function loop_start( $query = '' ) {

			if ( ! is_object( $query ) ) {
				return;
			}

			if ( ! $query->is_main_query() ) {
				return;
			}

			if ( isset( $this->current_actions['is_home'] ) ) {
				if ( isset( $this->current_actions['is_single'] ) && $this->current_actions['is_single'] == 'page' ) {
					return;
				}
			} elseif ( ! isset( $this->current_actions['is_search'] ) && ! isset( $this->current_actions['is_archive'] ) ) {
				return;
			}

			if ( empty( $this->current_widgets ) ) {
				return;
			}

			$content = '';
			foreach ( $this->current_widgets as $widget ) {
				if ( ( ! isset( $widget['active'] ) ) || ( $widget['active'] != 'yes' ) ) {
					continue;
				}

				foreach ( $this->current_actions as $current_action => $current_type ) {
					if ( $current_action == $current_type ) {
						if ( ( isset( $widget['placement'][ $current_action ]['top'] ) )
							&& ( ! empty( $widget['placement'][ $current_action ]['top'] ) ) ) {
							$content = AddNow_Plugin::wrapEmbedCode( $widget['code'] ) . $content;
						}
					} else {
						if ( ( isset( $widget['placement'][ $current_action ][ $current_type ]['top'] ) )
							&& ( ! empty( $widget['placement'][ $current_action ][ $current_type ]['top'] ) ) ) {
							$content = AddNow_Plugin::wrapEmbedCode( $widget['code'] ) . $content;
						}
					}
				}
			}

			if ( ! empty( $content ) ) {
				echo $content;
			}
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function loop_end( $query = '' ) {

			//TODO: loop_start() and loop_end() share the same logic. Create a dedicated function for this

			if ( ! is_object( $query ) ) {
				return;
			}

			if ( ! $query->is_main_query() ) {
				return;
			}

			if ( isset( $this->current_actions['is_home'] ) ) {
				if ( isset( $this->current_actions['is_single'] ) && $this->current_actions['is_single'] == 'page' ) {
					return;
				}
			} elseif ( ! isset( $this->current_actions['is_search'] ) && ! isset( $this->current_actions['is_archive'] ) ) {
				return;
			}

			if ( empty( $this->current_widgets ) ) {
				return;
			}

			$content = '';
			foreach ( $this->current_widgets as $widget ) {
				if ( ( ! isset( $widget['active'] ) ) || ( $widget['active'] != 'yes' ) ) {
					continue;
				}

				foreach ( $this->current_actions as $current_action => $current_type ) {
					if ( $current_action == $current_type ) {
						if ( ( isset( $widget['placement'][ $current_action ]['bottom'] ) )
							&& ( ! empty( $widget['placement'][ $current_action ]['bottom'] ) ) ) {
							$content = AddNow_Plugin::wrapEmbedCode( $widget['code'] ) . $content;
						}
					} else {
						if ( ( isset($widget['placement'][ $current_action ][ $current_type ]['bottom'] ) )
							&& ( ! empty( $widget['placement'][ $current_action ][ $current_type ]['bottom'] ) ) ) {
							$content = AddNow_Plugin::wrapEmbedCode( $widget['code'] ) . $content;
						}
					}
				}
			}

			if ( ! empty( $content ) ) {
				echo $content;
			}
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function get_widgets_for_current_action( $action = '', $post_type = '' ) {
			global $addnow_plugin;

			$widgets = array();

			if ( empty( $action ) ) {
				return $widgets;
			}
			if ( empty( $addnow_plugin->settings['widgets'] ) ) {
				return $widgets;
			}
			foreach ( $addnow_plugin->settings['widgets'] as $widget_id => $widget ) {
				if ( ( ! isset( $widget['active'] ) ) || ( $widget['active'] !== 'yes' ) ) {
					continue;
				}

				if ( empty( $post_type ) ) {
					if ( ( isset( $widget['placement'][$action] ) ) && ( ! empty( $widget[ 'placement' ][ $action ] ) ) ) {
						$widgets[ $widget_id ] = $widget;
					}
				} else {
					if ( ( isset( $widget[ 'placement' ][ $action ][ $post_type ] ) ) && ( ! empty( $widget[ 'placement' ][ $action ][ $post_type ] ) ) ) {
						$widgets[ $widget_id ] = $widget;
					}
				}
			}
			return $widgets;
		}
	}
}
