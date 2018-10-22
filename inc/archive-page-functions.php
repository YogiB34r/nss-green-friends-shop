<?php
function gf_check_if_slug_is_in_url_and_product_count()
{
    global $wp;
    $resault = '';
    $request_path = explode('/', $wp->request);
    $cat_args = array(
        'childless' => 1,
    );
    $product_categories = get_terms('product_cat', $cat_args);
    $slugArray = array();
    foreach ($product_categories as $category_slug) {
        $slugArray [] = $category_slug->slug;
    }
    foreach ($slugArray as $slug) {
        $category = get_term_by('slug', $slug, 'product_cat');
        if (in_array($slug, $request_path) && $category->count > 1) {
            $resault = true;
            break;
        }
        $resault = false;
    }
    return $resault;
}

function gf_check_for_second_level_categories()
{
    global $wp;
    $resault = '';
    $request_path = explode('/', $wp->request);
    $slugArray = [];
    $cat_args = array(
        'paretn' => 0,
    );
    $categories = get_terms('product_cat', $cat_args);
    $top_level_ids = [];
    foreach ($categories as $category) {
        if (!$category->parent) {
            $top_level_ids[] = $category->term_id;
        }
    }
    foreach ($categories as $category) {
        // Only output if the parent_id is a TOP level id
        if (in_array($category->parent, $top_level_ids)) {
            $slugArray[] = $category->slug;
        }
    }
    foreach ($slugArray as $slug) {
        $category = get_term_by('slug', $slug, 'product_cat');
        if (in_array($slug, $request_path) && $category->count > 1) {
            $resault = true;
            break;
        }
        $resault = false;
    }
    return $resault;
}


add_action('woocommerce_archive_description', 'gf_display_categories_on_archive_page', 15);
//Na nekim kategorijama $categories lepo uhvati kategorije a na nekima ne vraca nista 
function gf_display_categories_on_archive_page()
{
    if (is_product_category() and !isset($_GET['query'])) {
        get_template_part('templates/template-parts/category-page/gf-module-for-categories');
    }
    if (isset($_GET['query'])){
        get_template_part('templates/template-parts/category-page/gf-module-for-search');
    }
}

remove_action('woocommerce_before_shop_loop', 'wc_print_notices', 10);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10);

add_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
add_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 26);
//add_action('woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 26);
add_action('woocommerce_before_shop_loop', 'woocommerce_pagination', 27);
add_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 27);

function woocommerce_result_count() {
    if (!wc_get_loop_prop('is_paginated')) {
        return;
    }

    $total = wc_get_loop_prop('total');
    $per_page = wc_get_loop_prop('per_page');
    $current = wc_get_loop_prop('current_page');
    $first = ($per_page * $current) - $per_page + 1;
    $last = min($total, $per_page * $current);
    // @TODO solve this properly :)
    if ($last === 0) {
        $first = 0;
    }
    $tpl = '<p class="woocommerce-result-count">Prikazano %s-%s od %s rezultata.</p>';
    if (isset($_GET['query'])) {
        $tpl = '<p class="woocommerce-result-count">Prikazano %s-%s od %s rezultata za upit: <strong>'. $_GET['query'] .'</strong></p>';
    }

    echo sprintf($tpl, $first, $last, $total);
}


remove_action('woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10);
remove_action('woocommerce_archive_description', 'woocommerce_product_archive_description', 10);
add_action('woocommerce_archive_description', 'gf_archive_description', 10);
function gf_archive_description()
{
    global $wp_query;
    $cat_id = $wp_query->get_queried_object_id();
    $cat_desc = term_description($cat_id, 'product_cat');
    if ($cat_desc !== '') {
        echo '<div class="gf-archive-description-wrapper">';
        echo '<div class="row"><div class="gf-archive-description-button">Opis</div></div>';
        echo '<div class="row gf-archive-description">' . $cat_desc . '</div>';
        echo '</div>';
    }
}

