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
function gf_display_categories_on_archive_page()
{
    $categories = get_terms([
        'taxonomy' => get_queried_object()->taxonomy,
        'parent' => get_queried_object_id(),
    ]);

    echo '<div class="row gf-category-expander">';
    foreach ($categories as $category) {
        $child_args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'parent' => $category->term_id
        );
        $child_cats = get_terms($child_args);
        echo '<div class="col-3 gf-category-expander__col">
                <a class="gf-category-expander__col__category" href="' . get_term_link($category) . '">' . $category->name . '</a>
                <ul class="gf-expander__subcategory-list">';
        foreach ($child_cats as $child_cat) {
            echo '<li>
                     <a class="gf-category-expander__col__subcategory" href="' . get_term_link($child_cat) . '">' . $child_cat->name . '</a>
                  </li>';
        }
        echo '</ul>
              </div>';
    }
    echo '<div class="gf-category-expander__footer"><span class="fas fa-angle-down"></span></div>
    </div>';
}

remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10);
add_action('woocommerce_before_shop_loop', 'woocommerce_pagination', 30);
