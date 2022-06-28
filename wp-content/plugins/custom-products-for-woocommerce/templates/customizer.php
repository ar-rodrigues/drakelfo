<?php
/**
 * Customizer container for [woocp] shortcode
 *
 * @since      1.0.0
 * @package    WOOCP
 * @subpackage WOOCP/templates
 */
?>
<div class="woocp_customizer_container <?= isset($class) ? $class : null ?>">
	<?= isset($product_select) ? $product_select : null ?>
	<div class="woocp_customizer" data-productId="<?= isset($prodId) ? $prodId : null ?>">
		<div class="woocp_components_list <?= isset($listClass) ? $listClass : null ?>">
			<?= isset($component_list) ? $component_list : null ?>
			<?= isset($add_to_cart_button) ? $add_to_cart_button : null ?>
            <?php if(isset($msgArea) && $msgArea) : ?><div class="woocp_msg_area" style="height:0;"></div> <?php endif; ?>
		</div>
		<div class="woocp_product_customizer_image">
			<?= isset($customizer_img) ? $customizer_img : null ?>
		</div>
	</div>
</div>
