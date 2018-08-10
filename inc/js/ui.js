jQuery(document).ready(function() {
  jQuery('.category-item a').hover(function(e) {
    var currentLink = jQuery(this);
    var linkOffset = currentLink.offset();
    var currentLinkOffset = linkOffset.top - jQuery(window).scrollTop();
    var offsetLeft =  currentLink.offset().left + currentLink.width() + 1;
    currentLink.next().css({
      position: "fixed",
      top: (currentLinkOffset + currentLink.height())+ "px",
      left: (offsetLeft) + "px"
    });
  });

  jQuery('.gf-category-expander__footer .fas ').click(function() {
    jQuery('.gf-expander__subcategory-list').slideToggle();
    jQuery(this).toggleClass('fa-angle-down fa-angle-up');
  });

  jQuery('form.woocommerce-widget-layered-nav-dropdown, .widget_price_filter form').submit(function (e) {
    e.preventDefault();
  });

  jQuery('.price_slider_amount button').click(function () {
    let url = (location.origin).concat(location.pathname);

    let filterForm = jQuery('.widget_price_filter form');
    let min_price = jQuery('.widget_price_filter input[name="min_price"]').val();
    let max_price = jQuery('.widget_price_filter input[name="max_price"]').val();
    let filter_color= jQuery('.woocommerce-widget-layered-nav-dropdown input[name="filter_color"]').val();
    let filter_size= jQuery('.woocommerce-widget-layered-nav-dropdown input[name="filter_size"]').val();
    let filter_manufacturer = jQuery('.woocommerce-widget-layered-nav-dropdown input[name="filter_manufacturer"]').val();

    let filterContent = jQuery.extend({}, {
      min_price: min_price,
      max_price: max_price,
      filter_color: filter_color,
      filter_size: filter_size,
      filter_manufacturer: filter_manufacturer
    });

    console.log(filterContent);

    jQuery.ajax({
      method: 'GET',
      url: url,
      contentType: 'application/json; charset=utf-8',
      data: filterContent
    });
  });
});
