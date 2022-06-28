<?php /** @noinspection PhpToStringImplementationInspection */
/** @noinspection SpellCheckingInspection */

/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    WOOCP
 * @subpackage WOOCP/public
 * @author     Grega Radelj <info@grrega.com>
 */
class WOOCP_Public extends WOOCP {

	protected $woocp;

	protected $version;

	public function __construct($woocp, $version ) {

		$this->woocp = $woocp;
		$this->version = $version;
		$this->settings = $this->woocp_return_settings();
		if(!is_object($this->settings)) $this->settings = json_decode(json_encode($this->settings));
		if(!is_object($this->settings)) $this->settings = new stdClass();

	}

	public function woocp_public_enqueue_styles() {
		wp_enqueue_style( 'jquery-ui-css', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css');
		wp_enqueue_style( $this->woocp.'-public', plugin_dir_url( __FILE__ ) . 'css/woocp-public.css', array('jquery-ui-css'), $this->version, 'all' );
		wp_register_style($this->woocp.'-customcss',plugin_dir_url( __FILE__ ) . 'css/woocp-custom.css');
		wp_enqueue_style($this->woocp.'-customcss');

		$h1 = $this->settings->woocp_product_image_height;
		$w1 = $this->settings->woocp_product_image_width;
		$h2 = $this->settings->woocp_option_image_height;
		$w2 = $this->settings->woocp_option_image_width;
		$css = '.woocp_product_thumb{height:'.$h1.'px !important;width:'.$w1.'px !important;}
		.woocp_term_thumb{height:'.$h2.'px !important;width:'.$w2.'px !important;}
		.woocp_product_icons .ui-menu-item{height:calc(2px + '.$h1.'px) !important;padding:3px 1em 3px calc(0.4em + '.$w1.'px) !important;}
		.woocp_option_icons .ui-menu-item{height:calc(2px + '.$h2.'px) !important;padding:3px 1em 3px calc(0.4em + '.$w2.'px) !important;}';

		wp_add_inline_style($this->woocp.'-customcss',$css);
	}

	public function woocp_public_enqueue_scripts() {
		wp_enqueue_script( $this->woocp.'-public', plugin_dir_url( __FILE__ ) . 'js/woocp-public.js',  array( 'jquery', 'jquery-ui-selectmenu' ), $this->version, true );
		wp_localize_script( $this->woocp.'-public', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		wp_localize_script( $this->woocp.'-public', 'site_object', array( 'baseurl' => get_option('siteurl') ) );
	}

	/**
	 * WOOCP shortcode [woocp]
	 *
	 * Gets all the necessary data and returns the html.
	 *
	 * Arguments can be:
	 * - category_ids
	 * - product_ids
	 * - number_of_products
	 * - order
	 * - order_by
	 * - width
	 * - image_width (%)
	 * - list_width (%)
	 * - class
	 * - hide_msg_area (true/false)
	 * - hide_select (true/false)
	 *
	 * @since     1.0.0
	 * @param     array      $args  Array of arguments.
	 * @return    string     $html  Product customizer with product selector.
	 */
	public static function woocp_shortcode($args=array()){
		$plugin = new WOOCP();
		$plugin_public = new WOOCP_Public($plugin->woocp, $plugin->version);
		$settings = $plugin_public->settings;
		$prodId = isset($_GET['customize-product']) ? $_GET['customize-product'] : false;
		$html = null;

		//filter products by category ids
		if(isset($args['category_ids'])) $category_ids = $args['category_ids'];
		else $category_ids = false;
		if($category_ids !== false) $category_ids = explode(',',$category_ids);

		//filter products by product ids
		if(isset($args['product_ids'])) $product_ids = $args['product_ids'];
		else $product_ids = false;
		if($product_ids !== false) $product_ids = explode(',',$product_ids);

		//set args for query
		if(isset($args['number_of_products'])) $number_of_products = $args['number_of_products'];
		else $number_of_products = false;

		if(isset($args['order'])) $order = $args['order'];
		else $order = false;

		if(isset($args['orderby'])) $orderby = $args['orderby'];
		else $orderby = false;

		if(isset($args['width'])) $width = $args['width'];
		else $width = isset($settings->woocp_customizer_width) ? $settings->woocp_customizer_width : null;

		if(isset($args['image_width'])) $image_width = $args['image_width'];
		else $image_width = isset($settings->woocp_customizer_image_width) ? $settings->woocp_customizer_image_width : null;

		if(isset($args['list_width'])) $list_width = $args['list_width'];
		else $list_width = null !== $image_width ? 100 - (int)$image_width : null;

		if(isset($args['hide_msg_area']) && $args['hide_msg_area'] == 'true') $msgArea = false;
		else $msgArea = true;

		if(isset($args['hide_select']) && $args['hide_select'] == 'true') $hide_select = true;
		else $hide_select = false;

		if(isset($args['class'])) $class = $args['class'];
		else $class = null;


		//get products
		$products = $plugin->woocp_get_customizable_products($category_ids,$product_ids,$number_of_products,$order,$orderby);


		$html .= '<style>';
		if(!empty($image_width) && !empty($list_width)){
			$w1 = 'width:'.$image_width.'%;';
			$w2 = 'width:'.$list_width.'%;';
			$html .= '.woocp_product_customizer_image{'.$w1.'}
			.woocp_components_list{'.$w2.'}';
		}
		if(!empty($width)){
			$html .= '.woocp_customizer{width:'.$width.';}';
		}
		$html .= '</style>';

		//if there are products in array make a view for each
		if(is_array($products) && count($products) > 0){
			foreach($products as $product){
				if(!is_object($product)) continue;
				//get product data
				$data = $product->get_data();
				if(!$prodId || $prodId && $prodId == $data['id']){
					$prodId = $data['id'];
					$product_select = !$hide_select ? $plugin_public->woocp_get_product_select($products, $prodId) : null;
					//get product view
					$html .= $plugin_public->woocp_get_customizer_product_view($prodId, $product_select, $msgArea, $class);
					//stop after first product, let select take care of the rest
					break;
				}
			}
		}

		return $html;

	}

	/**
	 * WOOCP Single Produc shortcode [woocp_single_product]
	 *
	 * Arguments can be:
	 * - id (product ID if simple and variation ID if variable - default will be used if not set)
	 * - width
	 * - hide_msg_area (true/false)
	 * - class
	 *
	 * @since     1.1.0
	 * @param     array      $args  Array of arguments.
	 * @return    string     $html  Product components list.
	 */
	public static function woocp_shortcode_single_product($args=array()){
		$plugin = new WOOCP();
		$plugin_public = new WOOCP_Public($plugin->woocp,$plugin->version);
		$settings = $plugin_public->settings;
		$html = null;

		if(isset($args['id'])) {
			$prodId = $args['id'];
		}
		else {
			$prodId = get_the_ID();
		}
		if(!$prodId || null == $prodId) return $html;
		$product = wc_get_product($prodId);
		if(!$product || null == $product || !is_object($product)) return $html;

		$is_customizable = $plugin_public->woocp_is_customizable($prodId);

		if(!$is_customizable) return false;

		if(isset($args['width'])) $width = $args['width'];
		else $width = isset($settings->woocp_customizer_width) ? $settings->woocp_customizer_width : null;

		if(isset($args['image_width'])) $image_width = $args['image_width'];
		else $image_width = isset($settings->woocp_customizer_image_width) ? $settings->woocp_customizer_image_width : null;

		if(isset($args['list_width'])) $list_width = $args['list_width'];
		else $list_width = null !== $image_width ? 100 - (int)$image_width : null;

		if(isset($args['hide_msg_area']) && $args['hide_msg_area'] == 'true') $msgArea = false;
		else $msgArea = true;

		if(isset($args['class'])) $class = $args['class'];
		else $class = null;

		$html .= '<style>';
		if(!empty($image_width) && !empty($list_width)){
			$w1 = 'width:'.$image_width.'%;';
			$w2 = 'width:'.$list_width.'%;';
			$html .= '.woocp_product_customizer_image{'.$w1.'}
			.woocp_components_list{'.$w2.'}';
		}
		if(!empty($width)){
			$html .= '.woocp_customizer{width:'.$width.';}';
		}
		$html .= '</style>';

		$html .= $plugin_public->woocp_get_customizer_product_view($prodId, false, $msgArea, $class );

		return $html;

	}

	/**
	 * Gets a new product and returns the content template
	 *
	 * @since    1.0.0
	 * @param    int|bool  $prodId    New product id
	 */
	function woocp_change_product( $prodId=false ){		//AJAX
		$prodId = !$prodId && !empty($_POST['product_id']) ? $_POST['product_id'] : $prodId;

		$html = $this->woocp_get_customizer_product_content( $prodId, 'customizer' );

		echo $html;

		wp_die();
	}

	/**
	 * Add a "Customize" button (if applicable) to the current Product page
	 *
	 * @since     1.0.0
	 */
	function woocp_product_page_button() {
		$prodId = get_the_ID();
		$product = wc_get_product($prodId);
		$settings = $this->settings;

		$is_customizable = $this->woocp_is_customizable($prodId);

		if(!$is_customizable || $product->is_type('variable')) return false;

		$text = isset($settings->woocp_customize_text) && !empty($settings->woocp_customize_text) ? $settings->woocp_customize_text : __( 'Customize', $this->woocp );
		$url = isset($settings->woocp_customizer_url) && !empty($settings->woocp_customizer_url) ? $settings->woocp_customizer_url : get_site_url();
		$url = add_query_arg(array('customize-product'=>$prodId),$url);

		$html = '<a class="woocp_customize_button customize_button button" href="'.$url.'">'.$text.'</a>';
		echo $html;
		return $html;
	}

	/**
	 * Adds hidden input fields before add to cart button
	 *
	 * @since     1.0.0
	 */
	function woocp_before_atc_array(){
		echo '<input type="hidden" name="woocp_selected" id="woocp_selected" autocomplete="off" value="{}" />';
	}

	/**
	 * Retrieves data and builds the customizer product view
	 *
	 * @since     1.0.0
	 * @param     int     $prodId           Product ID.
	 * @param     bool    $product_select   Product selector if applicable.
	 * @param     bool    $msgArea
	 * @param     null    $class
	 * @return    string  $html            Product customizer view
	 */
	function woocp_get_customizer_product_view($prodId, $product_select=false, $msgArea=true, $class=null){

		$icons = [];

		//get product components
		$product_components = $this->woocp_get_product_components($prodId);
		//get attributes
		$attributes = $this->woocp_get_attributes();

		//get attribute images
		foreach($product_components as $prod_comp){
			foreach($prod_comp->attrs as $attr){
				if(isset($attr->id)){
					$attrId = $attr->id;
					$options = $attr->options;
					foreach($options as $option){
						$option = $attributes[$attrId]['options'][$option];
						$thumb_id = get_term_meta( $option->term_id, 'thumbnail_id', true );
						$term_img = wp_get_attachment_image_src(  $thumb_id,'woocp_option_image')[0];
						if(!in_array($term_img,$icons)) $icons[] = $term_img;
					}
				}
			}
		}

		$html = '<div class="woocp_icon_preloads">';
		$icons[] = plugin_dir_url( __FILE__ ) . 'img/placeholder.png';
		foreach($icons as $icon){
			if($icon !== null) $html .= '<img src="'.$icon.'" class="woocp_icon_preload" alt="" title="" />';
		}
		$html .='</div>';
		$html .= $this->woocp_get_customizer_product_content( $prodId, 'customizer', $product_components, $attributes, $product_select, $msgArea, $class );

		return $html;

	}

	/**
	 * Gets the correct template and builds content for the product customizer view
	 *
	 * @since     1.0.0
	 * @param     int         $prodId              Product ID.
	 * @param     string      $tpl                 Template to load (shortcode or product page tab).
	 * @param     array|bool  $product_components  Array of product components (components list).
	 * @param     bool        $attributes          Array of attributes (components list).
	 * @param     bool        $product_select      Product select if shortcode.
	 * @param     bool        $msgArea
	 * @param     null        $class
	 * @return    string      $html                Product customizer content.
	 */
	function woocp_get_customizer_product_content( $prodId, $tpl, $product_components=false, $attributes=false, $product_select=false, $msgArea=true, $class=null ){

		$settings = $this->settings;
		$html = '';

		$product = wc_get_product($prodId);

		$is_customizable = $this->woocp_is_customizable( $prodId );

		//get data
		if(!$product_components){
			$product_components = $this->woocp_get_product_components($prodId);
		}
		if(!$attributes){
			$attributes = $this->woocp_get_attributes();
		}
		$components = $this->woocp_get_components();

		$customizer_img_url = null;
		$customizer_img_id = get_post_meta($prodId,'_woocp_customizer_image_id');
		$customizer_img_id = isset($customizer_img_id[0]) ? $customizer_img_id[0] : $customizer_img_id;
		$customizer_img_url = wp_get_attachment_image_src($customizer_img_id,'full')[0];
		if($customizer_img_url) $img = $customizer_img_url;
		else{
			$img = get_the_post_thumbnail_url($prodId,'full');
			if(!$img) $img = false;
		}
		if($img) $customizer_img = '<img id="woocp_customizer_image" src="'.$img.'" alt="'.$product->get_name().'" title="'.$product->get_name().'" />';
		else $customizer_img = false;

		//get components list
		$component_list = $this->woocp_get_customizer_component_list($product_components,$components,$attributes,$prodId);

		//get add to cart button
		ob_start();
		$this->woocp_get_template('add_to_cart_button.php',array(
				'prodId'=>$prodId,
				'quantity'=> true,
			)
		);
		$add_to_cart_button = ob_get_clean();

		//if all is ok, show customizer
		$listClass = $settings->woocp_components_position;
		if($is_customizable == true && $customizer_img){
			//pass data to template, load and return it
			ob_start();
			$this->woocp_get_template($tpl.'.php',array(
					'prodId'=> $prodId,
					'listClass'=> $listClass,
					'class'=> $class,
					'component_list'=> $component_list,
					'add_to_cart_button'=> $add_to_cart_button,
					'customizer_img'=> $customizer_img,
					'product_select'=> $product_select,
					'message'=> null,
					'msgArea'=> $msgArea,
				)
			);
			$html .= ob_get_clean();
		}

		return $html;
	}

	/**
	 * Gets the customizer components and loads the template for the components list
	 *
	 * @since     1.0.0
	 * @param     array   $product_components   Array of product components.
	 * @param     array   $components           Array of all components.
	 * @param     array   $attributes           Array of all attributes.
	 * @return    string  $html                 Components list.
	 */
	function woocp_get_customizer_component_list($product_components,$components,$attributes,$prodId = 0){

		$settings = $this->settings;

		$html = '';
		$no_change_img = plugin_dir_url( __FILE__ ) . 'img/noedit.png';
		$no_change_text = isset($settings->woocp_no_change_text) && !empty($settings->woocp_no_change_text) ? $settings->woocp_no_change_text : __("Don't change",$this->woocp);
		$reverse = array_reverse($product_components);
		$titleTag = $settings->woocp_components_title_tag;
		$titleTag = empty($titleTag) ? 'h3' : $titleTag;
		$compClass = $settings->woocp_components_display;
		$compClass = $compClass == 'always' ? 'always_expanded' : $compClass;
		$compClass .= isset($settings->woocp_component_arrow) && (bool) $settings->woocp_component_arrow ? ' arrow' : '';

		$c_components = array();

		if(isset($_GET['customize-product'])){
			$p_id = $_GET['customize-product'];
		}else{
			$p_id = $prodId;
		}

		if($p_id){
			$costs_helper = get_field('components', $p_id);
			foreach ($costs_helper as $cckey => $helper){
				$help_attrs = array();
				foreach ($helper['attributes'] as $icckey => $hattr) {
					$help_attrs['aID_'.$hattr['attribute']] = $hattr['cost'];
				}
				$c_components['cID_'.$helper['component']] = $help_attrs;
			}
		}


		foreach($product_components as $order => $prod_comp){
			$c_helper = $c_components['cID_'.$prod_comp->id];
			$compClass2 = '';
			//if product is set up for customization
			if(isset($prod_comp->id)){
				$compId = $prod_comp->id;
				//get components
				foreach($components as $index => $comp){
					if($comp->term_id == $compId) {
						$comp_data = $comp;
						$height = $compClass == 'collapsed' ? 'style="height:0;"' : '';
						$componentLabel = !empty($prod_comp->label) ? $prod_comp->label : $comp_data->name;
						if(count($product_components) == 1) $compClass2 = ' first last';
						else if($compId === end($reverse)->id) $compClass2 = ' first';
						else if($compId === end($product_components)->id) $compClass2 = ' last';
						$desc = isset($settings->woocp_component_description) && (bool) $settings->woocp_component_description ? $comp->description : '';
						$desc = !empty($prod_comp->description) ? $prod_comp->description : $desc;
						$descClass = empty($desc) ? 'empty' : '';
						$component_attributes = '';

						//get attributes
						foreach($prod_comp->attrs as $attr){
							if(isset($attr->id)){
								$attrId = $attr->id;
								$currentId = rand(100,999).'-'.rand(100,999);
								$data = $attributes[$attrId];
								$options = $attr->options;
								$component_attribute_options = '<label class="attribute-option"><figure><img src="'.(isset($no_change_img) ? $no_change_img : null).'" /></figure><input type="radio" name="woocp_attribute_'.$currentId.'" value="0"/><span></span>'.(isset($no_change_text) ? $no_change_text : null).'</label>';

								$component_attribute_options = '';

								$attributeLabel = $this->woocp_get_translation(false,$data['attribute_label']);
								$attributeLabel = !$attributeLabel ? $data['attribute_label'] : $attributeLabel;

								$componentsArray = array();
								//get options
								foreach($options as $option){
									$lprice = '';
									$option = $attributes[$attrId]['options'][$option];

									$cost = $c_helper['aID_'.$option->term_id];
									$check = '';

									if($cost == 'default'){
										$check = 'checked="checked"';
									}

									$thumb_id = get_term_meta( $option->term_id, 'thumbnail_id', true );
									$term_img = wp_get_attachment_image_src(  $thumb_id,'large')[0];
									$style = $term_img !== null ? $term_img : plugin_dir_url( __FILE__ ) . 'img/placeholder.png';
									if($term_img){
										$cp = 'custom';
									}else{
										$cp = 'default';
									}

									if($cost > 0){
										$lprice = '<strong>+'.wc_price($cost).'</strong>';
									}

									$componentsArray['op_'.$option->term_id] = '<label class="attribute-option"><figure class="'.$cp.'"><img src="'.$style.'" /></figure><input type="radio" data-attr="'.sanitize_title($data['attribute_label']).'" data-cost="'.$cost.'" '.$check.' name="woocp_attribute_'.$currentId.'" value="'.$option->term_id.'"/><span></span>'.$option->name.$lprice.'</label>';
									//$component_attribute_options .= '<option value="'.$option->term_id.'" data-label="'.$option->name.'" data-class="woocp_term_thumb" data-style="background-image:url(\''.$style.'\');">'.$option->name.'</option>';
									//$component_attribute_options .= '<label class="attribute-option"><figure class="'.$cp.'"><img src="'.$style.'" /></figure><input type="radio" data-attr="'.sanitize_title($data['attribute_label']).'" data-cost="'.$cost.'" '.$check.' name="woocp_attribute_'.$currentId.'" value="'.$option->term_id.'"/><span></span>'.$option->name.$lprice.'</label>';
								}

								$acf_components = get_field('components',$prodId);

								foreach ($acf_components as $acf_key => $acf_component) {
									$acf_term = get_term_by('term_id', $acf_component['component'], 'product_component');
									if($acf_term->slug == $data['attribute_name']){
										foreach($acf_component['attributes'] as $acf_attr_key => $acf_attr){
											$component_attribute_options .= $componentsArray['op_'.$acf_attr['attribute']];
										}
									}
								}

								//get component attribute template
								ob_start();
								$this->woocp_get_template('product_component_attribute.php',array(
										'height'=>$height,
										'attrId'=>$attrId,
										'attributeLabel'=>$attributeLabel,
										'no_change_img'=>$no_change_img,
										'no_change_text'=>$no_change_text,
										'component_attribute_options'=>$component_attribute_options,
									)
								);
								$component_attributes .= ob_get_clean();
							}
						}

						//get component template
						ob_start();
						$this->woocp_get_template('product_component.php',array(
								'compClass'=>$compClass,
								'compClass2'=>$compClass2,
								'compId'=>$compId,
								'componentLabel'=>$componentLabel,
								'titleTag'=>$titleTag,
								'descClass'=>$descClass,
								'desc'=>$desc,
								'component_attributes'=>$component_attributes,
							)
						);
						$html .= ob_get_clean();

						break;
					}
				}
			}

		}
		return $html;
	}

	/**
	 * Gets product selector
	 *
	 * @since     1.0.0
	 * @param     array  $products    Array of products.
	 * @param     int    $prodId      Current product ID.
	 * @return    string $html        Html for the product selector.
	 */
	function woocp_get_product_select( $products, $prodId ){

		$html = '';
		$icons = [];
		$label = isset($this->settings->woocp_select_product_text) && !empty($this->settings->woocp_select_product_text) ? $this->settings->woocp_select_product_text : __("Select product",$this->woocp);

		$html .= '<label for="woocp_product_select">'.$label.'</label>';
		$html .= '<select id="woocp_product_select" name="woocp_product_select" class="woocp_product_select" autocomplete="off">';
		foreach($products as $prod){
			if(!is_object($prod)) continue;
			$data = $prod->get_data();

			$name = $data['name'];

			$selected = $data['id'] === $prodId ? 'selected="selected"' : '';

			$prodId2 = $data['id'];
			$icon = get_the_post_thumbnail_url($prodId2,'woocp_product_image');

			$icons[] = $icon;
			$style = !empty($icon) ? $icon : plugin_dir_url( __FILE__ ) . 'img/placeholder.png';
			$html .= '<option value="'.$prodId2.'" data-class="woocp_product_thumb" data-style="background-image:url(\''.$style.'\');" '.$selected.'>'.$name.'</option>';
		}
		$html .= '</select>';
		$html .= '<div class="woocp_icon_preloads">';
		foreach($icons as $icon){
			if($icon !== null) $html .= '<img src="'.$icon.'" class="woocp_icon_preload" alt="" title="" />';
		}
		$html .='</div>';

		return $html;

	}

	/**
	 * Adds the customized product to cart
	 * Normal ATC process, just adds some custom meta
	 *
	 * @since     1.0.0
	 */
	function woocp_add_to_cart(){	//AJAX
		global $woocommerce;
		$add_to_cart = $_POST['add_to_cart'];
		$quantity     = $_POST['qty'];
		$selected = $_POST['woocp_selected'];

		$action = $woocommerce->cart->add_to_cart( $add_to_cart, $quantity, 0, array(), array('woocp_selected'=>$selected));

		//errors
		if(!$action){
			$notices = WC()->session->get('wc_notices', array());
			foreach($notices as $type => $messages){
				foreach($messages as $key => $msg){
					wc_print_notice($msg,$type);
					unset($messages[$key]);
				}
				unset($notices[$type]);
			}
			WC()->session->set('wc_notices', $notices);
		}
		//success
		else{
			if (get_option('woocommerce_cart_redirect_after_add')=='yes') {
				$return_to  = get_permalink(wc_get_page_id('shop'));
				$msg = __('Tu producto se agrego al carrito.', $this->woocp);
			}
			else {
				$msg = sprintf('<a href="%s" class="button">%s</a> %s', get_permalink(wc_get_page_id('cart')), __('View Cart &rarr;', $this->woocp), __('Product successfully added to your cart.', $this->woocp) );
			}
			wc_print_notice($msg,'success');
		}

		wp_die();
	}

	/**
	 * Capture woocp data when adding item to cart.
	 *
	 * @since   1.0.0
	 * @param 	array 	$cart_item_data	 Cart item data.
	 * @return 	array 					 Cart item data with new data attached.
	 */
	function woocp_add_cart_item_data( $cart_item_data ){

		$new_value = array();
		$new_value['woocp_selected'] = $_POST['woocp_selected'];

		if(empty($new_value['woocp_selected'])) unset($new_value['woocp_selected']);

		if(empty($cart_item_data)) {
			return $new_value;
		} else {
			return array_merge($cart_item_data, $new_value);
		}
	}

	/**
	 * Attach woocp data to cart item.
	 *
	 * @since   1.0.0
	 * @param 	array 	$cartItemData			Current cart item data.
	 * @param 	array 	$cartItemSessionData	Session cart data.
	 * @return 	array 	$cartItemData			Cart item with new data attached.
	 */
	function woocp_get_cart_item_from_session( $cartItemData, $cartItemSessionData ) {
		if ( isset( $cartItemSessionData['woocp_selected'] ) ) {
			$cartItemData['woocp_selected'] = $cartItemSessionData['woocp_selected'];
		}

		return $cartItemData;
	}
	/**
	 * Display woocp data on cart & checkout form.
	 *
	 * @since 1.0.0
	 * @param 	array 	$data			Current cart data.
	 * @param 	array 	$cartItem		Current cart item.
	 * @return 	array	$data			New cart data.
	 */
	function woocp_cart_details( $data, $cartItem ) {
		$settings = $this->settings;
		$text = isset($settings->woocp_customized_text) && !empty($settings->woocp_customized_text) ? $settings->woocp_customized_text : __( 'Customized', $this->woocp );
		$html = null;
		if ( isset( $cartItem['woocp_selected'] ) && count(json_decode(stripslashes($cartItem['woocp_selected']),true)) > 0) {
			$html .= '<table class="woocp_cart_details">';
			$items = json_decode(stripslashes($cartItem['woocp_selected']));
			foreach($items as $item){
				if(!is_object($item)) continue;
				if(count($item->attrs) !== 0){
					$html .= '<tr>';
					$html .= '<td><ul>';
					foreach($item->attrs as $attr){
						if(isset($attr->selectedLabel))	{
							$label = '';
							if($attr->extraCost == 'default' OR $attr->extraCost == 0){
								$eCost = '';
							}else{
								$eCost = ' <strong class="extra-cost">('.wc_price($attr->extraCost).')</strong>';
							}
							$html .= '<li>'.ucfirst(str_replace(array('-'), ' ', $item->label)) . ': '. $attr->selectedLabel.'</li>';
						}
					}
					$html .= '</ul></td>';
					$html .= '</tr>';
				}
			}
			$html .= '</table>';
			$data[] = array(
				'name' => $text,
				'value' => $html
			);
		}
		return $data;

	}
	/**
	 * Display woocp data as part of the order.
	 *
	 * @since    1.0.0
	 * @param    int    $item_id    Order item ID.
	 * @param    array  $item       Item object.
	 * @param    array  $order_id   Order ID.
	 * @return   void
	 * @throws   Exception
	 */
	function woocp_add_order_item_meta( $item_id, $item, $order_id ) {

		$values = $item->legacy_values;

		if ( isset( $values['woocp_selected'] ) ) {
			$comps = json_decode(stripslashes($values['woocp_selected']));
			foreach($comps as $comp){
				if(!is_object($comp)) continue;
				$html = null;
				foreach($comp->attrs as $attr){
					if(!is_object($attr)) continue;
					if(isset($attr->selectedLabel))	{
						$html .= $attr->label . ' - '. $attr->selectedLabel;
						if($attr !== end($comp->attrs) && isset(end($comp->attrs)->selectedLabel)) $html .= ', ';
					}
				}
				wc_add_order_item_meta( $item_id, $comp->label, $html );
			}
		}

	}

}


function before_calculate_totals( $cart_obj ) {
 if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
 return;
 }
 // Iterate through each cart item
 foreach( $cart_obj->get_cart() as $cart_item_key => $values ) {

   if( $values['woocp_selected']) {
		 $extra = 0;

		 foreach (json_decode(stripslashes($values['woocp_selected'])) as $s_key => $s_value) {
			 $extraCost = $s_value->attrs[0]->extraCost;
		 	if($extraCost != 'default' AND $extraCost != 0){
				$extra += $extraCost;
			}
		 }
     $price = get_field($values['custom_quantity'],$values['product_id']);
     $values['data']->set_price( ( $values['data']->get_price() + $extra ) );
   }
 }
 return $cart_obj;
}

add_action( 'woocommerce_before_calculate_totals', 'before_calculate_totals', 10, 1 );
