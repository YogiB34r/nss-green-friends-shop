<?php
//ini_set('upload_max_size', '128M');
//ini_set('post_max_size', '128M');
//ini_set('max_execution_time', '300');

require (__DIR__ . DIRECTORY_SEPARATOR . "user.functions.php");
require (__DIR__ . DIRECTORY_SEPARATOR . "/search.functions.php");
require (__DIR__ . DIRECTORY_SEPARATOR . "/util.functions.php");

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

require (__DIR__ . "/inc/Search/AdapterInterface.php");
require (__DIR__ . "/inc/Search/Adapter/MySql.php");
require (__DIR__ . "/inc/Search/Adapter/Elastic.php");
require (__DIR__ . "/inc/Search/Search.php");
require (__DIR__ . "/inc/Search/Elastica/Search.php");
require (__DIR__ . "/inc/CheckoutHelper/CheckoutHelper.php");

add_action('after_setup_theme', 'require_on_init');

add_filter('woocommerce_currency_symbol', 'change_existing_currency_symbol', 10, 2);
function change_existing_currency_symbol($currency_symbol, $currency) {
    $currency_symbol = 'din.';

    return $currency_symbol;
}

add_filter('upload_dir', 'upload_dir_filter');
/**
 * Saves uploads into folders organized by day.
 *
 * @param $uploads
 * @return mixed
 */
function upload_dir_filter($uploads) {
    //$day = date('d');
    $day = date('d/i');
    $uploads['path'] .= '/' . $day;
    $uploads['url'] .= '/' . $day;

    return $uploads;
}

/**
 * custom breadcrumbs based on wc breadcrumbs
 *
 * @param array $args
 */
function woocommerce_breadcrumb($args = array()) {
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

/**
 * print all enqued styles
 *
 * @return array
 */
function gf_print_styles() {
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

/**
 * Custom loop that works with wp query
 *
 * @param WP_Query $sortedProducts
 */
function gf_custom_search_output(WP_Query $sortedProducts) {
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

function parseAttributes() {
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
function gf_parse_post_ids_for_list($allIds) {
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

function gf_set_product_categories($product_id, $category_ids) {
    $product = wc_get_product($product_id);
    $product_categories = $product->get_category_ids();
    $diff = array_diff($category_ids, $product_categories);
    $merge = array_merge($product_categories, $diff);
    $product->set_category_ids($merge);

    //maybe need to save product? $product->save()
}

/**
 * Prevent main wp query from returning 404 page on a category page when it thinks there are no more results.
 *
 * @param $query_string
 * @return mixed
 */
function custom_request($query_string) {
    if (isset($query_string['page'])) {
        if($query_string['page'] !== '') {
            if (isset($query_string['name'])) {
                unset($query_string['name']);
            }
        }
    }
    return $query_string;
}
add_filter('request', 'custom_request');

function gf_custom_shop_loop(\Elastica\ResultSet $products) {
    $html = '';

    foreach ($products->getResults() as $productData){
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
            $classes .= ' outofstock';
        }
        // klase koje mozda zatrebaju za <li> 'instock sale shipping-taxable purchasable product-type-simple'
        $html .= '<li class="product type-product status-publish has-post-thumbnail first instock sale shipping-taxable purchasable product-type-simple">';
        $html .= '<a href=" ' . $product->dto['permalink'] .' " title=" '. $product->getName() .' ">';
        $html .= add_stickers_to_products_on_sale($classes);
//        woocommerce_show_product_sale_flash('', '', '', $classes);
//        add_stickers_to_products_new($product);
        $html .= $product->dto['thumbnail'];
        $html .= add_stickers_to_products_soldout($classes);
        $html .= '</a>';
        $html .= '<a href="'. $product->dto['permalink'] .'" title="'.$product->getName().'">';
        $html .= '<h5>'.$product->getName().'</h5>';
        $html .= '</a>';
        $html .= '<span class="price">';
        if ($saved_percentage > 0) {
            $html .= '<del><span class="woocommerce-Price-amount amount">'.$product->getRegularPrice()
                  .'<span class="woocommerce-Price-currencySymbol">din.</span></span></del>';
            $html .= '<ins><span class="woocommerce-Price-amount amount">'.$price.
                     '<span class="woocommerce-Price-currencySymbol">din.</span></span></ins>';
            $html .= '<p class="saved-sale">Ušteda: <span class="woocommerce-Price-amount amount">'.$saved_price.
                     '<span class="woocommerce-Price-currencySymbol">din.</span></span><em>'.$saved_percentage.'%</em></p>';
        } else {
            $html .= '<ins><span class="woocommerce-Price-amount amount">'.$product->getRegularPrice()
                .'<span class="woocommerce-Price-currencySymbol">din.</span></span></ins>';
        }
        $html .= '</span>';
        $html .= '<p class="loop-short-description">'.$product->getShortDescription().'</p>';
        $html .= '</li>';
    }

    $html .= '</ul>';

    echo $html;
}

/**
 * Display shipping category and price
 */
add_filter('woocommerce_package_rates', 'gf_custom_shipping_rates', 10, 2);
function gf_custom_shipping_rates($rates, $package) {

    if (WC()->cart->cart_contents_weight <= 0.5) {
        if (isset($rates['flat_rate:3']))
            unset(
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);

    } elseif (WC()->cart->cart_contents_weight > 0.5 and WC()->cart->cart_contents_weight <= 2) {
        if (isset($rates['flat_rate:4']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);

    } elseif (WC()->cart->cart_contents_weight > 2 and WC()->cart->cart_contents_weight <= 5) {
        if (isset($rates['flat_rate:5']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);

    } elseif (WC()->cart->cart_contents_weight > 5 and WC()->cart->cart_contents_weight <= 10) {
        if (isset($rates['flat_rate:6']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);

    } elseif (WC()->cart->cart_contents_weight > 10 and WC()->cart->cart_contents_weight <= 20) {
        if (isset($rates['flat_rate:7']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);

    } elseif (WC()->cart->cart_contents_weight > 20 and WC()->cart->cart_contents_weight <= 30) {
        if (isset($rates['flat_rate:8']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);

    } elseif (WC()->cart->cart_contents_weight > 30 and WC()->cart->cart_contents_weight <= 50) {
        if (isset($rates['flat_rate:9']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:10']);

    } elseif (WC()->cart->cart_contents_weight > 50) {
        if (isset($rates['flat_rate:10'])) {
            $cartWeight = WC()->cart->cart_contents_weight;
            $myExtraWeight = $cartWeight - 50;
            $myNewPrice = 500 + (10 * $myExtraWeight);
            $rates['flat_rate:10']->set_cost($myNewPrice);

            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9']);
        }

    }

    return $rates;
}

function woocommerce_pagination() {
    $args = array(
        'total'   => wc_get_loop_prop( 'total_pages' ),
        'current' => wc_get_loop_prop( 'current_page' ),
        'base'    => esc_url_raw( add_query_arg( 'product-page', '%#%', false ) ),
        'format'  => '?product-page=%#%',
    );

    if ( ! wc_get_loop_prop( 'is_shortcode' ) ) {
        $args['format'] = '';
        $args['base']   = esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
    }
    wc_get_template( 'loop/pagination.php', $args );
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


//function remove_country_field_billing($fields)
//{
//    unset($fields['billing_country']);
//    unset($fields['billing_state']);
//    return $fields;
//
//}
//add_filter('woocommerce_billing_fields', 'remove_country_field_billing');
//function remove_country_field_shipping($fields)
//{
//    unset($fields['shipping_country']);
//    unset($fields['shipping_state']);
//    return $fields;
//}
//add_filter('woocommerce_shipping_fields', 'remove_country_field_shipping');

//function custom_override_checkout_fields( $fields ) {
//    unset($fields['billing']['billing_country']);
//    unset($fields['shipping_country']);
//
//    return $fields;
//}
//add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

