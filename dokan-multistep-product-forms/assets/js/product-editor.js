;(function($){
    let product_gallery_frame;
    let product_featured_frame;
    let $image_gallery_ids = $('#product_image_gallery');
    let $product_images = $('#product_images_container ul.product_images');

    var Dokan_Editor = {

        modal: false,

        /**
         * Constructor function
         */
        init: function() {

            product_type = 'simple';

            $('.product-edit-container').on('click', 'a.sale-schedule', this.showDiscountSchedule );

            // gallery
            $('body, #dokan-product-images').on('click', 'a.add-product-images', this.gallery.addImages );
            $('body, #dokan-product-images').on( 'click', 'a.action-delete', this.gallery.deleteImage );
            this.gallery.sortable();


            // File inputs
            $('body').on('click', 'a.insert-file-row', function(){
                $(this).closest('table').find('tbody').append( $(this).data( 'row' ) );
                return false;
            });

            // For new desing in product page
            $( '.dokan-product-listing' ).on( 'click', 'a.dokan-add-new-product', this.addProductPopup );

            this.loadSelect2();
            this.bindProductTagDropdown();
            $( 'body' ).on( 'click', '.product-container-footer input[type="submit"]', this.createNewProduct );

            this.attribute.disbalePredefinedAttribute();

            this.setCorrectProductId();

            $( 'body' ).trigger( 'dokan-product-editor-loaded', this );
        },

        saleSchedule: function() {
            var $wrap = $(this).closest( '.dokan-product-field-content', 'div, table' );
            $(this).hide();

            $wrap.find('.cancel_sale_schedule').show();
            $wrap.find('.sale_price_dates_fields').show();

            return false;
        },

        cancelSchedule: function() {
            var $wrap = $(this).closest( '.dokan-product-field-content', 'div, table' );

            $(this).hide();
            $wrap.find('.sale_schedule').show();
            $wrap.find('.sale_price_dates_fields').hide();
            $wrap.find('.sale_price_dates_fields').find('input').val('');

            return false;
        },

        loadSelect2: function() {
            $('.dokan-select2').select2(
                {
                    "language": {
                        "noResults": function () {
                            return dokan.i18n_no_result_found;
                        }
                    }
                }
            );
        },

        bindProductTagDropdown: function () {
            $(".product_tag_search").select2({
                allowClear: false,
                tags: ( dokan.product_vendors_can_create_tags && 'on' === dokan.product_vendors_can_create_tags ),
                createTag: function ( $params ) {
                    var $term = $.trim( $params.term );
                    if ( $term === '' ) {
                        return null;
                    }

                    return {
                        id: $term,
                        text: $term,
                        newTag: true // add additional parameters
                    }
                },
                insertTag: function ( data, tag ) {
                    var $found = false;

                    $.each( data, function ( index, value ) {
                        if ( $.trim( tag.text ).toUpperCase() == $.trim( value.text ).toUpperCase() ) {
                            $found = true;
                        }
                    });

                    if ( ! $found ) data.unshift( tag );
                },
                minimumInputLength: 2,
                maximumSelectionLength: dokan.maximum_tags_select_length !== undefined ? dokan.maximum_tags_select_length : -1,
                ajax: {
                    url: dokan.ajaxurl,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            action: 'dokan_json_search_products_tags',
                            security: dokan.search_products_tags_nonce,
                            page: params.page || 1
                        };
                    },
                    processResults: function( data ) {
                        var options = [];
                        if ( data ) {
                            $.each( data, function( index, text ) {
                                options.push( { id: text[0], text: text[1]  } );
                            });
                        }
                        return {
                            results: options,
                            pagination: {
                                more: options.length == 0 ? false : true
                            }
                        };
                    },
                    cache: true
                },
                language: {
                    errorLoading: function() {
                        return dokan.i18n_searching;
                    },
                    inputTooLong: function( args ) {
                        var overChars = args.input.length - args.maximum;

                        if ( 1 === overChars ) {
                            return dokan.i18n_input_too_long_1;
                        }

                        return dokan.i18n_input_too_long_n.replace( '%qty%', overChars );
                    },
                    inputTooShort: function( args ) {
                        var remainingChars = args.minimum - args.input.length;

                        if ( 1 === remainingChars ) {
                            return dokan.i18n_input_too_short_1;
                        }

                        return dokan.i18n_input_too_short_n.replace( '%qty%', remainingChars );
                    },
                    loadingMore: function() {
                        return dokan.i18n_load_more;
                    },
                    maximumSelected: function( args ) {
                        if ( args.maximum === 1 ) {
                            return dokan.i18n_selection_too_long_1;
                        }

                        return dokan.i18n_selection_too_long_n.replace( '%qty%', args.maximum );
                    },
                    noResults: function() {
                        return dokan.i18n_no_matches;
                    },
                    searching: function() {
                        return dokan.i18n_searching;
                    }
                },
            });
        },

        addProductPopup: function (e) {
            e.preventDefault();
            Dokan_Editor.openProductPopup();
        },

        openProductPopup: function() {
            const productTemplate = wp.template( 'dokan-add-new-product' ),
                modalElem = $( '#dokan-add-product-popup' );
            Dokan_Editor.modal = modalElem.iziModal( {
                headerColor : dokan.modal_header_color,
                overlayColor: 'rgba(0, 0, 0, 0.8)',
                width       : 690,
                top         : 32,
                onOpening   : () => {
                    Dokan_Editor.reRenderPopupElements();
                },
                onClosed: () => {
                    product_gallery_frame  = undefined;
                    product_featured_frame = undefined;
                    $( '#dokan-add-new-product-popup input[name="_sale_price_dates_from"], #dokan-add-new-product-popup input[name="_sale_price_dates_to"]' ).datepicker( 'destroy' );
                },
            } );
            Dokan_Editor.modal.iziModal( 'setContent', productTemplate().trim() );
            Dokan_Editor.modal.iziModal( 'open' );
        },

        reRenderPopupElements: function() {
            Dokan_Editor.loadSelect2();
            Dokan_Editor.bindProductTagDropdown();

            $( '#dokan-add-new-product-popup .sale_price_dates_fields input' ).daterangepicker({
                singleDatePicker: true,
                showDropdowns: false,
                autoApply: true,
                parentEl: '#dokan-add-new-product-popup',
                opens: 'left',
                autoUpdateInput : false,
            } ).on( 'apply.daterangepicker', function( ev, picker ) {
                $( this ).val( picker.startDate.format( 'YYYY-MM-DD' ) );
            } );

            $( '.tips' ).tooltip();

            Dokan_Editor.gallery.sortable();
            $( 'body' ).trigger( 'dokan-product-editor-popup-opened', Dokan_Editor );
        },

        createNewProduct: function (e) {
            e.preventDefault();

            var self = $(this),
                form = self.closest('form#dokan-add-new-product-form'),
                btn_id = self.attr('data-btn_id');

            form.find( 'span.dokan-show-add-product-success' ).html('');
            form.find( 'span.dokan-show-add-product-error' ).html('');
            form.find( 'span.dokan-add-new-product-spinner' ).css( 'display', 'inline-block' );

            self.attr( 'disabled', 'disabled' );

            if ( form.find( 'input[name="post_title"]' ).val() == '' ) {
                $( 'span.dokan-show-add-product-error' ).html( dokan.product_title_required );
                self.removeAttr( 'disabled' );
                form.find( 'span.dokan-add-new-product-spinner' ).css( 'display', 'none' );
                return;
            }

            if ( form.find( 'select[name="product_cat"]' ).val() == '-1' ) {
                $( 'span.dokan-show-add-product-error' ).html( dokan.product_category_required );
                self.removeAttr( 'disabled' );
                form.find( 'span.dokan-add-new-product-spinner' ).css( 'display', 'none' );
                return;
            }

            var data = {
                action:   'dokan_create_new_product',
                postdata: form.serialize(),
                _wpnonce : dokan.nonce
            };

            Dokan_Editor.modal.iziModal('startLoading');
            $.post( dokan.ajaxurl, data, function( resp ) {
                if ( resp.success ) {
                    self.removeAttr( 'disabled' );
                    if ( btn_id === 'create_new' ) {
                        $( '#dokan-add-product-popup' ).iziModal('close');
                        window.location.href = resp.data;
                    } else {
                        product_featured_frame = undefined;
                        $('.dokan-dashboard-product-listing-wrapper').load( window.location.href + ' table.product-listing-table' );
                        Dokan_Editor.modal.iziModal('resetContent');
                        Dokan_Editor.openProductPopup();
                        Dokan_Editor.reRenderPopupElements();
                        $( 'span.dokan-show-add-product-success' ).html( dokan.product_created_response );

                        setTimeout(function() {
                            $( 'span.dokan-show-add-product-success' ).html( '' );
                        }, 3000);
                    }
                } else {
                    self.removeAttr( 'disabled' );
                    $( 'span.dokan-show-add-product-error' ).html( resp.data );
                }
                form.find( 'span.dokan-add-new-product-spinner' ).css( 'display', 'none' );
            })
                .always( function () {
                    Dokan_Editor.modal.iziModal('stopLoading');
                });
        },

        showDiscountSchedule: function(e) {
            e.preventDefault();
            $('.sale-schedule-container').slideToggle('fast');
        },

        gallery: {

            addImages: function(e) {
                e.preventDefault();

                var self = $(this),
                    p_images = self.closest('.dokan-product-gallery').find('#product_images_container ul.product_images'),
                    images_gid = self.closest('.dokan-product-gallery').find('#product_image_gallery');

                if ( product_gallery_frame ) {
                    product_gallery_frame.open();
                    return;
                } else {
                    // Create the media frame.
                    product_gallery_frame = wp.media({
                        // Set the title of the modal.
                        title: dokan.i18n_choose_gallery,
                        library: {
                            type: 'image',
                        },
                        button: {
                            text: dokan.i18n_choose_gallery_btn_text,
                        },
                        multiple: true
                    });

                    product_gallery_frame.on( 'select', function() {

                        var selection = product_gallery_frame.state().get('selection');

                        selection.map( function( attachment ) {
                            attachment     = attachment.toJSON();
                            attachment_ids = [];

                            // Check if attachment doesn't exist or attachment type is not image
                            if ( ! attachment.id || 'image' !== attachment.type ) {
                                return;
                            }

                            $('<li class="image" data-attachment_id="' + attachment.id + '">\
                                    <img src="' + attachment.url + '" />\
                                    <a href="#" class="action-delete">&times;</a>\
                                </li>').insertBefore( p_images.find('li.add-image') );

                            $('#product_images_container ul li.image').css('cursor','default').each(function() {
                                var attachment_id = jQuery(this).attr( 'data-attachment_id' );
                                attachment_ids.push( attachment_id );
                            });

                            images_gid.val( attachment_ids.join(',') );
                        } );
                    });

                    product_gallery_frame.open();
                }

            },

            deleteImage: function(e) {
                e.preventDefault();

                var self = $(this),
                    p_images = self.closest('.dokan-product-gallery').find('#product_images_container ul.product_images'),
                    images_gid = self.closest('.dokan-product-gallery').find('#product_image_gallery');

                self.closest('li.image').remove();

                var attachment_ids = [];

                $('#product_images_container ul li.image').css('cursor','default').each(function() {
                    var attachment_id = $(this).attr( 'data-attachment_id' );
                    attachment_ids.push( attachment_id );
                });

                images_gid.val( attachment_ids.join(',') );

                return false;
            },

            sortable: function() {
                // Image ordering
                $('body').find('#product_images_container ul.product_images').sortable({
                    items: 'li.image',
                    cursor: 'move',
                    scrollSensitivity:40,
                    forcePlaceholderSize: true,
                    forceHelperSize: false,
                    helper: 'clone',
                    opacity: 0.65,
                    placeholder: 'dokan-sortable-placeholder',
                    start:function(event,ui){
                        ui.item.css('background-color','#f6f6f6');
                    },
                    stop:function(event,ui){
                        ui.item.removeAttr('style');
                    },
                    update: function(event, ui) {
                        var attachment_ids = [];

                        $('body').find('#product_images_container ul li.image').css('cursor','default').each(function() {
                            var attachment_id = jQuery(this).attr( 'data-attachment_id' );
                            attachment_ids.push( attachment_id );
                        });

                        $('body').find('#product_image_gallery').val( attachment_ids.join(',') );
                    }
                });
            }
        },

        featuredImage: {

            addImage: function(e) {
                e.preventDefault();

                var self = $(this);

                if ( product_featured_frame ) {
                    product_featured_frame.open();
                    return;
                } else {
                    product_featured_frame = wp.media({
                        // Set the title of the modal.
                        title: dokan.i18n_choose_featured_img,
                        library: {
                            type: 'image',
                        },
                        button: {
                            text: dokan.i18n_choose_featured_img_btn_text,
                        }
                    });

                    product_featured_frame.on('select', function() {
                        var selection = product_featured_frame.state().get('selection');

                        selection.map( function( attachment ) {
                            attachment = attachment.toJSON();

                            // Check if the attachment type is image.
                            if ( 'image' !== attachment.type ) {
                                return;
                            }

                            // set the image hidden id
                            self.siblings('input.dokan-feat-image-id').val(attachment.id);

                            // set the image
                            var instruction = self.closest('.instruction-inside');
                            var wrap = instruction.siblings('.image-wrap');

                            // wrap.find('img').attr('src', attachment.sizes.thumbnail.url);
                            wrap.find('img').attr('src', attachment.url);
                            wrap.find('img').removeAttr( 'srcset' );

                            instruction.addClass('dokan-hide');
                            wrap.removeClass('dokan-hide');
                        });
                    });

                    product_featured_frame.open();
                }
            },

            removeImage: function(e) {
                e.preventDefault();

                var self = $(this);
                var wrap = self.closest('.image-wrap');
                var instruction = wrap.siblings('.instruction-inside');

                instruction.find('input.dokan-feat-image-id').val('0');
                wrap.addClass('dokan-hide');
                instruction.removeClass('dokan-hide');
            }
        },
    };

    // On DOM ready
    $(function() {
        Dokan_Editor.init();

        // Sale price schedule.
        $( '.sale_price_dates_fields' ).each( function() {
            var $these_sale_dates = $( this );
            var sale_schedule_set = false;
            var $wrap = $these_sale_dates.closest( 'div, table' );

            $these_sale_dates.find( 'input' ).each( function() {
                if ( '' !== $( this ).val() ) {
                    sale_schedule_set = true;
                }
            });

            if ( sale_schedule_set ) {
                $wrap.find( '.sale_schedule' ).hide();
                $wrap.find( '.sale_price_dates_fields' ).show();
            } else {
                $wrap.find( '.sale_schedule' ).show();
                $wrap.find( '.sale_price_dates_fields' ).hide();
            }
        });
    });
})(jQuery);
