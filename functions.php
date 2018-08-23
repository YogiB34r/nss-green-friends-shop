<?php
ini_set('upload_max_size', '128M');
ini_set('post_max_size', '128M');
ini_set('max_execution_time', '300');
add_action('after_setup_theme', 'wc_support');
function wc_support()
{
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('yoast-seo-breadcrumbs');
}

function require_on_init()
{
    foreach (glob(get_stylesheet_directory() . "/inc/*.php") as $file) {
        require $file;
    }
}

add_action('after_setup_theme', 'require_on_init');

add_filter('woocommerce_currency_symbol', 'change_existing_currency_symbol', 10, 2);

function change_existing_currency_symbol($currency_symbol, $currency)
{
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
function gf_get_categories()
{
    $args = array(
        'orderby' => 'name',
        'order' => 'asc',
        'hide_empty' => false,
    );
    $product_cats = get_terms('product_cat', $args);
    return $product_cats;
}
function gf_get_top_level_categories(){
    $top_level_categories =[];
    foreach (gf_get_categories() as $category){
        if (!$category->parent){
            $top_level_categories[]= $category;
        }
    }
    return $top_level_categories;
}
function gf_get_second_level_categories($parent_id = null)
{
    $categories = gf_get_categories();
    $top_level_ids = [];
    $second_level_categories = [];
    foreach ($categories as $category) {
        if (!$category->parent) {
            $top_level_ids[] = $category->term_id;
        }
    }
    foreach ($categories as $category) {
        if ($parent_id) {
            if ($category->parent == $parent_id) {
                $second_level_categories[] = $category;
            }
        }elseif (in_array($category->parent, $top_level_ids)) {
            $second_level_categories[] = $category;
        }
    }
    return $second_level_categories;
}
function gf_get_third_level_categories($parent_id = null){
    $categories = gf_get_categories();
    $second_level_ids = [];
    foreach (gf_get_second_level_categories() as $cat){
        $second_level_ids[] = $cat->term_id;
    }
    $third_level_categories = [];
    foreach ($categories as $category) {
        if ($parent_id) {
            if ($category->parent == $parent_id) {
                $third_level_categories[] = $category;
            }
        }elseif (in_array($category->parent, $second_level_ids)) {
            $third_level_categories[] = $category;
        }
    }
    return $third_level_categories;
}
function gf_check_level_of_category($cat_id){
    $result = null;
    $top_level_ids=[];
    $second_level_ids=[];
    $third_level_ids=[];
    foreach (gf_get_top_level_categories() as $category){
        $top_level_ids[]=$category->term_id;
    }
    foreach (gf_get_second_level_categories() as $category){
        $second_level_ids[]=$category->term_id;
    }
    foreach (gf_get_third_level_categories() as $category){
        $third_level_ids[]=$category->term_id;
    }
    if (in_array($cat_id,$top_level_ids)){
        $result = 1;
    }
    if (in_array($cat_id,$second_level_ids)){
        $result = 2;
    }
    if (in_array($cat_id,$third_level_ids)){
        $result = 3;
    }
    return $result;
}

