<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Gateway_Woo_Pay_Alipay extends WC_Payment_Gateway {

    public function __construct() {
        $this->id = 'woo_pay_alipay';
        $this->has_fields = false;
        $this->method_title = __( 'Alipay (Native)', 'woo-pay-alipay-native' );
        $this->method_description = __( 'Accept Alipay payments (PC/H5/APP).', 'woo-pay-alipay-native' );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option( 'title', 'Alipay' );
        $this->description = $this->get_option( 'description', '' );
        $this->enabled = $this->get_option( 'enabled' );
        $this->app_id = $this->get_option( 'app_id' );
        $this->private_key = $this->get_option( 'private_key' );
        $this->alipay_public_key = $this->get_option( 'alipay_public_key' );
        $this->sandbox = $this->get_option( 'sandbox', 'yes' );

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', 'woo-pay-alipay-native' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Alipay gateway', 'woo-pay-alipay-native' ),
                'default' => 'yes',
            ),
            'title' => array(
                'title'       => __( 'Title', 'woo-pay-alipay-native' ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'woo-pay-alipay-native' ),
                'default'     => 'Alipay',
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __( 'Description', 'woo-pay-alipay-native' ),
                'type'        => 'textarea',
                'default'     => __( 'Pay with Alipay.', 'woo-pay-alipay-native' ),
            ),
            'app_id' => array(
                'title' => __( 'Alipay App ID', 'woo-pay-alipay-native' ),
                'type' => 'text',
            ),
            'private_key' => array(
                'title' => __( 'Merchant Private Key (RSA2)', 'woo-pay-alipay-native' ),
                'type' => 'textarea',
                'description' => __( 'Paste your RSA2 private key here. Keep it secret.', 'woo-pay-alipay-native' ),
            ),
            'alipay_public_key' => array(
                'title' => __( 'Alipay Public Key', 'woo-pay-alipay-native' ),
                'type' => 'textarea',
                'description' => __( 'Paste Alipay RSA2 public key here for notify signature verification.', 'woo-pay-alipay-native' ),
            ),
            'sandbox' => array(
                'title' => __( 'Sandbox mode', 'woo-pay-alipay-native' ),
                'type' => 'checkbox',
                'label' => __( 'Enable Alipay sandbox', 'woo-pay-alipay-native' ),
                'default' => 'yes',
            ),
        );
    }

    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );

        // Mark as on-hold (we're awaiting the payment)
        $order->update_status( 'on-hold', __( 'Awaiting Alipay payment', 'woo-pay-alipay-native' ) );

        // Generate form and redirect to Alipay
        $form = $this->generate_alipay_form( $order );

        // Return redirect to a custom page that outputs the form (we use a transient to store it)
        $transient_key = 'woo_pay_alipay_form_' . $order_id;
        set_transient( $transient_key, $form, 300 );

        $redirect_url = add_query_arg( array( 'woo_pay_alipay_form' => $transient_key ), home_url( '/' ) );

        return array(
            'result'   => 'success',
            'redirect' => $redirect_url,
        );
    }

    private function generate_alipay_form( $order ) {
        $params = array(
            'app_id' => $this->app_id,
            'method' => 'alipay.trade.page.pay',
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date( 'Y-m-d H:i:s' ),
            'version' => '1.0',
            'notify_url' => esc_url( rest_url( 'woo-pay/alipay/notify' ) ),
            'return_url' => esc_url( home_url( '/payment-result/alipay-return' ) ),
            'biz_content' => json_encode( array(
                'out_trade_no' => strval( $order->get_id() ),
                'product_code' => 'FAST_INSTANT_TRADE_PAY',
                'total_amount' => wc_format_decimal( $order->get_total(), 2 ),
                'subject' => $order->get_order_number(),
            ) ),
        );

        // Build the unsigned string
        $unsigned_string = $this->build_query_string( $params );
        $sign = $this->rsa_sign( $unsigned_string, $this->private_key );
        $params['sign'] = $sign;

        $gateway_url = $this->sandbox === 'yes' ? 'https://openapi.alipaydev.com/gateway.do' : 'https://openapi.alipay.com/gateway.do';

        // Build HTML form
        $form = '<form id="woo_pay_alipay_form" name="alipaysubmit" action="' . esc_attr( $gateway_url ) . '" method="POST">';
        foreach ( $params as $k => $v ) {
            $form .= '<input type="hidden" name="' . esc_attr( $k ) . '" value="' . esc_attr( $v ) . '" />';
        }
        $form .= '<input type="submit" value="' . esc_attr__( 'Pay via Alipay', 'woo-pay-alipay-native' ) . '" />';
        $form .= '</form>';
        $form .= '<script>document.getElementById("woo_pay_alipay_form").submit();</script>';

        return $form;
    }

    private function build_query_string( $params ) {
        ksort( $params );
        $pieces = array();
        foreach ( $params as $k => $v ) {
            if ( $v === '' || is_null( $v ) ) continue;
            $pieces[] = $k . '=' . $this->char_encode( $v );
        }
        return implode( '&', $pieces );
    }

    private function char_encode( $val ) {
        // Alipay expects the biz_content to be JSON without escaping of slashes
        return $val;
    }

    private function rsa_sign( $data, $private_key ) {
        $res = openssl_get_privatekey( "-----BEGIN PRIVATE KEY-----\n" . wordwrap( trim( $private_key ), 64, "\n", true ) . "\n-----END PRIVATE KEY-----" );
        if ( ! $res ) return '';
        openssl_sign( $data, $sign, $res, OPENSSL_ALGO_SHA256 );
        openssl_free_key( $res );
        return base64_encode( $sign );
    }

}
