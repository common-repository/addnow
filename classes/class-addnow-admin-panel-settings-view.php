<?php
include_once $addnow_plugin->plugin_dir . '/classes/class-addnow-admin-panel-base.php';

if ( ! class_exists( 'AddNow_Admin_Panel_Settings_View' ) ) {
	class AddNow_Admin_Panel_Settings_View extends AddNow_Admin_Panel_View_Base {

		CONST API_URL = 'http://api.addnow.com/';

		var $action 			= '';
		var $action_messages	= array();
		var $action_errors	    = array();
		var $admin_url 			= '';
		var $settings_page 		= 'addnow-plugin';

		var $placement_options	= array();
		var $textarea_rows		= 0;

		var $current_widget;
		var $list_table;

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function __construct() {

			parent::__construct();

			$this->tabs = array(
				'widgets'	=> array(
					'tab'	=> __( 'Widgets', 'addnow' ),
					'title' => __( 'Define the AddNow Sharing Buttons Widgets you will use on this site', 'addnow' ),
				),
				'settings'	=> array(
					'tab'	=> __( 'Settings', 'addnow' ),
					'title' => __( 'The AddNow plugin Settings', 'addnow' ),
				),
				'setup' => array(
					'tab'	=> __( 'Setup', 'addnow' ),
					'title'	=> __( 'How to setup and use this plugin', 'addnow' ),
				),
			);
			$this->set_default_tab();

			$this->placement_options = array(
				'top' => __( 'Top', 'addnow' ),
				'bottom' => __( 'Bottom', 'addnow' ),
			);
			$this->placement_options_singular = array(
				'before_title' => __( 'Before Title', 'addnow' ),
				'after_title' => __( 'After Title', 'addnow' ),
				'top' => __( 'Before Content', 'addnow' ),
				'bottom' => __( 'After Content', 'addnow' ),
			);
		}

		/**
		 * This function is called by WordPress when our Settings menu within the custom post type is
		 * loaded. Here we register a custom stylesheet to effect some screen elements.
		 *
		 * @since 1.0.0
		 * @param none
		 * @return none
		 */
		function on_load_panel() {
			global $addnow_plugin;

			if ( ( isset( $_GET['tab'] ) ) && ( isset( $this->tabs[ $_GET['tab'] ] ) ) ) {
				$this->tab_current = esc_attr( $_GET['tab'] );
			}

			$this->admin_url = add_query_arg(
				array(
					'page' => $_GET['page'],
					'tab' => $this->tab_current,
				)
			);

			wp_enqueue_style(
				'addnow-admin-styles',
				$addnow_plugin->plugin_url .'styles/addnow-admin-styles.css',
				array(),
				$addnow_plugin->version
			);

			wp_enqueue_style(
				'jquery-ui-smmothness-1.11.2',
				'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css',
				array(),
				'1.11.2'
			);

			wp_enqueue_script( 'jquery-ui-accordion' );
			wp_enqueue_script(
				'addnow-admin-settings-view',
				$addnow_plugin->plugin_url .'/scripts/addnow-admin-settings-view.js',
				array( 'jquery', 'jquery-ui-accordion' ),
				$addnow_plugin->version,
				true
			);

			$this->admin_help();

			$this->on_process_actions();

			if (empty($addnow_plugin->settings['tracking_key'])) {
				$this->action_errors[$this->action] = ['The Api key must not be empty.'];
			}
		}

		/**
		 * This function is called to show the Setting page we registered in the admin_menu()
		 * function. This function handles all the HTML settings field output.
		 *
		 * @since 1.0.0
		 * @param none
		 * @return none
		 */
		function on_show_panel() {
			global $addnow_plugin;

			?>
			<div class="wrap wrap-addnow-settings">

				<h2 class="addnow-page-title"><span class="addnow-logo"><a title="<?php _e( 'Go to AddNow.com Dashboard', 'addnow' ) ?>" href="http://addnow.com/site/dashboard/"><?php _e( 'AddNow', 'addnow' ); ?></a></span></h2>

				<?php
				if ( ! empty( $this->action_messages[ $this->action ] ) ) {
					?>
					<div id="addnow-settings-saved" class="updated below-h2">
					<p><?php echo $this->action_messages[ $this->action ] ?></p></div>
					<?php
				}
				?>

				<?php
				if ( ! empty( $this->action_errors[ $this->action ] ) ) {
					?>
                    <div id="addnow-settings-errors" class="error below-h2">
                        <?php
                            foreach ($this->action_errors[ $this->action ] as $key => $error) {
                                if (is_array($error)) {
                                    foreach ($error as $e) {
                                        echo '<div class="post-form__pubkey_error">'.$key.': '.$e.'</div>';
                                    }
                                } else {
                                    echo '<div class="post-form__pubkey_error">'.$error.'</div>';
                                }
                            }
                        ?>
                    </div>
					<?php
				}
				?>

				<h2 class="nav-tab-wrapper">
					<?php
					foreach( $this->tabs as $tab_key => $tab ) {
						$tab_class = ( $tab_key == $this->tab_current ) ? ' nav-tab-active' : '';
						?>
						<a class="nav-tab<?php echo $tab_class ?>" href="<?php echo add_query_arg( array( 'tab' => $tab_key ), remove_query_arg( array( 'action', 'widget_id' ) ) )  ?>">
							<?php echo $tab['tab'] ?>
						</a>
						<?php
					}
					?>
				</h2>
				<div class="wrap-addnow-settings-<?php echo $this->action ?>-content">
					<form id="addnow-settngs" action="<?php echo $this->admin_url ?>" method="post">
						<input type="hidden" name="page" value="<?php echo $this->settings_page; ?>" />
						<input type="hidden" name="tab" value="<?php echo $this->tab_current ?>" />
						<input type="hidden" name="action" value="<?php echo $this->action ?>" />
						<?php wp_nonce_field( $this->settings_page .'-'. $this->action .'-'. $this->tab_current .'-nonce', $this->settings_page .'-'. $this->action .'-'. $this->tab_current .'-nonce' ); ?>

						<?php
						switch ( $this->tab_current ) {

							case 'setup':
								$this->show_content_tab_setup();
								break;

							case 'widgets':
								if ( ! empty( $this->current_widget )  || ! empty( $this->action_errors[ $this->action ] ) ) {
									$this->show_content_tab_widgets();
								} else {
									?>
									<h2><a class="add-new-h2" href="<?php echo remove_query_arg( array( 'paged' ), add_query_arg( array( 'action' => 'new' ) ) ) ?>"><?php _e( 'Add New', 'addnow' ); ?></a></h2><?php
									$this->list_table->prepare_items();
									$this->list_table->display();
								}
								break;

							case 'settings':
							default:
								$this->show_content_tab_settings();
								break;
						}
						?>
					</form>
				</div>

			</div>
			<?php
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function show_content_tab_setup() {
			global $addnow_plugin;

			?>
			<h3><?php echo $this->tabs[$this->tab_current]['title'] ?></h3>

			<h4><?php _e( 'Setup your AddNow.com account and initial Sharing Buttons Widget', 'addnow' ) ?></h4>
			<ol>
				<li><?php _e( 'To start using the AddNow plugin you first need to signup for an account on <a href="http://addnow.com">AddNow.com</a>.', 'addnow' ) ?></li>
				<li><?php _e( 'After you have created your account, follow the steps for creating one or more <a href="https://support.addnow.com/hc/en-us/articles/204075999-Getting-Started-with-AddNow">Sharing Buttons Widgets</a> to be used on your website.', 'addnow' ) ?></li>
                <li><?php _e( 'In the settings, specify Api key which can be obtained from AddNow', 'addnow' ) ?></li>
            </ol>

			<h4><?php _e( 'Adding the AddNow.com Sharing Buttons Widget(s) to your website' )?></h4>
			<ol>
				<li><?php _e( "On your website to go Settings > AddNow then to the 'Widgets' tab. Click the 'Add New' button.'", 'addnow' ) ?></li>
				<li><?php _e( "Enter a 'Name' for the Widget. This is suggested to be the same name as the Sharing Buttons Widget you created on AddNow.com", 'addnow' )?></li>
				<li><?php _e( "Select widget from existing. Before making a choice, create it on AddNow.com", 'addnow' ) ?></li>
                <li><?php _e( "In the 'Visibility and Placement' section select where you want the Sharing Buttons Widget to be displayed." , 'addnow' ); ?></li>
			</ol>
			<?php
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function show_content_tab_widgets() {
			global $addnow_plugin;
			$this->getWidget();
			?>
			<p><a href="<?php echo remove_query_arg( array( 'widget_id', 'action' ) ) ?>">&larr; <?php _e( 'Return to Widgets', 'addnow' ); ?></a></p>

			<h3><?php echo $this->tabs[$this->tab_current]['title'] ?></h3>


            <?php  if (!empty($addnow_plugin->settings['entity'])) { ?>

                <input type="hidden" name="addnow_form[widget][id]" id="addnow-form-widget-id"
                       value="<?php echo stripslashes( $this->current_widget['id'] ) ?>"/>
                <table id="addnow-<?php echo $this->action ?>-<?php echo $this->tab_current ?>" class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="addnow-form-widget-name"><?php _e( 'Widget Name',
									'addnow' ) ?></label><?php echo $this->get_help_item( 'widget_name', 'tip' ); ?>
                        </th>
                        <td>
                            <input type="text" name="addnow_form[widget][name]" id="addnow-form-widget-name"
                                   class="large-text"
                                   value="<?php echo stripslashes( $this->current_widget['name'] ) ?>"/><br/>
                            <span class="description"><?php _e( "This is a name to identify this Widget from other widgets on your site. You can use the same name as the widget you created via your AddNow Dashboard",
									'addnow' ) ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="addnow-form-widget-active"><?php _e( 'Widget Active',
									'addnow' ) ?></label><?php echo $this->get_help_item( 'widget_active', 'tip' ); ?>
                        </th>
                        <td>
                            <select name="addnow_form[widget][active]" id="addnow-form-widget-name">
                                <option value="yes" <?php selected( 'yes',
									$this->current_widget['active'] ) ?>><?php _e( 'Yes', 'addnow' ) ?></option>
                                <option value="no" <?php selected( 'no',
									$this->current_widget['active'] ) ?>><?php _e( 'No', 'addnow' ) ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="addnow-form-widget-code"><?php _e( 'Widget AddNow',
									'addnow' ) ?></label><?php echo $this->get_help_item( 'widget_entity', 'tip' ); ?>
                        </th>
                        <td>
                            <select name="addnow_form[widget][entity]" id="addnow-form-widget-entity">
								<?php
								if ( isset( $addnow_plugin->settings['entity'] ) ) {
									foreach ( $addnow_plugin->settings['entity'] as $entity ) {
										_e( '<option value="' . $entity->id . '"' . selected( $entity->id,
												$this->current_widget['entity']->id ) . '>' . $entity->name . ': ' . $entity->domain . '</option>',
											'addnow' );
									}
								}
								?>
                            </select>
                        </td>
                    </tr>

					<?php if ( ! empty( $this->current_widget['id'] ) ) { ?>
                        <tr>
                            <th scope="row">
                                <label for="addnow-form-widget-shortcode"><?php _e( 'Widget Shortcode',
										'addnow' ) ?></label><?php echo $this->get_help_item( 'widget_shortcode',
									'tip' ); ?>
                            </th>
                            <td>
								<?php echo '<code>[addnow id="' . stripslashes( $this->current_widget['id'] ) . '"]</code>'; ?>
                                <br/><br/>
                                <span class="description"><?php _e( "The shortcode above can be used where you need better control over the placement of the AddNow buttons.",
										'addnow' ) ?></span>
                            </td>
                        </tr>

					<?php } ?>

                    <tr>
                        <th scope="row">
                            <label for="addnow-form-settings-where-placement"><?php _e( 'Visibility and Placement',
									'addnow' ) ?></label><?php echo $this->get_help_item( 'where', 'tip' ); ?>
                        </th>
                        <td>
                            <p class="description"><?php _e( 'This settings controls on what page types of your site the AddNow buttons will be displayed.',
									'addnow' ); ?></p>

                            <input type="hidden" id="addnow-form-widget-placement-labels"
                                   name="addnow_form[widget][placement_labels]" values=""/>
							<?php
							$show_on_front = get_option( 'show_on_front' );
							$page_on_front = get_option( 'page_on_front' );
							$page_for_posts = get_option( 'page_for_posts' );
							?>

                            <div id="addnow-placement-accordion-wrap">

                                <h3><?php _e( 'Home and Search', 'addnow' ); ?> <span
                                            class="addnow-placement-selections"></span></h3>
                                <div class="addnow-placement-accordion-item">
                                    <table class="addnow-settings-placement-list form-table widefat fixed striped"
                                           style="width:100%">
                                        <thead>
                                        <tr>
                                            <th><?php _e( 'Visibility', 'addnow' ) ?></th>
                                            <th style="text-align: center;"
                                                colspan="<?php echo count( $this->placement_options ) ?>"><?php _e( 'Placement',
													'addnow' ) ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
										<?php if ( $show_on_front == 'posts' ) { ?>
                                            <tr>
                                                <th>
                                                    <label for="addnow-form-widget-where-home"><?php _e( 'Home - Latest Posts',
															'addnow' ); ?></label></th>
												<?php
												foreach ( $this->placement_options as $placement_key => $placement_label ) {
													?>
                                                    <td><input type="checkbox" value="<?php echo $placement_key ?>"
                                                               id="addnow-form-widget-placement-is_home-<?php echo $placement_key ?>"
                                                               name="addnow_form[widget][placement][is_home][<?php echo $placement_key ?>]" <?php if ( isset( $this->current_widget['placement']['is_home'][ $placement_key ] ) ) {
														echo ' checked="checked" ';
													} ?> /> <label
                                                            for="addnow-form-widget-placement-is_home-<?php echo $placement_key ?>"><?php echo $placement_label; ?></label>
                                                    </td><?php
												}
												?>
                                            </tr>
										<?php } elseif ( $show_on_front === 'page' ) { ?>
											<?php if ( ! empty( $page_on_front ) ) { ?>
                                                <tr>
                                                    <th>
                                                        <label for="addnow-form-widget-where-home"><?php _e( 'Home - Page',
																'addnow' ); ?><?php echo ' - ' . get_the_title( $page_on_front ) ?></label>
                                                    </th><?php

													foreach ( $this->placement_options as $placement_key => $placement_label ) {
														?>
                                                        <td><input type="checkbox" value="<?php echo $placement_key ?>"
                                                                   id="addnow-form-widget-placement-is_home-page_on_front-<?php echo $placement_key ?>"
                                                                   name="addnow_form[widget][placement][is_home][page_on_front][<?php echo $placement_key ?>]" <?php if ( isset( $this->current_widget['placement']['is_home']['page_on_front'][ $placement_key ] ) ) {
																echo ' checked="checked" ';
															} ?> /> <label
                                                                    for="addnow-form-widget-placement-is_home-page_on_front-<?php echo $placement_key ?>"><?php echo $placement_label; ?></label>
                                                        </td>
														<?php
													}
													?>
                                                </tr>
											<?php } ?>
											<?php if ( ! empty( $page_for_posts ) ) { ?>
                                                <tr>
                                                    <th scope="row"><label
                                                                for="addnow-form-widget-where-home"><?php _e( 'Home - Posts',
																'addnow' ); ?><?php echo ' - ' . get_the_title( $page_for_posts ) ?></label>
                                                    </th><?php

													foreach ( $this->placement_options as $placement_key => $placement_label ) {
														?>
                                                        <td><input type="checkbox" value="<?php echo $placement_key ?>"
                                                                   id="addnow-form-widget-placement-is_home-page_for_posts-<?php echo $placement_key ?>"
                                                                   name="addnow_form[widget][placement][is_home][page_for_posts][<?php echo $placement_key ?>]" <?php if ( isset( $this->current_widget['placement']['is_home']['page_for_posts'][ $placement_key ] ) ) {
																echo ' checked="checked" ';
															} ?> /> <label
                                                                    for="addnow-form-widget-placement-is_home-page_for_posts-<?php echo $placement_key ?>"><?php echo $placement_label; ?></label>
                                                        </td>
														<?php
													}
													?>
                                                </tr>
											<?php } ?>
										<?php } ?>

                                        <tr>
                                            <th scope="row"><label
                                                        for="addnow-form-widget-where-search"><?php _e( 'Search',
													'addnow' ); ?></th>
											<?php
											foreach ( $this->placement_options as $placement_key => $placement_label ) {
												?>
                                                <td><input type="checkbox" value="<?php echo $placement_key ?>"
                                                           id="addnow-form-widget-placement-is_search-<?php echo $placement_key ?>"
                                                           name="addnow_form[widget][placement][is_search][<?php echo $placement_key ?>]" <?php if ( isset( $this->current_widget['placement']['is_search'][ $placement_key ] ) ) {
														echo ' checked="checked" ';
													} ?> /> <label
                                                            for="addnow-form-widget-placement-is_search-<?php echo $placement_key ?>"><?php echo $placement_label; ?></label>
                                                </td>
												<?php
											}
											?>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
								<?php
								$post_types = get_post_types( array( 'public' => true, 'show_ui' => true ), 'objects' );
								if ( isset( $post_types['attachment'] ) ) {
									unset( $post_types['attachment'] );
								}
								if ( ! empty( $post_types ) ) {
									?>
                                    <h3><?php _e( 'Single Post Types', 'addnow' ); ?> <span
                                                class="addnow-placement-selections"></span></h3>
                                    <div class="addnow-placement-accordion-item">
                                        <table class="addnow-settings-placement-list form-table widefat fixed striped"
                                               style="width:100%">
                                            <thead>
                                            <tr>
                                                <th><?php _e( 'Visibility', 'addnow' ) ?></th>
                                                <th style="text-align: center;"
                                                    colspan="<?php echo count( $this->placement_options_singular ) ?>"><?php _e( 'Placement',
														'addnow' ) ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
											<?php
											foreach ( $post_types as $post_type_slug => $post_type ) {
												?>
                                                <tr>
                                                    <th scope="row"><label
                                                                for="addnow-form-widget-where-post-types-single-<?php echo $post_type_slug ?>"><?php echo $post_type->labels->singular_name ?></label>
                                                    </th>
													<?php
													foreach ( $this->placement_options_singular as $placement_key => $placement_label ) {
														?>
                                                        <td><input type="checkbox" value="top"
                                                                   id="addnow-form-widget-placement-is_single-<?php echo $post_type_slug ?>-<?php echo $placement_key ?>"
                                                                   name="addnow_form[widget][placement][is_single][<?php echo $post_type_slug ?>][<?php echo $placement_key ?>]" <?php if ( isset( $this->current_widget['placement']['is_single'][ $post_type_slug ][ $placement_key ] ) ) {
															echo ' checked="checked" ';
														} ?>><label
                                                                for="addnow-form-widget-placement-is_single-<?php echo $post_type_slug ?>-<?php echo $placement_key ?>"><?php echo $placement_label; ?></label>
                                                        </td><?php
													}
													?>
                                                </tr>
												<?php
											}
											?>
                                            </tbody>
                                        </table>
                                    </div>
									<?php
								}
								?>
								<?php
								$post_types = get_post_types( array( 'public' => true, 'has_archive' => true ),
									'objects' );
								if ( isset( $post_types['attachment'] ) ) {
									unset( $post_types['attachment'] );
								}
								if ( ! empty( $post_types ) ) {

									?>
                                    <h3><?php _e( 'Archives for Post Types', 'addnow' ); ?> <span
                                                class="addnow-placement-selections"></span></h3>
                                    <div class="addnow-placement-accordion-item">
                                        <table class="addnow-settings-placement-list form-table widefat fixed striped"
                                               style="width:100%">
                                            <thead>
                                            <tr>
                                                <th><?php _e( 'Visibility', 'addnow' ) ?></th>
                                                <th style="text-align: center;"
                                                    colspan="<?php echo count( $this->placement_options ) ?>"><?php _e( 'Placement',
														'addnow' ) ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
											<?php
											foreach ( $post_types as $post_type_slug => $post_type ) {
												?>
                                                <tr>
                                                    <th scope="row"><label
                                                                for="addnow-form-widget-where-post-types-is_archive-<?php echo $post_type_slug ?>"><?php echo $post_type->labels->singular_name ?></label>
                                                    </th>
													<?php
													foreach ( $this->placement_options as $placement_key => $placement_label ) {
														?>
                                                        <td><input type="checkbox" value="top"
                                                                   id="addnow-form-widget-placement-is_archive-<?php echo $post_type_slug ?>-<?php echo $placement_key ?>"
                                                                   name="addnow_form[widget][placement][is_archive][<?php echo $post_type_slug ?>][<?php echo $placement_key ?>]" <?php if ( isset( $this->current_widget['placement']['is_archive'][ $post_type_slug ][ $placement_key ] ) ) {
															echo ' checked="checked" ';
														} ?>><label
                                                                for="addnow-form-widget-placement-is_archive-<?php echo $post_type_slug ?>-<?php echo $placement_key ?>"><?php echo $placement_label; ?></label>
                                                        </td><?php
													}
													?>
                                                </tr>
												<?php
											}
											?>
                                            </tbody>
                                        </table>
                                    </div>
									<?php
								}
								?>
								<?php
								$taxonomies = get_taxonomies( array( 'public' => true, 'show_ui' => true ), 'objects' );
								if ( ! empty( $taxonomies ) ) {
									?>
                                    <h3><?php _e( 'Archives for Taxonomies', 'addnow' ) ?> <span
                                                class="addnow-placement-selections"></span></h3>
                                    <div class="addnow-placement-accordion-item">
                                        <table class="addnow-settings-placement-list form-table widefat fixed striped"
                                               style="width:100%">
                                            <thead>
                                            <tr>
                                                <th><?php _e( 'Visibility', 'addnow' ) ?></th>
                                                <th style="text-align: center;"
                                                    colspan="<?php echo count( $this->placement_options ) ?>"><?php _e( 'Placement',
														'addnow' ) ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
											<?php
											foreach ( $taxonomies as $taxonomy_slug => $taxonomy ) {
												if ( $taxonomy->public == true ) {
													?>
                                                    <tr>
                                                    <th scope="row"><label
                                                                for="addnow-form-taxonomy-archives<?php echo $taxonomy_slug ?>"><?php echo $taxonomy->labels->singular_name ?></label>
                                                    </th>
													<?php
													foreach ( $this->placement_options as $placement_key => $placement_label ) {
														?>
                                                        <td><input type="checkbox" value="top"
                                                                   id="addnow-form-widget-placement-is_archive-<?php echo $taxonomy_slug ?>-<?php echo $placement_key ?>"
                                                                   name="addnow_form[widget][placement][is_archive][<?php echo $taxonomy_slug ?>][<?php echo $placement_key ?>]" <?php if ( isset( $this->current_widget['placement']['is_archive'][ $taxonomy_slug ][ $placement_key ] ) ) {
															echo ' checked="checked" ';
														} ?>><label
                                                                for="addnow-form-widget-placement-is_archive-<?php echo $taxonomy_slug ?>-<?php echo $placement_key ?>"><?php echo $placement_label; ?></label>
                                                        </td><?php
													}
													?>
                                                    </tr><?php
												}
											}
											?>
                                            </tbody>
                                        </table>
                                    </div>
									<?php
								}
								?>
								<?php
								$archive_types = array(
									'date' => __( 'Date', 'addnow' ),
								);
								if ( ! empty( $archive_types ) ) {
									?>
                                    <h3><?php _e( 'Archives General', 'addnow' ) ?> <span
                                                class="addnow-placement-selections"></span></h3>
                                    <div class="addnow-placement-accordion-item">
                                        <table class="addnow-settings-placement-list form-table widefat fixed striped"
                                               style="width:100%">
                                            <thead>
                                            <tr>
                                                <th><?php _e( 'Visibility', 'addnow' ) ?></th>
                                                <th style="text-align: center;"
                                                    colspan="<?php echo count( $this->placement_options ) ?>"><?php _e( 'Placement',
														'addnow' ) ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
											<?php
											foreach ( $archive_types as $archive_slug => $archive_label ) {
												if ( $taxonomy->public === true ) {
													?>
                                                    <tr>
                                                    <th scope="row"><label
                                                                for="addnow-form-taxonomy-archives<?php echo $archive_slug ?>"><?php echo $archive_label ?></label>
                                                    </th>
													<?php
													foreach ( $this->placement_options as $placement_key => $placement_label ) {
														?>
                                                        <td><input type="checkbox" value="top"
                                                                   id="addnow-form-widget-placement-is_archive-<?php echo $archive_slug ?>-<?php echo $placement_key ?>"
                                                                   name="addnow_form[widget][placement][is_archive][<?php echo $archive_slug ?>][<?php echo $placement_key ?>]" <?php if ( isset( $this->current_widget['placement']['is_archive'][ $archive_slug ][ $placement_key ] ) ) {
															echo ' checked="checked" ';
														} ?>><label
                                                                for="addnow-form-widget-placement-is_archive-<?php echo $archive_slug ?>-<?php echo $placement_key ?>"><?php echo $placement_label; ?></label>
                                                        </td><?php
													}
													?>
                                                    </tr><?php
												}
											}
											?>
                                            </tbody>
                                        </table>
                                    </div>
									<?php
								}
								?>
                            </div>
                        </td>
                    </tr>
                </table>
                <p><input type="submit" value="<?php _e( 'Submit', 'addnow' ); ?>" class="button button-primary"/></p>
            <?php
			} else {
            ?>
                <div id="addnow-settings-errors" class="error below-h2"><?php
                    if (empty($addnow_plugin->settings['tracking_key'])) {
	                    _e( 'Set in settings Api key from <a title="Go to AddNow.com Dashboard" href="http://addnow.com/site/dashboard/">AddNow</a>',
		                    'addnow' );
                    } else {
	                    _e( 'Add widgets to <a title="Go to AddNow.com Dashboard" href="http://addnow.com/site/dashboard/">AddNow</a>',
		                    'addnow' );
                    }
                    ?></div>
            <?php
			}
            ?>

			<?php

		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function show_content_tab_settings() {
			global $addnow_plugin;

			?>
			<h3><?php echo $this->tabs[ $this->tab_current]['title'] ?></h3>

			<table id="addnow-settings-<?php echo $this->action ?>-<?php echo $this->tab_current ?>" class="form-table">
				<tr>
					<th scope="row">
						<label for="addnow-form-settings-tracking-key"><?php _e('Api key', 'addnow') ?></label><?php echo $this->get_help_item('tracking_key', 'tip'); ?>
					</th>
					<td>
						<p class="description"><?php _e('The Api key from AddNow.com is the same for all widgets used on your site. ', 'addnow'); ?></p>
						<input name="addnow_form[tracking_key]" id="addnow-form-settings-tracking-key" class="large-text" value="<?php echo stripslashes(urldecode($addnow_plugin->settings['tracking_key'])) ?>"/>
					</td>
				</tr>
			</table>



			<p><input type="submit" value="<?php _e( 'Submit', 'addnow' ); ?>" class="button button-primary" /></p>
			<?php
		}

		/**
		 *
		 * @author  Paul Menard <paul@codehooligans.com>
		 *
		 * @since 1.0
		 *
		 */
		function delete_widget( $widget_id = '' ) {
			global $addnow_plugin;

			if ( ! empty( $widget_id ) ) {
				if ( isset( $addnow_plugin->settings['widgets'][$widget_id] ) ) {
					unset( $addnow_plugin->settings['widgets'][$widget_id] );
					return true;
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
		function bulk_delete_widgets() {
			$delete_count = 0;
			if ((isset($_POST['addnow-widgets-bulk-items'])) && (!empty($_POST['addnow-widgets-bulk-items']))) {
				foreach($_POST['addnow-widgets-bulk-items'] as $widget_id) {
					$return_status = $this->delete_widget($widget_id);
					if ($return_status == true)
						$delete_count += 1;
				}
			}
			return $delete_count;
		}


		function getWidget() {
			global $addnow_plugin;

			$response = wp_remote_get( self::API_URL.'api/v1/sites', array('headers' => 'Authorization: api-key '. $addnow_plugin->settings['tracking_key'] ));

			if(! empty($response->errors)) {
				$this->action_errors[$this->action] = $response->errors;
				return;
			}

			if($response['response']['code'] !== 200) {
				$this->action_errors[$this->action] = json_decode( $response['body'] );
				return;
			}

			$sites = json_decode( $response['body'] );

			$widgets = [];

			foreach ($sites as $site) {
				$response = wp_remote_get( self::API_URL.'api/v1/sites/'.$site->id.'/widgets', array('headers' => 'Authorization: api-key '. $addnow_plugin->settings['tracking_key'] ));
				if(! empty($response->errors)) {
					$this->action_errors[$this->action] = $response->errors;
					return;
				}

				if($response['response']['code'] !== 200) {
					$this->action_errors[$this->action] = json_decode( $response['body'] );
					return;
				}

				$response = array_map(function ($item) use ($site){
					$item->hash_id = $site->hash_id;
					$item->domain = $site->domain;
					return $item;
				}, json_decode( $response['body'] ));

				$widgets = array_merge($widgets, $response);
			}

			$addnow_plugin->settings['entity'] = $widgets;
			$addnow_plugin->update_settings();
        }


		/**
		 * This function is called from admin_menu_settings() and handles processing the settings form updates
		 *
		 * @since 1.0.0
		 * @param none
		 * @return none
		 */
		function on_process_actions() {
			global $addnow_plugin;

			if ( ( isset( $_POST['action'] ) ) && ( !empty( $_POST['action'] ) ) && ( $_POST['action'] != '-1' ) ) {
				$this->action = esc_attr( $_POST['action'] );
			} else if ( ( isset( $_POST['action2'] ) ) && ( !empty( $_POST['action2'] ) ) && ( $_POST['action2'] != '-1' ) ) {
				$this->action = esc_attr( $_POST['action2'] );
			} else 	if (( isset( $_GET['action'] ) ) && ( !empty( $_GET['action'] ) ) ) {
				$this->action = esc_attr( $_GET['action'] );
			}

			if ($this->tab_current == 'widgets') {

				if ( ( $this->action == 'edit' )
					&& ( isset( $_GET['widget_id'] ) )
					&& ( isset( $addnow_plugin->settings['widgets'][$_GET['widget_id']] ) ) ) {
					$this->current_widget = $addnow_plugin->settings['widgets'][$_GET['widget_id']];
					if (!isset($this->current_widget['active'])) $this->current_widget['active'] = 'yes';

					$this->action = 'update';

				} else if ( $this->action == 'new' ) {
					$this->current_widget['id']			=	'';
					$this->current_widget['name'] 		= 	'';
					$this->current_widget['active']		=	'yes';
					$this->current_widget['entity'] 	= 	'';
					$this->current_widget['code'] 		= 	'';
					$this->current_widget['where']		=	array();
					$this->current_widget['placement']	=	array();

					$this->action = 'update';

					return;

				} elseif ($this->action == 'update') {

					if ( ( isset( $_POST['addnow_form']['widget'] ) ) && ( !empty( $_POST['addnow_form']['widget'] ) ) ) {

						if ( ( empty( $_POST['addnow_form']['widget']['name'] ) ) ) {
							$this->action_errors[$this->action] = ['Widget Name' => [0 => 'This field can not be empty.']];
							return;
						}

						$nonce_key = $this->settings_page .'-'. $this->action .'-'. $this->tab_current .'-nonce';

						if ( !isset( $_POST[$nonce_key] ) ) {
							return;
						}

						if ( !wp_verify_nonce( $_POST[$nonce_key], $nonce_key ) ) {
							return;
						}

						$widget = $_POST['addnow_form']['widget'];
						if ( ( !isset( $widget['id'] ) ) || ( empty( $widget['id'] ) ) ) {
							$widget_action = 'new';
							$widget['id'] = sanitize_title_with_dashes($widget['name'], '', 'save');

							$widget_id = $widget['id'];
							$_iter = 1;
							while(true) {
								$existing_widget = $addnow_plugin->get_setting( $widget_id, 'widgets' );
								if (!empty($existing_widget)) {
									$_iter += 1;
									$widget_id = $widget['id'] .'-'. $_iter;
								} else {
									$widget['id'] = $widget_id;
									break;
								}
							}
						}
                        if ((isset($widget['id'])) && (!empty($widget['id']))) {
							$SETTINGS_CHANGED = true;
							$addnow_plugin->settings['widgets'][$widget['id']] = $widget;


							$this->current_widget = $widget;

							if ( ( isset( $_POST['addnow_form']['widget']['entity'] ) )
								&& ( !empty( $_POST['addnow_form']['widget']['entity'] ) ) ) {

							    $this->getWidget();
							    foreach ($addnow_plugin->settings['entity'] as $entity) {
								    if ($entity->id == $_POST['addnow_form']['widget']['entity']) {
									    $this->current_widget['entity'] = $entity;
									    $this->current_widget['code'] = '<div class="addnow" data-id="' . $entity->id . '"></div>';
                                    }
                                }

								$addnow_plugin->settings['widgets'][$widget['id']] = $this->current_widget;

							}

							$this->action_messages[$this->action] = __('Widget Saved', 'addnow');
							$addnow_plugin->update_settings();
						}
					}

				} else {
					if ( $this->action == 'delete' ) {

						if ( ! isset($_GET['widget_id']) ) {
							return;
						}
						$widget_id = esc_attr( $_GET['widget_id'] );
						if ( ! isset( $_GET['addnow-nonce'] ) ) {
							return;
						}
						$nonce_value = 'addnow-plugin-delete-widget-nonce-'. $widget_id;
						if ( !wp_verify_nonce( $_GET['addnow-nonce'], $nonce_value ) ) {
							return;
						}

						$SETTINGS_CHANGED = $this->delete_widget($widget_id);
						if ( $SETTINGS_CHANGED === true ) {

							$this->action_messages[$this->action] = __('Widget Deleted', 'addnow');

							$addnow_plugin->update_settings();
						}
					} else if ($this->action == 'delete-widget') {
						$delete_count = $this->bulk_delete_widgets();
						if ($delete_count > 0) {
							$this->action_messages[$this->action] = sprintf(__('%d Widget(s) Deleted', 'addnow'), intval($delete_count));

							$addnow_plugin->update_settings();
						}
					}

					if ( isset( $addnow_plugin->settings['widgets'] ) && ! empty( $addnow_plugin->settings['widgets'] ) ) {
						include dirname( __FILE__ ) . "/class-addnow-admin-panel-widgets-list-table.php";
						$this->list_table = new AddNow_Admin_Panel_Widgets_View_List_Table();

						$screen = get_current_screen();
						$screen_per_page_option = str_replace( '-', '_', $screen->id ."_per_page" );

						if ((isset($_POST['wp_screen_options']['option']))
							&& ($_POST['wp_screen_options']['option'] == $screen_per_page_option)) {

							if (isset($_POST['wp_screen_options']['value'])) {
								$per_page = intval($_POST['wp_screen_options']['value']);
								if ((!$per_page) || ($per_page < 1)) {
									$per_page = 20;
								}

								update_user_meta( get_current_user_id(), $screen_per_page_option, $per_page );
							}
						}
						$per_page = get_user_meta( get_current_user_id(), $screen_per_page_option, true );
						if ( empty( $per_page ) || $per_page < 1 ) {
							$per_page = 20;
						}

						$this->list_table->per_page = $per_page;
						add_screen_option( 'per_page', array( 'label' => __( 'per Page', 'wp-product' ), 'default' => $per_page ) );
					} else {
						$this->current_widget['id'] = '';
						$this->current_widget['name'] = '';
						$this->current_widget['active'] = 'yes';
						$this->current_widget['entity'] = '';
						$this->current_widget['code'] = '';
						$this->current_widget['placement'] = array();
						$this->action = 'update';
					}
					return;
				}
			} else if ( $this->tab_current == 'settings' ) {
				if ( isset( $_POST['addnow_form']['tracking_key'] ) ) {
					$nonce_key = $this->settings_page .'-'. $this->action .'-'. $this->tab_current .'-nonce';

					if ( ! isset( $_POST[ $nonce_key ] ) ) {
						return;
					}

					if ( ! wp_verify_nonce( $_POST[$nonce_key], $nonce_key ) ) {
						return;
					}

					if ( ! empty($_POST['addnow_form']['tracking_key']) ) {

						$response = wp_remote_get( self::API_URL.'api/v1/sites', array('headers' => 'Authorization: api-key '. $_POST['addnow_form']['tracking_key'] ));

						if(! empty($response->errors)) {
							$this->action_errors[$this->action] = $response->errors;
							$addnow_plugin->settings['tracking_key'] = '';
							$addnow_plugin->settings['entity'] = [];
							$addnow_plugin->update_settings();
							return;
						}

						if($response['response']['code'] !== 200) {
							$this->action_errors[$this->action] = json_decode( $response['body'] );
							$addnow_plugin->settings['tracking_key'] = '';
							$addnow_plugin->settings['entity'] = [];
							$addnow_plugin->update_settings();
							return;
						}

						$addnow_plugin->settings['tracking_key'] = urlencode( $_POST['addnow_form']['tracking_key'] );
					} else {
						$addnow_plugin->settings['tracking_key'] = '';
						$addnow_plugin->settings['entity'] = [];
					}

					$this->action_messages[$this->action] = __( 'Settings Saved', 'addnow' );

					$addnow_plugin->update_settings();

				}
			}

			return;
		}
	}
}
