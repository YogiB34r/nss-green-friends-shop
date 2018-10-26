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
            cursor: 'move',
            items: 'li'
        });
        $('.parent-cat-children').sortable({
            handle: "h4",
            axis: 'y',
            cursor: 'move',
            items: 'li'
        });
        $('.child-cat-children').sortable({
            handle: "h5",
            axis: 'y',
            cursor: 'move',
            items: 'li'
        });
    }

    jQuery('.edit_address').click(function () {
        jQuery('.gf-admin-orders-pib-field').hide();
    })




    //Add new product page, required fields
    jQuery('.post-type-product #title').attr('required', true);
    jQuery('.post-type-product #titlewrap').attr('class', 'required');

    jQuery('.post-type-product #postdivrich').attr('class', 'required');
    jQuery('.post-type-product #product_catdiv h2 span').attr('class', 'required');
    jQuery('.post-type-product #postimagediv h2 span').attr('class', 'required');
    jQuery('.post-type-product #postimagediv h2 span').attr('class', 'required');
    jQuery('.post-type-product ._regular_price_field label').attr('class', 'required-price');







});

