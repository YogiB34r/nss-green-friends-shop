<?php
//ini_set('upload_max_size', '128M');
//ini_set('post_max_size', '128M');
//ini_set('max_execution_time', '80');
ini_set('max_execution_time', '40');


require (__DIR__ . '/inc/autoload.php');

add_action('after_setup_theme', 'wc_support');
function wc_support()
{
    add_theme_support('title-tag');
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('yoast-seo-breadcrumbs');
}

//add_action('after_setup_theme', 'require_on_init');
//function require_on_init()
//{
//    foreach (glob(get_stylesheet_directory() . "/inc/*.php") as $file) {
//        require $file;
//    }
//}

//@TODO Custom admin product table
//require(__DIR__ . '/templates/admin/search-settings.php');
//require(__DIR__ . '/templates/admin/list-product-search-settings.php');



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
//========================WOOCOMMERCE==============================
$wooFunctions = new WooFunctions();

/**
 * custom breadcrumbs based on wc breadcrumbs
 *
 * @param array $args
 */
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


function woocommerce_pagination()
{
    $args = array(
        'total' => wc_get_loop_prop('total_pages'),
        'current' => wc_get_loop_prop('current_page'),
        'base' => esc_url_raw(add_query_arg('product-page', '%#%', false)),
        'format' => '?product-page=%#%',
    );

    if (!wc_get_loop_prop('is_shortcode')) {
        $args['format'] = '';
        $args['base'] = esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false))));
    }
    wc_get_template('loop/pagination.php', $args);
}

/**
 * @param \Elastica\ResultSet $products
 */
function gf_custom_shop_loop(\Elastica\ResultSet $products)
{
    $html = '';

    $i = 0;
    foreach ($products->getResults() as $productData) {
        $productId = $productData->postId;
        $product = new \Nss\Feed\Product($productData->getData());
        $saved_price = $product->getRegularPrice() - $product->getSalePrice();
        $price = $product->getRegularPrice();
        if ($product->getSalePrice() > 0) {
            $price = $product->getSalePrice();
        }
        $saved_percentage = 0;
        if ($saved_price > 0 && $product->getSalePrice() > 0) {
            $saved_percentage = number_format($saved_price * 100 / $product->getRegularPrice(), 2);
        }

        $classes = '';
        if ($saved_percentage > 0 && $product->getStockStatus() !== 0) {
            $classes .= ' sale ';
        }
        if ($product->getStockStatus() == 0) {
            $classes .= ' outofstock ';
        }
        // klase koje mozda zatrebaju za <li> 'instock sale shipping-taxable purchasable product-type-simple'
        $classes .= " instock ";
        if (!$product->getStockStatus()) {
            $classes = " outofstock ";
        }
        if ($product->getSalePrice() > 0) {
            $classes .= " sale ";
        }
        if ($i === 0) {
            $classes .= " first ";
        }

        $classes .= " product type-product status-publish has-post-thumbnail shipping-taxable purchasable  ";
        $html .= '<li class="product-type-' . $product->getType() . $classes . '">';
        $html .= '<a href=" ' . $product->dto['permalink'] . ' " title=" ' . $product->getName() . ' ">';
        $html .= add_stickers_to_products_on_sale($classes, $productId);
//        woocommerce_show_product_sale_flash('', '', '', $classes);
//        add_stickers_to_products_new($product);
        $html .= $product->dto['thumbnail'];
        ob_start();
        add_stickers_to_products_soldout($classes);
        $html .= ob_get_clean();
//        $html .= add_stickers_to_products_soldout($classes);
        $html .= '</a>';
        $html .= '<a href="' . $product->dto['permalink'] . '" title="' . $product->getName() . '">';
        $html .= '<h3>' . $product->getName() . '</h3>';
        $html .= '</a>';
        $html .= '<span class="price">';
        if ($saved_percentage > 0) {
            $html .= '<del><span class="woocommerce-Price-amount amount">' . $product->getRegularPrice()
                . '<span class="woocommerce-Price-currencySymbol">din.</span></span></del>';
            $html .= '<ins><span class="woocommerce-Price-amount amount">' . $price .
                '<span class="woocommerce-Price-currencySymbol">din.</span></span></ins>';
            $html .= '<p class="saved-sale">Ušteda: <span class="woocommerce-Price-amount amount">' . $saved_price .
                '<span class="woocommerce-Price-currencySymbol">din.</span></span> <em> (' . $saved_percentage . '%)</em></p>';
        } else {
            $html .= '<ins><span class="woocommerce-Price-amount amount">' . $product->getRegularPrice()
                . '<span class="woocommerce-Price-currencySymbol">din.</span></span></ins>';
        }
        $html .= '</span>';
        $html .= '<p class="loop-short-description">' . $product->getShortDescription() . '</p>';
        $html .= '</li>';
        $i++;
    }
    $html .= '</ul>';

    echo $html;
}


// ===========   WOOCOMMERCE END==========================================

/**
 * Custom loop that works with wp query
 *
 * @param WP_Query $sortedProducts
 */
function gf_custom_search_output(WP_Query $sortedProducts)
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
    $atributes = unserialize($redis->redis->get('attributes-collection'));
    if ($atributes === false) {
        $atributes = [];
        foreach (get_terms('pa_boja') as $term) {
//            $atributes[] = rtrim($term->name, 'aeiou');
            $atributes[] = $term->name;
        }
        foreach (get_terms('pa_velicina') as $term) {
//            $atributes[] = rtrim($term->name, 'aeiou');
            $atributes[] = $term->name;
        }
        $redis->redis->set('attributes-collection', serialize($atributes));
    }

    return $atributes;
}

/**
 * Parses array of post ids and fetches them via wp query to prepare for loop.
 *
 * @param $allIds
 * @return bool|WP_Query
 */
function gf_parse_post_ids_for_list($allIds)
{
    $per_page = apply_filters('loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page());
    if (isset($_POST['ppp'])) {
        $per_page = ($_POST['ppp'] > 48) ? 48 : $_POST['ppp'];
    }
    $currentPage = (get_query_var('paged')) ? get_query_var('paged') : 1;

    $resultCount = count($allIds);
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
        'suppress_filters' => true,
        'no_found_rows' => true
    );

    wc_set_loop_prop('total', $resultCount);
    wc_set_loop_prop('per_page', $per_page);
    wc_set_loop_prop('current_page', $currentPage);
    wc_set_loop_prop('total_pages', $totalPages);
    $sortedProducts = new WP_Query($args);

    return $sortedProducts;
}

function gf_ajax_view_count($postId)
{
    $key = 'post-view-count#' . $postId;
    $cache = new GF_Cache();
    $count = (int)$cache->redis->get($key);
    if ($count == 10) {
        global $wpdb;
        $wpdb->query("UPDATE wp_gf_products SET viewCount = viewCount + {$count} WHERE postId = {$postId}");
        $cache->redis->set($key, 0);
    } else {
        $count++;
        $cache->redis->set($key, $count);
    }
    echo 1;
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

add_filter('request', 'custom_request');
/**
 * Prevent main wp query from returning 404 page on a category page when it thinks there are no more results.
 *
 * @param $query_string
 * @return mixed
 */
function custom_request($query_string)
{
    if (isset($query_string['page'])) {
        if ($query_string['page'] !== '') {
            if (isset($query_string['name'])) {
                unset($query_string['name']);
            }
        }
    }
    return $query_string;
}

add_action('wp_print_scripts', 'iconic_remove_password_strength', 10);
function iconic_remove_password_strength()
{
    wp_dequeue_script('wc-password-strength-meter');
}

// prevent bug with members plugin
add_filter('members_check_parent_post_permission', function () {
    return false;
});


//@TODO Make it work
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
    $ajax = false;
    //check ajax call or not
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $ajax = true;
    }

    gf_custom_shop_loop($args);

    //check ajax call
    if ($ajax) die();
}

function ajax_script_load_more_backup($args)
{
    //init ajax
    $ajax = false;
    //check ajax call or not
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $ajax = true;
    }
    //number of posts per page default
    $num = 4;
    //page number
    $paged = $_POST['page'] + 1;
    //args
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => $num,
        'paged' => $paged
    );
    //query
    $query = new WP_Query($args);
    var_dump($_POST);
    //check
    if ($query->have_posts()):
        //loop articales
        while ($query->have_posts()): $query->the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title('<h3 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h3>'); ?>
                </header>
            </article>
        <?php endwhile;
    else:
        echo 0;
    endif;
    //reset post data
    wp_reset_postdata();
    //check ajax call
    if ($ajax) die();
}


//    if ($typenow == 'product' && !empty($_GET['product_supplier_filter'])) {
//        $query->query_vars['meta_key'] = 'supplier';
//        $query->query_vars['meta_value'] = $_GET['product_supplier_filter'];
//    }
//}
//admin product list filter by supplier END

//*
// * create short code.
// */
//add_shortcode('ajax_posts', 'script_load_more');

//********* infinite scroll END *********


