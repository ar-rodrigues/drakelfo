<?php /** @noinspection PhpToStringImplementationInspection */
/** @noinspection SpellCheckingInspection */

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WOOCP
 * @subpackage WOOCP/admin
 * @author     Grega Radelj <info@grrega.com>
 */
class WOOCP_Admin extends WOOCP {

	protected $woocp;

	protected $version;

	public function __construct( $woocp, $version ) {

		$this->woocp = $woocp;
		$this->version = $version;
		$this->settings = $this->woocp_return_settings();
		if(!is_object($this->settings)) $this->settings = json_decode(json_encode($this->settings));
		if(!is_object($this->settings)) $this->settings = new stdClass();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function woocp_admin_enqueue_styles() {
		if ($this->woocp_check_perms()){
			wp_enqueue_style( $this->woocp.'-admin', plugin_dir_url( __FILE__ ) . 'css/woocp-admin.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'css/select2.min.css' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function woocp_admin_enqueue_scripts() {
		if ($this->woocp_check_perms()){
			wp_enqueue_media();
			wp_register_script( 'select2', plugin_dir_url( __FILE__ ) . 'js/select2.min.js');
			wp_enqueue_script( $this->woocp.'-admin', plugin_dir_url( __FILE__ ) . 'js/woocp-admin.js', array('jquery', 'jquery-ui-tabs', 'jquery-ui-selectmenu', 'jquery-ui-sortable', 'select2'), $this->version, true );
			wp_register_script( $this->woocp.'-components', plugin_dir_url( __FILE__ ) . 'js/woocp-components.js', array('jquery','select2', $this->woocp.'-admin' ), $this->version, true );
		}
	}

	/**
	 * Setup taxonomy image hooks
	 *
	 * @since     1.0.0
	 */
	function woocp_setup_taxonomy_based_fields(){
		if ( !in_array( 'woocommerce-colororimage-variation-select/woocommerce-colororimage-variation-select.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )  {
			$createdattributes=wc_get_attribute_taxonomies();

			foreach ($createdattributes as $attribute) {
				add_action( 'pa_'.$attribute->attribute_name.'_add_form_fields', array($this, 'woocp_add_term_image_field'), 10, 2 ) ;
				add_action( 'pa_'.$attribute->attribute_name.'_edit_form_fields', array($this, 'woocp_edit_term_image_field'), 10, 2 );
				add_filter( 'manage_edit-pa_'.$attribute->attribute_name.'_columns', array( $this, 'woocp_term_image_header' ) );
				add_filter( 'manage_pa_'.$attribute->attribute_name.'_custom_column', array( $this, 'woocp_term_image_column' ), 10, 3 );
			}
		}
	}

	/**
	 * Adds an image field to add component page
	 *
	 * @since     1.0.0
	 * @param     $term
	 */
	function woocp_add_term_image_field( $term ) {

		$html = '<div class="form-field term-group">
			 <label for="woocp_taxonomy_image_id">'.__('Image', $this->woocp).'</label>
			 <input type="hidden" id="woocp_taxonomy_image_id" name="woocp_taxonomy_image_id" class="custom_media_url" value="">
			 <div id="woocp_taxonomy_image_wrapper">
				<img src="'.str_replace('/admin','/public',plugin_dir_url( __FILE__ )) . 'img/placeholder.png'.'" alt="Taxonomy image" />
			 </div>
			 <p>
			   <input type="button" class="button button-secondary" id="woocp_add_attribute_image_button" name="woocp_add_attribute_image_button" value="'.__( 'Add Image', $this->woocp ).'" />
			   <input type="button" class="button button-secondary" id="woocp_remove_attribute_image_button" name="woocp_remove_attribute_image_button" value="'.__( 'Remove Image', $this->woocp ).'" />
			</p>
		   </div>';
		echo $html;
	}

	/**
	 * Adds an image field to edit component page
	 *
	 * @since     1.0.0
	 * @param	  array	   $term  Taxonomy.
	 */
	function woocp_edit_term_image_field( $term ) {
		$image_id = get_term_meta ( $term->term_id, 'thumbnail_id', true );
		$html = '<tr class="form-field term-group-wrap">
		 <th scope="row">
		   <label for="woocp_taxonomy_image_id">'.__('Image',$this->woocp).'</label>
		 </th>
		 <td>
		   <input type="hidden" id="woocp_taxonomy_image_id" name="woocp_taxonomy_image_id" value="'.$image_id.'">
		   <div id="woocp_taxonomy_image_wrapper">';
		if ($image_id)  $html .= '<img src="'.wp_get_attachment_image_url($image_id, 'thumbnail').'" alt="Taxonomy image" />';
		$html .=
			'</div>
		   <p>
			 <input type="button" class="button button-secondary" id="woocp_add_attribute_image_button" name="woocp_add_attribute_image_button" value="'.__('Add Image', $this->woocp).'" />
			 <input type="button" class="button button-secondary" id="woocp_remove_attribute_image_button" name="woocp_remove_attribute_image_button" value="'.__('Remove Image', $this->woocp).'" />
		   </p>
		 </td>
	   </tr>';
		echo $html;
	}

	/**
	 * Adds an image header to component list page
	 *
	 * @since     1.0.0
	 * @param     array  $columns
	 * @return    array  $columns    New columns.
	 */
	function woocp_term_image_header( $columns ) {
		$new_columns          = array();
		$new_columns['cb']    = $columns['cb'];
		$new_columns['thumb'] = __('Image', $this->woocp);

		unset( $columns['cb'] );

		return array_merge( $new_columns, $columns );
	}

	/**
	 * Adds an image column to edit component list page
	 *
	 * @since     1.0.0
	 * @param	  array	   $columns		Array of columns.
	 * @param	  string   $column		slug of column.
	 * @param	  int	   $id			Term id.
	 * @return	  array	   $columns		New columns.
	 */
	function woocp_term_image_column( $columns, $column, $id ) {

		if ( $column == 'thumb' ) {
			$thumbnail_id = get_term_meta( $id, 'thumbnail_id', true );

			if ($thumbnail_id) $image = wp_get_attachment_thumb_url( $thumbnail_id );
			else $image = plugin_dir_url( __FILE__ ) . 'img/placeholder.png';

			$columns .= '<div style="width:32px; height:32px;"><img src="' . esc_url( $image ) . '" alt="" class="wp-post-image" height="32" width="32" style="max-height:32px;max-width:32px;" /></div>';
		}
		return $columns;
	}

	/**
	 * Saves the term image
	 *
	 * @since     1.0.0
	 * @param	  int	   $term_id		Term id.
	 * @param	  int	   $tt_id		Passed from the hook.
	 * @param	  string	   $taxonomy	Taxonomy - passed from the hook.
	 */
	public function woocp_save_term_image( $term_id, $tt_id, $taxonomy ) {
		if ( isset( $_POST['woocp_taxonomy_image_id'] ) ) update_term_meta( $term_id, 'thumbnail_id', absint( $_POST['woocp_taxonomy_image_id'] ) );
	}

	/**
	 * Create woocommerce settings tab
	 *
	 * @since    1.0.0
	 * @param 	 array 	$settings_tabs
	 * @return 	 array 	$settings_tabs
	 */
	function woocp_settings_tab($settings_tabs) {
		if (!$this->woocp_check_perms()) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', $this->woocp ) );
			return $settings_tabs;
		}

		$settings_tabs['custom_products'] = __( 'Custom Products', $this->woocp );
		return $settings_tabs;
	}

	/**
	 * Gathers and echoes the content of the settings page
	 *
	 * @since     1.0.0
	 */
	function woocp_settings_content() {
		if (!$this->woocp_check_perms()) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', $this->woocp ) );
			return false;
		}
		$plugin_data = $this->woocp_get_plugin_data();
		echo '<div class="woocp_settings">';
		woocommerce_admin_fields( $this->woocp_get_settings_array() );
		echo '<p style="text-align:center;">'.sprintf(__( 'Upgrade to  <a style="margin:0 5px;" class="woocp_pro_label" href="%s" target="_blank"><b>%s Premium</b></a> and get acces to more features like clickable tags and customization fees!<br/>View a list of all available features <a href="%s" target="_blank">%s</a>.', $this->woocp), $plugin_data->plugin_url, $plugin_data->plugin_name,$plugin_data->plugin_url.'#tab-features',__('here',$this->woocp)).'</p></div>';
	}

	/**
	 * Gets an array of WooCommerce settings fields for the settings page
	 *
	 * @since     1.0.0
	 * @return	  array|bool	  $settings		An array of settings
	 */
	function woocp_get_settings_array() {
		if (!$this->woocp_check_perms()) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', $this->woocp ) );
			return false;
		}
		$saved = $this->woocp_get_settings();
		if(!is_object($saved)) $saved = new stdClass();

		$settings = array(
			'woocp_display_settings' => array(
				'name'     => __( 'Display settings', $this->woocp ),
				'type'     => 'title',
				'desc'     => __( 'These settings affect how and where product customizer is displayed on front-end.', $this->woocp ),
				'id'       => 'woocp_display_settings'
			),
			'woocp_full_product_button' => array(
				'name' => __( 'Product page button', $this->woocp ),
				'type' => 'checkbox',
				'desc' => __( 'Show Customizer button on product page.', $this->woocp ),
				'id'   => 'woocp_full_product_button',
				'class'   => '',
				'default'   => isset($saved->woocp_full_product_button) && (bool)$saved->woocp_full_product_button ? 'yes' : '',
				'desc_tip'   => ''
			),
			'woocp_component_arrow' => array(
				'name' => __( 'Component arrow', $this->woocp ),
				'type' => 'checkbox',
				'desc' => __( 'Show arrows on Customizer components, indicating that they can be expanded.', $this->woocp ),
				'id'   => 'woocp_component_arrow',
				'class'   => '',
				'default'   => isset($saved->woocp_component_arrow) && (bool)$saved->woocp_component_arrow ? 'yes' : '',
				'desc_tip'   => ''
			),
			'woocp_component_description' => array(
				'name' => __( 'Component description', $this->woocp ),
				'type' => 'checkbox',
				'desc' => __( 'Show a description of the component.', $this->woocp ),
				'id'   => 'woocp_component_description',
				'class'   => '',
				'default'   => isset($saved->woocp_component_description) && (bool)$saved->woocp_component_description ? 'yes' : '',
				'desc_tip'   => ''
			),
			'woocp_customize_button_position' => array(
				'name' => __( 'Customize button position', $this->woocp ),
				'type' => 'select',
				'desc' => '',
				'id'   => 'woocp_customize_button_position',
				'class'   => '',
				'default'   => $saved->woocp_customize_button_position,
				'desc_tip'   => __( 'Select where to show "Customize" button on product page.<br/>"Product page button" has to be checked for this to work.', $this->woocp ),
				'options'	=> array(
					'before' => __( 'Before Add to cart', $this->woocp ),
					'after' => __( 'After Add to cart', $this->woocp ),
				)
			),
			'woocp_components_display' => array(
				'name' => __( 'Components display', $this->woocp ),
				'type' => 'select',
				'desc' => '',
				'id'   => 'woocp_components_display',
				'class'   => '',
				'default'   => $saved->woocp_components_display,
				'desc_tip'   => __( 'Select how product components will be displayed on page load.<br/>You can completely disable expanding/collapsing by selecting "Always expanded".', $this->woocp ),
				'options'	=> array(
					'collapsed' => __( 'Collapsed', $this->woocp ),
					'expanded' => __( 'Expanded', $this->woocp ),
					'always' => __( 'Always expanded', $this->woocp ),
				)
			),
			'woocp_components_position' => array(
				'name' => __( 'Components position', $this->woocp ),
				'type' => 'select',
				'desc' => '',
				'id'   => 'woocp_components_position',
				'class'   => '',
				'default'   => $saved->woocp_components_position,
				'desc_tip'   => __( 'Select on which side product component list will be displayed', $this->woocp ),
				'options'	=> array(
					'left' => __( 'Left', $this->woocp ),
					'right' => __( 'Right', $this->woocp ),
				)
			),
			'woocp_customize_text' => array(
				'name' => __( '"Customize" button text', $this->woocp ),
				'type' => 'text',
				'desc' => '',
				'css'   => '',
				'id'   => 'woocp_customize_text',
				'class'   => '',
				'default'   => $saved->woocp_customize_text,
				'desc_tip'   => __( 'This text will be visible on Customizer tab and button.<br>If you need this text translated leave it blank, then edit the translation files.', $this->woocp ),
			),
			'woocp_customized_text' => array(
				'name' => __( '"Customized" text', $this->woocp ),
				'type' => 'text',
				'desc' => '',
				'css'   => '',
				'id'   => 'woocp_customized_text',
				'class'   => '',
				'default'   => $saved->woocp_customized_text,
				'desc_tip'   => __( 'This text will be visible on cart, checkout and invoice to show what has been customized on the product.<br>If you need this text translated leave it blank, then edit the translation files.', $this->woocp ),
			),
			'woocp_select_product_text' => array(
				'name' => __( '"Select product" text', $this->woocp ),
				'type' => 'text',
				'desc' => '',
				'css'   => '',
				'id'   => 'woocp_select_product_text',
				'class'   => '',
				'default'   => $saved->woocp_select_product_text,
				'desc_tip'   => __( 'This text will be used as a label for product select in [woocp] shortcode.<br>If you need this text translated leave it blank, then edit the translation files.', $this->woocp ),
			),
			'woocp_no_change_text' => array(
				'name' => __( '"Don\'t change" text', $this->woocp ),
				'type' => 'text',
				'desc' => '',
				'css'   => '',
				'id'   => 'woocp_no_change_text',
				'class'   => '',
				'default'   => $saved->woocp_no_change_text,
				'desc_tip'   => __( 'This text will be used as the default option for attributes select.<br>If you need this text translated leave it blank, then edit the translation files.', $this->woocp ),
			),
			'woocp_components_title_tag' => array(
				'name' => __( 'Components title tag', $this->woocp ),
				'type' => 'text',
				'desc' => '',
				'placeholder'   => 'h3',
				'id'   => 'woocp_components_title_tag',
				'css'   => 'width:75px;',
				'default'   => $saved->woocp_components_title_tag,
				'desc_tip'   => __( 'HTML tag for the component title in components list.<br/>Defaults to h3.', $this->woocp ),
			),
			'woocp_display_settings_end' => array(
				'type' => 'sectionend',
				'id' => 'woocp_display_settings_end'
			),
			'woocp_customizer_settings' => array(
				'name'     => __( 'Cutomizer settings', $this->woocp ),
				'type'     => 'title',
				'desc'     => __( 'These settings are the defaults for [woocp] shortcode. <b>"Customizer image width"</b> also applies to the product page customizer.', $this->woocp ),
				'id'       => 'woocp_customizer_settings'
			),
			'woocp_customizer_width' => array(
				'name' => __( 'Customizer width', $this->woocp ),
				'type' => 'text',
				'desc' => '',
				'css'   => 'width:75px;',
				'id'   => 'woocp_customizer_width',
				'default'   => $saved->woocp_customizer_width,
				'desc_tip'   => __( 'This setting determines the width of the product customizer.<br/>You can use any CSS legal value (px, %, em,...).<br/>Valid only for [woocp] shortcode.', $this->woocp ),
			),
			'woocp_customizer_image_width' => array(
				'name' => __( 'Customizer image width', $this->woocp ),
				'type' => 'number',
				'desc' => '%',
				'css'   => 'width:75px;',
				'id'   => 'woocp_customizer_image_width',
				'default'   => $saved->woocp_customizer_image_width,
				'desc_tip'   => __( 'This setting determines the width of the customizer image in % (relative to Customizer width).<br/>Defaults to 60%.', $this->woocp ),
			),
			'woocp_option_image_height' => array(
				'name' => __( 'Option image height', $this->woocp ),
				'type' => 'number',
				'desc' => 'px &nbsp;&nbsp;('.__('For best performance thumbnails should be regenerated after changing this setting.',$this->woocp).')',
				'css'   => 'width:75px;',
				'id'   => 'woocp_option_image_height',
				'default'   => $saved->woocp_option_image_height,
				'desc_tip'   => __( 'Attribute option select image height.<br/>Defaults to 40px.<br/>Don\'t forget to regenerate thumbnails after changing this setting for best performance. A third party plugin can be used to achieve this.', $this->woocp ),
			),
			'woocp_option_image_width' => array(
				'name' => __( 'Option image width', $this->woocp ),
				'type' => 'number',
				'desc' => 'px &nbsp;&nbsp;('.__('For best performance thumbnails should be regenerated after changing this setting.',$this->woocp).')',
				'css'   => 'width:75px;',
				'id'   => 'woocp_option_image_width',
				'default'   => $saved->woocp_option_image_width,
				'desc_tip'   => __( 'Attribute option select image width.<br/>Defaults to 40px.<br/>Don\'t forget to regenerate thumbnails after changing this setting for best performance. A third party plugin can be used to achieve this.', $this->woocp ),
			),
			'woocp_product_image_height' => array(
				'name' => __( 'Product image height', $this->woocp ),
				'type' => 'number',
				'desc' => 'px &nbsp;&nbsp;('.__('For best performance thumbnails should be regenerated after changing this setting.',$this->woocp).')',
				'css'   => 'width:75px;',
				'id'   => 'woocp_product_image_height',
				'default'   => $saved->woocp_product_image_height,
				'desc_tip'   => __( 'Product select image height.<br/>Defaults to 40px.<br/>Don\'t forget to regenerate thumbnails after changing this setting for best performance. A third party plugin can be used to achieve this.', $this->woocp ),
			),
			'woocp_product_image_width' => array(
				'name' => __( 'Product image width', $this->woocp ),
				'type' => 'number',
				'desc' => 'px &nbsp;&nbsp;('.__('For best performance thumbnails should be regenerated after changing this setting.',$this->woocp).')',
				'css'   => 'width:75px;',
				'id'   => 'woocp_product_image_width',
				'default'   => $saved->woocp_product_image_width,
				'desc_tip'   => __( 'Product select image height.<br/>Defaults to 40px.<br/>Don\'t forget to regenerate thumbnails after changing this setting for best performance. A third party plugin can be used to achieve this.', $this->woocp ),
			),
			'woocp_customizer_url' => array(
				'name' => __( 'Customizer URL', $this->woocp ),
				'type' => 'text',
				'desc' => '',
				'css'   => '',
				'id'   => 'woocp_customizer_url',
				'class'   => 'woocp_customizer_url',
				'default'   => isset($saved->woocp_customizer_url) && !empty($saved->woocp_customizer_url) ? $saved->woocp_customizer_url : '',
				'desc_tip'   => __( 'URL of a page that contains [woocp] shortcode. Must contain full URL.<br/>If customizer tab is disabled on full product page, customizer buttons will redirect to this URL.<br/> Defaults to homepage.', $this->woocp ),
			),
			'woocp_customizer_settings_end' => array(
				'type' => 'sectionend',
				'id' => 'woocp_customizer_settings_end'
			),
		);

		return apply_filters( 'woocp_settings', $settings );
	}

	/**
	 * Puts together an array of settings and saves them as one option in JSON format
	 *
	 * @since     1.0.0
	 */
	function woocp_update_settings() {
		if (!$this->woocp_check_perms()) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			return false;
		}
		$ar = $_POST;
		unset($ar['save']);
		unset($ar['_wp_http_referer']);
		unset($ar['_wpnonce']);

		foreach($ar as $key => $value){
			$ar[$key] = stripslashes(htmlentities($value));
		}

		$ar = json_encode($ar);
		update_option('woocp_settings',$ar);
	}

	/**
	 * Add a "Custom products" tab on the edit product page
	 *
	 * @since     1.0.0
	 * @param	  array	   $tabs	  Array of existing tabs.
	 * @return	  array	   $tabs	  Array of new tabs.
	 */
	function woocp_product_customizer_tab( $tabs ) {
		if (!$this->woocp_check_perms()) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			return $tabs;
		}
		$tabs['product_customizer'] = array(
			'label'		=> __( 'Custom Products', $this->woocp ),
			'target'	=> 'woocp_product_customizer_container',
			'class'		=> array('hide_if_variable','show_if_simple','hide_if_grouped','hide_if_external','hide_if_virtual','hide_if_downloadable'),
		);
		return $tabs;
	}

	/**
	 * WOOCP product customizator
	 * Builds and echoes the Html for the "Custom products" panel for the "Custom products" tab on the edit product page
	 *
	 * @since     1.0.0
	 */
	function woocp_product_customizer_panel() {
		if (!$this->woocp_check_perms()) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', $this->woocp ) );
			return false;
		}

		$plugin_components = new WOOCP_Components($this->woocp,$this->version);
		wp_enqueue_script( $this->woocp.'-components' );
		wp_enqueue_script( 'select2' );

		$components = $this->woocp_get_components();
		$id = get_the_ID();

		$is_customizable =  (array) get_post_meta($id,'_woocp_is_customizable');
		if(count($is_customizable) > 0) $is_customizable = $is_customizable[0];
		$checked = $is_customizable == 1 ? 'checked="checked"' : '';

		$attached_components =  $this->woocp_get_product_components($id);
		$attached_components_view = $available_components = '';

		//list all components as options for select
		foreach( $components as $component ) {
			$term_id = (string) $component->term_id;
			$available_components .= '<option value="' . $term_id . '">' . $component->name . '</option>';
		}

		//if no components for this product create a new object
		if(!$attached_components || $attached_components == null || count($attached_components) == 0) $attached_components = array();

		//list attached components in a view
		if(count($attached_components) > 0){
			foreach((array)$attached_components as $att_component){
				$compId = $att_component->id;
				if($compId !== null){
					$attached_components_view .= $plugin_components->woocp_add_component_metabox($att_component);
				}
			}
		}

		$html = '<div id="woocp_product_customizer_container" class="panel woocommerce_options_panel">
				<p class="form-field _woocp_is_customizable_field">
					<label for="_woocp_is_customizable">'.__( 'Is customizable', $this->woocp ).'</label>
					<input type="checkbox" name="_woocp_is_customizable" class="left" id="_woocp_is_customizable" value="1" '.$checked.' />
					<label class="spinner woocp_update_customizable left"></label>
				</p>

			<div class="woocp_message_container">
				<div class="woocp_message">
					<div class="msg1">
						<i class="fa fa-info-circle"></i>
						'.__('You have made changes to components, you should <b>Save Changes</b> when you are done.',$this->woocp).'
					</div>
				</div>
			</div>';

		$html .= '<div id="woocp_product_customizer_wrapper" class="hide">';

		//tabs
		$html .= '<div id="woocp_product_customizer_tabs" >';
		$html .= '<ul class="woocp_product_tabs">
					<li><a href="#woocp_components_panel">'.__('Components',$this->woocp).'</a></li>
					<li><a href="#woocp_tags_panel" class="disabled">'.__('Image',$this->woocp).'</a></li>
				</ul>';
		//components panel
		$attached_components = stripslashes(htmlspecialchars(json_encode($attached_components, JSON_UNESCAPED_UNICODE)));
		$html .= $this->woocp_components_panel($available_components,$attached_components_view,$attached_components);

		//tags panel
		$html .= '<div id="woocp_tags_panel" class="meta-options">';
		$html .= $this->woocp_components_tags_panel($id);

		$html .= '</div></div></div></div>';

		echo $html;
		return null;
	}

	/**
	 * Gets the Html for the "Components" part of the product customizator
	 *
	 * @since     1.0.0
	 * @param	  string	   $available_components		  Html options for the "Add component" select.
	 * @param	  string	   $attached_components_view	  Html of currently attached components.
	 * @param	  string       $attached_components			  JSON array of currently attached components.
	 * @return	  string	   $html						  Html for the components part.
	 */
	function woocp_components_panel($available_components,$attached_components_view,$attached_components) {
		if (!$this->woocp_check_perms()) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			return false;
		}

		$html = '
		<input type="hidden" id="AtachedComponents" value='.$attached_components.'>
		<div id="woocp_components_panel" class="meta-options">
					<div id="woocp_product_components_list_container" class="wc-metaboxes-wrapper">
						<div class="toolbar toolbar-top">
							<span class="expand-close">
								<a href="#" class="woocp_expand_components">'.__('Expand',$this->woocp).'</a> / <a href="#" class="woocp_close_components">'.__('Close',$this->woocp).'</a>
							</span>
							<div id="woocp_components_select_container">
								<select id="_woocp_product_components_select" class="woocp_components_select" name="_woocp_product_components_select">
									<option value="placeholder">' . __('Add product component',$this->woocp) . '</option>
									'.$available_components.'
								</select>
							</div>
							<button class="button woocp_add_product_component left">'.__('Add',$this->woocp).'</button>
							<div class="spinner woocp_add_component left"></div>
						</div>
						<div id="woocp_product_components_list" class="wc-metaboxes ui-sortable">
							'.$attached_components_view.'
						</div>
						<div class="toolbar">
							<span class="expand-close">
								<a href="#" class="woocp_expand_components">'.__('Expand',$this->woocp).'</a> / <a href="#" class="woocp_close_components">'.__('Close',$this->woocp).'</a>
							</span>
							<button type="button" class="button woocp_save_product_components button-primary left">'.__('Save Changes',$this->woocp).'</button>
							<div class="spinner woocp_save_components left"></div>
						</div>
					</div>
					<input type="hidden" name="_woocp_product_components" value="'.$attached_components.'" autocomplete="off"/>
				</div>';
		return $html;
	}

	/**
	 * Gets content for the component tags panel on Product edit page
	 *
	 * @since     1.0.0
	 * @param	  int	       $prodId	  				Array of existing tabs.
	 * @return	  string|bool  $html	  				Array of new tabs.
	 */
	function woocp_components_tags_panel($prodId) {
		if (!$this->woocp_check_perms()) return false;
		$plugin_data = $this->woocp_get_plugin_data();

		$html = '';

		//customizer image field
		$image_field = $this->woocp_get_product_customizer_image_field($prodId);

		$html .= '<div id="woocp_tags_content">
					'.$image_field.'
					<p>'.sprintf(__('Not Upgrade to <a style="color:orange;" href="%s" target="_blank">%s Premium</a> and add clickable tags to your customizer image.', $this->woocp,true),$plugin_data->plugin_url,$plugin_data->plugin_name).'</p>
				</div>';

		return $html;
	}
	/**
	 * Gets the image upload field
	 *
	 * @since     1.0.0
	 * @param	  int	       $prodId	  	Product ID.
	 * @return	  string|bool  $html	  	Html for the image field.
	 */
	function woocp_get_product_customizer_image_field($prodId) {
		if (!$this->woocp_check_perms()) return false;

		$imageId = get_post_meta( $prodId, '_woocp_customizer_image_id');
		$imageId = isset($imageId[0]) ? $imageId[0] : '';
		$product_image = wp_get_attachment_image_src( $imageId,'full')[0];
		$featured_image = get_the_post_thumbnail_url( $prodId, 'full' );
		if(!$product_image) $product_image = $featured_image;
		$html = '
		<div id="woocp_customizer_image_upload">
			<p  class="form-field">
				<label>'.__('Customizer image', $this->woocp).wc_help_tip(__('Change the product customizer image. It will affect the selected variation only, but you have to re-save the customizer image.',$this->woocp),true).'</label>
				<button data-productId="'.$prodId.'" class="button button-primary woocp_add_customizer_image_button" id="woocp_add_customizer_image_button" name="woocp_add_customizer_image_button">'.__( 'Change Image', $this->woocp ).'</button>
				<button style="margin-left:2px;" class="button button-secondary woocp_remove_customizer_image_button" id="woocp_remove_customizer_image_button" name="woocp_remove_customizer_image_button">'.__( 'Remove Image', $this->woocp ).'</button>
				<div class="spinner woocp_save_customizer_image left"></div>
			</p>
			<input type="hidden" id="woocp_customizer_image_id" name="woocp_customizer_image_id" class="custom_media_id" value="'.$imageId.'" autocomplete="off">
			<img src="'.$product_image.'" alt="" title="" id="woocp_customizer_image" />
			<img src="'.$featured_image.'" alt="" title="" id="woocp_customizer_image_original" style="display:none;" />
			<div id="customizer-image-wrapper"></div>
		</div>';
		return $html;
	}

	/**
	 * Saves the product as customizable
	 *
	 * @since     1.0.0
	 */
	function woocp_update_customizable() {		//AJAX
		if (!$this->woocp_check_perms()) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			return false;
		}
		if(isset($_POST['postId']) && isset($_POST['_woocp_is_customizable'])){
			update_post_meta( $_POST['postId'], '_woocp_is_customizable', $_POST['_woocp_is_customizable'] );
		}

		wp_die();
		return null;
	}

	/**
	 * Saves the product customizer when updating the product
	 *
	 * @since     1.0.0
	 * @param	  int	   $product_id		  Product ID.
	 */
	function woocp_save_product_customizer( $product_id ) {

		if(isset($_POST['_woocp_is_customizable']) || isset($_POST['_woocp_product_components'])){
			if(isset($_POST['_woocp_is_customizable'])){
				$is_customizable = isset( $_POST['_woocp_is_customizable'] ) ? 1 : 0;
				update_post_meta( $product_id, '_woocp_is_customizable', $is_customizable );
			}
			if(isset($_POST['_woocp_product_components'])){
				var_dump($_POST['_woocp_product_components']);
				$plugin_components = new WOOCP_Components($this->woocp, $this->version);
				$plugin_components->woocp_save_product_components($product_id, $_POST['_woocp_product_components']);
			}
		}

	}
	/**
	 * Saves the product customizer when updating the product
	 *
	 * @since     1.0.0
	 */
	function woocp_save_customizer_image( ) {	//AJAX

		if(isset($_POST['image_id'])){
			update_post_meta( $_POST['postId'], '_woocp_customizer_image_id', $_POST['image_id'] );
		}
		wp_die();
	}
	/**
	 * Saves the product customizer when updating the product
	 *
	 * @since     1.0.0
	 */
	function woocp_remove_customizer_image( ) {	//AJAX

		if(isset($_POST['postId'])){
			delete_post_meta( $_POST['postId'], '_woocp_customizer_image_id' );
		}
		wp_die();
	}

}
