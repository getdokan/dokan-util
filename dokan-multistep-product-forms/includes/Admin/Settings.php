<?php

namespace WeDevs\Dokan\MultiStepProductForms\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

/**
 * Class Settings
 *
 * @since   DOKAN_PRO_SINCE
 *
 * @package WeDevs\DokanPro\VendorDiscount\Admin
 */
class Settings {

    /**
     * Class constructor
     *
     * @since DOKAN_PRO_SINCE
     *
     * @return void
     */
    public function __construct() {
        add_filter( 'dokan_settings_selling_option_vendor_capability', [ $this, 'add_discount_settings' ], 12, 1 );
    }

    /**
     * Add discount settings
     *
     * @since 0.0.1
     *
     * @param array $settings_fields
     *
     * @return array
     */
    public function add_discount_settings( $settings_fields ): array {
        // New subsection for discount settings
        $settings_fields['disable_product_popup_section'] = [
            'name'          => 'disable_product_popup_section',
            'type'          => 'sub_section',
            'label'         => __( 'Disable Product Popup', 'dokan' ),
            'description'   => '',
            'content_class' => 'sub-section-styles',
        ];

        // discount settings field
        $settings_fields['disable_product_popup'] = [
            'name'    => 'disable_product_popup',
            'label'   => __( 'Disable Product Popup', 'dokan' ),
            'desc'    => __( 'Disable add new product in popup view', 'dokan' ),
            'type'    => 'switcher',
            'default' => 'off',
            'show_if' => [
                'dokan_selling.one_step_product_create' => [ 'equal' => 'off' ],
            ],
            'tooltip' => __( 'If disabled, instead of a pop up window vendor will redirect to product page when adding new product.', 'dokan' ),
        ];

        return $settings_fields;
    }

    /**
     * Check if the product popup form is disabled
     *
     * @since 0.0.1
     *
     * @return bool
     */
    public function is_product_popup_disabled() {
        $discount_settings = dokan_get_option( 'disable_product_popup', 'dokan_selling', 'off' );

        return 'on' === $discount_settings;
    }
}
