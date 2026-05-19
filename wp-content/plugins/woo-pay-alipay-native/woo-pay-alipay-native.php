<?php
/**
 * Plugin Name: Woo Pay - Alipay Native Gateway
 * Description: Alipay (native) payment gateway and integration for WooCommerce. Support sandbox / production. Requires WooCommerce.
 * Version: 0.1.0
 * Author: ulafox (via GitHub Copilot Chat Assistant)
 * Text Domain: woo-pay-alipay-native
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Load composer-style autoload if present (not required)
// require_once __DIR__ . '/vendor/autoload.php';

// Include gateway class
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require_once __DIR__ . '/includes/class-wc-gateway-woo-pay-alipay.php';
    require_once __DIR__ . '/includes/class-woo-pay-alipay-rest.php';

    add_filter( 'woocommerce_payment_gateways', 'woo_pay_alipay_add_gateway_class' );
    function woo_pay_alipay_add_gateway_class( $gateways ) {
        $gateways[] = 'WC_Gateway_Woo_Pay_Alipay';
        return $gateways;
    }

    // Initialize REST endpoints for notify/return
    add_action( 'rest_api_init', function () {
        WooPay_Alipay_REST::register_routes();
    } );
}
