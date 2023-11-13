<?php

namespace WeDevs\Dokan\MultiStepProductForms\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class
 *
 * @since 0.0.1
 */
class Assets {

    /**
     * Plugin Constructor
     *
     * @since 0.0.1
     */
    public function __construct() {
        add_action( 'ini', [ $this, 'register_scripts' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Register scripts
     *
     * @since 0.0.1
     *
     * @return void
     */
    public function register_scripts() {
        wp_register_style( 'dokan-multistep-product-forms', multistep_product_form()->get_assets_url() . '/css/dokan-multistep-product-forms.css', [], DOKAN_MULTISTEP_PRODUCT_FORMS_VERSION );
        wp_register_script( 'dokan-multistep-product-forms', multistep_product_form()->get_assets_url() . '/js/dokan-multistep-product-forms.js', [ 'jquery' ], DOKAN_MULTISTEP_PRODUCT_FORMS_VERSION, true );
    }

    /**
     * Enqueue scripts
     *
     * @since 0.0.1
     *
     * @return void
     */
    public function enqueue_scripts() {
        wp_enqueue_style( 'dokan-multistep-product-forms' );
        wp_enqueue_script( 'dokan-multistep-product-forms' );
    }


}
