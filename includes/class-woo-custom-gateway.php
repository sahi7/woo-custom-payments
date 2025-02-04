<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Custom Payment Gateway Class for WooCommerce.
 */
class WC_Custom_Gateway extends WC_Payment_Gateway {

    /**
     * Constructor for the gateway.
     */
    public function __construct() {
        $this->id                 = 'custom_gateway'; // Payment gateway ID (must be unique)
        $this->icon               = ''; // URL of the icon to display (optional)
        $this->has_fields         = true; // Set to true if you need custom payment fields
        $this->method_title       = __('Custom Payment Gateway', 'woo-custom-payment-gateway'); // Title in WooCommerce settings
        $this->method_description = __('Add a custom payment gateway to WooCommerce.', 'woo-custom-payment-gateway'); // Description in WooCommerce settings

        // Load the settings
        $this->init_form_fields();
        $this->init_settings();

        // Define user-facing settings
        $this->title       = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled     = $this->get_option('enabled');
        $this->logo_url    = $this->get_option('logo_url');

        // Save settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    /**
     * Initialize Gateway Settings Form Fields.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __('Enable/Disable', 'woo-custom-payment-gateway'),
                'type'    => 'checkbox',
                'label'   => __('Enable Custom Payment Gateway', 'woo-custom-payment-gateway'),
                'default' => 'no',
            ),
            'title' => array(
                'title'       => __('Title', 'woo-custom-payment-gateway'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'woo-custom-payment-gateway'),
                'default'     => __('Custom Payment Gateway', 'woo-custom-payment-gateway'),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __('Description', 'woo-custom-payment-gateway'),
                'type'        => 'textarea',
                'description' => __('This controls the description which the user sees during checkout.', 'woo-custom-payment-gateway'),
                'default'     => __('Pay using your custom payment method.', 'woo-custom-payment-gateway'),
                'desc_tip'    => true,
            ),
            'logo_url' => array(
                'title'       => __('Payment Logo URL', 'woo-custom-payment-gateway'),
                'type'        => 'text',
                'description' => __('Add a logo for your payment gateway (optional).', 'woo-custom-payment-gateway'),
                'default'     => '',
                'desc_tip'    => true,
            ),
        );
    }

    /**
     * Display Payment Fields on the Checkout Page.
     */
    public function payment_fields() {
        if ($this->description) {
            echo wpautop(wp_kses_post($this->description));
        }

        // Get custom fields configuration
        $custom_fields = get_option('woo_custom_gateway_fields_' . $this->id, array());

        // Render custom fields
        echo '<fieldset id="wc-' . esc_attr($this->id) . '-form" class="wc-payment-form">';
        foreach ($custom_fields as $field) {
            echo '<div class="form-row form-row-wide">';
            echo '<label for="' . esc_attr($field['label']) . '">' . esc_html($field['label']);
            if ($field['required']) {
                echo ' <span class="required">*</span>';
            }
            echo '</label>';

            switch ($field['type']) {
                case 'textarea':
                    echo '<textarea id="' . esc_attr($field['label']) . '" name="' . esc_attr($field['label']) . '"></textarea>';
                    break;
                case 'checkbox':
                    echo '<input type="checkbox" id="' . esc_attr($field['label']) . '" name="' . esc_attr($field['label']) . '">';
                    break;
                default:
                    echo '<input type="text" id="' . esc_attr($field['label']) . '" name="' . esc_attr($field['label']) . '" autocomplete="off">';
                    break;
            }

            echo '</div>';
        }
        echo '</fieldset>';
    }

    /**
     * Validate Payment Fields on the Checkout Page.
     */
    public function validate_fields() {
        $custom_fields = get_option('woo_custom_gateway_fields_' . $this->id, array());
        foreach ($custom_fields as $field) {
            if ($field['required'] && empty($_POST[$field['label']])) {
                wc_add_notice(sprintf(__('%s is a required field.', 'woo-custom-payment-gateway'), $field['label']), 'error');
                return false;
            }
        }
        return true;
    }

    /**
     * Process the Payment and Return the Result.
     *
     * @param int $order_id Order ID.
     * @return array
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        // Save custom field data
        $custom_fields = get_option('woo_custom_gateway_fields_' . $this->id, array());
        foreach ($custom_fields as $field) {
            if (isset($_POST[$field['label']])) {
                $order->update_meta_data($field['label'], sanitize_text_field($_POST[$field['label']]));
            }
        }
        $order->save();

        // Mark the order as on-hold
        $order->update_status('on-hold', __('Awaiting payment confirmation.', 'woo-custom-payment-gateway'));

        // Reduce stock levels
        wc_reduce_stock_levels($order_id);

        // Empty the cart
        WC()->cart->empty_cart();

        // Return thank-you page redirect
        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        );
    }

    /**
     * Get Payment Gateway Icon.
     *
     * @return string
     */
    public function get_icon() {
        $icon = '';
        if ($this->logo_url) {
            $icon = '<img src="' . esc_url($this->logo_url) . '" alt="' . esc_attr($this->title) . '" />';
        }
        return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
    }
}