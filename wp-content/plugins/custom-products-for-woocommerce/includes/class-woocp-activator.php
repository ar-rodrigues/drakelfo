<?php
/**
 * Fired during plugin activation.
 *
 * @since      1.0.0
 * @package    WOOCP
 * @subpackage WOOCP/includes
 * @author     Grega Radelj <info@grrega.com>
 */
class WOOCP_Activator {

    /**
     * Activation hook
     *
     * Register components, create default settings
     * and save the current version to database
     *
     * @since    1.0.0
     */
    public static function woocp_activate() {
        if ( in_array( 'custom-products-for-woocommerce-premium/custom-products-for-woocommerce-premium.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )  {
            deactivate_plugins( '/custom-products-for-woocommerce-premium/custom-products-for-woocommerce-premium.php' );
        }
        if ( in_array( 'custom-products-for-woocommerce-ultimate/custom-products-for-woocommerce-ultimate.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )  {
            deactivate_plugins( '/custom-products-for-woocommerce-ultimate/custom-products-for-woocommerce-ultimate.php' );
        }
        $plugin = new WOOCP();
        $plugin->woocp_register_components();
        flush_rewrite_rules();
        $plugin->woocp_create_default_settings();
        update_option('woocp_version',WOOCP_VERSION);
    }

}
