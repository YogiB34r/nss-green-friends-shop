jQuery(document).ready(function ($) {
//===========
// GF WIDGETS
//===========//
    var custom_uploader;

    function clickHandler(event, input, submitButton, target) {
        event.preventDefault();
        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Upload image',
            button: {
                text: 'Select'
            },
            multiple: true
        });
        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function () {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            input.val(attachment.url);
            submitButton.click();
        });

        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }

        //Open the uploader dialog
        custom_uploader.open();
        return;
    }

//Gf- image slider
    $('#gf-homepage-row-1').on('click', '.gf-upload-image-1', function (e) {
        clickHandler(e, $('.image_1_value'), $('#widget-41_gf_image_slider_widget-5 input[name="savewidget"]'));
    });
    $('#gf-homepage-row-1').on('click', '.gf-upload-image-2', function (e) {
        clickHandler(e, $('.image_2_value'), $('#widget-41_gf_image_slider_widget-5 input[name="savewidget"]'));
    });
    $('#gf-homepage-row-1').on('click', '.gf-upload-image-3', function (e) {
        clickHandler(e, $('.image_3_value'), $('#widget-41_gf_image_slider_widget-5 input[name="savewidget"]'));
    });
    //remove image
    $('#gf-homepage-row-1').on('click', '#gf-remove-image-1', function () {
        if (confirm("Da li ste sigurni da želite da obrišete sliku ?")) {
            $('.image_1_value').val('');
            $('#widget-41_gf_image_slider_widget-5 input[name="savewidget"]').click();
        }
    });
    $('#gf-homepage-row-1').on('click', '#gf-remove-image-2', function () {
        if (confirm("Da li ste sigurni da želite da obrišete sliku ?")) {
            $('.image_2_value').val('');
            $('#widget-41_gf_image_slider_widget-5 input[name="savewidget"]').click();
        }
    });
    $('#gf-homepage-row-1').on('click', '#gf-remove-image-3', function () {
        if (confirm("Da li ste sigurni da želite da obrišete sliku ?")) {
            $('.image_3_value').val('');
            $('#widget-41_gf_image_slider_widget-5 input[name="savewidget"]').click();
        }
    });



//GF - image banners *************************

    $('#gf-homepage-row-1-mobile').on('click', '.gf-upload-banner-image-1', function (e) {
        clickHandler(e, $('.image_banner_1_value'), $('#widget-gf_image_banners_widget-5-savewidget'));
    });
    $('#gf-homepage-row-1-mobile').on('click', '.gf-upload-banner-image-2', function (e) {
        clickHandler(e, $('.image_banner_2_value'), $('#widget-gf_image_banners_widget-5-savewidget'));
    });
    $('#gf-homepage-row-1-mobile').on('click', '.gf-upload-banner-image-3', function (e) {
        clickHandler(e, $('.image_banner_3_value'), $('#widget-gf_image_banners_widget-5-savewidget'));
    });
    $('#gf-homepage-row-1-mobile').on('click', '.gf-upload-banner-image-4', function (e) {
        clickHandler(e, $('.image_banner_4_value'), $('#widget-gf_image_banners_widget-5-savewidget'));
    });
    $('#gf-homepage-row-1-mobile').on('click', '.gf-upload-banner-image-5', function (e) {
        clickHandler(e, $('.image_banner_5_value'), $('#widget-gf_image_banners_widget-5-savewidget'));
    });
    $('#gf-homepage-row-1-mobile').on('click', '.gf-upload-banner-image-6', function (e) {
        clickHandler(e, $('.image_banner_6_value'), $('#widget-gf_image_banners_widget-5-savewidget'));
    });
    //remove image
    $('#gf-homepage-row-1-mobile').on('click', '#gf-remove-image-banner-1', function () {
        if (confirm("Da li ste sigurni da želite da obrišete sliku ?")) {
            $('.image_banner_1_value').val('');
            $('#widget-gf_image_banners_widget-5-savewidget').click();
        }
    });
    $('#gf-homepage-row-1-mobile').on('click', '#gf-remove-image-banner-2', function () {
        if (confirm("Da li ste sigurni da želite da obrišete sliku ?")) {
            $('.image_banner_2_value').val('');
            $('#widget-gf_image_banners_widget-5-savewidget').click();
        }
    });
    $('#gf-homepage-row-1-mobile').on('click', '#gf-remove-image-banner-3', function () {
        if (confirm("Da li ste sigurni da želite da obrišete sliku ?")) {
            $('.image_banner_3_value').val('');
            $('#widget-gf_image_banners_widget-5-savewidget').click();
        }
    });
    $('#gf-homepage-row-1-mobile').on('click', '#gf-remove-image-banner-4', function () {
        if (confirm("Da li ste sigurni da želite da obrišete sliku ?")) {
            $('.image_banner_4_value').val('');
            $('#widget-gf_image_banners_widget-5-savewidget').click();
        }
    });
    $('#gf-homepage-row-1-mobile').on('click', '#gf-remove-image-banner-5', function () {
        if (confirm("Da li ste sigurni da želite da obrišete sliku ?")) {
            $('.image_banner_5_value').val('');
            $('#widget-gf_image_banners_widget-5-savewidget').click();
        }
    });
    $('#gf-homepage-row-1-mobile').on('click', '#gf-remove-image-banner-6', function () {
        if (confirm("Da li ste sigurni da želite da obrišete sliku ?")) {
            $('.image_banner_6_value').val('');
            $('#widget-gf_image_banners_widget-5-savewidget').click();
        }
    });


//GF- custom logo
    $('#gf-header-row-2-col-1').on('click', '.gf-upload-image-logo', function (e) {
        clickHandler(e, $('.logo-image-value'), $('#widget-gf_custom_logo_widget-3-savewidget'));
    });

//GF- product slider
    $('#gf-homepage-row-2').on('change', '.gf-category-select', function () {
        $('#widget-gf_product_slider_widget-12-savewidget').click();
    });

    /* show lightbox when clicking a thumbnail */
    $('a.thumb').click(function (event) {
        event.preventDefault();
        var content = $('.modal-body');
        content.empty();
        var title = $(this).attr("title");
        $('.modal-title').html(title);
        content.html($(this).html());
        $(".modal-profile").modal({
            show: true
        });
    });
//====================
// GF PRODUCT STICKERS
//====================//
    var custom_uploader;

    function clickHandler(event, input, submitButton, target) {
        event.preventDefault();
        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Upload image',
            button: {
                text: 'Select'
            },
            multiple: false
        });
        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            input.val(attachment.url);
            submitButton.click();
        });

        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }

        //Open the uploader dialog
        custom_uploader.open();
        return;
    }

    $('.row').on('click', '#upload-sticker-image-new', function(e) {
        clickHandler(e, $('.image_select_new'), $('#sticker_submit'));
    });
    $('.row').on('click', '#upload-sticker-image-soldout', function(e) {
        clickHandler(e, $('.image_select_soldout'), $('#sticker_submit'));
    });
    $('.row').on('click', '#upload-sticker-image-sale', function(e) {
        clickHandler(e, $('.image_select_sale'), $('#sticker_submit'));
    });

//=======================
// GF SORTABLE CATEGORIES
//=======================//

    /********************************************/
    /* AJAX SAVE FORM */
    /********************************************/
    //ako dodje do nekog problema ovo treba da se koristi valjda :P
    // $('#theme-options-form').submit(function () {
    //     $(this).ajaxSubmit({
    //         onLoading: $('.loader').show(),
    //         success: function () {
    //             $('.loader').hide();
    //             $('#save-result').fadeIn();
    //             setTimeout(function () {
    //                 $('#save-result').fadeOut('fast');
    //             }, 2000);
    //         },
    //         timeout: 5000
    //     });
    //     return false;
    // });
    if ($('.gf-sortable-categories-wrapper').length > 0) {
        $('.accordion-first-level').accordion({
            collapsible: true,
            header: ">h2",
            heightStyle: "content",
            active: false,
            icons: {"header": "ui-icon-plus", "activeHeader": "ui-icon-minus"}
        });
        $('.accordion-second-level').accordion({
            collapsible: true,
            header: ">h4",
            heightStyle: "content",
            active: false,
            icons: {"header": "ui-icon-plus", "activeHeader": "ui-icon-minus"}
        });
        $('.filter-fields-list').sortable({
            handle: "h2",
            axis: 'y',
            containment: 'parent',
            cursor: 'move',
            items: '> li'
        });
        $('.parent-cat-children').sortable({
            handle: "> h4",
            axis: 'y',
            cursor: 'move',
            containment: 'parent',
            // connectWith: '.parent-cat-children',
            items: 'li.child-cat'
        });
        $('.child-cat-children').sortable({
            handle: "> h4",
            axis: 'y',
            cursor: 'move',
            containment: 'parent',
            // connectWith: 'ul.child-cat-children',
            items: 'li.child-child-cat'
        });
    }

    jQuery('.edit_address').click(function () {
        jQuery('.gf-admin-orders-pib-field').hide();
    });

    //Add new product page, required fields
    jQuery('.post-type-product #title').attr('required', true);
    jQuery('.post-type-product #titlewrap').attr('class', 'required');

    jQuery('.post-type-product #postdivrich').attr('class', 'required');
    jQuery('.post-type-product #product_catdiv h2 span').attr('class', 'required');
    jQuery('.post-type-product #postimagediv h2 span').attr('class', 'required');
    jQuery('.post-type-product #postimagediv h2 span').attr('class', 'required');
    jQuery('.post-type-product ._regular_price_field label').attr('class', 'required-price');


    jQuery('.nssOrderJitexExport, .nssOrderAdresnica').click(function() {
    // jQuery('.nssOrderJitexExport').click(function() {
        jQuery(this).css({
            color:'white',
            backgroundColor:'gray',
            fontStyle:'italic'
        });
    });

    jQuery('#_payment_method').change(function () {
        if (jQuery(this).val() == 'bacs') {
            status = 'wc-cekaseuplata';
            jQuery('#order_status').val(status).trigger('change');
        } else if (jQuery(this).val() == 'cod') {
            status = 'wc-u-pripremi';
            jQuery('#order_status').val(status).trigger('change');
        }
    });

    // $('.external-item-banners-widget-Form #sku').change(function() {
    $('#sku').keyup(function() {
        $.get('/back-ajax/?action=findBySku&sku=' + $(this).val(), function (JSON) {
            $('#title').val(JSON.title);
            $('#description').val(JSON.description);
            if (JSON.salePrice > 0) {
                $('#salePrice').val(JSON.salePrice);
                $('#regularPrice').val(JSON.regularPrice);
            } else {
                $('#salePrice').val(JSON.regularPrice);
            }
            $('#categoryUrl').val(JSON.categoryUrl);
            $('#itemUrl').val(JSON.itemUrl);
            $('#itemId').val(JSON.id);
            $('#imageSrc').val(JSON.imageSrc);
            $('#image').val(JSON.image);
            $('.externalCarouselItemImage').attr('src', JSON.imageSrc);
        }, 'JSON');
    });

    if ($('#sale_sticker_from').length > 0) {
        $('#sale_sticker_from').datepicker();
        $('#sale_sticker_to').datepicker();

        $('#sale_sticker_active').change(function() {
            if ($(this).prop('checked')) {
                $('.saleStickerOptionContainer').show();
            } else {
                $('.saleStickerOptionContainer').hide();
            }
        });
    }

    var select2ConfigForCustomAddProductButton = {
        ajax: {
            url: '/back-ajax/',
            dataType: 'json',
            data: function (params) {
                return {
                    query: params.term,
                    action: 'backendProductSearch'
                };
        },
        placeholder: "Search product"
    }};

    function createCloneForCustomAddButton() {
        var cl = $('.custom-add-template .custom-add-row').clone();
        cl.find('.item-list').select2(select2ConfigForCustomAddProductButton);
        return cl;
    }

    $('body').on('change', '.item-list', function () {
        $('#custom-add .content').append(createCloneForCustomAddButton());
    });

    $('body').on('click', '.save-items', function () {
        var items = [];
        $('#custom-add select').each(function(i, v) {
            items.push({
                'id': $(v).val(),
                'qty': $(v).siblings('.item-qty').val()
            });
        });
        $('.custom-add-row').remove();
        var data = {
            'action':'woocommerce_add_order_item',
            'order_id': $('#post_ID').val(),
            'security': woocommerce_admin_meta_boxes.order_item_nonce,
            'data': items
        };
        $.post('/wp-admin/admin-ajax.php?_fs_blog_admin=true', data, function(response) {
            $('.woocommerce_order_items_wrapper').parent().html(response.data.html);
            $.modal.close();
            $('.calculate-action').trigger('click');
        })
    });

    $('body').on('click', '.add-order-item-custom', function () {
        $("#custom-add").modal();
        $(".close-modal").click(function() {
            $.modal.close();
            $('#custom-add .content').html('');
            $('#custom-add .content').html(createCloneForCustomAddButton());
        });

        $('.content .item-list').select2(select2ConfigForCustomAddProductButton);
        $('.content .item-list').select2('open');
    });

});

