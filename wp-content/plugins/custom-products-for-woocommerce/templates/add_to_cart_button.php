<?php
/**
 * Add to cart section of the customizer
 *
 * @since      1.0.0
 * @package    WOOCP
 * @subpackage WOOCP/templates
 */
$domain = 'custom-products-for-woocommerce';
?>
<div class="woocp_add_to_cart_container">
    <input type="hidden" name="woocp_selected" autocomplete="off" value="{}" />
    <?php if(isset($quantity) && $quantity) : ?>
        <div class="quantity woocp_quantity_container">
            <input class="input-text qty text woocp_quantity" step="1" min="1" max="" name="quantity" value="1" title="<?php _e('Quantity',$domain); ?>" size="4" pattern="[0-9]*" inputmode="numeric" type="number" />
        </div>
    <?php endif; ?>
    <button class="button woocp_add_to_cart_button single_add_to_cart_button alt <?= isset($class) ? $class : null ?>" type="submit" data-productid="<?= isset($prodId) ? $prodId : null ?>">
        <?php _e('Add to cart','woocommerce');?>
    </button>
    <div class="woocp_msg_area" style="height:0;"></div>
    <div class="woocp_spinner_container" style="display:none;">
        <div class="spinner woocp_add_product"></div>
    </div>
</div>
