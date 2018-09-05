jQuery(document).ready(function() {
  var siteHeader = jQuery('.site-header');
  var siteHeaderHeight = siteHeader.height();

  if (jQuery(window).scrollTop() > siteHeaderHeight) {
    jQuery('.gf-header-logo').css({
      'max-width': '102px'
    });
  }

  var radioValue='';
  jQuery(".gf-search-form").submit(function () {
     var radioValue = jQuery('input[name=search-radiobutton]:checked').val();
     if (radioValue === 'category'){
       jQuery(".gf-search-form").attr("action", "");
     }
  });
  jQuery('label[for="search-checkbox"]').click(function() {
    var radio = jQuery(this).children('.search-radio-box');
    radio.prop('checked', !radio.prop('checked'));
  });

  jQuery('.gf-category-box__item h5').each(function(index, element) {
    $clamp(element, { clamp: 3, useNativeClamp: false });
  });
  jQuery('.woocommerce-loop-product__title').each(function(index, element) {
    $clamp(element, { clamp: 3, useNativeClamp: false });
  });
	
  jQuery('.woosticker.custom_sticker_image:contains("Sold")').addClass('woosticker-onsale').each(function() {
	if(jQuery(this).parent().is('.products .product') || jQuery(this).parent().parent().is('.gf-category-box__item')) {
	  jQuery(this).addClass('woosticker-onsale--loop');	 
    }
  });

  if(jQuery('.products').hasClass('list')) {
    jQuery('.products .woosticker.custom_sticker_image:contains("Sold")').toggleClass('woosticker-onsale--loop-list');
  }
	
  jQuery('.gridlist-toggle a').click(function() {
    jQuery('.products .woosticker.custom_sticker_image:contains("Sold")').toggleClass('woosticker-onsale--loop-list');
  });

  jQuery(document).scroll(function() {
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

  jQuery(document).mouseup(function(e) {
    var category_list_accordion = jQuery('.gf-category-accordion');
    var category_list_toggle = jQuery('.gf-category-mobile-toggle');

    if (category_list_toggle.is(e.target)) {
      category_list_accordion.slideToggle();
    }
    else if(category_list_accordion.has(e.target).length === 0) {
      category_list_accordion.hide();
    }
  });

  jQuery('.gf-category-accordion__expander').click(function() {
    jQuery(this).parent().children('.gf-category-accordion__item').slideToggle();
    jQuery(this).toggleClass('fa-plus fa-minus');
  });

  jQuery('.gf-hamburger-menu').click(function() {
    jQuery('.gf-mobile-menu').slideToggle();
  });

  // jQuery('.gf-search-toggle').click(function() {
  //   jQuery('.search-input-wrapper').toggle();
  //   jQuery('.gf-search-toggle i').toggleClass('fa-times fa-search');
  // });

  jQuery('.category-item a').hover(function(e) {

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

  jQuery('.gf-category-expander__footer .fas ').click(function() {
    jQuery('.gf-expander__subcategory-list').slideToggle();
    jQuery(this).toggleClass('fa-angle-down fa-angle-up');
  });

  if (jQuery('body').is('.archive, .single-product')) {
    jQuery('.gf-wrapper-before span').toggleClass('fa-angle-down fa-angle-up');
  }

  jQuery('.gf-wrapper-before').click(function() {
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
});
