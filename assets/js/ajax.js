jQuery(document).ready( function(){
    jQuery('.gf-content-wrapper').on('click', 'a.gf-ajax', function(e) {
        e.preventDefault();
        var userId = jQuery(this).data( 'user-id' );
        jQuery.ajax({
            url : ajax_object.ajax_url,
            type : 'post',
            data : {
                action : 'ajax_test',
                query: '',
                user_id : userId
            },
            success : function(data) {
                console.log(data);
                $('#search-results-wrapper').append(data);
            }
        });
    });
});