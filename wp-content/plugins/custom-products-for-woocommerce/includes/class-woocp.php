<?php
/** @noinspection SpellCheckingInspection */

/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    WOOCP
 * @subpackage WOOCP/includes
 * @author     Grega Radelj <info@grrega.com>
 */
class WOOCP {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WOOCP_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $woocp    The string used to uniquely identify this plugin.
	 */
	protected $woocp;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Settings object
	 *
	 * @since    1.1.0
	 * @access   protected
	 * @var      string    $settings    Settings object.
	 */
	protected $settings;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WOOCP_VERSION' ) ) {
			$this->version = WOOCP_VERSION;
		} else {
			$this->version = '1.1.0';
		}
		$this->woocp = 'custom-products-for-woocommerce';

		$this->woocp_load_dependencies();
		$this->woocp_set_locale();
		$this->woocp_define_admin_hooks();
		$this->woocp_define_public_hooks();

		$this->settings = $this->woocp_return_settings();

		if(!is_object($this->settings)) $this->settings = json_decode(json_encode($this->settings));
		if(!is_object($this->settings)) $this->settings = new stdClass();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WOOCP_Loader. Orchestrates the hooks of the plugin.
	 * - WOOCP_i18n. Defines internationalization functionality.
	 * - WOOCP_Admin. Defines all hooks for the admin area.
	 * - WOOCP_Components. Contains product components editing functionality.
	 * - WOOCP_Public. Defines all hooks for the public side of the site.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function woocp_load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocp-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocp-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocp-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocp-components.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocp-public.php';

		$this->loader = new WOOCP_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WOOCP_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function woocp_set_locale() {

		$plugin_i18n = new WOOCP_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'woocp_load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function woocp_define_admin_hooks() {

		//init
		$this->loader->add_action('init', $this, 'woocp_register_components');
		$this->loader->add_action('init', $this, 'woocp_register_image_sizes');

		//if on admin page
		if (is_admin()){
			$plugin_admin = new WOOCP_Admin( $this->woocp, $this->version );
			$plugin_components = new WOOCP_Components( $this->woocp, $this->version );

			$this->loader->add_action( 'admin_init', $plugin_admin, 'woocp_setup_taxonomy_based_fields' );
			$this->loader->add_action( 'created_term', $plugin_admin, 'woocp_save_term_image', 10, 3 );
			$this->loader->add_action( 'edit_term', $plugin_admin, 'woocp_save_term_image', 10, 3 );

			//scripts
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'woocp_admin_enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'woocp_admin_enqueue_scripts' );

			//plugin action links
			$this->loader->add_filter( 'plugin_action_links_'.$this->woocp.'/'.$this->woocp.'.php', $this, 'woocp_action_links', 10, 3 );
			$this->loader->add_filter( 'plugin_row_meta', $this, 'woocp_plugin_desc_links', 10, 4 );

			//admin navigation
			$this->loader->add_filter( 'woocommerce_settings_tabs_array', $plugin_admin, 'woocp_settings_tab', 50, 1 );
			$this->loader->add_filter( 'woocommerce_settings_tabs_custom_products', $plugin_admin, 'woocp_settings_content', 50, 1 );
			$this->loader->add_action( 'woocommerce_update_options_custom_products', $plugin_admin, 'woocp_update_settings', 50, 1 );
			$this->loader->add_action( 'admin_init', $this, 'woocp_is_in_admin', 10, 2 );

			//add customizer tab and panel to product settings panel
			if(strpos($_SERVER['REQUEST_URI'], 'post-new') == false){
				$this->loader->add_filter( 'woocommerce_product_data_tabs', $plugin_admin, 'woocp_product_customizer_tab' );
				$this->loader->add_filter( 'woocommerce_product_data_panels', $plugin_admin, 'woocp_product_customizer_panel' );
			}

			//save product customizer
			$this->loader->add_action( 'save_post', $plugin_admin, 'woocp_save_product_customizer' );

			//add is customizable column to product list
			$this->loader->add_action( 'manage_product_posts_custom_column', $this, 'woocp_is_customizable_column', 10, 2 );
			$this->loader->add_filter( 'manage_edit-product_columns', $this, 'woocp_is_customizable_header', 10, 1 );
			$this->loader->add_filter( 'manage_edit-product_sortable_columns', $this, 'woocp_products_list_sortable_column', 10, 1 );
			$this->loader->add_action( 'pre_get_posts', $this, 'woocp_sort_products_by_customizable', 10, 1 );

			//hide custom fields from product page
			$this->loader->add_filter( 'isprotected_meta', $this, 'woocp_isprotected_meta_filter', 10, 2 );

			//AJAX
			$this->loader->add_action( 'wp_ajax_woocp_update_customizable', $plugin_admin, 'woocp_update_customizable', 10, 2 );
			$this->loader->add_action( 'wp_ajax_woocp_add_product_component', $plugin_components, 'woocp_add_product_component', 10, 2 );
			$this->loader->add_action( 'wp_ajax_woocp_save_product_components', $plugin_components, 'woocp_save_product_components', 10, 2 );
			$this->loader->add_action( 'wp_ajax_woocp_save_customizer_image', $plugin_admin, 'woocp_save_customizer_image', 10, 2 );
			$this->loader->add_action( 'wp_ajax_woocp_add_component_attribute', $plugin_components, 'woocp_add_component_attribute', 10, 2 );
			$this->loader->add_action( 'wp_ajax_woocp_dismissed_notice_handler', $this, 'woocp_dismissed_notice_handler', 10,  2 );
			$this->loader->add_action( 'wp_ajax_woocp_remove_customizer_image', $plugin_admin, 'woocp_remove_customizer_image', 10, 2 );

		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function woocp_define_public_hooks() {

		$plugin_public = new WOOCP_Public( $this->get_woocp(), $this->woocp_get_version() );
		$settings = $this->woocp_return_settings();

		//scripts
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'woocp_public_enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'woocp_public_enqueue_scripts' );

		//override templates
		$this->loader->add_filter( 'woocommerce_locate_template',$this, 'woocp_template_loader',10,2);

		//show button on product page
		if(isset($settings->woocp_full_product_button) && (bool)$settings->woocp_full_product_button && isset($settings->woocp_customize_button_position)){
			$position = $settings->woocp_customize_button_position;
			$this->loader->add_action( 'woocommerce_'.$position.'_add_to_cart_button', $plugin_public, 'woocp_product_page_button',10,2 );
		}
		//add hidden input before add to cart button on product page
		$this->loader->add_action( 'woocommerce_before_add_to_cart_button', $plugin_public, 'woocp_before_atc_array',10,2 );

		//shortcode
		add_shortcode( 'woocp', array('WOOCP_Public', 'woocp_shortcode') );
		add_shortcode( 'woocp_single_product', array('WOOCP_Public', 'woocp_shortcode_single_product') );

		//add to cart
		$this->loader->add_filter( 'woocommerce_add_cart_item_data', $plugin_public, 'woocp_add_cart_item_data', 10, 2 );
		$this->loader->add_filter( 'woocommerce_get_cart_item_from_session', $plugin_public, 'woocp_get_cart_item_from_session', 10, 2 );
		$this->loader->add_filter( 'woocommerce_get_item_data', $plugin_public, 'woocp_cart_details', 10, 2 );
		$this->loader->add_action( 'woocommerce_new_order_item', $plugin_public, 'woocp_add_order_item_meta', 10, 3 );

		//AJAX
		$this->loader->add_action( 'wp_ajax_woocp_change_product', $plugin_public, 'woocp_change_product', 10, 2 );
		$this->loader->add_action( 'wp_ajax_nopriv_woocp_change_product', $plugin_public, 'woocp_change_product', 10, 2 );
		$this->loader->add_action( 'wp_ajax_woocp_add_to_cart', $plugin_public, 'woocp_add_to_cart', 10, 2 );
		$this->loader->add_action( 'wp_ajax_nopriv_woocp_add_to_cart', $plugin_public, 'woocp_add_to_cart', 10, 2 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function woocp_run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_woocp() {
		return $this->woocp;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WOOCP_Loader    Orchestrates the hooks of the plugin.
	 */
	public function woocp_get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function woocp_get_version() {
		return $this->version;
	}

	/**
	 * Register components
	 *
	 * @since     1.0.0
	 */
	function woocp_register_components() {
		$labelsComponent = array(
			'name'               => _x( 'Components', 'post type general name', $this->woocp ),
			'singular_name'      => _x( 'Component', 'post type singular name', $this->woocp ),
			'menu_name'          => _x( 'Components', 'admin menu', $this->woocp ),
			'name_admin_bar'     => _x( 'Component', 'add new on admin bar', $this->woocp ),
			'add_new'            => _x( 'Add New', 'product_component', $this->woocp ),
			'add_new_item'       => __( 'Add New Component', $this->woocp ),
			'new_item'           => __( 'New Component', $this->woocp ),
			'edit_item'          => __( 'Edit Component', $this->woocp ),
			'view_item'          => __( 'View Component', $this->woocp ),
			'all_items'          => __( 'All Components', $this->woocp ),
			'search_items'       => __( 'Search Components', $this->woocp ),
			'parent_item_colon'  => __( 'Parent Components:', $this->woocp ),
			'not_found'          => __( 'No Components found.', $this->woocp ),
			'not_found_in_trash' => __( 'No Components found in Trash.', $this->woocp )
		);

		$slug = 'product_component';

		register_taxonomy(
			'product_component',
			array('product'),
			array(
				'hierarchical' => false,
				'label' => __( 'Components' , $this->woocp),
				'labels' => $labelsComponent,
				'meta_box_cb' => false,
				'rewrite' => array('slug'=>$slug)
			)
		);

		register_taxonomy_for_object_type( 'product_component', 'product' );
	}

	/**
	 * Register image sizes
	 *
	 * @since     1.0.0
	 */
	function woocp_register_image_sizes() {
		$h1 = $this->settings->woocp_product_image_height;
		$w1 = $this->settings->woocp_product_image_width;
		$h2 = $this->settings->woocp_option_image_height;
		$w2 = $this->settings->woocp_option_image_width;
		add_image_size( 'woocp_product_image', $w1, $h1, true ); // Hard Crop Mode
		add_image_size( 'woocp_option_image', $w2, $h2, true ); // Hard Crop Mode
	}

	/**
	 * Adds links to the plugin list
	 *
	 * @since     1.0.0
	 * @param 	  array 	$links
	 * @return 	  array 	$link
	 */
	function woocp_action_links($links) {
		$plugin_data = $this->woocp_get_plugin_data();
		$links[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=wc-settings&tab=custom_products') ) .'">'.__('Settings',$this->woocp).'</a>';
		$links[] = '<b>'.__('NETZDO',$this->woocp).'</b>';
		return $links;
	}

	/**
	 * Adds links to the plugin list - meta section
	 *
	 * @since     1.0.0
	 * @param    array   $links
	 * @param    string  $file
	 * @param            $a
	 * @param            $b
	 * @return   array   $link
	 */
	function woocp_plugin_desc_links($links, $file, $a, $b) {
		$newlinks = array();
		$plugin_data = $this->woocp_get_plugin_data();
		if(strpos($file,$plugin_data->plugin_reference.'.php') !== false){
			$newlinks[] = '<a href="'.$plugin_data->documentation_url.'" target="_blank">'.__('Documentation', $this->woocp).'</a>';
			$links = array_merge($links,$newlinks);
		}
		return $links;
	}

	/**
	 * Hides custom meta data from product page custom fields
	 *
	 * @since   1.0.0
	 * @param 	array 	$protected
	 * @param 	string 	$meta_key
	 * @return 	array 	$protected
	 */
	function woocp_isprotected_meta_filter($protected, $meta_key) {
		$protected_values = array(
			'_woocp_is_customizable',
			'_woocp_customizer_image_id',
			'_woocp_product_components',
		);
		if(in_array($meta_key,$protected_values)) return null;
		else return $protected;
	}

	/**
	 * Locate template.
	 *
	 * Locate the called template.
	 * Search Order:
	 * 1. /themes/THEME/woocp/$template_name	(parent + child)
	 * 2. /themes/THEME/$template_name			(parent + child)
	 * 3. /plugins/custom-products-for-woocommerce/templates/$template_name.
	 * 4. /plugins/woocommerce/templates/$template_name		(for overriding woocommerce templates if needed)
	 *
	 * @since   1.0.0
	 * @param 	string 	$template_name			Template to load.
	 * @param 	string 	$template_path			Path to templates.
	 * @param 	string	$default_path			Default path to template files.
	 * @return 	string 							Path to the template file.
	 */
	function woocp_locate_template( $template_name, $template_path = '', $default_path = '' ) {
		// Set variable to search in custom-products-for-woocommerce folder of theme.
		if ( ! $template_path ) {
			$template_path = 'woocommerce-custom-products/';
		}
		// Set default plugin templates path.
		if ( ! $default_path ) {
			$default_path = str_replace('includes/','',plugin_dir_path( __FILE__ )) . 'templates/'; // Path to the template folder
		}
		// Search template file in theme folder.
		$template = locate_template( array(
			$template_path . $template_name,
			$template_name
		) );
		// Get plugins template file.
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		// Get woocommerce template file.
		if ( !file_exists( $template ) ) {
			$template = WP_PLUGIN_DIR .'/'. $template_path .'/'. $template_name;
		}

		return apply_filters( 'woocp_locate_template', $template, $template_name, $template_path, $default_path );
	}

	/**
	 * Get template.
	 *
	 * Search for the given template and include the file.
	 *
	 * @since   1.0.0
	 * @param   string   $template_name  Template to load.
	 * @param   array    $args           Args passed for the template file.
	 * @param   null     $tempate_path
	 * @param   string   $default_path   Default path to template files.
	 */
	function woocp_get_template( $template_name, $args = array(), $tempate_path = null, $default_path = null ) {
		if ( is_array( $args ) && isset( $args ) ) {
			extract( $args );
		}
		$template_file = $this->woocp_locate_template( $template_name, $tempate_path, $default_path );
		if ( ! file_exists( $template_file ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), $this->version );
			return;
		}
		include $template_file;
	}
	/**
	 * WooCommerce template override
	 *
	 * Get woocommerce template, check if override exists anywhere (plugin, theme, parent theme)
	 * and return the override or original template.
	 *
	 * @since 1.0.0
	 * @param	string	$template	Template file that is being loaded.
	 * @return	string	$template	Template file that should be loaded (override if file exists else original).
	 */
	function woocp_template_loader( $template ) {
		$file = explode('\\',$template);
		$file = end($file);
		$file = explode('woocommerce/',$file);
		$file = end($file);
		$file = str_replace('templates/','',$file);

		$tpl = $this->woocp_locate_template( 'woocommerce/'.$file);

		if ( file_exists( $tpl ) ) {
			$template = $tpl;
			return $template;
		}
		return $template;
	}

	/**
	 * Saves the dismissed notice in session so it doesn't appear on next request
	 *
	 * @since 1.0.0
	 */
	function woocp_dismissed_notice_handler() {
		if(!session_id()) session_start();

		$type = $_POST['type'];
		$_SESSION['dismissed-'.$type] = TRUE;
		wp_die();
	}

	/**
	 * Adds a table header for is_customizable field to the product list page
	 *
	 * @since     1.0.0
	 * @param	  array	      $columns	  Existing table headers.
	 * @return    array|bool
	 */
	function woocp_is_customizable_header($columns) {
		if (!$this->woocp_check_perms()) return false;

		$new = array();
		foreach($columns as $key => $title){
			if($key == 'featured')
				$new['is_customizable'] = '<span class="woocp_customizable_icon parent-tips" data-tip="'.__('Customizable',$this->woocp).'">'.__('Customizable',$this->woocp).'</span>';
			$new[$key] = $title;
		}
		return $new;
	}

	/**
	 * Adds a table column for is_customizable field to the product list page
	 *
	 * @since     1.0.0
	 * @param     string $column
	 * @param     int    $post_id  Post ID.
	 * @return    bool
	 */
	function woocp_is_customizable_column($column,$post_id) {
		if (!$this->woocp_check_perms()) return false;

		if($column == 'is_customizable'){
			$val = get_post_meta($post_id,'_woocp_is_customizable');
			if(is_array($val) && isset($val[0]) && (bool) $val[0])
				echo '<span class="woocp_customizable_icon tips" data-tip="'.__('Is customizable',$this->woocp).'">'.__('Customizable',$this->woocp).'</span>';
		}
		return false;
	}

	/**
	 * Makes the is_customizable column sortable on the product list page
	 *
	 * @since     1.0.0
	 * @param	  array	      $columns	Existing table columns.
	 * @return    array|bool  $columns	New table columns.
	 */
	function woocp_products_list_sortable_column( $columns ){
		if (!$this->woocp_check_perms()) return false;

		$columns['is_customizable'] = 'is_customizable';
		return $columns;
	}

	/**
	 * Sort the is_customizable columns on the product list page
	 *
	 * @since     1.0.0
	 * @param     array   $query   Current product list query.
	 * @return    bool
	 */
	function woocp_sort_products_by_customizable($query) {
		if (!$this->woocp_check_perms()) return false;

		$orderby = $query->get( 'orderby');

		if ( 'is_customizable' == $orderby ) {
			$query->set( 'meta_key', '_woocp_is_customizable' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'meta_query', array(
					array(
						'key'     => '_woocp_is_customizable',
						'value'   => 1,
						'compare' => '=',
					),
				)
			);
		}
		return false;
	}

	/**
	 * Creates default settings for the plugin
	 *
	 * @since     1.0.0
	 */
	function woocp_create_default_settings() {
		if (!$this->woocp_check_perms()) return false;

		$defaults = array(
			'woocp_full_product_button' => 1,
			'woocp_component_arrow' => 1,
			'woocp_component_description' => 1,
			'woocp_customize_button_position' => 'before',
			'woocp_components_display' => 'collapsed',
			'woocp_components_position' => 'left',
			'woocp_customize_text' => '',
			'woocp_customized_text' => '',
			'woocp_no_change_text' => '',
			'woocp_select_product_text' => '',
			'woocp_components_title_tag' => "h4",
			'woocp_customizer_width' => '100%',
			'woocp_customizer_image_width' => 60,
			'woocp_option_image_height' => 40,
			'woocp_option_image_width' => 40,
			'woocp_product_image_height' => 40,
			'woocp_product_image_width' => 40,
			'woocp_customizer_url' => '',
		);
		$defaults = json_encode($defaults);
		add_option('woocp_settings',$defaults);
		return $defaults;
	}

	/**
	 * Gets the settings or creates default settings
	 *
	 * @since     1.0.0
	 * @return	  array	  $settings	  WOOCP settings.
	 */
	function woocp_get_settings() {
		$settings = get_option('woocp_settings');
		if(empty($settings)) {
			$this->woocp_create_default_settings();
			$settings = get_option('woocp_settings');
		}
		return json_decode($settings);
	}

	/**
	 * Returns the settings
	 *
	 * @since     1.0.0
	 * @return	  array	$settings	WOOCP settings.
	 */
	function woocp_return_settings() {
		if(!isset($this->settings)) $this->settings = $this->woocp_get_settings();
		return $this->settings;
	}

	/**
	 * Get filtered products
	 *
	 * @since     1.0.0
	 * @param     bool      $category_ids       Included category IDs
	 * @param     bool      $product_ids        Included product IDs
	 * @param     bool      $number_of_products Number of products to return
	 * @param     bool      $order              ASC or DESC
	 * @param     bool      $orderby            Name of field to order by
	 * @return    array     $products           Array of product objects
	 */
	public function woocp_get_customizable_products($category_ids=false, $product_ids=false, $number_of_products=false, $order=false, $orderby=false){

		$products = array();
		$i = 0;

        $args = array(
            'post_type'  => 'product',
            'posts_per_page'   => -1,
            'meta_query' => array(
                array(
                    'key'     => '_woocp_is_customizable',
                    'value'   => 1,
                ),
            ),
        );

		//filter by category
		if(is_array($category_ids) && count($category_ids) !== 0) {
			$cats = array();
			foreach($category_ids as $catId){
				$cats[] = array(
					'taxonomy'      => 'product_cat',
					'field' => 'term_id',
					'terms'         => $catId,
					'operator'      => 'IN'
				);
			}
			$args['tax_query'] = array($cats);
		}
		if(!is_array($product_ids) || count($product_ids) == 0) $product_ids = false;

		//set order
		if($order !== false) $args['order'] = $order;
		if($orderby !== false) $args['orderby'] = $orderby;

		//get products
		$posts = get_posts($args);

		foreach($posts as $post) {

			$prodId = $post->ID;
			$product = wc_get_product($prodId);

			//simple products
			if ($product->is_type('simple')) {
				//if products are filtered by product IDs and this one is one of them
				if (!$product_ids || is_array($product_ids) && in_array((int)$prodId, (array)$product_ids)) {
					//if number of products is not set or hasn't been reached yet
					if (!$number_of_products || $i < $number_of_products) {
						$i++;
						$products[] = $product;
					}
				}
			}

		}
		wp_reset_postdata();
		wp_reset_query();

		//if that's we have what we need, return product objects
		if(count($products) > 0) return $products;
		return array();
	}
	/**
	 * Get all components
	 *
	 * @since     1.0.0
	 * @param     array|bool  $ids       Component IDs to include
	 * @return    array       $return    Array of component objects
	 */
	public function woocp_get_components($ids=false){
		$args = array(
			'taxonomy'=>'product_component',
			'hide_empty'=>false,
		);
		if(is_array($ids) && count($ids) > 0){
			$args['include'] = $ids;
		}
		$components =  get_terms($args);
		return  $components;
	}

	/**
	 * Get all attributes
	 *
	 * @since     1.0.0
	 * @param     bool|int    $attrId     ID of the attribute to get
	 * @return    array       $attrs      Array of attribute objects
	 */
	public function woocp_get_attributes($attrId=false){
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		$attrs = array();

		if ( $attribute_taxonomies ) {
			foreach ($attribute_taxonomies as $tax) {
				$newOptions = array();
				if (!$attrId && taxonomy_exists(wc_attribute_taxonomy_name($tax->attribute_name)) || $attrId && (int) $tax->attribute_id == (int) $attrId && taxonomy_exists(wc_attribute_taxonomy_name($tax->attribute_name))) {
					$attrs[$tax->attribute_id] = (array) $tax;
					$options = get_terms( wc_attribute_taxonomy_name($tax->attribute_name), 'orderby=name&hide_empty=0' );
					foreach($options as $option) $newOptions[$option->term_id] = $option;
					$attrs[$tax->attribute_id]['options'] = $newOptions;
				}
			}
		}
		return $attrs;
	}

	/**
	 * Get product components
	 *
	 * @since     1.0.0
	 * @param     int       $post_id      Product ID
	 * @param     bool      $full         Include component data or just return the ids?
	 * @return    array	    $components    Array of component objects
	 */
	public function woocp_get_product_components($post_id, $full=false){
		$components = get_post_meta($post_id,'_woocp_product_components', true);
		if($full && $components){
			$ar = array();
			$i = 0;
			foreach((array)json_decode($components,true) as $key => $comp){
				$ar[$i] = $comp['id'];
				$i++;
			}
			if(count($ar) > 0) {
				$comps = $this->woocp_get_components($ar);
				$components = array();
				foreach($comps as $comp){
					$id = $comp->term_id;
					$key = array_search($id,$ar);
					$components[$key] = $comp;
				}
				ksort($components);
			}
			else $components = false;
		}
		if($components !== false && !is_array($components))$components = json_decode($components);
		return $components;
	}

	/**
	 * Gets the order of product components
	 *
	 * @since     1.0.0
	 * @param     int    $prodId       Product ID
	 * @return    array	    $order    	Array of ordered components ids
	 */
	public function woocp_get_product_components_order($prodId){
		$order = get_post_meta($prodId,'_woocp_product_components_order', true);
		return $order;
	}

	/**
	 * Check if product/variation is customizable
	 *
	 * If parent ID is given (is variable and variation selected) it checks if the parent is customizable,
	 * else $prodId is considered to be the parent (main product - is not variable).
	 * Image is always checked for the given $prodId
	 *
	 * @since     1.0.0
	 * @param     int        $prodId Product ID
	 * @return    string
	 */
	function woocp_is_customizable($prodId) {
		//if is_customizable is set for this product
		//simple products
		$result = (bool)get_post_meta($prodId,'_woocp_is_customizable', true);
		return $result;
	}

	/**
	 * Translates a term
	 *
	 * @since     1.0.0
	 * @param     bool|int       $prodId    Product ID
	 * @param     bool|string    $termName  Term to translate
	 * @return    string	     $return    Translated string
	 */
	function woocp_get_translation($prodId=false,$termName=false) {
		$wpml = $poly = false;
		//POLYLANG
		if( function_exists('pll_current_language') ) $poly = true;
		//WMPL
		else if( function_exists('icl_object_id') ) $wpml = true;

		if($poly){
			$current_lang = pll_current_language();
			if($termName){	//attribute translation
				$return = pll_translate_string($termName,$current_lang);
				return $return;
			}
		}
		return false;
	}

	/**
	 * Checks if user can manage WooCommerce
	 *
	 * @since     1.0.0
	 * @return    bool
	 */
	function woocp_check_perms() {
		if ( !current_user_can('manage_woocommerce') )  {
			return false;
		}
		return true;
	}

	/**
	 * Get plugin data for verification
	 *
	 * @since     1.0.0
	 * @return    object	$data    Plugin data
	 */
	static function woocp_get_plugin_data() {

		$data = new stdClass();
		$data->plugin_reference = 'custom-products-for-woocommerce';
		$data->plugin_name = 'Custom Products for WooCommerce';
		$data->plugin_url = 'https://grrega.com/projects/custom-products-for-woocommerce';
		$data->subscription_url = 'https://grrega.com/my-account/view-subscription/'.get_option('grr_subscription_id');
		$data->documentation_url = 'https://grrega.com/documentation/custom-products-for-woocommerce-docs';
		return $data;
	}

	function woocp_is_in_admin(){
		$ajax = wp_doing_ajax();
		if(!session_id()) session_start();

		if( current_user_can('manage_woocommerce') && !$ajax && isset($_SESSION['dismissed-upgrade_notice']) && $_SESSION['dismissed-upgrade_notice'] !== TRUE ) {
			add_action( 'admin_notices',array($this, 'woocp_upgrade_plugin_notice') );
		}
	}

	function woocp_upgrade_plugin_notice(){
		$data = self::woocp_get_plugin_data();
		echo '<div class="notice notice-info upgrade_notice is-dismissible" data-notice="upgrade_notice">
				<p>'.sprintf(__( 'Upgrade to  <a href="%s" target="_blank"><b>%s Premium</b></a> and get acces to more features like clickable tags and customization fees!<br/>View a list of all available features <a href="%s" target="_blank">%s</a>.', $this->woocp), $data->plugin_url, $data->plugin_name, $data->plugin_url.'#tab-features',__('here',$this->woocp)).'</p>
		</div>';
	}

}
