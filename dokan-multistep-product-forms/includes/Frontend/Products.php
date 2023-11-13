<?php
namespace WeDevs\Dokan\MultiStepProductForms\Frontend;

use WeDevs\Dokan\ProductCategory\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

/**
 * Products class
 *
 * @since 0.0.1
 */
class Products {

    /**
     * Class constructor
     *
     * @since 0.0.1
     */
    public function __construct() {
        add_action( 'dokan_render_new_product_template', [ $this, 'render_new_product_template' ], 10 );
        add_action( 'dokan_after_listing_product', [ $this, 'load_add_new_product_popup' ], 10 );
        add_action( 'dokan_after_listing_product', [ $this, 'load_add_new_product_modal' ], 10 );
        add_action( 'template_redirect', [ $this, 'handle_product_add' ], 11 );
    }

    /**
     * Render New Product Template for only free version
     *
     * @since 2.4
     *
     * @param array $query_vars
     *
     * @return void
     */
    public function render_new_product_template( $query_vars ) {
        if ( isset( $query_vars['new-product'] ) && ! dokan()->is_pro_exists() ) {
            dokan_get_template_part( 'products/new-product' );
        }
    }

    /**
     * Add new product open modal html
     *
     * @since 3.7.0
     *
     * @return void
     */
    public function load_add_new_product_popup() {
        dokan_get_template_part( 'products/tmpl-add-product-popup' );
    }

    /**
     * Add new product open modal html
     *
     * @since 3.7.0
     *
     * @return void
     */
    public function load_add_new_product_modal() {
        dokan_get_template_part( 'products/add-new-product-modal' );
    }

    /**
     * Handle product add
     *
     * @return void
     */
    public function handle_product_add() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( ! dokan_is_user_seller( get_current_user_id() ) ) {
            return;
        }

        if ( ! isset( $_POST['dokan_add_new_product_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dokan_add_new_product_nonce'] ), 'dokan_add_new_product' ) ) {
            return;
        }

        $postdata = wp_unslash( $_POST ); // phpcs:ignore

        $errors             = [];
        self::$product_cat  = - 1;
        self::$post_content = __( 'Details of your product ...', 'dokan-lite' );

        if ( isset( $postdata['add_product'] ) ) {
            $post_title     = sanitize_text_field( $postdata['post_title'] );
            $post_content   = wp_kses_post( $postdata['post_content'] );
            $post_excerpt   = wp_kses_post( $postdata['post_excerpt'] );
            $featured_image = absint( sanitize_text_field( $postdata['feat_image_id'] ) );

            if ( empty( $post_title ) ) {
                $errors[] = __( 'Please enter product title', 'dokan-lite' );
            }

            if ( ! isset( $postdata['chosen_product_cat'] ) ) {
                if ( Helper::product_category_selection_is_single() ) {
                    if ( absint( $postdata['product_cat'] ) < 0 ) {
                        $errors[] = __( 'Please select a category', 'dokan-lite' );
                    }
                } else {
                    if ( ! isset( $postdata['product_cat'] ) || empty( $postdata['product_cat'] ) ) {
                        $errors[] = __( 'Please select at least one category', 'dokan-lite' );
                    }
                }
            } elseif ( empty( $postdata['chosen_product_cat'] ) ) {
                $errors[] = __( 'Please select a category', 'dokan-lite' );
            }

            self::$errors = apply_filters( 'dokan_can_add_product', $errors );

            if ( ! self::$errors ) {
                $timenow        = dokan_current_datetime()->setTimezone( new \DateTimeZone( 'UTC' ) );
                $product_status = dokan_get_default_product_status( dokan_get_current_user_id() );
                $post_data      = apply_filters(
                    'dokan_insert_product_post_data', [
                        'post_type'         => 'product',
                        'post_status'       => $product_status,
                        'post_title'        => $post_title,
                        'post_content'      => $post_content,
                        'post_excerpt'      => $post_excerpt,
                        'post_date_gmt'     => $timenow->format( 'Y-m-d H:i:s' ),
                        'post_modified_gmt' => $timenow->format( 'Y-m-d H:i:s' ),
                    ]
                );

                $product_id = wp_insert_post( $post_data );

                if ( $product_id ) {
                    // set images
                    if ( $featured_image ) {
                        set_post_thumbnail( $product_id, $featured_image );
                    }

                    if ( isset( $postdata['product_tag'] ) && ! empty( $postdata['product_tag'] ) ) {
                        $tags_ids = array_map( 'absint', (array) $postdata['product_tag'] );
                        wp_set_object_terms( $product_id, $tags_ids, 'product_tag' );
                    }

                    /* set product category */
                    if ( ! isset( $postdata['chosen_product_cat'] ) ) {
                        if ( Helper::product_category_selection_is_single() ) {
                            wp_set_object_terms( $product_id, (int) $postdata['product_cat'], 'product_cat' );
                        } else {
                            if ( isset( $postdata['product_cat'] ) && ! empty( $postdata['product_cat'] ) ) {
                                $cat_ids = array_map( 'absint', (array) $postdata['product_cat'] );
                                wp_set_object_terms( $product_id, $cat_ids, 'product_cat' );
                            }
                        }
                    } else {
                        $chosen_cat = Helper::product_category_selection_is_single() ? [ reset( $postdata['chosen_product_cat'] ) ] : $postdata['chosen_product_cat'];
                        Helper::set_object_terms_from_chosen_categories( $product_id, $chosen_cat );
                    }

                    /** Set Product type, default is simple */
                    $product_type = empty( $postdata['product_type'] ) ? 'simple' : $postdata['product_type'];
                    wp_set_object_terms( $product_id, $product_type, 'product_type' );

                    // Gallery Images
                    if ( ! empty( $postdata['product_image_gallery'] ) ) {
                        $attachment_ids = array_filter( explode( ',', wc_clean( $postdata['product_image_gallery'] ) ) );
                        update_post_meta( $product_id, '_product_image_gallery', implode( ',', $attachment_ids ) );
                    }

                    if ( isset( $postdata['_regular_price'] ) ) {
                        update_post_meta( $product_id, '_regular_price', ( $postdata['_regular_price'] === '' ) ? '' : wc_format_decimal( $postdata['_regular_price'] ) );
                    }

                    if ( isset( $postdata['_sale_price'] ) ) {
                        update_post_meta( $product_id, '_sale_price', ( $postdata['_sale_price'] === '' ? '' : wc_format_decimal( $postdata['_sale_price'] ) ) );
                        $date_from = isset( $postdata['_sale_price_dates_from'] ) ? wc_clean( $postdata['_sale_price_dates_from'] ) : '';
                        $date_to   = isset( $postdata['_sale_price_dates_to'] ) ? wc_clean( $postdata['_sale_price_dates_to'] ) : '';
                        $now       = dokan_current_datetime();

                        // Dates
                        if ( $date_from ) {
                            update_post_meta( $product_id, '_sale_price_dates_from', $now->modify( $date_from )->setTime( 0, 0, 0 )->getTimestamp() );
                        } else {
                            update_post_meta( $product_id, '_sale_price_dates_from', '' );
                        }

                        if ( $date_to ) {
                            update_post_meta( $product_id, '_sale_price_dates_to', $now->modify( $date_to )->setTime( 23, 59, 59 )->getTimestamp() );
                        } else {
                            update_post_meta( $product_id, '_sale_price_dates_to', '' );
                        }

                        if ( $date_to && ! $date_from ) {
                            update_post_meta( $product_id, '_sale_price_dates_from', $now->setTime( 0, 0, 0 )->getTimestamp() );
                        }

                        if ( '' !== $postdata['_sale_price'] && '' === $date_to && '' === $date_from ) {
                            update_post_meta( $product_id, '_price', wc_format_decimal( $postdata['_sale_price'] ) );
                        } else {
                            update_post_meta( $product_id, '_price', ( $postdata['_regular_price'] === '' ) ? '' : wc_format_decimal( $postdata['_regular_price'] ) );
                        }
                        // Update price if on sale
                        if ( '' !== $postdata['_sale_price'] && $date_from && $now->modify( $date_from )->getTimestamp() < $now->getTimestamp() ) {
                            update_post_meta( $product_id, '_price', wc_format_decimal( $postdata['_sale_price'] ) );
                        }
                    }

                    update_post_meta( $product_id, '_visibility', 'visible' );
                    update_post_meta( $product_id, '_stock_status', 'instock' );

                    do_action( 'dokan_new_product_added', $product_id, $postdata );

                    if ( current_user_can( 'dokan_edit_product' ) ) {
                        $redirect = dokan_edit_product_url( $product_id );
                    } else {
                        $redirect = dokan_get_navigation_url( 'products' );
                    }

                    if ( 'create_and_add_new' === $postdata['add_product'] ) {
                        $redirect = add_query_arg(
                            [
                                'created_product'          => $product_id,
                                '_dokan_add_product_nonce' => wp_create_nonce( 'dokan_add_product_nonce' ),
                            ],
                            dokan_get_navigation_url( 'new-product' )
                        );
                    }

                    wp_safe_redirect( apply_filters( 'dokan_add_new_product_redirect', $redirect, $product_id ) );
                    exit;
                }
            }
        }
    }
}
