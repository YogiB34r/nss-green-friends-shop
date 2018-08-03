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
});
