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

    // Hide duplicate notices
    private function brs_notice_exists( $message ) {
        $notices = wc_get_notices();

        if ( empty( $notices ) ) {
            return false;
        }

        foreach ( $notices as $notice_group ) {
            foreach ( $notice_group as $notice ) {
                if ( isset( $notice['notice'] ) && $notice['notice'] === $message ) {
                    return true;
                }
            }
        }
        return false;
    }

    // Tracks whether a specific notice has already been shown in the current WC session.
    // Returns true if the notice was already displayed, otherwise sets the flag and returns false.
    private function brs_notice_session_flag( $key ) {
        $flag = WC()->session->get( $key );
        if ( $flag ) {
            return true;
        }
        WC()->session->set( $key, true );
        return false;
    }

    public function auto_apply_coupon() {      

        // Prevent re-application in same request after failed validation
        if ( ! empty( $GLOBALS['brs_auto_apply_coupon_skip'] ) ) {
            return;
        }

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

            // Pre-check: does this cart contain ONLY excluded items?
            $coupon = new WC_Coupon( $coupon_code );
            $valid_for_any_item = false;

            foreach ( WC()->cart->get_cart() as $cart_item ) {
                $product_id = $cart_item['product_id'];
                $product = wc_get_product( $product_id );

                // Skip excluded IDs
                if ( in_array( $product_id, $coupon->get_excluded_product_ids(), true ) ) {
                    continue;
                }

                // Skip excluded categories
                $terms = wp_get_post_terms( $product_id, 'product_cat', [ 'fields' => 'ids' ] );
                if ( array_intersect( $terms, $coupon->get_excluded_product_categories() ) ) {
                    continue;
                }

                // Skip sale items if coupon excludes sale items
                if ( $coupon->get_exclude_sale_items() && $product->is_on_sale() ) {
                    continue;
                }

                // If we reached here → at least one valid product exists
                $valid_for_any_item = true;
                break;
            }

            if ( ! $valid_for_any_item ) {
                // All items are excluded. Do NOT apply coupon.
                $no_items_msg = sprintf(
                    'A %s discount is available, but none of the items in your cart qualify for this promotion.',
                    esc_html( ucfirst( $coupon_code ) )
                );

                if ( ! $this->brs_notice_session_flag( 'brs_no_items_notice' ) ) {
                    wc_add_notice( $no_items_msg, 'notice' );
                }
                return;
            }
            
            // Reset stored notice flags when coupon becomes valid again
            WC()->session->__unset( 'brs_no_items_notice' );
            WC()->session->__unset( 'brs_mixed_notice' );            
            $result = WC()->cart->apply_coupon( $coupon_code );

            if ( ! is_wp_error( $result ) && ! headers_sent() ) {
                wc_add_notice( esc_html( $s['notice_message'] ), 'success' );
            }
        }

        // Validation of the auto-applied coupon
        if ( WC()->cart->has_discount( $coupon_code ) ) {

            $coupon = new WC_Coupon( $coupon_code );
            $valid  = true;
            $error  = '';

            try {
                $coupon->is_valid(); // WooCommerce will throw an exception if invalid
            } catch ( WC_Coupon_Exception $e ) {
                $valid = false;
                $error = $e->getMessage();
            }

            if ( ! $valid ) {

                // Remove invalid coupon so checkout is not blocked
                WC()->cart->remove_coupon( $coupon_code );

                // Show friendly notice explaining why it couldn't be applied
                wc_add_notice(
                    sprintf(
                        'A %s discount is available, but it could not be applied because: %s',
                        esc_html( ucfirst( $coupon_code ) ),
                        esc_html( $error )
                    ),
                    'notice'
                );

                // Prevent auto-apply loop
                $GLOBALS['brs_auto_apply_coupon_skip'] = true;

                return;
            }
        }

        // Mixed cart (partial exclusion) notice
        if ( WC()->cart->has_discount( $coupon_code ) ) {

            $coupon = new WC_Coupon( $coupon_code );

            $excluded_ids        = $coupon->get_excluded_product_ids();
            $excluded_cats       = $coupon->get_excluded_product_categories();
            $exclude_sale_items  = $coupon->get_exclude_sale_items();

            $found_excluded = false;

            foreach ( WC()->cart->get_cart() as $cart_item ) {
                $product_id = $cart_item['product_id'];
                $product    = wc_get_product( $product_id );

                // 1. Excluded by product ID
                if ( in_array( $product_id, $excluded_ids, true ) ) {
                    $found_excluded = true;
                    break;
                }

                // 2. Excluded by category
                $terms = wp_get_post_terms( $product_id, 'product_cat', [ 'fields' => 'ids' ] );
                if ( array_intersect( $terms, $excluded_cats ) ) {
                    $found_excluded = true;
                    break;
                }

                // 3. Excluded if on sale
                if ( $exclude_sale_items && $product->is_on_sale() ) {
                    $found_excluded = true;
                    break;
                }
            }

            if ( $found_excluded ) {
                wc_add_notice(
                    sprintf(
                        'Some items in your cart do not qualify for the %s discount. The coupon was applied only to eligible products.',
                        esc_html( ucfirst( $coupon_code ) )
                    ),
                    'notice'
                );
            }
        }


    }
}
