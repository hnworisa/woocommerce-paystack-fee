<?php
/**
 * Paystack Transaction Fee for WooCommerce
 *
 * @package           WooCommercePaystackFee
 * @author            Chinemerem Nworisa
 * @copyright         2024 Chinemerem Nworisa
 * @license           GPL-3.0-or-later
 *
 * Plugin Name: Paystack Transaction Fee for WooCommerce
 * Description: Add Paystack fees to WooCommerce checkout
 * Version: 1.0.0
 * Author: Chinemerem Nworisa
 * Author URI: https://github.com/hnworisa
 * License: GPLv3
 * Text Domain: woocommerce-paystack-fee
 * Requires Plugins: woocommerce
*/

// Require loading through WordPress
if (! defined('ABSPATH')) {
    die;
}

/**
 * Declare compatible with HPOS new order table 
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

add_action( 'woocommerce_checkout_init', 'payment_method_change_trigger_update_checkout_js' );
function payment_method_change_trigger_update_checkout_js() {
    wc_enqueue_js('jQuery( function($){
        jQuery("form.checkout").on("change", "input[name=payment_method]", function(){
            jQuery(document.body).trigger("update_checkout");
        });
    });');
}

// Add a new tab to WooCommerce settings
add_filter( 'woocommerce_settings_tabs_array', 'wcpf_add_transaction_fee_tab', 50 );
function wcpf_add_transaction_fee_tab( $settings_tabs ) {
    $settings_tabs['transaction_fee'] = __( 'Paystack Transaction Fee', 'woocommerce-paystack-fee' );
    return $settings_tabs;
}

// Show settings for Transaction Fee tab
add_action( 'woocommerce_settings_transaction_fee', 'wcpf_add_transaction_fee_settings_page' );
function wcpf_add_transaction_fee_settings_page() {
    woocommerce_admin_fields( wcpf_get_transaction_fee_settings() );
}

// Define fields for the Transaction Fee tab
function wcpf_get_transaction_fee_settings() {
    $settings = array(
        'wcpf_section_title' => array(
            'name' => __( 'Paystack Transaction Fee Settings', 'woocommerce-paystack-fee' ),
            'type' => 'title',
            'id'   => 'wcpf_transaction_fee_settings_section_title'
        ),
        array(
            'name' => __( 'Transaction Fee Label', 'woocommerce-paystack-fee' ),
            'type' => 'text',
            'desc' => __( 'Enter the label to display for the transaction fee (e.g., Service Charge)', 'woocommerce-paystack-fee' ),
            'id'   => 'wcpf_transaction_fee_label',
            'default' => __( 'Transaction Fee', 'woocommerce-paystack-fee' ),
        ),

        // Option to include/exclude taxes in fee calculation
        array(
            'name' => __( 'Include Taxes in Fee Calculation', 'woocommerce-paystack-fee' ),
            'type' => 'checkbox',
            'desc' => __( 'Check this if you want to include taxes in the fee calculation.', 'woocommerce-paystack-fee' ),
            'id'   => 'wcpf_include_taxes_in_fee',
            'default' => 'no',
        ),

        // Option to include/exclude shipping in fee calculation
        array(
            'name' => __( 'Include Shipping in Fee Calculation', 'woocommerce-paystack-fee' ),
            'type' => 'checkbox',
            'desc' => __( 'Check this if you want to include shipping costs in the fee calculation.', 'woocommerce-paystack-fee' ),
            'id'   => 'wcpf_include_shipping_in_fee',
            'default' => 'no',
        ),
        'wcpf_fee_percentage' => array(
            'name' => __( 'Fee Percentage', 'woocommerce-paystack-fee' ),
            'type' => 'number',
            'desc' => __( 'Enter the percentage fee for transactions (e.g., 1.5 for 1.5%)', 'woocommerce-paystack-fee' ),
            'id'   => 'wcpf_fee_percentage',
            'css'  => 'min-width:300px;',
            'default' => '1.5',
            'custom_attributes' => array(
                'step' => '0.01',
                'min'  => '0',
            ),
        ),
        'wcpf_flat_fee' => array(
            'name' => __( 'Flat Fee', 'woocommerce-paystack-fee' ),
            'type' => 'number',
            'desc' => __( 'Enter the flat fee for transactions (e.g., 100)', 'woocommerce-paystack-fee' ),
            'id'   => 'wcpf_flat_fee',
            'css'  => 'min-width:300px;',
            'default' => '100',
            'custom_attributes' => array(
                'min' => '0',
            ),
        ),
        'wcpf_fee_cap' => array(
            'name' => __( 'Fee Cap', 'woocommerce-paystack-fee' ),
            'type' => 'number',
            'desc' => __( 'Enter the maximum fee cap for transactions (e.g., 2000)', 'woocommerce-paystack-fee' ),
            'id'   => 'wcpf_fee_cap',
            'css'  => 'min-width:300px;',
            'default' => '2000',
            'custom_attributes' => array(
                'min' => '0',
            ),
        ),
        'wcpf_section_end' => array(
            'type' => 'sectionend',
            'id'   => 'wcpf_transaction_fee_settings_section_end'
        ),
    );
    return $settings;
}

// Save settings
add_action( 'woocommerce_update_options_transaction_fee', 'wcpf_update_transaction_fee_settings' );
function wcpf_update_transaction_fee_settings() {
    woocommerce_update_options( wcpf_get_transaction_fee_settings() );
}

// Hook into WooCommerce to add Paystack fee
add_action( 'woocommerce_cart_calculate_fees', 'wcpf_add_paystack_fee', 10, 1 );

/**
 * Get_current_gateway.
 *
 * @version 1.0.7
 */
$last_known_current_gateway = '';
function wcpf_get_current_gateway() {
    global $last_known_current_gateway;
    $current_gateway = WC()->session->chosen_payment_method;

    if ( '' === $current_gateway ) {
        $current_gateway = ( ! empty( $_REQUEST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['payment_method'] ) ) : '' );
        if ( '' === $current_gateway ) {
            $current_gateway = ( isset( $last_known_current_gateway ) ? $last_known_current_gateway : get_option( 'woocommerce_default_gateway', '' ) );
        }
    }
    $current_gateway = apply_filters( 'wcpf_current_gateway', $current_gateway );
    $last_known_current_gateway = $current_gateway;
    return $current_gateway;
}

function wcpf_add_paystack_fee( $cart ) {
    if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
        return;
    }

    $current_gateway = apply_filters( 'wcpf_add_default_gateway_on_cart', wcpf_get_current_gateway() );

    if ( strpos( $current_gateway, 'paystack' ) !== false ) {
        
        $include_taxes = get_option( 'wcpf_include_taxes_in_fee', 'no' ) === 'yes';
        $include_shipping = get_option( 'wcpf_include_shipping_in_fee', 'no' ) === 'yes';
        $fee_percentage = get_option( 'wcpf_fee_percentage', 1.5 );
        $flat_fee = get_option( 'wcpf_flat_fee', 100 );
        $fee_cap = get_option( 'wcpf_fee_cap', 2000 );
        $decimal_fee = $fee_percentage / 100;
        $fee_label = sanitize_text_field( get_option( 'wcpf_transaction_fee_label', __( 'Transaction Fee', 'woocommerce-paystack-fee' ) ) );

        // Get the cart total
        $total = $cart->get_cart_contents_total();

        // Optionally include taxes and shipping
        if ( $include_taxes ) {
            $total += $cart->get_taxes_total();
        }
        if ( $include_shipping ) {
            $total += $cart->get_shipping_total();
        }

        if ( ! empty( WC()->cart->credit_used ) && is_array( WC()->cart->credit_used ) ) { // for "WooCommerce Gift Certificates" plugin.
            $total -= array_sum( WC()->cart->credit_used );
        }

        $applicable_fees = ( $decimal_fee * $total ) + $flat_fee;

        if ( $fee_cap > 0 && $applicable_fees > $fee_cap ) {
            $applicable_fees = $fee_cap;
        }

        WC()->cart->add_fee( $fee_label, $applicable_fees );
    }
}
