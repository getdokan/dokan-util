<?php
/**
 * Plugin Name: Dokan PayPal Adaptive Payment
 * Plugin URI: https://dokan.co/wordpress/
 * Description: Allows to send split payments to vendor via PayPal Adaptive Payment gateway.
 * Version: 0.0.1
 * Author: weDevs
 * Author URI: https://dokan.co/
 * WC requires at least: 5.0.0
 * WC tested up to: 8.2.1
 * License: GPL2
 * TextDomain: dokan-paypal-adaptive-payments
 */

namespace WeDevs\Dokan\PayPalAdaptivePayments;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class
 *
 * @since 0.0.1
 */
final class DokanAdaptivePayments {

    /**
     * Class instance
     *
     * @since 0.0.1
     *
     * @return DokanAdaptivePayments
     */
    public static function init(): DokanAdaptivePayments {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Class constructor
     *
     * @since 0.0.1
     *
     * @var string
     */
    private function __construct() {
        $this->define_constants();
        $this->includes();
        add_action(
            'woocommerce_loaded',
            function () {
                $this->hooks();
            }
        );
    }

    /**
     * Define constants
     *
     * @since 0.0.1
     *
     * @return void
     */
    private function define_constants() {
        if ( ! defined( 'DOKAN_PAYPAL_ADAPTIVE_PLUGIN_PATH' ) ) {
            define( 'DOKAN_PAYPAL_ADAPTIVE_PLUGIN_PATH', plugins_url( '', __FILE__ ) );
        }
    }

    /**
     * Include files
     *
     * @since 0.0.1
     *
     * @return void
     */
    private function includes() {
        require_once __DIR__ . 'vendor/autoload.php';
        require_once __DIR__ . 'module.php';

        new Module();
    }

    /**
     * @return void
     */
    public function hooks() {
        add_action( 'init', [ $this, 'localization_setup' ] );
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'dokan-lite', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }
}
