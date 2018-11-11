<?php

//add_filter( 'wc_order_statuses', 'gf_remove_processing_status', 6666666 );
function gf_remove_processing_status($statuses){
    if(isset($statuses['wc-processing'])){
        unset($statuses['wc-processing']);
    }
    if(isset($statuses['wc-pending'])){
        unset($statuses['wc-pending']);
    }
    if(isset($statuses['wc-cancelled'])){
        unset($statuses['wc-cancelled']);
    }
    if(isset($statuses['wc-failed'])){
        unset($statuses['wc-failed']);
    }
    return $statuses;
}

// ako zatreba za neki prevod koji ne mozemo da nadjemo
//add_filter( 'gettext', 'theme_sort_change', 20, 3 );
//function theme_sort_change( $translated_text, $text, $domain ) {
//
//    if ( is_woocommerce() ) {
//
//        switch ( $translated_text ) {
//
//            case 'Sort by latest' :
//
//                $translated_text = __( 'Sortiraj po najnovijem', 'theme_text_domain' );
//                break;
//        }
//
//    }
//
//    return $translated_text;
//}

//maybe we will need this function...
//function gf_custom_add_to_cart_message($message, $products)
//{
//    $titles = array();
//    $count = 0;
//    $show_qty = true;
//    if (!is_array($products)) {
//        $products = array($products => 1);
//        $show_qty = false;
//    }
//    if (!$show_qty) {
//        $products = array_fill_keys(array_keys($products), 1);
//    }
//    foreach ($products as $product_id => $qty) {
//        $titles[] = ($qty > 1 ? absint($qty) . ' &times; ' : '') . sprintf(_x('&ldquo;%s&rdquo;', 'Item name in quotes', 'woocommerce'), strip_tags(get_the_title($product_id)));
//        $count += $qty;
//    }
//    $titles = array_filter($titles);
//    $added_text = sprintf(_n('%s has been added to your cart.', '%s have been added to your cart.', $count, 'woocommerce'), wc_format_list_of_items($titles));
//    // Output success messages.
//    if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
//        $return_to = apply_filters('woocommerce_continue_shopping_redirect', wc_get_raw_referer() ? wp_validate_redirect(wc_get_raw_referer(), false) : wc_get_page_permalink('shop'));
//        $message = sprintf('<a href="%s" class="button wc-forward">%s</a> %s', esc_url($return_to), esc_html__('Continue shopping', 'woocommerce'), esc_html($added_text));
//    } else {
//        $message = sprintf('<a href="%s" class="button wc-forward">%s</a> %s', esc_url(wc_get_page_permalink('cart')), esc_html__('View cart', 'woocommerce'), esc_html($added_text));
//    }
//
//    if (has_filter('wc_add_to_cart_message')) {
//        wc_deprecated_function('The wc_add_to_cart_message filter', '3.0', 'wc_add_to_cart_message_html');
//        $message = apply_filters('wc_add_to_cart_message', $message, $product_id);
//    }
//    return $message;
//}


//function remove_country_field_billing($fields)
//{
//    unset($fields['billing_country']);
//    unset($fields['billing_state']);
//    return $fields;
//
//}
//add_filter('woocommerce_billing_fields', 'remove_country_field_billing');
//function remove_country_field_shipping($fields)
//{
//    unset($fields['shipping_country']);
//    unset($fields['shipping_state']);
//    return $fields;
//}
//add_filter('woocommerce_shipping_fields', 'remove_country_field_shipping');

//function custom_override_checkout_fields( $fields ) {
//    unset($fields['billing']['billing_country']);
//    unset($fields['shipping_country']);
//
//    return $fields;
//}
//add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
