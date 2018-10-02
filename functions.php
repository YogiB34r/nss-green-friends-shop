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
    foreach (gf_get_categories($exclude) as $category) {
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

function parseOrderBy()
{
    $order = (isset($_GET['orderby'])) ? $_GET['orderby'] : 'date';
    switch ($order) {
        //@TODO implement view count
        case 'popularity':
//            $orderBy = " ORDER BY viewCount DESC ";
            $orderBy = " createdAt DESC ";

            break;

        //@TODO add sync for ratings
        case 'rating':
//            $orderBy = " ORDER BY rating DESC ";
            $orderBy = " createdAt DESC ";

            break;

        case 'date':
            $orderBy = " createdAt DESC ";

            break;

        case 'price-desc':
            $orderBy = " priceOrder DESC ";

            break;

        case 'price':
            $orderBy = " priceOrder ";

            break;

        default:
            $orderBy = " createdAt DESC ";

            break;
    }
    return $orderBy;
}

function gf_get_category_query()
{
    global $wpdb;
    $cat = get_term_by('slug', get_query_var('term'), 'product_cat');
    $per_page = apply_filters('loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page());
    if (isset($_POST['ppp'])) {
        $per_page = ($_POST['ppp'] > 48) ? 48 : $_POST['ppp'];
    }
    $orderBy = parseOrderBy();
    $searchCondition = " 1=1 ";
    $customOrdering = " 1=1 ";
    if (isset($_GET['query'])) {
        $searchCondition = "";
        $customOrdering = "";
        $input = addslashes($_GET['query']);
        $explodedInput = explode(' ', $input);
        $gradeCount = 0;
        foreach ($explodedInput as $key => $word) {
            if ($key > 0) {
                $searchCondition .= " OR ";
                $customOrdering .= " + ";
            }
            $searchCondition .= " productName LIKE '%{$word}%' OR description LIKE '%{$word}%' 
                OR attributes LIKE '%{$word}%'";
            $customOrdering .= "
                CASE
                    WHEN productName LIKE '% {$word} %' THEN 16
                    WHEN productName LIKE '{$word} %' THEN 15
                    WHEN productName LIKE '{$word}%' THEN 12
                    WHEN productName LIKE '%{$word}%' THEN 9
                    ELSE 0
                END
                + CASE
                    WHEN categories LIKE '%{$word}%' THEN 14 ELSE 0
                END
                + CASE
                    WHEN description LIKE '%{$word}%' THEN 4 ELSE 0
                END
                + CASE WHEN attributes LIKE '%{$word}%' THEN 13 ELSE 0 END ";
        }
        $customOrdering .= " as o ";
        $orderBy .= " , o DESC ";
        $gradeCount = $gradeCount * 7;
    }

    $priceOrdering = " CASE
                WHEN salePrice > 0 THEN salePrice
                ELSE regularPrice 
            END as priceOrder ";

    $priceCondition = "";
    if (isset($_GET['min_price'])) {
        $minPrice = (int)$_GET['min_price'];
        $maxPrice = (int)$_GET['max_price'];
        $priceCondition = " HAVING priceOrder >= {$minPrice} AND priceOrder <= {$maxPrice} ";
    }

    $sql = "SELECT postId, {$priceOrdering}, {$customOrdering} FROM wp_gf_products WHERE salePrice > 0 AND stockStatus = 1 AND status = 1
                AND categories LIKE '%{$cat->name}%' AND categoryIds LIKE '%{$cat->term_id}%'
                AND ({$searchCondition})
                {$priceCondition} 
                ORDER BY $orderBy ";
    $productsSale = $wpdb->get_results($sql, OBJECT_K);

    $sql = "SELECT postId, {$priceOrdering}, {$customOrdering} FROM wp_gf_products WHERE salePrice = 0 AND stockStatus = 1 AND status = 1
                AND categories LIKE '%{$cat->name}%' AND categoryIds LIKE '%{$cat->term_id}%' 
                AND ({$searchCondition})
                {$priceCondition} ORDER BY $orderBy ";
    $productsNotOnSale = $wpdb->get_results($sql, OBJECT_K);
    $allIds = array_merge(array_keys($productsSale), array_keys($productsNotOnSale));

    $sql = "SELECT postId, {$priceOrdering}, {$customOrdering} FROM wp_gf_products WHERE stockStatus = 0 AND status = 1
                AND categories LIKE '%{$cat->name}%' AND categoryIds LIKE '%{$cat->term_id}%' 
                AND ({$searchCondition})
                {$priceCondition} ORDER BY $orderBy ";
    $productsOutOfStock = $wpdb->get_results($sql, OBJECT_K);
    $allIds = array_merge($allIds, array_keys($productsOutOfStock));
    $currentPage = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $resultCount = count($allIds);
    if ($resultCount === 0) {
        $allIds[] = 0;
    }
    $totalPages = ceil($resultCount / $per_page);
    if ($currentPage > $totalPages) {
        $currentPage = $totalPages;
    }
    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
    $args = array(
        'post_type' => 'product',
        'orderby' => 'post__in',
        'post__in' => $allIds,
        'posts_per_page' => $per_page,
        'paged' => $paged,
        'suppress_filters' => true,
        'no_found_rows' => true
    );
    $sortedProducts = new WP_Query($args);

    wc_set_loop_prop('total', $resultCount);
    wc_set_loop_prop('per_page', $per_page);
    wc_set_loop_prop('current_page', $currentPage);
    wc_set_loop_prop('total_pages', $totalPages);

    return $sortedProducts;
}

function custom_woo_product_loop($sortedProducts)
{
    if (is_tax() || is_product_category() || is_product_tag()) {
        global $wpdb;

        if (get_query_var('taxonomy') === 'product_cat') { // Za kategorije
            if ($sortedProducts->have_posts()) :
                while ($sortedProducts->have_posts()) : $sortedProducts->the_post();
//                    do_action('woocommerce_shop_loop');
                    wc_get_template_part('content', 'product');
                endwhile;
                wp_reset_postdata();
            endif;
        } else { // Za main shop
//            echo 'other page';
        }
    } else { //za ostale page-eve
        woocommerce_content();
    }
}

function gf_custom_search_output($sortedProducts)
{
    if ($sortedProducts->have_posts()):
//        global $sw;
        wc_setup_loop();
        woocommerce_product_loop_start();
        while ($sortedProducts->have_posts()) :
            $sortedProducts->the_post();
//            do_action('woocommerce_shop_loop');
//            $sw->start('wc_get_template_part');
            wc_get_template_part('content', 'product');
//            $sw->stop('wc_get_template_part');
        endwhile;
//        $sw->start('loop_end');
        wp_reset_postdata();
        woocommerce_product_loop_end();
//        $sw->stop('loop_end');
    endif;
}

function parseAttributes()
{
    $redis = new GF_Cache();
    $atributes = unserialize($redis->redis->get('atributes-collection'));
    if ($atributes === false) {
        $atributes = [];
        foreach (get_terms('pa_boja') as $term) {
            $atributes[] = rtrim($term->name, 'aeiou');
        }
        foreach (get_terms('pa_velicina') as $term) {
            $atributes[] = rtrim($term->name, 'aeiou');
        }
        $redis->redis->set('attributes-collection', serialize($atributes));
    }

    return $atributes;
}

//@TODO implement category as filter
function gf_custom_search($input, $limit = 0)
{
    global $wpdb;

    $input = addslashes($input);
    $per_page = apply_filters('loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page());
    if (isset($_POST['ppp'])) {
        $per_page = ($_POST['ppp'] > 48) ? 48 : $_POST['ppp'];
    }
    $currentPage = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $searchCondition = "";
    $customOrdering = "";
    $explodedInput = explode(' ', $input);
    $attributes = parseAttributes();
    $gradeCount = 0;
    foreach ($explodedInput as $key => $word) {
        if (strlen($word) > 2) {
            $gradeCount++;
            //query is attribute
            if (in_array(rtrim($word, 'aeiou'), $attributes)) {
                if ($key > 0) {
                    $searchCondition .= " OR ";
                    $customOrdering .= " + ";
                }
                $searchCondition .= " attributes LIKE '%{$word}%' ";
                $customOrdering .= "
                CASE
                    WHEN productName LIKE '%{$word}%' THEN 15
                    ELSE 0
                END +
                CASE WHEN description LIKE '%{$word}%' THEN 10 ELSE 0 END 
                ";
            } else {
                $word = rtrim($word, 'aeiou');
                if ($key > 0) {
                    $searchCondition .= " OR ";
                    $customOrdering .= " + ";
                }
                $searchCondition .= " productName LIKE '%{$word}%' OR description LIKE '%{$word}%' 
                OR attributes LIKE '%{$word}%' OR categories LIKE '%{$word}%'";
                $customOrdering .= "
                CASE
                    WHEN productName LIKE '% {$word} %' THEN 16
                    WHEN productName LIKE '{$word} %' THEN 15
                    WHEN productName LIKE '{$word}%' THEN 12
                    WHEN productName LIKE '%{$word}%' THEN 9
                    ELSE 0
                END
                + CASE
                    WHEN categories LIKE '%{$word}%' THEN 14 ELSE 0
                END
                + CASE
                    WHEN description LIKE '%{$word}%' THEN 4 ELSE 0
                END
                + CASE WHEN attributes LIKE '%{$word}%' THEN 13 ELSE 0 END ";
            }
        }
    }
    $priceCondition = "";
    if (isset($_GET['min_price'])) {
        $minPrice = (int)$_GET['min_price'];
        $maxPrice = (int)$_GET['max_price'];
        $priceCondition = " AND priceOrder >= {$minPrice} AND priceOrder <= {$maxPrice} ";
    }

    $gradeCount = $gradeCount * 7;
    $priceOrdering = " CASE
        WHEN salePrice > 0 THEN salePrice
        ELSE regularPrice 
     END as priceOrder ";

    switch (get_query_var('orderby')) {
        //@TODO implement view count
        case 'popularity':
//            $orderBy = " ORDER BY viewCount DESC ";
            $orderBy = " ORDER BY createdAt DESC ";

            break;

        //@TODO add sync for ratings
        case 'rating':
//            $orderBy = " ORDER BY rating DESC ";
            $orderBy = " ORDER BY createdAt DESC ";

            break;

        case 'date':
            $orderBy = " ORDER BY o DESC, createdAt DESC ";

            break;

        case 'price-desc':
            $orderBy = " ORDER BY priceOrder DESC ";

            break;

        case 'price':
            $orderBy = " ORDER BY priceOrder ";

            break;

        default:
            $orderBy = " ORDER BY o DESC, createdAt DESC ";

            break;
    }

    $sql = "SELECT 
        postId,
        {$customOrdering} as o,
        {$priceOrdering}
        FROM wp_gf_products
        WHERE stockStatus = 1 
        AND status = 1
        AND ({$searchCondition}) 
        HAVING o > {$gradeCount}
        {$priceCondition}
        {$orderBy}";
    if ($limit) {
        $sql .= " LIMIT {$limit} ";
    }

//    echo $sql;
    $products = $wpdb->get_results($sql, OBJECT_K);
    $allIds = array_keys($products);
    $resultCount = count($allIds);
    if ($resultCount === 0) {
        $allIds[] = 0;
    }
    if ($resultCount === 0) {
        return false;
    }
    $totalPages = ceil($resultCount / $per_page);
    if ($currentPage > $totalPages) {
        $currentPage = $totalPages;
    }
    $args = array(
        'post_type' => 'product',
        'orderby' => 'post__in',
        'post__in' => $allIds,
        'posts_per_page' => $per_page,
        'paged' => $currentPage,
    );

    wc_set_loop_prop('total', $resultCount);
    wc_set_loop_prop('per_page', $per_page);
    wc_set_loop_prop('current_page', $currentPage);
    wc_set_loop_prop('total_pages', $totalPages);
    $sortedProducts = new WP_Query($args);

    return $sortedProducts;
}

//for loged in users
add_action('wp_ajax_ajax_gf_autocomplete', 'gf_ajax_search_autocomplete');
//for logged out users
add_action('wp_ajax_nopriv_ajax_gf_autocomplete', 'gf_ajax_search_autocomplete');
function gf_ajax_search_autocomplete()
{
    if (isset($_POST['keyword'])) {
        global $wpdb;

        $query = addslashes($_POST['keyword']);

        $cache = new GF_Cache();
        $key = 'category-search-' . md5($query);
        $cat_results = unserialize($cache->redis->get($key));
        if ($cat_results === false || $cat_results === '') {
            $sql_cat = "SELECT `name`,`term_id` FROM wp_terms t JOIN wp_term_taxonomy tt USING (term_id) WHERE t.name LIKE '{$query}%' AND tt.taxonomy = 'product_cat' LIMIT 4";
            $cat_results = $wpdb->get_results($sql_cat);
            if (!empty($cat_results)) {
                $cache->redis->set($key, serialize($cat_results));
            }
        }

//        $sql_product = "SELECT `productName`, `postId` FROM wp_gf_products WHERE `productName` LIKE '%{$keyword}%' LIMIT 4";
//        $product_results = $wpdb->get_results($sql_product);
        $product_results = gf_custom_search($query, 4);

        $html = '';
        if (!empty($cat_results)) {
            $html = '<span>Kategorije</span>';
            $html .= '<ul>';
            foreach ($cat_results as $category) {
                $category_link = get_term_link((int)$category->term_id);
                $html .= '<li><a href="' . $category_link . '">' . $category->name . '</a></li>';
            }
            $html .= '</ul>';
        }

        $html .= '<span>Proizvodi</span>';
        $html .= '<ul>';
        if ($product_results) {
            foreach ($product_results->get_posts() as $post) {
                $product_link = get_permalink((int)$post->ID);
                $html .= '<li><a href="' . $product_link . '">' . $post->post_title . '</a></li>';
            }
        } else {
            $html .= '<li>Nema rezultata</li>';
        }
        $html .= '</ul>';

        echo $html;
    }
}

//for loged in users
//add_action('wp_ajax_ajax_gf_autocomplete', 'gf_ajax_view_count');

//for logged out users
add_action('wp_ajax_nopriv_ajax_gf_view_count', 'gf_ajax_view_count');
function gf_ajax_view_count()
{
    $postId = (int)$_POST['postId'];
    $key = 'post-view-count#' . $postId;
    $cache = new GF_Cache();
    $count = (int)$cache->redis->get($key);
//    if ($count == 10) {
    if ($count > 0) {
        global $wpdb;
        $wpdb->query("UPDATE wp_gf_products SET viewCount = viewCount + {$count} WHERE postId = {$postId}");
        $cache->redis->set($key, 0);
    } else {
        $count++;
        $cache->redis->set($key, $count);
    }
}

function gf_change_supplier_id_by_vendor_id()
{
    $failedMatchIds = [];
    for ($i = 0; $i < 10; $i++) {
        $products_ids = wc_get_products(array(
            'limit' => 3000,
            'return' => 'ids'
        ));
        $users = get_users();
        foreach ($products_ids as $product_id) {
            if (get_post_meta($product_id, 'synced', true) != 1) {
                $supplier_id = (int)get_post_meta($product_id, 'supplier', 'true');
                foreach ($users as $user) {
                    $vendor_id = (int)get_user_meta($user->ID, 'vendorid', true);
                    if ($supplier_id === $vendor_id) {
                        update_post_meta($product_id, 'supplier', $user->ID);
                        add_post_meta($product_id, 'synced', true);
                    }
                }
            }
            if (get_post_meta($product_id, 'synced', true) === '') {
                $failedMatchIds[] = $product_id;
            }
        }
    }
    echo 'Nisu pronadđeni parovi za sledeće proizvode:';
    echo '<ul>';
    foreach ($failedMatchIds as $failedMatchId) {
        echo '<li>' . $failedMatchId . '</li>';
    }
    echo '</ul>';
}


function gf_set_product_categories($product_id, $category_ids)
{
    $product = wc_get_product($product_id);
    $product_categories = $product->get_category_ids();
    $diff = array_diff($category_ids, $product_categories);
    $merge = array_merge($product_categories, $diff);
    $product->set_category_ids($merge);

    //maybe need to save product? $product->save()
}


//Custom addd to cart message
add_filter('wc_add_to_cart_message_html', '__return_null');
add_filter('wc_add_to_cart_message_html', 'gf_custom_add_to_cart_message', 10, 2);
function gf_custom_add_to_cart_message($message)
{
    if (isset($_POST['quantity']) && isset($_POST['add-to-cart'])) {
        $qty = $_POST['quantity'];
        $product_id = $_POST['add-to-cart'];
        $product_title = wc_get_product($product_id)->get_name();
        if ($qty <= 1) {
            $message = '&ldquo;' . $product_title . '&rdquo; je dodat u Vašu korpu.';
        } else {
            $message = $qty . ' &times; ' . '&ldquo;' . $product_title . '&rdquo; je dodat u Vašu korpu.';
        }
    }
    $cart_link = '<a href = "'.wc_get_page_permalink('cart').'" class="button wc-forward" >Pogledaj korpu</a >';
    $message .= $cart_link;

    return $message;

}
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


