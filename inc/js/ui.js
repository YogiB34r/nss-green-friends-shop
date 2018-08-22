jQuery(document).ready(function() {

  var siteHeader = jQuery('.site-header');
  var siteHeaderHeight = siteHeader.height();

  if (jQuery(window).scrollTop() > siteHeaderHeight) {
    jQuery('.gf-header-logo').css({
      'max-width': '102px'
    });
  }

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
    else {
      category_list_accordion.hide();
    }
  });

  jQuery('.gf-category-mobile-toggle').click(function(e) {
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
      menuOffsetTop = 0;
    } else {
      if (menu.height() > linkOffsetTop) {
        menuOffsetBottom = siteHeader.offset().top + siteHeaderHeight;
      } else {
        menuOffsetBottom = 0;
      }
    }
    currentLink.next().css({
      position: "absolute",
      top: menuOffsetTop + 'px',
      bottom: '-' + menuOffsetBottom + 'px',
      left: menuOffsetLeft + 'px'
    });
  });

  jQuery('.gf-category-expander__footer .fas ').click(function() {
    jQuery('.gf-expander__subcategory-list').slideToggle();
    jQuery(this).toggleClass('fa-angle-down fa-angle-up');
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
