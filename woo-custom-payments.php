<?php
/**
 * Plugin Name: Woo Custom Payments
 * Plugin URI: https://yourwebsite.com/
 * Description: A WooCommerce plugin to add multiple custom payment gateways with logos.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com/
 * License: GPL-2.0+
 * Text Domain: woo-custom-payments
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include the payment gateway class
require_once plugin_dir_path(__FILE__) . 'includes/class-woo-custom-gateway.php';

// Register the payment gateway
add_filter('woocommerce_payment_gateways', 'woo_custom_add_gateway_class');
function woo_custom_add_gateway_class($gateways) {
    $gateways[] = 'WC_Custom_Gateway';
    return $gateways;
}

// Load the payment gateway class
add_action('plugins_loaded', 'woo_custom_init_gateway_class');
function woo_custom_init_gateway_class() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-woo-custom-gateway.php';
}

add_action('wp_enqueue_scripts', 'woo_custom_payment_styles');
function woo_custom_payment_styles() {
    wp_enqueue_style('woo-custom-payments-style', plugin_dir_url(__FILE__) . 'assets/style.css');
}
