<?php
/**
 * Component attribute view
 *
 * @since      1.0.0
 * @package    WOOCP
 * @subpackage WOOCP/templates
 */

$currentId = rand(100,999).'-'.rand(100,999);

?>
<div class="woocp_component_attribute" <?= isset($height) ? $height : null ?> data-attributeId="<?= isset($attrId) ? $attrId : null ?>" data-attributeLabel="<?= isset($attributeLabel) ? $attributeLabel : null ?>">
	<label class="woocp_attribute_name woocp_noselect" for="woocp_attribute_<?= $currentId ?>">
		<?= isset($attributeLabel) ? $attributeLabel : null ?>
	</label>
	<select class="woocp_attribute_select" name="woocp_attribute_<?= $currentId ?>" id="woocp_attribute_<?= $currentId ?>" autocomplete="off">
		<option value="0" data-class="woocp_term_thumb" data-style="background-image:url('<?= isset($no_change_img) ? $no_change_img : null ?>');">
			<?= isset($no_change_text) ? $no_change_text : null ?>
		</option>
		<?= isset($component_attribute_options) ? $component_attribute_options : null ?>
	</select>
</div>
