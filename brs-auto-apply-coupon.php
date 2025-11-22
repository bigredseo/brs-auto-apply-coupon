<?php
/**
 * Plugin Name: BRS Auto Apply Coupon
 * Description: Automatically applies a WooCommerce coupon during a configured date/time window. Includes admin settings. Requires WooCommerce.
 * Version: 1.0.1
 * Author: Big Red SEO
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BRS_AAC_PATH', plugin_dir_path( __FILE__ ) );
define( 'BRS_AAC_URL',  plugin_dir_url( __FILE__ ) );

/**
 * WooCommerce dependency check
 */
add_action( 'admin_init', function() {

    if ( ! class_exists( 'WooCommerce' ) ) {

        deactivate_plugins( plugin_basename( __FILE__ ) );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
});

/**
 * Admin notice when WooCommerce missing
 */
add_action( 'admin_notices', function() {

    if ( class_exists( 'WooCommerce' ) ) {
        return;
    }

    ?>
    <div class="notice notice-error">
        <p><strong>BRS Auto Apply Coupon</strong> requires <strong>WooCommerce</strong> to be installed and active. The plugin has been deactivated.</p>
    </div>
    <?php
});

/**
 * Load plugin only if WooCommerce is active
 */
add_action( 'plugins_loaded', function() {

    if ( ! class_exists( 'WooCommerce' ) ) return;

    require_once BRS_AAC_PATH . 'includes/class-brs-admin.php';
    require_once BRS_AAC_PATH . 'includes/class-brs-coupon-handler.php';

    new BRS_Admin_Settings();
    new BRS_Coupon_Handler();
});

/**
 * Add Settings link on the Plugins screen
 */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function( $links ) {

    $settings_url = admin_url( 'admin.php?page=brs-auto-apply-coupon' );

    $settings_link = '<a href="' . esc_url( $settings_url ) . '">Settings</a>';

    array_unshift( $links, $settings_link );

    return $links;
});
