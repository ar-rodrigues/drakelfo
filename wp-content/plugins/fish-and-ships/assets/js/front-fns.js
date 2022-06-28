/**
 * Front-end JS for cart 
 *
 * @package Fish and Ships
 * @since 1.1.12
 */

jQuery(document).ready(function($) {
	
	/*
	function change_cupon_info() {

		if (jQuery('.wc_fns_cart_control').length == 0) return;

		coupons_applied = jQuery('.wc_fns_cart_control').last().attr('data-coupons_applied');
		coupons_applied = coupons_applied.split(',');
		
		for (var i=0; i<coupons_applied.length; i++) {
			if (coupons_applied[i] != '') {
				jQuery('.cart-discount.coupon-' + coupons_applied[i] + ' .woocommerce-remove-coupon').hide();
			}
		}
		
	}
	change_cupon_info();
	*/
	jQuery(document).on('updated_shipping_method', function () {
		
		if (jQuery('.wc_fns_cart_control').length == 0) return;
		
		if (jQuery('.wc_fns_cart_control').last().attr('data-some_notice_from_action') == '1') {
			
			jQuery(document).trigger('wc_update_cart');
			//change_cupon_info();
		}
	});
	
	/*
	jQuery(document).on('updated_cart_totals', function () {
		change_cupon_info();
	});
	*/
});
