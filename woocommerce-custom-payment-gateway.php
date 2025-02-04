<?php
/**
 * Plugin Name: WooCommerce Custom Payment Gateway
 * Plugin URI: https://example.com/woocommerce-custom-payment-gateway
 * Description: A plugin to add multiple custom payment gateways to WooCommerce.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: woo-custom-payment-gateway
 * Domain Path: /languages
 * WC requires at least: 5.0.0
 * WC tested up to: 7.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', 'woo_custom_payment_gateway_woocommerce_missing_notice');
    return;
}

/**
 * Display an admin notice if WooCommerce is not active.
 */
function woo_custom_payment_gateway_woocommerce_missing_notice() {
    echo '<div class="error"><p>';
    printf(
        esc_html__('WooCommerce Custom Payment Gateway requires WooCommerce to be installed and active. You can download %s here.', 'woo-custom-payment-gateway'),
        '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>'
    );
    echo '</p></div>';
}

// Load the plugin text domain for localization
add_action('plugins_loaded', 'woo_custom_payment_gateway_load_textdomain');
function woo_custom_payment_gateway_load_textdomain() {
    load_plugin_textdomain('woo-custom-payment-gateway', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

// Include the main payment gateway class
require_once plugin_dir_path(__FILE__) . 'includes/class-woo-custom-gateway.php';

// Register the payment gateway with WooCommerce
add_filter('woocommerce_payment_gateways', 'woo_custom_payment_gateway_add_gateway_class');
function woo_custom_payment_gateway_add_gateway_class($gateways) {
    $gateways[] = 'WC_Custom_Gateway'; // Add your gateway class to the list
    return $gateways;
}

// Initialize the payment gateway class
add_action('plugins_loaded', 'woo_custom_payment_gateway_init_gateway_class');
function woo_custom_payment_gateway_init_gateway_class() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-woo-custom-gateway.php';
}

// Enqueue styles for the frontend
add_action('wp_enqueue_scripts', 'woo_custom_payment_gateway_enqueue_styles');
function woo_custom_payment_gateway_enqueue_styles() {
    wp_enqueue_style(
        'woo-custom-payment-gateway-style',
        plugin_dir_url(__FILE__) . 'assets/style.css',
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/style.css')
    );
}