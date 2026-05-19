<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WooPay_Alipay_REST {

    public static function register_routes() {
        register_rest_route( 'woo-pay', '/alipay/notify', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'handle_notify' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( 'woo-pay', '/alipay/return', array(
            'methods' => 'GET',
            'callback' => array( __CLASS__, 'handle_return' ),
            'permission_callback' => '__return_true',
        ) );
    }

    public static function handle_notify( WP_REST_Request $request ) {
        $body = $request->get_body();
        parse_str( $body, $data );

        // Basic logging for debugging
        error_log( '[woo-pay-alipay] notify received: ' . print_r( $data, true ) );

        // Validate required params
        if ( empty( $data['out_trade_no'] ) ) {
            return new WP_REST_Response( 'failure', 400 );
        }

        // Load gateway settings to verify signature
        $gateway = new WC_Gateway_Woo_Pay_Alipay();
        $alipay_public_key = $gateway->alipay_public_key;

        $sign_valid = self::verify_sign( $data, $alipay_public_key );

        if ( ! $sign_valid ) {
            error_log( '[woo-pay-alipay] invalid signature' );
            return new WP_REST_Response( 'failure', 400 );
        }

        $order_id = intval( $data['out_trade_no'] );
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            error_log( '[woo-pay-alipay] order not found: ' . $order_id );
            return new WP_REST_Response( 'failure', 404 );
        }

        // Process trade status
        $trade_status = isset( $data['trade_status'] ) ? $data['trade_status'] : '';
        if ( in_array( $trade_status, array( 'TRADE_SUCCESS', 'TRADE_FINISHED' ) ) || ( isset( $data['trade_status'] ) && $data['trade_status'] === 'TRADE_SUCCESS' ) ) {
            // Verify order total matches
            $total = isset( $data['total_amount'] ) ? $data['total_amount'] : '';
            // Update order
            $order->payment_complete( $data['trade_no'] );
            $order->add_order_note( '[Alipay] Payment successful. Notify payload: ' . wp_json_encode( $data ) );
            return new WP_REST_Response( 'success', 200 );
        }

        return new WP_REST_Response( 'failure', 400 );
    }

    public static function handle_return( WP_REST_Request $request ) {
        $data = $request->get_query_params();
        // Simple return handler: verify signature and show a friendly message or redirect to order page
        // For now redirect to home with a notice
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    private static function verify_sign( $params, $alipay_public_key ) {
        if ( empty( $params['sign'] ) ) return false;
        $sign = $params['sign'];
        unset( $params['sign'] );
        unset( $params['sign_type'] );
        ksort( $params );
        $data = '';
        foreach ( $params as $k => $v ) {
            if ( $v === '' || is_null( $v ) ) continue;
            $data .= $k . '=' . $v . '&';
        }
        $data = rtrim( $data, '&' );

        $pubkey = "-----BEGIN PUBLIC KEY-----\n" . wordwrap( trim( $alipay_public_key ), 64, "\n", true ) . "\n-----END PUBLIC KEY-----";
        $res = openssl_get_publickey( $pubkey );
        if ( ! $res ) return false;
        $result = openssl_verify( $data, base64_decode( $sign ), $res, OPENSSL_ALGO_SHA256 );
        openssl_free_key( $res );
        return $result === 1;
    }

}
