<?php
/**
 * Customizer container content for changing the product or variation
 *
 * @since      1.0.0
 * @package    WOOCP
 * @subpackage WOOCP/templates
 */
?>
<div class="woocp_public_notice <?= !isset($customizer_img) || null == $customizer_img ? 'clear' : null  ?>">
	<?= isset($message) ? $message : null ?>
</div>
<div class="woocp_components_list <?= isset($listClass) ? $listClass : null ?>">
	<?= isset($component_list) ? $component_list : null ?>
	<input name="woocp_selected" type="hidden" value="{}" />
	<?= isset($add_to_cart_button) ? $add_to_cart_button : null ?>
	<div class="woocp_msg_area" style="height:0;"></div>
</div>
<div class="woocp_product_customizer_image">
	<?= isset($customizer_img) ? $customizer_img : null ?>
</div>
