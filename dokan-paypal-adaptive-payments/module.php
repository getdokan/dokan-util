<?php

namespace WeDevs\Dokan\PayPalAdaptivePayments;

/**
 * Dokan_Paypal_AP class
 *
 * @class Dokan_Paypal_AP The class that holds the entire Dokan_Paypal_AP plugin
 */
class Module {

    public function __construct() {
        add_action( 'init', [ $this, 'init' ] );

        add_filter( 'woocommerce_payment_gateways', [ $this, 'register_gateway' ] );
        add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'validate_cart' ], 10, 3 );

        add_filter( 'dokan_get_dashboard_nav', [ $this, 'unset_withdraw_page' ] );
        add_filter( 'woocommerce_available_payment_gateways', [ $this, 'checkout_filter_gateway' ], 1 );

    }

    public function init() {
        if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
            return;
        }

        require_once dirname( __FILE__ ) . '/classes/class-dokan-paypal-ap-gateway.php';
    }

    public function register_gateway( $gateways ) {
        $gateways[] = 'WC_Dokan_Paypal_Ap_Gateway';

        return $gateways;
    }

    public function checkout_filter_gateway( $gateways ) {

        $settings = get_option( 'woocommerce_dokan_paypal_adaptive_settings' );

        // check if Dokan Paypal Adaptive Payments is Enabled or not
        if ( ! isset( $settings['enabled'] ) || $settings['enabled'] != 'yes' ) {
            return $gateways;
        }

        if ( empty( WC()->cart->cart_contents ) ) {
            return $gateways;
        }

        foreach ( WC()->cart->cart_contents as $key => $values ) {

            if ( $values['data']->get_type() == 'product_pack' ) {
                unset( $gateways['dokan_paypal_adaptive'] );
                break;
            } else {
                unset( $gateways['paypal'] );
                break;
            }
        }

        return $gateways;
    }

    /**
     * Don't permit to add products more than payee limit of PayPal
     *
     * @param boolean $valid
     * @param int     $product_id
     * @param type    $quantity
     *
     * @return boolean
     */
    public function validate_cart( $valid, $product_id, $quantity ) {
        $products = WC()->cart->get_cart();

        // emulate add-to-cart by pushing the new content to the array
        $products[ $product_id ] = [ 'product_id' => $product_id ];

        if ( $products ) {
            $settings = get_option( 'woocommerce_dokan_paypal_adaptive_settings' );

            if ( ! isset( $settings['enabled'] ) || $settings['enabled'] != 'yes' ) {
                return $valid;
            }

            $payees      = [];
            $single_mode = ( isset( $settings['single_mode'] ) && $settings['single_mode'] == 'yes' ) ? true : false;

            foreach ( $products as $key => $data ) {
                $product_id = $data['product_id'];
                $seller_id  = get_post_field( 'post_author', $product_id );

                if ( ! array_key_exists( $seller_id, $payees ) ) {
                    $payees[ $seller_id ] = $seller_id;
                }
            }

            // single seller mode
            if ( $single_mode && count( $payees ) > 1 ) {
                $error_message = __( 'You can not add more than one vendors product in the cart', 'dokan-paypal-adaptive-payments' );
                wc_add_notice( $error_message, 'error' );

                return false;
            }

            // PayPal doesn't allow more than 6 payees in adaptive
            // so 5 + site_owner = 6
            if ( count( $payees ) > 5 ) {
                $error_message = isset( $settings['max_error'] ) ? $settings['max_error'] : '';

                wc_add_notice( $error_message, 'error' );

                return false;
            }
        }

        return $valid;
    }

    /**
     * Unset Seller dashboard withdraw page
     *
     * @param array $urls
     *
     * @return array
     */
    public function unset_withdraw_page( $urls ) {
        $withdraw_settings    = get_option( 'dokan_withdraw' );
        $hide_withdraw_option = isset( $withdraw_settings['hide_withdraw_option'] ) ? $withdraw_settings['hide_withdraw_option'] : 'off';

        if ( $hide_withdraw_option == 'on' ) {
            $enable = get_option( 'woocommerce_dokan_paypal_adaptive_settings' );
            // bailout if the gateway is not enabled
            if ( isset( $enable['enabled'] ) && $enable['enabled'] !== 'yes' ) {
                return $urls;
            }

            if ( array_key_exists( 'withdraw', $urls ) ) {
                unset( $urls['withdraw'] );
            }

            return $urls;
        }

        return $urls;
    }
}
