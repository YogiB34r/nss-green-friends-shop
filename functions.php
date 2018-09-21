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
function gf_get_categories($exlcude = array())
{
    $args = array(
        'orderby' => 'name',
        'order' => 'asc',
        'hide_empty' => false,
        'exclude' => $exlcude,
    );
    $product_cats = get_terms('product_cat', $args);
    return $product_cats;
}

function gf_get_top_level_categories($exclude = array())
{
    $top_level_categories = [];
    $param = '';
    if (isset($exclude)) {
        $param = $exclude;
    }
    foreach (gf_get_categories($param) as $category) {
        if (!$category->parent) {
            $top_level_categories[] = $category;
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
        } elseif (in_array($category->parent, $top_level_ids)) {
            $second_level_categories[] = $category;
        }
    }
    return $second_level_categories;
}

function gf_get_third_level_categories($parent_id = null)
{
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

//Testira razliku array-a order sensitive
function gf_array_reccursive_difrence(array $array1, array $array2, array $_ = null)
{
    $diff = [];
    $args = array_slice(func_get_args(), 1);
    foreach ($array1 as $key => $value) {
        foreach ($args as $item) {
            if (is_array($item)) {
                if (array_key_exists($key, $item)) {
                    if (is_array($value) && is_array($item[$key])) {
                        $tmpDiff = gf_array_reccursive_difrence($value, $item[$key]);

                        if (!empty($tmpDiff)) {
                            foreach ($tmpDiff as $tmpKey => $tmpValue) {
                                if (isset($item[$key][$tmpKey])) {
                                    if (is_array($value[$tmpKey]) && is_array($item[$key][$tmpKey])) {
                                        $newDiff = array_diff($value[$tmpKey], $item[$key][$tmpKey]);
                                    } else if ($value[$tmpKey] !== $item[$key][$tmpKey]) {
                                        $newDiff = $value[$tmpKey];
                                    }

                                    if (isset($newDiff)) {
                                        $diff[$key][$tmpKey] = $newDiff;
                                    }
                                } else {
                                    $diff[$key][$tmpKey] = $tmpDiff;
                                }
                            }
                        }
                    } else if ($value !== $item[$key]) {
                        $diff[$key] = $value;

                    }
                } else {
                    $diff[$key] = $value;
                }
            }
        }
    }

    return $diff;
}

function gf_insert_in_array_by_index($array, $index, $val)
{
    $size = count($array); //because I am going to use this more than one time
    if (!is_int($index) || $index < 0 || $index > $size) {
        return -1;
    } else {
        $temp = array_slice($array, 0, $index);
        $temp[] = $val;
        return array_merge($temp, array_slice($array, $index, $size));
    }
}

function gf_check_level_of_category($cat_id)
{
    $result = null;
    $top_level_ids = [];
    $second_level_ids = [];
    $third_level_ids = [];
    foreach (gf_get_top_level_categories() as $category) {
        $top_level_ids[] = $category->term_id;
    }
    foreach (gf_get_second_level_categories() as $category) {
        $second_level_ids[] = $category->term_id;
    }
    foreach (gf_get_third_level_categories() as $category) {
        $third_level_ids[] = $category->term_id;
    }
    if (in_array($cat_id, $top_level_ids)) {
        $result = 1;
    }
    if (in_array($cat_id, $second_level_ids)) {
        $result = 2;
    }
    if (in_array($cat_id, $third_level_ids)) {
        $result = 3;
    }
    return $result;
}

//add_filter('pre_get_posts', 'order_by_stock_status');
function order_by_stock_status($posts_clauses)
{
    global $wpdb;

    // only change query on WooCommerce loops
    if (is_woocommerce() && (is_shop() || is_product_category() || is_product_tag())) {
        $posts_clauses['join'] .= " INNER JOIN $wpdb->postmeta istockstatus ON ($wpdb->posts.ID = istockstatus.post_id) ";
        $posts_clauses['orderby'] = " istockstatus.meta_value ASC, " . $posts_clauses['orderby'];
        $posts_clauses['where'] = " AND istockstatus.meta_key = '_stock_status' AND istockstatus.meta_value <> '' " . $posts_clauses['where'];
    }
    return $posts_clauses;
}

/**
 * Saves uploads into folders organized by day.
 *
 * @param $uploads
 * @return mixed
 */
function upload_dir_filter($uploads)
{
    //$day = date('d');
    $day = date('d/i');
    $uploads['path'] .= '/' . $day;
    $uploads['url'] .= '/' . $day;

    return $uploads;
}

add_filter('upload_dir', 'upload_dir_filter');

//ne brisati ovo zatrebace mozda <3 Vlada
//function advanced_search_query($query) {
//
//    if($query->is_search()) {
//        // category terms search.
//        if (isset($_GET['search-checkbox']) && !empty($_GET['search-checkbox']) && $_GET['search-checkbox'] != 'shop') {
//            $query->set('tax_query', array(array(
//                'taxonomy' => 'product_cat',
//                'field' => 'slug',
//                'terms' => array($_GET['search-checkbox']))
//            ));
//        }
//    }
//    return $query;
//}
//add_action('pre_get_posts', 'advanced_search_query', 1000);


//add_action( 'pre_get_posts', function ( $q ) {
//    if (   !is_admin()                 // Target only front end
//        && $q->is_main_query()        // Only target the main query
//        && $q->is_post_type_archive() // Change to suite your needs
//    ) {
//        $q->set( 'meta_key', '_stock_status' );
//        $q->set( 'orderby',  'meta_value'    );
//        $q->set( 'order',    'ASC'           );
//        $q->set( 'orderby',  'date'    );
//        $q->set( 'order',    'ASC'           );
//    }
//}, PHP_INT_MAX );

//
//add_filter('posts_clauses', 'order_by_test');
//function order_by_test($posts_clauses)
//{
//    global $wpdb;
//    // only change query on WooCommerce loops
////    if (is_woocommerce() && (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy())) {
//    if (is_shop() || is_product_category()) {
//        $posts_clauses['join'] .= " INNER JOIN $wpdb->postmeta istockstatus ON ($wpdb->posts.ID = istockstatus.post_id) ";
//        $posts_clauses['orderby'] = " istockstatus.meta_value ASC, " . $posts_clauses['orderby'];
//        $posts_clauses['where'] = " AND istockstatus.meta_key = '_stock_status' AND istockstatus.meta_value <> '' " . $posts_clauses['where'];
//    }
//    return $posts_clauses;
//}

// custom breadcrumbs based on wc breadcrumbs
function woocommerce_breadcrumb($args = array())
{
    $args = wp_parse_args($args, apply_filters('woocommerce_breadcrumb_defaults', array(
        'delimiter' => '&nbsp;&#47;&nbsp;',
        'wrap_before' => '<nav class="woocommerce-breadcrumb" ' . (is_single() ? 'itemprop="breadcrumb"' : '') . '>',
        'wrap_after' => '</nav>',
        'before' => '',
        'after' => '',
        'home' => _x('Home', 'breadcrumb', 'woocommerce')
    )));

    $breadcrumbs = new gf_breadcrumbs();

    if ($args['home']) {
        $breadcrumbs->add_crumb($args['home'], apply_filters('woocommerce_breadcrumb_home_url', home_url()));
    }

    $args['breadcrumb'] = $breadcrumbs->generate();

    wc_get_template('global/breadcrumb.php', $args);
}

//print all enqued styles
function gf_print_styles()
{
    $result = [];
    $result['scripts'] = [];
    $result['styles'] = [];

    // Print all loaded Scripts
    global $wp_scripts;
    foreach ($wp_scripts->queue as $script) :
        $result['scripts'][] = $wp_scripts->registered[$script]->src . ";";
    endforeach;
    //Print all loaded Styles
    global $wp_styles;
    foreach ($wp_styles->queue as $style) :
        $result['styles'][] = $wp_styles->registered[$style]->src . ";";
    endforeach;

    return $result;
}


function custom_woo_product_loop()
{
    if (is_shop() || is_product_category() || is_product_tag()) { // Only run on shop archive pages, not single products or other pages
        global $wpdb;
        $allIds = [];
        $per_page = apply_filters('loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page());

        if (get_query_var('taxonomy') === 'product_cat') { // Za kategorije
            $category = get_term_by('slug', get_query_var('term'), 'product_cat');

            $sql = "SELECT postId FROM wp_gf_products WHERE salePrice > 0 AND stockStatus = 1 AND status = 1
                AND categoryIds LIKE '%{$category->term_id}%' ORDER BY createdAt";
            $productsSale = $wpdb->get_results($sql, OBJECT_K);

            $sql = "SELECT postId FROM wp_gf_products WHERE salePrice = 0 AND stockStatus = 1 AND status = 1
                AND categoryIds LIKE '%{$category->term_id}%' ORDER BY createdAt";
            $productsNotOnSale = $wpdb->get_results($sql, OBJECT_K);
            $allIds = array_merge(array_keys($productsSale), array_keys($productsNotOnSale));

            $sql = "SELECT postId FROM wp_gf_products WHERE stockStatus = 0 AND status = 1
                AND categoryIds LIKE '%{$category->term_id}%' ORDER BY createdAt";
            $productsOutOfStock = $wpdb->get_results($sql, OBJECT_K);
            $allIds = array_merge($allIds, array_keys($productsOutOfStock));

            $args =array(
                'post_type' => 'product',
                'orderby' => 'post__in',
                'post__in' => $allIds,
                'posts_per_page' => $per_page,
                'paged' => (get_query_var('paged')) ? get_query_var('paged') : 1,
            );
            $sortedProducts = new WP_Query($args);
            if ($sortedProducts->have_posts()) :
                while ($sortedProducts->have_posts()) : $sortedProducts->the_post();
                    do_action('woocommerce_shop_loop');
                    wc_get_template_part('content', 'product');
                endwhile;
                wp_reset_postdata();
            endif;
        } else { // Za main shop
            //@TODO add category
            $sql = "SELECT postId FROM wp_gf_products WHERE salePrice > 0 AND stockStatus = 1 AND status = 1";
            $productsSale = $wpdb->get_results($sql);
            foreach ($productsSale as $post) {
                $allIds[] = $post->ID;
            }

            $sql = "SELECT postId FROM wp_gf_products WHERE salePrice > 0 AND stockStatus = 1 AND status = 1";
            $productsNotOnSale = $wpdb->get_results($sql);
            foreach ($productsNotOnSale as $post) {
                $allIds[] = $post->ID;
            }

            $sql = "SELECT postId FROM wp_gf_products WHERE stockStatus = 0 AND status = 1";
            $productsOutOfStock = $wpdb->get_results($sql);
            foreach ($productsOutOfStock as $post) {
                $allIds[] = $post->ID;
            }
            $args = array(
                'post_type' => 'product',
                'orderby' => 'post__in',
                'post__in' => $allIds,
                'posts_per_page' => $per_page,
                'paged' => (get_query_var('paged')) ? get_query_var('paged') : 1,
            );
            $sortedProducts = new WP_Query($args);
            if ($sortedProducts->have_posts()) :
                while ($sortedProducts->have_posts()) : $sortedProducts->the_post();
                    do_action('woocommerce_shop_loop');
                    wc_get_template_part('content', 'product');
                endwhile;
                wp_reset_postdata();
            endif;
        }
    } else { //za ostale page-eve
        woocommerce_content();
    }
}

function gf_custom_search_output($sortedProducts)
{
    if ($sortedProducts->have_posts()) :
        wc_setup_loop();
        while ($sortedProducts->have_posts()) : $sortedProducts->the_post();
            do_action('woocommerce_shop_loop');
            wc_get_template_part('content', 'product');
        endwhile;
        wp_reset_postdata();
    endif;
}

function parseAttributes()
{
//    var_dump(get_terms( 'pa_boja' ));
//    var_dump(get_terms( 'pa_velicina' ));
    $atributes = [];
    foreach (get_terms( 'pa_boja' ) as $term) {
        $atributes[] = rtrim($term->name, 'aeiou');
    }
    foreach (get_terms( 'pa_velicina' ) as $term) {
        $atributes[] = rtrim($term->name, 'aeiou');
    }
    return $atributes;
}

function gf_custom_search($input)
{
    //@TODO cleanup input
    $input = $input;

    global $wpdb;
    $per_page = apply_filters('loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page());

    //@TODO add category

    $searchCondition = "";
    $customOrdering = "";
    $explodedInput = explode(' ', $input);
    $attributes = parseAttributes();
    $limiter = 0;
    foreach ($explodedInput as $key => $word) {
        if (strlen($word) > 3) {
            $limiter++;
            //query is attribute
            if (in_array(rtrim($word, 'aeiou'), $attributes)) {
//                $word = rtrim($word, 'aeiou');
                if ($key > 0) {
                    $searchCondition .= " OR ";
                    $customOrdering .= " + ";
                }
                $searchCondition .= " MATCH(attributes) AGAINST('{$word}') ";
                $customOrdering .= "
                CASE
                    WHEN MATCH(productName) AGAINST('{$word}') THEN 15
                    ELSE 0
                END +
                CASE WHEN MATCH(description) AGAINST('{$word}') THEN 10 ELSE 0 END 
                ";
            } else {
                if ($key > 0) {
                    $searchCondition .= " OR ";
                    $customOrdering .= " + ";
                }
                $searchCondition .= " productName LIKE '%{$word}%' OR MATCH(description) AGAINST('{$word}') 
                OR attributes LIKE ('%{$word}%') OR categories LIKE ('%{$word}%')";
                $customOrdering .= "
                CASE
                    WHEN productName LIKE '% {$word} %' THEN 15
                    WHEN productName LIKE '%{$word}%' THEN 10
                    ELSE 0
                END
                + CASE
                    WHEN categories LIKE ('%{$word}%') THEN 10 ELSE 0
                END
                + CASE
                    WHEN MATCH(description) AGAINST('{$word}') THEN 4 ELSE 0
                END
                + CASE WHEN attributes LIKE ('%{$word}%') THEN 10 ELSE 0 END ";
            }

//            $searchCondition .= " productName LIKE '%{$word}%' OR description LIKE '%{$word}%'
//                OR attributes LIKE '%{$word}%' OR categories LIKE '%{$word}%'";
//            $customOrdering .= "
//            CASE
//                WHEN productName LIKE '% {$word} %' THEN 5
//                WHEN productName LIKE '%{$word}%' THEN 4
//                ELSE 0
//            END
//            + CASE
//                WHEN categories LIKE '%{$word}%' THEN 7
//                ELSE 0
//            END
//            + CASE
//                WHEN description LIKE '% {$word} %' THEN 3
//                WHEN description LIKE '%{$word}%' THEN 1
//                ELSE 0
//            END
//            + CASE WHEN attributes LIKE '%{$word}%' THEN 7 ELSE 0 END ";
        }
    }
    $limiter = $limiter * 7;

    echo $sql = "SELECT 
        postId,
        {$customOrdering}  as o
        FROM wp_gf_products
        WHERE stockStatus = 1 
        AND status = 1
        AND ({$searchCondition}) 
        HAVING o > {$limiter}
        ORDER BY o DESC, createdAt DESC";

    $products = $wpdb->get_results($sql, OBJECT_K);
    $allIds = array_keys($products);
    $resultCount = count($allIds);
    if ($resultCount === 0) {
        return false;
    }

    $currrentPage = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $args = array(
        'post_type' => 'product',
        'orderby' => 'post__in',
        'post__in' => $allIds,
        'posts_per_page' => $per_page,
        'paged' => $currrentPage,
    );

    wc_set_loop_prop('total', $resultCount);
    wc_set_loop_prop('per_page', $per_page);
    wc_set_loop_prop('current_page', $currrentPage);
    wc_set_loop_prop('total_pages', ceil($resultCount / $per_page));
    $sortedProducts = new WP_Query($args);

    return $sortedProducts;
}

function custom_woo_product_loop_backup()
{
    if (is_shop() || is_product_category() || is_product_tag()) { // Only run on shop archive pages, not single products or other pages
        $per_page = apply_filters('loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page());;
        if (get_query_var('taxonomy')) { // Za kategorije
            $allIds = [];
            $args = array(
                'post_type' => 'product',
                'orderby' => 'date',
                'order' => 'ASC',
                'posts_per_page' => -1,
                'paged' => (get_query_var('paged')) ? get_query_var('paged') : 1,
                'tax_query' => array(
                    array(
                        'taxonomy' => get_query_var('taxonomy'),
                        'field' => 'slug',
                        'terms' => get_query_var('term'),
                    ),
                    'meta_query' => array(
                        array('relation' => 'OR',
                            array( // Simple products type
                                'key' => '_sale_price',
                                'value' => 0,
                                'compare' => '>',
                                'type' => 'numeric'
                            ),
                            array( // Variable products type
                                'key' => '_min_variation_sale_price',
                                'value' => 0,
                                'compare' => '>',
                                'type' => 'numeric'
                            ),
                        ),
                        array(
                            'relation' => 'AND',
                            array(
                                'key' => '_stock_status',
                                'value' => 'instock',
                                'compare' => '='
                            )
                        )
                    )
                ));
            $productsSale = new WP_Query($args);
            foreach ($productsSale->get_posts() as $post) {
                $allIds[] = $post->ID;
            }

            $args = array(
                'post_type' => 'product',
                'orderby' => 'date',
                'order' => 'ASC',
                'posts_per_page' => -1,
                'paged' => (get_query_var('paged')) ? get_query_var('paged') : 1,
                'tax_query' => array(
                    array(
                        'taxonomy' => get_query_var('taxonomy'),
                        'field' => 'slug',
                        'terms' => get_query_var('term'),
                    ),
                    'meta_query' => array(
                        array('relation' => 'OR',
                            array( // Simple products type
                                'key' => '_sale_price',
                                'value' => '',
                                'compare' => '=',
                                'type' => 'char'
                            ),
                            array( // Variable products type
                                'key' => '_min_variation_sale_price',
                                'value' => '',
                                'compare' => '=',
                                'type' => 'char'
                            ),
                        ),
                        array(
                            'relation' => 'AND',
                            array(
                                'key' => '_stock_status',
                                'value' => 'instock',
                                'compare' => '='
                            )
                        )
                    )
                ));
            $productsNotOnSale = new WP_Query($args);
            foreach ($productsNotOnSale->get_posts() as $post) {
                $allIds[] = $post->ID;
            }
            $args = array(
                'post_type' => 'product',
                'orderby' => 'date',
                'order' => 'ASC',
                'posts_per_page' => -1,
                'paged' => (get_query_var('paged')) ? get_query_var('paged') : 1,
                'tax_query' => array(
                    array(
                        'taxonomy' => get_query_var('taxonomy'),
                        'field' => 'slug',
                        'terms' => get_query_var('term'),
                    ),
                    'meta_query' => array(
                        'key' => '_stock_status',
                        'value' => 'outofstock',
                        'compare' => '='
                    )
                ));
            $productsOutOfStock = new WP_Query($args);
            foreach ($productsOutOfStock->get_posts() as $post) {
                $allIds[] = $post->ID;
            }
            $args = array(
                'post_type' => 'product',
                'orderby' => 'post__in',
                'post__in' => $allIds,
                'posts_per_page' => $per_page,
                'paged' => (get_query_var('paged')) ? get_query_var('paged') : 1,
            );
            $sortedProducts = new WP_Query($args);
            if ($sortedProducts->have_posts()) :
                while ($sortedProducts->have_posts()) : $sortedProducts->the_post();
                    do_action('woocommerce_shop_loop');
                    wc_get_template_part('content', 'product');
                endwhile;
                wp_reset_postdata();
            endif;
        } else { // Za main shop
            $allIds = [];
            $args = array(
                'post_type' => 'product',
                'orderby' => 'date',
                'order' => 'ASC',
                'posts_per_page' => -1,
                'paged' => (get_query_var('paged')) ? get_query_var('paged') : 1,
                'meta_query' => array(
                    array('relation' => 'OR',
                        array( // Simple products type
                            'key' => '_sale_price',
                            'value' => 0,
                            'compare' => '>',
                            'type' => 'numeric'
                        ),
                        array( // Variable products type
                            'key' => '_min_variation_sale_price',
                            'value' => 0,
                            'compare' => '>',
                            'type' => 'numeric'
                        ),
                    ),
                    array(
                        'relation' => 'AND',
                        array(
                            'key' => '_stock_status',
                            'value' => 'instock',
                            'compare' => '='
                        )
                    )
                )
            );
            $productsSale = new WP_Query($args);
            foreach ($productsSale->get_posts() as $post) {
                $allIds[] = $post->ID;
            }
            $args = array(
                'post_type' => 'product',
                'orderby' => 'date',
                'order' => 'ASC',
                'posts_per_page' => -1,
                'paged' => (get_query_var('paged')) ? get_query_var('paged') : 1,
                'meta_query' => array(
                    array('relation' => 'OR',
                        array( // Simple products type
                            'key' => '_sale_price',
                            'value' => '',
                            'compare' => '=',
                            'type' => 'char'
                        ),
                        array( // Variable products type
                            'key' => '_min_variation_sale_price',
                            'value' => '',
                            'compare' => '=',
                            'type' => 'char'
                        ),
                    ),
                    array(
                        'relation' => 'AND',
                        array(
                            'key' => '_stock_status',
                            'value' => 'instock',
                            'compare' => '='
                        )
                    )
                )
            );
            $productsNotOnSale = new WP_Query($args);
            foreach ($productsNotOnSale->get_posts() as $post) {
                $allIds[] = $post->ID;
            }
            $args = array(
                'post_type' => 'product',
                'orderby' => 'date',
                'order' => 'ASC',
                'posts_per_page' => -1,
                'paged' => (get_query_var('paged')) ? get_query_var('paged') : 1,
                'meta_query' => array(
                    'key' => '_stock_status',
                    'value' => 'outofstock',
                    'compare' => '='
                )
            );
            $productsOutOfStock = new WP_Query($args);
            foreach ($productsOutOfStock->get_posts() as $post) {
                $allIds[] = $post->ID;
            }
            $args = array(
                'post_type' => 'product',
                'orderby' => 'post__in',
                'post__in' => $allIds,
                'posts_per_page' => $per_page,
                'paged' => (get_query_var('paged')) ? get_query_var('paged') : 1,
            );
            $sortedProducts = new WP_Query($args);
            if ($sortedProducts->have_posts()) :
                while ($sortedProducts->have_posts()) : $sortedProducts->the_post();
                    do_action('woocommerce_shop_loop');
                    wc_get_template_part('content', 'product');
                endwhile;
                wp_reset_postdata();
            endif;
        }
    } else { //za ostale page-eve
        woocommerce_content();
    }
}




