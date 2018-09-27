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
    if (is_product_category()) {
        $categories = get_terms([
            'taxonomy' => get_queried_object()->taxonomy,
            'parent' => get_queried_object_id(),
            'hide_empty' => true
        ]);
        if (count($categories) != 0) {
            echo '<div class="row gf-category-expander">';
            echo '<div class="gf-jos-kategorija"><p>Još kategorija</p></div>';
            $second_lvl_cat_ids = [];
            foreach ($categories as $category) {
                $child_args = array(
                    'taxonomy' => 'product_cat',
                    'parent' => $category->term_id
                );

                $second_lvl_cat_ids[] = $category->term_id;

                $child_cats = get_terms($child_args);
                echo '<div class="col-12 col-sm-6 col-md-3 gf-category-expander__col">';
                echo '<a class="gf-category-expander__col__category" href="' . get_term_link($category) . '">' . $category->name . '</a>
                <ul class="gf-expander__subcategory-list">';
                foreach ($child_cats as $child_cat) {
                    echo '<li>
                     <a class="gf-category-expander__col__subcategory" href="' . get_term_link($child_cat) . '">' . $child_cat->name . '</a>
                  </li>';
                }
                echo '</ul>
              </div>';
            }
            $args = array(
                'taxonomy' => 'product_cat',
                'childless' => 1,
            );
            $childless_cats = get_terms($args);
            $childless_cats_ids = [];
            foreach ($childless_cats as $cat) {
                $childless_cats_ids[] = $cat->term_id;
            }
            $result = false;
            foreach ($second_lvl_cat_ids as $second_lvl_cat_id) {
                if (!in_array($second_lvl_cat_id, $childless_cats_ids)) {
                    $result = true;
                    break;
                }
            }
            echo '<div class="gf-category-expander__footer"><span class="fas fa-angle-down"></span></div>';
            echo '</div>';
        }
    }
}

remove_action('woocommerce_before_shop_loop', 'wc_print_notices', 10);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10);

add_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
add_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 26);
add_action('woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 26);
add_action('woocommerce_before_shop_loop', 'woocommerce_pagination', 27);
add_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 27);

//function wpa_98244_filter_short_description(  ){
//    if (is_shop() || is_product_category()){
//        return '';
//    }
//}
//add_filter( 'woocommerce_short_description', 'wpa_98244_filter_short_description' );