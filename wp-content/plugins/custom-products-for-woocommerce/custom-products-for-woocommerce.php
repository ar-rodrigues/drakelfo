<?php
/**
 * Custom Products for WooCommerce by Grega Radelj
 *
 * @link              https://grrega.com/projects/custom-products-for-woocommerce
 * @since             1.0.0
 * @package           WOOCP
 *
 * @wordpress-plugin
 * Plugin Name:       Custom Products for WooCommerce
 * Plugin URI:        #
 * Description:       Offer your visitors a unique shopping experience. Split products into components and let your visitors customize each one before purchasing.
 * Version:           13.02.91
 * Author:            Netzdo
 * Author URI:        #
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl.txt
 * Text Domain:       custom-products-for-woocommerce
 * Domain Path:       /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 3.6.5
 *
 * Copyright 2018 Grrega.com  (email : info@grrega.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'WOOCP_VERSION', '1.1.1' );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocp-activator.php
 */
function activate_woocp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocp-activator.php';
	WOOCP_Activator::woocp_activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocp-deactivator.php
 */
function deactivate_woocp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocp-deactivator.php';
	WOOCP_Deactivator::woocp_deactivate();
}

register_activation_hook( __FILE__, 'activate_woocp' );
register_deactivation_hook( __FILE__, 'deactivate_woocp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocp.php';

if(!function_exists('write_log')){
    /**
     * Write Debug Log
     * @param $log
     */
    function write_log($log )  {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_custom_products_for_woocommerce() {

	if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )  {
		add_action('admin_notices', 'woocp_admin_notice_woocommerce_not_active'); return false;
	}
	else{
		$plugin = new WOOCP();
		$plugin->woocp_run();
	}

}
run_custom_products_for_woocommerce();


function woocp_admin_notice_woocommerce_not_active(){
    echo '<div class="notice notice-error">
          <p>'.__('Custom Products for WooCommerce is enabled but not effective. It requires WooCommerce in order to work.','custom-products-for-woocommerce').'</p>
         </div>';
}
