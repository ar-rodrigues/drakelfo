<?php /** @noinspection PhpToStringImplementationInspection */
/** @noinspection SpellCheckingInspection */

/**
 * WOOCP components class
 *
 * @since      1.0.0
 * @package    WOOCP
 * @subpackage WOOCP/components
 * @author     Grega Radelj <info@grrega.com>
 */
class WOOCP_Components extends WOOCP {

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
	 * - Retrieves and echoes the html for the new component edit screen
	 * - Saves the new components array to db
	 *
	 * @since     1.0.0
	 */
	function woocp_add_product_component() {	//AJAX
		if (!$this->woocp_check_perms()) return false;

		$post_id = intval( $_POST['postId'] );
		$component_id = intval( $_POST['componentId'] );
		$product_components = $_POST['_woocp_product_components'];

		$component = $this->woocp_get_components(array($component_id))[0];
		$metabox = $this->woocp_add_component_metabox($component);

		echo $metabox;

		$action = $this->woocp_save_product_components($post_id,$product_components);

		wp_die();
		return null;
	}

	/**
	 * Retrieves and echoes the html for the new attribute edit screen
	 *
	 * @since     1.0.0
	 */
	function woocp_add_component_attribute() {	//AJAX
		if (!$this->woocp_check_perms()) return false;

		$attrId = intval( $_POST['attributeId'] );

		$attributes = $this->woocp_get_attributes($attrId);
		$attrbox = $this->woocp_add_options_select($attributes[$attrId]);

		echo $attrbox;

		wp_die();
		return null;
	}

	/**
	 * Gets the html for the component metabox
	 *
	 * @since     1.0.0
	 * @param	  object   $comp  Component.
	 * @return	  string   $html  Html of the component metabox.
	 */
	function woocp_add_component_metabox($comp){
		if (!$this->woocp_check_perms()) return false;

		$attrs = $this->woocp_get_attributes();

		$compId = isset($comp->id) ? $comp->id : $comp->term_id;
		$attached = $comp->attrs !== null ? $comp->attrs : array();
		$component = $this->woocp_get_components(array($compId))[0];
		$label = isset($comp->label) ? $comp->label : '';
		$desc = isset($comp->description) ? $comp->description : null;
		$class1 = ' form-row form-row-first fee';
		$class2 = 'up';

		$html = '
		<div class="woocp_product_component wc-metabox closed taxonomy" data-taxonomy="woocp_product_component" data-componentId="'.$compId.'">
			<h3 class="sorthandle">
				<a href="#" class="woocp_delete_product_component delete">' . __('Remove',$this->woocp) . '</a>
				<div class="handlediv" title="' . __('Click to toggle',$this->woocp) . '" aria-expanded="true"></div>
				<strong>'.$component->name.'</strong>
			</h3>
			<div class="woocp_component_data wc-metabox-content" style="display: none;">
				<div class="component_form">
					<p class="form-field'.$class1.'">
						<label class="'.$class2.'" for="component_frontend_name_'.$compId.'">'.__('Name', $this->woocp).': '.wc_help_tip(__('Name of the component that will be visible for the current product only. Leave blank to use default component name.',$this->woocp),true).'</label>

						<input type="text" size="50" name="component_label_'.$compId.'" value="'.$label.'" placeholder="'.$component->name.'" />
					</p>
					<p class="form-field'.$class1.' component_description">
					<label class="'.$class2.'" for="woocp_component_description_'.$compId.'">'.__('Description', $this->woocp).': '.wc_help_tip(__('Component description that will be visible for the current product only. Leave blank to use default component description.',$this->woocp),true).'</label>
					<textarea name="woocp_component_description_'.$compId.'" rows="10" cols="10" placeholder="'.$component->description.'" >'.$desc.'</textarea>
					</p>
					<p class="woocp_add_attribute_field">
					<select class="woocp_attributes_select" name="woocp_select_attributes_'.$compId.'">
						<option value="placeholder">' . __('Add attribute',$this->woocp) . '</option>';
		foreach( $attrs as $attr_name => $attr ) {
			$html .= '<option value="' . $attr['attribute_id'] . '">' . $attr['attribute_label'] . '</option>';
		}
		$html .= '</select>
						<button class="button woocp_add_component_attribute left">'.__('Add',$this->woocp).'</button>
						<label class="spinner woocp_add_attribute left"></label>
					</p>
					<input type="hidden" name="woocp_component_attributes_'.$compId.'" value="'.htmlspecialchars(json_encode($attached, JSON_UNESCAPED_UNICODE)).'" autocomplete="off"/>
				</div>
				<div class="woocp_component_attributes_list">';
		foreach( $attached as $atobj ) {
			$attr_id = $atobj->id;
			$options = $atobj->options;
			$fullAttr = $attrs[$attr_id];
			$html .= $this->woocp_add_options_select($fullAttr,$options);
		}
		$html .= '</div>
			</div>
		</div>';
		return $html;
	}

	/**
	 * Gets the html for the new attribute metabox
	 *
	 * @since     1.0.0
	 * @param     array   $attribute  Attribute.
	 * @param     bool    $options    Currently saved options.
	 * @return    string  $html       Html for the attribute metabox.
	 */
	function woocp_add_options_select($attribute, $options=false){
		if (!$this->woocp_check_perms()) return false;

		$html = '<div class="woocp_options_select_container" data-attributeId="'.$attribute['attribute_id'].'" data-attributeName="'.$attribute['attribute_name'].'">
					<div class="data sorthandle">
						<label>'.__('Name',$this->woocp).':</label>
						<br/>
						<strong>'.$attribute['attribute_label'].'</strong>
					</div>
					<div class="options">
						<label for="woocp_select2_attributes_options_'.$attribute['attribute_id'].'[]">'.__('Value(s)',$this->woocp).':</label>
						<a class="woocp_remove_attribute hide" href="#">'.__('Remove',$this->woocp).'</a>
						<select class="woocp_attribute_options_select woocp_select2" multiple="multiple" name="woocp_select2_attributes_options_'.$attribute['attribute_id'].'[]">';
		foreach( $attribute['options'] as $option ) {
			$selected = (in_array((int) $option->term_id, (array) $options ) ) ? ' selected="selected"' : '';
			$html .= '<option value="' . $option->term_id . '" ' . $selected . '>' . $option->name . '</option>';
		}
		$html .= '</select>
						<div class="woocp_select2_buttons">
							<button class="button plus select_all_options">'.__('Select all',$this->woocp).'</button>
							<button class="button minus clear_all_options">'.__('Select none',$this->woocp).'</button>
						</div>
					</div>
				</div>';
		return $html;
	}

	/**
	 * Saves the product components and product customzation fee (if given) to db
	 * Can be done over ajax or PHP function
	 *
	 * @since     1.0.0
	 * @param     bool  $product_id  Product ID.
	 * @param     bool  $components  Array of components.
	 * @return    bool
	 */
	function woocp_save_product_components( $product_id=false, $components=false){
		if (!$this->woocp_check_perms()) return false;

		$ajax = true;
		if(!$product_id) $product_id = intval( $_POST['postId'] );
		if($components !== false) {
			$insert = $components;
			$ajax = false;
		}
		else if( !$components && isset( $_POST['_woocp_product_components'] ) && $_POST['_woocp_product_components'] !== null ) $insert = $_POST['_woocp_product_components'];
		else $insert = '[]';
		if(is_array($insert)) $insert = json_encode($insert);
		$insert = stripslashes($insert);

		update_post_meta( $product_id, '_woocp_product_components', $insert );

		if($ajax) wp_die();
		return null;
	}

}
