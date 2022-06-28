<?php
/**
 * Define the internationalization functionality.
 *
 * @since      1.0.0
 * @package    WOOCP
 * @subpackage WOOCP/includes
 * @author     Grega Radelj <info@grrega.com>
 */
class WOOCP_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function woocp_load_plugin_textdomain() {

		load_plugin_textdomain(
			'custom-products-for-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

}
