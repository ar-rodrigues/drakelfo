<?php
/**
 * Fired during plugin deactivation.
 *
 * @since      1.0.0
 * @package    WOOCP
 * @subpackage WOOCP/includes
 * @author     Grega Radelj <info@grrega.com>
 */
class WOOCP_Deactivator {

	/**
	 * Deactivation hook
	 *
	 *
	 * @since    1.0.0
	 */
	public static function woocp_deactivate() {
		flush_rewrite_rules();

	}

}
