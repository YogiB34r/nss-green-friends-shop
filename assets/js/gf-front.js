jQuery(document).ready(function ($) {
    //refresh cart count
    $.ajax({
        type:"POST",
        url : "/gf-ajax/",
        data : {action:"refreshCartCount"},
        success : function(response) {
            $('.shopping-cart__count').html(response);
        },
        error: function() {
            console.log('AJAX error - cannot refresh cart count.');
        }
    });

    var siteHeader = jQuery('.site-header');
    var siteHeaderHeight = siteHeader.height();

    if (jQuery(window).scrollTop() > siteHeaderHeight) {
        jQuery('.gf-header-logo').css({
            'max-width': '102px'
        });
    }

    var radioValue = '';
    jQuery(".gf-search-form, .gf-search-form--mobile").on('submit',function () {
        var radioValue = jQuery('input[name=search-radiobutton]:checked').val();
        if (radioValue === 'category') {
            jQuery(".gf-search-form, .gf-search-form--mobile").attr("action", "");
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

    // jQuery('.gf-sticker--center').each(function () {
    //     if (jQuery(this).parent().parent().is('.products .product') || jQuery(this).parent().parent().is('.gf-category-box__item')) {
    //         jQuery(this).addClass('gf-sticker--loop-grid');
    //     }
    // });

    if (jQuery('.products').hasClass('list')) {
        jQuery('.products .gf-sticker--center').toggleClass('gf-sticker--loop-list');
    }

    jQuery('.gridlist-toggle a').click(function () {
        jQuery('.products .gf-sticker--center').toggleClass('gf-sticker--loop-list');
    });


    jQuery(document).on('mouseup',function (e) {
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
        jQuery('.mobile-search').toggle('slow', function() {
            jQuery('.search-field').trigger('focus');
        });

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


    jQuery('.category-item a').on('hover', function (e) {

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
        jQuery('.gf-expander__subcategory-list').slideToggle();
        jQuery('.gf-category-expander__col').slideToggle();
        jQuery('.gf-expander-ul-first-line').slideToggle();


        jQuery(this).toggleClass('fa-angle-down fa-angle-up');

        // if (jQuery('#gf-expander-id').hasClass('gf-height-test')) {
        //     jQuery('#gf-expander-id').removeClass('gf-height-test');
        // } else {
        //     jQuery('#gf-expander-id').addClass('gf-height-test');
        // }

    });

    if (jQuery('body').is('.archive, .single-product, .woocommerce-account')) {
        jQuery('.gf-wrapper-before span').toggleClass('fa-angle-down fa-angle-up');
    }

    jQuery('.gf-wrapper-before').click(function () {
        jQuery('.gf-navblock').slideToggle();
        jQuery('.gf-wrapper-before span').toggleClass('fa-angle-down fa-angle-up');
    });

    /**
     * Ajax search disabled
     * @type {boolean}
     */
    var preventSearch = false, timer, searchQuery, delay = 300;
    jQuery(".gf-search-form").on('keyup', '.gf-search-box', function (e) {
        var _this = jQuery(this);
        //     prevent further search when enter detected
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
    jQuery(".gf-search-form").on('submit', function () {
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
            jQuery(".search-box").css("background", "#eee");
            if (response != '') {
                jQuery(".suggesstion-box").html(response);
                jQuery(".suggesstion-box").fadeIn(200);
            }
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
/*        $("#tabs").tabs({
            activate: function (event, ui) {
                if (!$(ui.newPanel.selector).hasClass('slick-initialized')) {
                    startSlider(ui.newPanel.selector);
                }
            }
        });
        startSlider('#tabs-1');
        hookSliderEvents();
        startSlider('.without-tabs');*/
    }

    function startSlider(selector) {
        $(selector).on('init', function (slick) {
            $('.gf-product-slider').css("visibility", "visible");
        })
            .slick({
                infinite: true,
                slidesToShow: gfSliderColumnCount,
                // slidesToShow: $(selector).parents('.gf-product-slider').data('sliderItemCount'),
                slidesToScroll: gfSliderColumnCount,
                // slidesToScroll: $(selector).parents('.gf-product-slider').data('sliderItemCount'),
                arrows: false,
                dots: false,
                lazyLoad: 'ondemand',
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
const swiper = new Swiper(".swiper-container", {
        slidesPerView: 2,
        spaceBetween: 0,
        loop: !0,
        arrows: !1,
        breakpoints: {
            320: {
                slidesPerView: 2,
                spaceBetween: 0
            },
            640: {
                slidesPerView: 2,
                spaceBetween: 5
            },
            1023: {
                slidesPerView: 2,
                spaceBetween: 5
            },
            1024: {
                slidesPerView: 3,
                spaceBetween: 10
            },
            1376: {
                slidesPerView: 4,
                spaceBetween: 15
            }
        }
    }),
    prevButtons = document.getElementsByClassName("product-slider__control-prev"),
    nextButtons = document.getElementsByClassName("product-slider__control-next");
if (null != prevButtons)
    for (let e of prevButtons) {
        const t = e.parentElement.parentElement.parentElement.lastElementChild.swiper;
        e.addEventListener("click", e => {
            e.preventDefault(), t.slidePrev()
        })
    }
if (null != nextButtons)
    for (let e of nextButtons) {
        const t = e.parentElement.parentElement.parentElement.lastElementChild.swiper;
        e.addEventListener("click", e => {
            e.preventDefault(), t.slideNext()
        })
    }
jQuery(document).ready(function ($) {

    if ($('#billing_postcode').length === 1) {
        //force pib to number
        $('#billing_postcode').val($('#billing_postcode').val().replace(/\D/g,''));
        $('#shipping_postcode').val($('#shipping_postcode').val().replace(/\D/g,''));
        $('#billing_postcode, #shipping_postcode').on('keypress', function(e) {
            var code = e.keyCode || e.which;
            if (code < 48 || code > 57) {
                e.preventDefault();
            }
            if (this.value.length >= 6) {
                e.preventDefault();
            }
        });

        $('#ship-to-different-address-checkbox').click(); //@TODO kad se sredi css treba izbrisati
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
    }

});

jQuery('.gf-archive-description-button').on('click',function () {
    jQuery('.gf-archive-description p').toggleClass('gf-display-category-description');
});
jQuery(document).ready(function ($) {
    $('.tnp-email').attr('title', 'Ovo polje mora biti popunjeno').attr('onInvalid', 'this.setCustomValidity(\'Neispravna email adresa\')').attr('onInput', 'this.setCustomValidity(\'\')');
    $('.tnp-privacy').attr('title', 'Da bi ste nastavili morate čekirati ovo polje').attr('onInvalid', 'this.setCustomValidity(\'Morate prihvatiti politiku privatnosti\')');
    $('.tnp-privacy').attr('onchange', 'this.setCustomValidity(\'\')');
});

function showPassword() {
    var x = jQuery('#password');
    if (x.attr('type') === "password") {
        x.attr('type', "text");
    } else {
        x.attr('type', "password");
    }
}

jQuery(document).ready(function ($) {
    $('.search-radiobutton-cat').prop('checked', true);
    // $('#search-radiobutton-cat').bind( "click" );
    $('.s-radio-btn-1').addClass("color-orange");

    $('.s-radio-btn-1, .search-radiobutton-cat').click(function () {
        $('.search-radiobutton-cat').prop('checked', true);
        $('.search-radiobutton-main').prop('checked', false);
        $('.s-radio-btn-1').addClass("color-orange");
        $('.s-radio-btn-2').removeClass("color-orange");
    });

    $('.s-radio-btn-2, .search-radiobutton-main').click(function () {
        $('.search-radiobutton-cat').prop('checked', false);
        $('.search-radiobutton-main').prop('checked', true);
        $('.s-radio-btn-2').addClass("color-orange");
        $('.s-radio-btn-1').removeClass("color-orange");
    });


//    checkout city ajax
    $('#billing_city, #shipping_city').on('change',function () {
        var city = $(this).val();
        var name = $(this).attr('name');
        $.ajax({
            type:"POST",
            url : "/gf-ajax/",
            data : {
                city: city,
                action: 'getZipCode'
            },
            success : function(response) {
                if (name === 'shipping_city') {
                    $('#shipping_postcode').val(response);
                } else {
                    $('#billing_postcode').val(response);
                }
            },
            error: function() {
                console.log('AJAX error - get zip by city');
            }
        });
    });

    //*** Infinite scroll ***
    startInfiniteScroll();

    // var targetHeight = $(document).height() - ($(window).height());
    function startInfiniteScroll() {
        var that = $('#loadMore');
        if (that.length > 0) {
            //init
            var loading = false;
            $(window).on('scroll', function() {
                var page = parseInt($('#loadMore').data('page'));
                if (page < 1) {
                    page = 1;
                }
                var newPage = page + 1;

                // if ($(window).scrollTop() === $(document).height() - $(window).height()) {
                var windowPos = window.scrollY + $(window).height() * 3/4;
                if (!loading && windowPos > that.offset().top - 200 && windowPos < that.offset().top + 100) {
                    url = '/gf-ajax/?query=' + that.data('query');
                    if (findGetParameter('orderby') && findGetParameter('orderby') !== '') {
                        url += '&orderby=' + findGetParameter('orderby');
                    }
                    if (findGetParameter('min_price') && findGetParameter('min_price') !== '') {
                        url += '&min_price=' + findGetParameter('min_price') + '&max_price=' + findGetParameter('max_price');
                    }
                    // load filter params as well
                    loading = true;
                    $.ajax({
                        url: url,
                        type: 'post',
                        data: {
                            page: newPage,
                            term: that.data('term'),
                            type: that.data('action'),
                            action: 'ajax_load_more'
                        },
                        error: function(response) {
                            loading = false;
                            // console.log(response);
                        },
                        success: function(response) {
                            loading = false;
                            if (response == 0) {
                                if ($("#no-more").length == 0) {
                                    $('#ajax-content').append('<div id="no-more" class="text-center"><h3>Stigli ste do kraja!</h3><p>Nema proizvoda za traženi kriterijum.</p></div>');
                                }
                                $('#loadMore').hide();
                                console.log('the end');
                            } else {
                                $('#loadMore').data('page', newPage);
                                $('#ajax-content').append(response);

                                window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
                                // ga('create', 'UA-108239528-1', { 'cookieDomain': 'nonstopshop.rs' } );
                                ga('send', 'pageview', location + 'page/' + page);
                            }
                        }
                    });
                }
            });
        }
    }

});


function findGetParameter(parameterName) {
    var result = null,
        tmp = [];
    location.search
        .substr(1)
        .split("&")
        .forEach(function (item) {
            tmp = item.split("=");
            if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
        });
    return result;
}