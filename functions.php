<?php
//ini_set('max_execution_time', '40');
require (__DIR__ . '/inc/autoload.php');
global $wpdb;

$useElastic = true; // create admin setting
if (defined('USE_ELASTIC')) {
    $useElastic = USE_ELASTIC;
}
$searchFunctions = new \Gf\Search\Functions($wpdb, $useElastic);
$wooFunctions = new WooFunctions();
$theme = new \GF\Theme();
$theme->init();


function get_search_category_aggregation() {
    return $GLOBALS['gf-search']['facets']['category'];
}

add_filter('upload_dir', 'upload_dir_filter');
/**
 * Saves uploads into folders organized by day.
 *
 * @param $uploads
 * @return mixed
 */
function upload_dir_filter($uploads)
{
    $day = date('d/i');
    $uploads['path'] .= '/' . $day;
    $uploads['url'] .= '/' . $day;

    return $uploads;
}

//********* infinite scroll START *********

/*
 * load more script ajax hooks
 */
add_action('wp_ajax_nopriv_ajax_script_load_more', 'ajax_script_load_more');
add_action('wp_ajax_ajax_script_load_more', 'ajax_script_load_more');
/*
 * initial posts dispaly
 */
function ajax_infinite_scroll($args)
{
    //initial posts load
    echo '<div id="ajax-primary" class="content-area">';
    echo '<div id="ajax-content" class="content-area">';

    ajax_script_load_more($args);

    $mobile = 'desktop';
    if (wp_is_mobile()) {
        $mobile = 'mobile';
    }

    echo '</div>';
    echo '<a href="#" id="loadMore" class="'.$mobile.'" data-page="1" data-url="' . admin_url("admin-ajax.php") . '" ></a>';
    echo '</div>';
}

/*
 * load more script call back
 */
function ajax_script_load_more($args)
{
    global $searchFunctions;
    $searchFunctions->customShopLoop($args);

    exit();
}

//********* infinite scroll END *********


function gf_get_categories($exlcude = array()) {
    $args = array(
        'orderby' => 'name',
        'order' => 'asc',
        'hide_empty' => false,
        'exclude' => $exlcude,
    );
    $product_cats = get_terms('product_cat', $args);
    return $product_cats;
}

function gf_get_top_level_categories($exclude = array()) {
    $top_level_categories = [];
    foreach (gf_get_categories($exclude) as $category) {
        if (!$category->parent) {
            $top_level_categories[] = $category;
        }
    }
    return $top_level_categories;
}

function gf_get_second_level_categories($parent_id = null) {
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
        } elseif (in_array($category->parent, $top_level_ids)) {
            $second_level_categories[] = $category;
        }
    }
    return $second_level_categories;
}

function gf_get_third_level_categories($parent_id = null) {
    $categories = gf_get_categories();
    $second_level_ids = [];
    foreach (gf_get_second_level_categories() as $cat) {
        $second_level_ids[] = $cat->term_id;
    }
    $third_level_categories = [];
    foreach ($categories as $category) {
        if ($parent_id) {
            if ($category->parent == $parent_id) {
                $third_level_categories[] = $category;
            }
        } elseif (in_array($category->parent, $second_level_ids)) {
            $third_level_categories[] = $category;
        }
    }
    return $third_level_categories;
}

function gf_check_level_of_category($cat_id) {
    $cat = get_term_by('id', $cat_id, 'product_cat');
    if ($cat->parent === 0){
        return 1;
    } else {
        if (get_term($cat->parent, 'product_cat')->parent === 0){
            return 2;
        } else{
            return 3;
        }
    }
}

function gf_get_category_children_ids($slug) {
    $cat = get_term_by('slug', $slug, 'product_cat');
    $childrenIds = [];
    if ($cat) {
        $catChildren = get_term_children($cat->term_id, 'product_cat');
        $childrenIds[] = $cat->term_id;
        foreach ($catChildren as $child) {
            $childrenIds[] = $child;
        }
    }
    return $childrenIds;
}

add_filter('request', 'customRequestOverride');
/**
 * Prevent main wp query from returning 404 page on a category page when it thinks there are no more results.
 *
 * @param $query_string
 * @return mixed
 */
function customRequestOverride($query_string)
{
    if (isset($query_string['page']) && $query_string['page'] !== '' && isset($query_string['name'])) {
        unset($query_string['name']);
    }
    return $query_string;
}



//@TODO Custom admin product table
//require(__DIR__ . '/templates/admin/search-settings.php');
//require(__DIR__ . '/templates/admin/list-product-search-settings.php');
