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
            $slugArray[]= $category->slug;
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
