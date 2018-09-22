// jQuery(document).ready( function(){
//     jQuery('.gf-content-wrapper').on('click', 'a.gf-ajax', function(e) {
//         e.preventDefault();
//         var userId = jQuery(this).data( 'user-id' );
//         jQuery.ajax({
//             url : ajax_object.ajax_url,
//             type : 'post',
//             data : {
//                 action : 'ajax_test',
//                 query: '',
//                 user_id : userId
//             },
//             success : function(data) {
//                 console.log(data);
//                 $('#search-results-wrapper').append(data);
//             }
//         });
//     });
// });

jQuery(document).ready(function(){
    var timer, delay = 500;
    jQuery("#gf-search-box").bind('keydown blur change', function(e) {
        if(jQuery(this).val().length >= 3) {
            var _this = jQuery(this);
            clearTimeout(timer);
            timer = setTimeout(function() {
                ajaxSearch(_this.val());
            }, delay );
        }
    });
});

function ajaxSearch(value) {
    jQuery.ajax({
        type: "POST",
        url: ajax_object.ajax_url,
        data:{'keyword': value, action:'ajax_gf_autocomplete'},
        minLength: 0,
        beforeSend: function(){
            jQuery("#gf-search-box").css("background","#fafafa url(/wp-content/themes/nss-green-friends-shop/assets/images/LoaderIcon.gif)no-repeat 36px");
        },
        success: function(response){
            jQuery("#gf-search-box").css("background","none");
            jQuery("#suggesstion-box").html(response.slice(0, -1));
            jQuery("#suggesstion-box").fadeIn();
            jQuery("#search-box").css("background","#eee");
        }
    });
}