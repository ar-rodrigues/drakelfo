<?php
/**
 * Product component view
 *
 * @since      1.0.0
 * @package    WOOCP_Premium
 * @subpackage WOOCP_Premium/templates
 */
?>
<div class="woocp_product_component <?= isset($compClass) && isset($compClass2) ? $compClass.$compClass2 : null ?>" data-componentId="<?= isset($compId) ? $compId : null ?>" data-componentLabel="<?= isset($componentLabel) ? $componentLabel : null ?>">
    <<?= isset($titleTag) ? $titleTag : 'div' ?> class="woocp_component_label expand woocp_noselect">
    <?= isset($componentLabel) ? $componentLabel : null ?>
    <div class="arrow"></div>
</<?= isset($titleTag) ? $titleTag : 'div' ?>>
<p class="woocp_component_description <?= isset($descClass) ? $descClass : null;?>">
  <?php var_dump($compId) ?>
  LOREM
    <?= isset($desc) ? $desc : null ?>
</p>
<?= isset($component_attributes) ? $component_attributes : null ?>
</div>
