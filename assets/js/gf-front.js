jQuery(document).ready(function () {
    var siteHeader = jQuery('.site-header');
    var siteHeaderHeight = siteHeader.height();

    if (jQuery(window).scrollTop() > siteHeaderHeight) {
        jQuery('.gf-header-logo').css({
            'max-width': '102px'
        });
    }

    var radioValue = '';
    jQuery(".gf-search-form").submit(function () {
        var radioValue = jQuery('input[name=search-radiobutton]:checked').val();
        if (radioValue === 'category') {
            jQuery(".gf-search-form").attr("action", "");
        }
    });
    jQuery('label[for="search-checkbox"]').click(function () {
        var radio = jQuery(this).children('.search-radio-box');
        radio.prop('checked', !radio.prop('checked'));
    });

    jQuery('.gf-category-box__item h5, .slider-item h5').each(function (index, element) {
        $clamp(element, {clamp: 3, useNativeClamp: false});
    });
    jQuery('.woocommerce-loop-product__title').each(function (index, element) {
        $clamp(element, {clamp: 3, useNativeClamp: false});
    });

    jQuery('.gf-sticker--center').each(function () {
        if (jQuery(this).parent().parent().is('.products .product') || jQuery(this).parent().parent().is('.gf-category-box__item')) {
            jQuery(this).addClass('gf-sticker--loop-grid');
        }
    });

    if (jQuery('.products').hasClass('list')) {
        jQuery('.products .gf-sticker--center').toggleClass('gf-sticker--loop-list');
    }

    jQuery('.gridlist-toggle a').click(function () {
        jQuery('.products .gf-sticker--center').toggleClass('gf-sticker--loop-list');
    });

    jQuery(document).scroll(function () {
        // if (jQuery(window).scrollTop() > siteHeaderHeight) {
        //   jQuery('.gf-header-logo').css({
        //     'max-width': '102px',
        //     'top': '0'
        //   });
        // } else {
        //   jQuery('.gf-header-logo').css({
        //     'max-width': '180px'
        //   });
        //   jQuery('body.home .gf-header-logo').css({
        //     'max-width': '235px'
        //   });
        //
        //   if (jQuery(window).width() > 767) {
        //     jQuery('.gf-header-logo').css({
        //       'top': '-42px'
        //     });
        //   }
        //   else {
        //     jQuery('.gf-header-logo').css({
        //       'top': '0'
        //     });
        //   }
        // }
    });


    jQuery(document).mouseup(function (e) {
        var category_list_accordion = jQuery('.gf-category-accordion');
        var category_list_toggle = jQuery('.gf-category-mobile-toggle');
        var category_list_bars_icon_toggle = jQuery('#gf-bars-icon-toggle');

        if (category_list_toggle.is(e.target) || category_list_bars_icon_toggle.is(e.target)) {
            category_list_accordion.slideToggle();
        }
        else if (category_list_accordion.has(e.target).length === 0) {
            category_list_accordion.hide();
        }
    });

    jQuery('.gf-category-accordion__expander').click(function () {
        jQuery(this).parent().children('.gf-category-accordion__item').slideToggle();
        jQuery(this).toggleClass('fa-plus fa-minus');
    });

    jQuery('#my-search-icon-toggle').click(function () {
        jQuery('.mobile-search').toggle('slow');

    });

    jQuery('.gf-user-account-menu').click(function () {
        jQuery('.gf-mobile-menu').slideToggle();
    });

    jQuery('#my-fancy-search').click(function () {
        if (jQuery('#my-fancy-search').hasClass('fancy-header-icons')) {
            jQuery('#my-fancy-search').removeClass('fancy-header-icons')
        } else {
            jQuery('#my-fancy-search').addClass('fancy-header-icons')
            if (jQuery('#my-fancy-user').hasClass('fancy-header-icons')) {
                jQuery('#my-fancy-user').removeClass('fancy-header-icons')
                jQuery('.gf-mobile-menu').slideToggle();
            }
            if (jQuery('#gf-bars-icon-toggle').hasClass('fancy-header-icons')) {
                jQuery('#gf-bars-icon-toggle').removeClass('fancy-header-icons');
                jQuery('.gf-category-accordion').slideToggle();
            }
        }

    });
    jQuery('#my-fancy-user').click(function () {
        if (jQuery('#my-fancy-user').hasClass('fancy-header-icons')) {
            jQuery('#my-fancy-user').removeClass('fancy-header-icons')
        } else {
            jQuery('#my-fancy-user').addClass('fancy-header-icons')
            if (jQuery('#gf-bars-icon-toggle').hasClass('fancy-header-icons')) {
                jQuery('#gf-bars-icon-toggle').removeClass('fancy-header-icons');
                jQuery('.gf-category-accordion').hide();
            }
            if (jQuery('#my-fancy-search').hasClass('fancy-header-icons')) {
                jQuery('#my-fancy-search').removeClass('fancy-header-icons');
                jQuery('.mobile-search').toggle('slow');
            }

        }
    });
    jQuery('#gf-bars-icon-toggle').click(function () {
        if (jQuery('#gf-bars-icon-toggle').hasClass('fancy-header-icons')) {
            jQuery('#gf-bars-icon-toggle').removeClass('fancy-header-icons')
        } else {
            jQuery('#gf-bars-icon-toggle').addClass('fancy-header-icons')
            if (jQuery('#my-fancy-user').hasClass('fancy-header-icons')) {
                jQuery('#my-fancy-user').removeClass('fancy-header-icons');
                jQuery('.gf-mobile-menu').slideToggle();
            }
            if (jQuery('#my-fancy-search').hasClass('fancy-header-icons')) {
                jQuery('#my-fancy-search').removeClass('fancy-header-icons');
                jQuery('.mobile-search').toggle('slow');
            }
        }
    });


    jQuery('.category-item a').hover(function (e) {

        var half_a_screen = jQuery(window).height() / 2;
        var currentLink = jQuery(this);
        var menu = currentLink.next();
        var firstLinkOffsetTop = currentLink.parent().parent().parent().find('a:first-child').offset().top;

        currentLink.next().css({
            top: 'unset',
            bottom: 'unset',
        });

        var linkOffset = currentLink.offset();
        var linkOffsetTop = linkOffset.top - (jQuery(window).scrollTop() + siteHeaderHeight);
        var linkOffsetBottom = jQuery(window).height() - linkOffset.top - currentLink.height();

        var menuOffsetTop;
        var menuOffsetBottom;
        var menuOffsetLeft = currentLink.width();

        if (linkOffsetTop < half_a_screen) {
            menuOffsetTop = 0 + 'px';
        } else {
            if (menu.height() > linkOffsetTop - firstLinkOffsetTop) {
                menuOffsetTop = '-' + (linkOffsetTop - firstLinkOffsetTop) + 'px';
            } else {
                menuOffsetBottom = 0;
            }
        }
        currentLink.next().css({
            position: "absolute",
            top: menuOffsetTop,
            bottom: '-' + menuOffsetBottom + 'px',
            left: menuOffsetLeft + 'px'
        });
    });

    jQuery('.gf-category-expander__footer .fas ').click(function () {
        jQuery('.gf-jos-kategorija').slideToggle();

        // jQuery('.gf-category-expander__col').slideToggle();
        jQuery('.gf-expander__subcategory-list').slideToggle();
        jQuery(this).toggleClass('fa-angle-down fa-angle-up');

        if (jQuery('#gf-expander-id').hasClass('gf-height-test')) {
            jQuery('#gf-expander-id').removeClass('gf-height-test');
        } else {
            jQuery('#gf-expander-id').addClass('gf-height-test');
        }
    });

    if (jQuery('body').is('.archive, .single-product')) {
        jQuery('.gf-wrapper-before span').toggleClass('fa-angle-down fa-angle-up');
    }

    jQuery('.gf-wrapper-before').click(function () {
        jQuery('.gf-navblock').slideToggle();
        jQuery('.gf-wrapper-before span').toggleClass('fa-angle-down fa-angle-up');
    });


    // jQuery('form.woocommerce-widget-layered-nav-dropdown, .widget_price_filter form').submit(function(e) {
    //   e.preventDefault();
    // });

    // jQuery('.price_slider_amount button').click(function () {
    //   let url = (location.origin).concat(location.pathname);
    //
    //   let filterForm = jQuery('.widget_price_filter form');
    //   let min_price = jQuery('.widget_price_filter input[name="min_price"]').val();
    //   let max_price = jQuery('.widget_price_filter input[name="max_price"]').val();
    //   let filter_color= jQuery('.woocommerce-widget-layered-nav-dropdown input[name="filter_color"]').val();
    //   let filter_size= jQuery('.woocommerce-widget-layered-nav-dropdown input[name="filter_size"]').val();
    //   let filter_manufacturer = jQuery('.woocommerce-widget-layered-nav-dropdown input[name="filter_manufacturer"]').val();
    //
    //   let filterContent = jQuery.extend({}, {
    //     min_price: min_price,
    //     max_price: max_price,
    //     filter_color: filter_color,
    //     filter_size: filter_size,
    //     filter_manufacturer: filter_manufacturer
    //   });
    //
    //   jQuery.ajax({
    //     method: 'GET',
    //     url: url,
    //     contentType: 'application/json; charset=utf-8',
    //     data: filterContent
    //   });
    // });

    var preventSearch = false, timer, searchQuery, delay = 300;
    jQuery(".gf-search-form").on('keyup', '.gf-search-box', function (e) {
        var _this = jQuery(this);
        // prevent further search when enter detected
        if (e.keyCode === 13 || e.keyCode === 99) {
            preventSearch = true;
            return false;
        }

        if (jQuery(this).val().length >= 3) {
            if (searchQuery !== _this.val()) {
                searchQuery = _this.val();
                clearTimeout(timer);
                timer = setTimeout(function () {
                    if (!preventSearch) {
                        ajaxSearch(_this.val());
                    }
                }, delay);
            }
        }
        if (_this.val().length === 0) {
            jQuery('.suggesstion-box').hide();
            return false;
        }
    });
    jQuery(document).click(function (event) {
        if (!jQuery(event.target).closest('.suggesstion-box').length) {
            if (jQuery('.suggesstion-box').is(":visible")) {
                jQuery('.suggesstion-box').hide();
            }
        }
    });

    /**
     * Track product views.
     */
    if (jQuery('body').hasClass('single-product')) {
        jQuery.ajax({
            type: "POST",
            url: '/gf-ajax/?viewCount=true&postId=' + jQuery('div.type-product').attr('id').split('-')[1],
            minLength: 0,
            success: function (response) {
            }
        });
    }

    // set default search radio button value
    jQuery(".gf-search-form").submit(function () {
        var radioValue = jQuery('input[name=search-radiobutton]:checked').val();
        if (radioValue === 'category') {
            jQuery(".gf-search-form").attr("action", "");
        }
    });
});

// grid view
jQuery(document).ready(function (jQuery) {
    if (jQuery.cookie('gridcookie') == null) {
        jQuery('ul.products').addClass('grid');
        jQuery('.gridlist-toggle #grid').addClass('active');
    }
});


function ajaxSearch(value) {
    jQuery.ajax({
        type: "POST",
        // url: ajax_object.ajax_url,
        url: '/gf-ajax/',
        data: {'query': value},
        minLength: 0,
        beforeSend: function () {
            jQuery(".gf-search-box").css("background", "#fafafa url(/wp-content/themes/nss-green-friends-shop/assets/images/LoaderIcon.gif)no-repeat center");
        },
        success: function (response) {
            jQuery(".gf-search-box").css("background", "none");
            jQuery(".suggesstion-box").html(response.slice(0, -1));
            jQuery(".suggesstion-box").fadeIn(200);
            jQuery(".search-box").css("background", "#eee");
        }
    });
}

// gf-widgets.js
jQuery(document).ready(function ($) {
    //don't start on wrong pages
    if (jQuery('.gf-product-slider').length > 0) {
        var gfSliderColumnCount = 4;
        // if (typeof gfSliderColumnCount !== "undefined") {
        // @Important activate slider after tab is displayed in order to have access to proper width
        $("#tabs").tabs({
            activate: function (event, ui) {
                if (!$(ui.newPanel.selector).hasClass('slick-initialized')) {
                    startSlider(ui.newPanel.selector);
                }
            }
        });
        startSlider('#tabs-1');
        hookSliderEvents();
        startSlider('.without-tabs');
    }

    function startSlider(selector) {
        $(selector).slick({
            infinite: true,
            // slidesToShow: gfSliderColumnCount,
            slidesToShow: $(selector).parents('.gf-product-slider').data('sliderItemCount'),
            // slidesToScroll: gfSliderColumnCount,
            slidesToScroll: $(selector).parents('.gf-product-slider').data('sliderItemCount'),
            arrows: false,
            dots: false,
            responsive: [{
                breakpoint: 1024,
                settings: {
                    slidesToShow: 3,
                    slidesToScroll: 3,
                    infinite: true
                }
            },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2
                    }
                }
            ]
        });
    }

    function hookSliderEvents() {
        // standard carousel controls
        $('.widget_gf_product_slider_without_tabs_widget .product-slider__control-prev').click(function (e) {
            e.preventDefault();
            $(this).parents('.widget_gf_product_slider_without_tabs_widget').find('.slider-inner').slick('slickPrev');
        });

        $('.widget_gf_product_slider_without_tabs_widget .product-slider__control-next').click(function (e) {
            e.preventDefault();
            $(this).parents('.widget_gf_product_slider_without_tabs_widget').find('.slider-inner').slick('slickNext');
        });

        //tabbed carousel controls
        $('#tabs .product-slider__control-prev').click(function (e) {
            e.preventDefault();
            $('.slider-inner').each(function (key, value) {
                if ($(value).attr('aria-hidden') == 'false') {
                    $(this).slick('slickPrev');
                }
            });
        });

        $('#tabs .product-slider__control-next').click(function (e) {
            e.preventDefault();
            $('.slider-inner').each(function (key, value) {
                if ($(value).attr('aria-hidden') == 'false') {
                    $(this).slick('slickNext');
                }
            });
        });
    }

});
jQuery(document).ready(function ($) {
    $('#billing_company_checkbox').removeAttr('checked').click(function () {
        $('#billing_pib_field').toggle();
        $('#billing_company_field').toggle();
        if ($('#billing_company_checkbox').prop('checked')) {
            $('#billing_company_field').addClass('validate-required');
            $('#billing_pib_field').addClass('validate-required');
            $('p#billing_company_field > label > span').text('*').removeClass('optional').addClass('required');
            $('p#billing_pib_field > label > span').text('*').removeClass('optional').addClass('required');
        }
    });
});


jquery(document).ready(function ($) {
    $('.category-item').hover(function(){

        $('.mega-menu').delay(2000).show();

    });
});
