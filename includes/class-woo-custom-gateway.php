<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Custom_Gateway extends WC_Payment_Gateway {
    
    public function __construct() {
        $this->id = 'custom_gateway';
        $this->method_title = __('Custom Payment', 'woo-custom-payments');
        $this->method_description = __('A custom payment gateway with a logo.', 'woo-custom-payments');
        $this->has_fields = false; // No custom fields

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        // Get settings
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->payment_logo = $this->get_option('payment_logo');

        // Save settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    // Admin settings fields
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __('Enable/Disable', 'woo-custom-payments'),
                'type'    => 'checkbox',
                'label'   => __('Enable this payment method', 'woo-custom-payments'),
                'default' => 'yes'
            ),
            'title' => array(
                'title'       => __('Title', 'woo-custom-payments'),
                'type'        => 'text',
                'description' => __('Title displayed at checkout.', 'woo-custom-payments'),
                'default'     => __('Custom Payment', 'woo-custom-payments'),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __('Description', 'woo-custom-payments'),
                'type'        => 'textarea',
                'description' => __('Payment method description displayed at checkout.', 'woo-custom-payments'),
                'default'     => __('Pay with this custom payment gateway.', 'woo-custom-payments'),
                'desc_tip'    => true,
            ),
            'payment_logo' => array(
                'title'       => __('Payment Logo URL', 'woo-custom-payments'),
                'type'        => 'text',
                'description' => __('Enter the URL of the payment logo to display.', 'woo-custom-payments'),
                'default'     => '',
                'desc_tip'    => true,
            ),
        );
    }

    // Custom checkout display (logo below label)
    public function get_title() {
        return esc_html($this->title);
    }

    // Add logo below the title
    public function get_description() {
        $logo = $this->payment_logo ? '<img src="' . esc_url($this->payment_logo) . '" class="woo-payment-logo">' : '';
        return $logo . '<p class="woo-payment-description">' . esc_html($this->description) . '</p>';
    }

    // Process the payment (for order status update)
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $order->update_status('on-hold', __('Awaiting payment confirmation.', 'woo-custom-payments'));
        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        );
    }
}
