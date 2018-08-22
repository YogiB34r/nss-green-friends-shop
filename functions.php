<?php
ini_set( 'upload_max_size' , '128M' );
ini_set( 'post_max_size', '128M');
ini_set( 'max_execution_time', '300' );
add_action( 'after_setup_theme', 'wc_support' );
function wc_support() {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
    add_theme_support( 'yoast-seo-breadcrumbs' );
}
function require_on_init(){
    foreach(glob(get_stylesheet_directory()."/inc/*.php") as $file){
        require $file;
    }
}
add_action('after_setup_theme', 'require_on_init');

add_filter('woocommerce_currency_symbol', 'change_existing_currency_symbol', 10, 2);

function change_existing_currency_symbol( $currency_symbol, $currency ) {
    $currency_symbol = 'din.';

    return $currency_symbol;
}

//remove_action( 'woocommerce_before_main_content','woocommerce_breadcrumb', 20);

/**
 * Show out of stock items last.
 */
//add_filter('posts_clauses', 'order_by_stock_status');
//function order_by_stock_status($posts_clauses) {
//    global $wpdb;
    // only change query on WooCommerce loops
//    if (is_woocommerce() && (is_shop() || is_product_category() || is_product_tag())) {
//        $posts_clauses['join'] .= " INNER JOIN $wpdb->postmeta istockstatus ON ($wpdb->posts.ID = istockstatus.post_id) ";
//        $posts_clauses['orderby'] = " istockstatus.meta_value ASC, " . $posts_clauses['orderby'];
//        $posts_clauses['where'] = " AND istockstatus.meta_key = '_stock_status' AND istockstatus.meta_value <> '' " . $posts_clauses['where'];
//    }
//    return $posts_clauses;
//}