<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BRS_Coupon_Handler {

    public function __construct() {
        add_action( 'woocommerce_before_calculate_totals', [ $this, 'auto_apply_coupon' ], 20 );
    }

    private function get_settings() {
        return wp_parse_args( get_option( BRS_Admin_Settings::OPTION_KEY ), [
            'coupon_code'    => '',
            'start_datetime' => '',
            'end_datetime'   => '',
            'notice_message' => 'Black Friday discount applied!',
        ] );
    }

    public function auto_apply_coupon() {

        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
        if ( empty( WC()->cart ) ) return;

        $s = $this->get_settings();

        $coupon_code = trim( $s['coupon_code'] );
        if ( $coupon_code === '' ) return;

        $start = strtotime( $s['start_datetime'] );
        $end   = strtotime( $s['end_datetime'] );
        $now   = current_time( 'timestamp' );

        if ( $start && $end && ( $now < $start || $now > $end ) ) {
            return;
        }

        if ( ! WC()->cart->has_discount( $coupon_code ) ) {

            $result = WC()->cart->apply_coupon( $coupon_code );

            if ( ! is_wp_error( $result ) && ! headers_sent() ) {
                wc_add_notice( esc_html( $s['notice_message'] ), 'success' );
            }
        }
    }
}
