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
    jQuery("#gf-search-box").keyup(function(){
        if(jQuery(this).val().length >= 3)
        {
            jQuery.ajax({
                type: "POST",
                url: ajax_object.ajax_url,
                data:{'keyword': jQuery(this).val(), action:'ajax_gf_autocomplete'},
                minLength: 0,
                beforeSend: function(){
                    jQuery("#gf-search-box").css("background","#fafafa url(wp-content/themes/nss-green-firends-shop/assets/images/LoaderIcon.gif)no-repeat 36px");
                },
                success: function(data){
                    jQuery("#suggesstion-box").show();
                    jQuery("#suggesstion-box").html(data);
                    jQuery("#search-box").css("background","#eee");
                }
            });
        }

    });
});