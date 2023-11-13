<?php
/**
 * Plugin Name: Dokan Multistep Product Forms
 * Plugin URI: https://dokan.co/wordpress/
 * Description: Multistep product creating feature for Dokan.
 * Version: 0.0.1
 * Author: weDevs
 * Author URI: https://dokan.co/
 * WC requires at least: 5.0.0
 * WC tested up to: 8.2.1
 * License: GPL2
 * TextDomain: dokan-multistep-product-form
 */

namespace WeDevs\Dokan\MultiStepProductForms;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class
 *
 * @since 0.0.1
 *
 * @property Admin\Settings    $admin_settings
 * @property Frontend\Assets   $assets
 * @property Frontend\Products $products
 */
class MultiStepProductForms {

    /**
     * Plugin version
     *
     * @since 0.0.1
     *
     * @var string
     */
    private $version = '0.0.1';

    /**
     * Contains chainable class instances
     *
     * @var array
     */
    protected $container = [];

    /**
     * Magic getter to get chainable container instance.
     *
     * @since 0.0.1
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }
    }

    /**
     * Class instance
     *
     * @since 0.0.1
     *
     * @return MultiStepProductForms
     */
    public static function init() {
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
     * @return void
     */
    private function __construct() {
        require_once __DIR__ . '/vendor/autoload.php';

        $this->define_constants();

        // only load plugin files in case of dokan is active
        add_action(
            'dokan_loaded',
            function () {
                // check if dokan version is greater than 3.9.1
                if ( ! function_exists( 'dokan' ) || version_compare( dokan()->version, '3.9.1', '<' ) ) {
                    return;
                }

                $this->set_controllers();
            }
        );
    }

    /**
     * Set controllers
     *
     * @since 0.0.1
     *
     * @return void
     */
    private function set_controllers() {
        $this->container['admin_settings'] = new Admin\Settings();
        $this->container['assets']         = new Frontend\Assets();
        $this->container['products']       = new Frontend\Products();
    }

    /**
     * Define constants
     *
     * @since 0.0.1
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_MULTISTEP_PRODUCT_FORMS_VERSION', $this->version );
        define( 'DOKAN_MULTISTEP_PRODUCT_FORMS_FILE', __FILE__ );
        define( 'DOKAN_MULTISTEP_PRODUCT_FORMS_DIR', dirname( DOKAN_MULTISTEP_PRODUCT_FORMS_FILE ) );
        define( 'DOKAN_MULTISTEP_PRODUCT_FORMS_TEMPLATE_DIR', DOKAN_MULTISTEP_PRODUCT_FORMS_DIR . '/templates' );
        define( 'DOKAN_MULTISTEP_PRODUCT_FORMS_INC', DOKAN_MULTISTEP_PRODUCT_FORMS_DIR . '/includes' );
        define( 'DOKAN_MULTISTEP_PRODUCT_FORMS_ASSETS', plugins_url( 'assets', DOKAN_MULTISTEP_PRODUCT_FORMS_FILE ) );
    }

    /**
     * Get plugin version
     *
     * @since 0.0.1
     *
     * @return string
     */
    public function get_version(): string {
        return $this->version;
    }

    /**
     * Get plugin file
     *
     * @since 0.0.1
     *
     * @return string
     */
    public function get_plugin_file(): string {
        return DOKAN_MULTISTEP_PRODUCT_FORMS_FILE;
    }

    /**
     * Get plugin dir
     *
     * @since 0.0.1
     *
     * @return string
     */
    public function get_plugin_dir(): string {
        return DOKAN_MULTISTEP_PRODUCT_FORMS_DIR;
    }

    /**
     * Get plugin template dir
     *
     * @since 0.0.1
     *
     * @return string
     */
    public function get_template_dir(): string {
        return DOKAN_MULTISTEP_PRODUCT_FORMS_TEMPLATE_DIR;
    }

    /**
     * Get plugin include dir
     *
     * @since 0.0.1
     *
     * @return string
     */
    public function get_inc_dir(): string {
        return DOKAN_MULTISTEP_PRODUCT_FORMS_INC;
    }

    /**
     * Get plugin assets dir
     *
     * @since 0.0.1
     *
     * @return string
     */
    public function get_assets_url(): string {
        return DOKAN_MULTISTEP_PRODUCT_FORMS_ASSETS;
    }
}

/**
 * Load pro plugin for dokan
 *
 * @since 0.0.1
 *
 * @return MultiStepProductForms
 * */
function multistep_product_form() { // phpcs:ignore
    return MultiStepProductForms::init();
}

multistep_product_form();
