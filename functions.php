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

require (__DIR__ . "/inc/Search/AdapterInterface.php");
require (__DIR__ . "/inc/Search/Adapter/MySql.php");
require (__DIR__ . "/inc/Search/Adapter/Elastic.php");
require (__DIR__ . "/inc/Search/Search.php");
require (__DIR__ . "/inc/Search/Elastica/Search.php");


add_action('after_setup_theme', 'require_on_init');

add_filter('woocommerce_currency_symbol', 'change_existing_currency_symbol', 10, 2);
function change_existing_currency_symbol($currency_symbol, $currency)
{
    $currency_symbol = 'din.';

    return $currency_symbol;
}

//remove_action( 'woocommerce_before_main_content','woocommerce_breadcrumb', 20);

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

//@TODO implement category as filter
/**
 * @param $input
 * @param int $limit
 * @return bool|WP_Query
 */
function gf_custom_search($input, $limit = 0)
{
    global $wpdb;

    $search = new \GF\Search\Search(new \GF\Search\Adapter\MySql($wpdb));
    $allIds = $search->getItemIdsForSearch($input, $limit);

    return gf_parse_post_ids_for_list($allIds);
}

function gf_elastic_search($input, $limit = 0)
{
    $config = array(
        'host' => ES_HOST,
        'port' => 9200
    );
    $elasticaSearch = new \GF\Search\Elastica\Search(new \Elastica\Client($config));
    $search = new \GF\Search\Search(new \GF\Search\Adapter\Elastic($elasticaSearch));
    $allIds = $search->getItemIdsForSearch($input, $limit);

    return gf_parse_post_ids_for_list($allIds);
}

function gf_elastic_search_with_data($input, $limit = 0)
{
    $config = array(
        'host' => ES_HOST,
        'port' => 9200
    );
    $per_page = apply_filters('loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page());
    if (isset($_POST['ppp'])) {
        $per_page = ($_POST['ppp'] > 48) ? 48 : $_POST['ppp'];
    }
    if ($limit) {
        $per_page = $limit;
    }
    $currentPage = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $elasticaSearch = new \GF\Search\Elastica\Search(new \Elastica\Client($config));
    $search = new \GF\Search\Search(new \GF\Search\Adapter\Elastic($elasticaSearch));
    $resultSet = $search->getItemsForSearch($input, $per_page, $currentPage);

    wc_set_loop_prop('total', $resultSet->getTotalHits());
    wc_set_loop_prop('per_page', $per_page);
    wc_set_loop_prop('current_page', $currentPage);
    wc_set_loop_prop('total_pages', ceil($resultSet->getTotalHits() / $per_page));

    return $resultSet;
}

function gf_get_category_query()
{
    global $wpdb;

    $search = new \GF\Search\Search(new \GF\Search\Adapter\MySql($wpdb));
    $allIds = $search->getItemIdsForCategory(get_query_var('term'));

    return gf_parse_post_ids_for_list($allIds);
}

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
    $cart_link = '<a href = "' . wc_get_page_permalink('cart') . '" class="button wc-forward" >Pogledaj korpu</a >';
    $message .= $cart_link;

    return $message;

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

function gf_add_custom_meta_to_users() {
    $users = get_users(array('fields' => array('ID')));
    foreach ($users as $user) {
        update_user_meta($user->ID, 'migrated', '0');
    }
}

function gf_check_if_user_is_migrated($user, $password) {
    if (!empty($user)) {
        if (get_user_meta($user->ID, 'migrated', true) != 0) {

            global $wpdb;

            //skloniti posle testiranja, promenjeno je trenutno za usera 'admin'
//        update_user_meta($user->ID, 'migrated', '1');

            $salt = 'd@uy/o%b^';
            $passwordHash = $salt . md5($salt, $password);
            $sql = "SELECT user_pass FROM wp_users WHERE ID = '{$user->ID}'";
            $password_in_db = $wpdb->get_results($sql)[0]->user_pass;

//            var_dump($passwordHash);
//            var_dump($password_in_db);
//            var_dump($user);
//            die();

            if ($passwordHash === $password_in_db) {
                return $user;
            } else {
                return new WP_Error('incorrect_password',
                    sprintf(
                    /* translators: %s: user name */
                        __('<strong>GREŠKA</strong>: Lozinka koju ste uneli za korisničko ime %s nije ispravna.'),
                        '<strong>' . $user->user_login . '</strong>'
                    ) .
                    ' <a href="' . wp_lostpassword_url() . '">' .
                    __('Izgubili ste lozinku?') .
                    '</a>'
                );
            }
        }
    }

    return false;
}

//add_filter('wp_authenticate_user', 'gf_check_if_user_is_migrated', 10, 2);


function remove_country_field_billing($fields)
{
    unset($fields['billing_country']);
    unset($fields['billing_state']);
    return $fields;

}
//add_filter('woocommerce_billing_fields', 'remove_country_field_billing');
function remove_country_field_shipping($fields)
{
    unset($fields['shipping_country']);
    unset($fields['shipping_state']);
    return $fields;
}
//add_filter('woocommerce_shipping_fields', 'remove_country_field_shipping');

function custom_override_checkout_fields( $fields ) {
    unset($fields['billing']['billing_country']);
    unset($fields['shipping_country']);

    return $fields;
}
//add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );



// Disable W3TC footer comment for everyone but Admins (single site & network mode)
if (!current_user_can('activate_plugins')) {
    add_filter('w3tc_can_print_comment', function ($w3tc_setting) {
        return false;
    }, 10, 1);
}


function action_woocommerce_register_form()
{
    ?>
    <div class="gf-wc-registration-info">
        <div class="woocommerce-info ">
            <p>Podaci o Vašem nalogu biće poslati na unetu email adresu</p>
        </div>
    </div>
    <?php
}
add_action('woocommerce_register_form', 'action_woocommerce_register_form', 20, 10);

remove_filter( 'authenticate', 'wp_authenticate_username_password' );
add_filter( 'authenticate', 'gf_authenticate_username_password', 20, 3 );
/**
 * Remove Wordpress filer and write our own with changed error text.
 */
function gf_authenticate_username_password( $user, $username, $password ) {
    if ( is_a($user, 'WP_User') )
        return $user;

    if ( empty( $username ) || empty( $password ) ) {
        if ( is_wp_error( $user ) )
            return $user;

        $error = new WP_Error();

        if ( empty( $username ) )
            return new WP_Error( 'invalid_username', sprintf( __( '<strong>GREŠKA</strong>: Polje korisničko ime ne može biti prazno.' ), wp_lostpassword_url() ) );

        if ( empty( $password ) )
            return new WP_Error( 'invalid_username', sprintf( __( '<strong>GREŠKA</strong>: Polje lozinka ne može biti prazno.' ), wp_lostpassword_url() ) );

        return $error;
    }

    $user = get_user_by( 'login', $username );

    if ( !$user )
        return new WP_Error( 'invalid_username', sprintf( __( '<strong>GREŠKA</strong>: Ne postojeće korisničko ime ili email. <a href="%s" title="Lozinka izgubljena">Izgubili ste lozinku</a>?' ), wp_lostpassword_url() ) );

    $user = apply_filters( 'wp_authenticate_user', $user, $password );
    if ( is_wp_error( $user ) )
        return $user;

    if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) )
        return new WP_Error( 'incorrect_password', sprintf( __( '<strong>GREŠKA</strong>: Lozinka koju ste uneli za korisničko ime <strong>%1$s</strong> nije ispravna. <a href="%2$s" title="Lozinka izgubljena">Izgubili ste lozinku</a>?' ),
            $username, wp_lostpassword_url() ) );

    return $user;
}

function gf_custom_shop_loop(\Elastica\ResultSet $products) {
    $html = '<ul class="products columns-4 grid">';

    $per_page = apply_filters('loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page());
    if (isset($_POST['ppp'])) {
        $per_page = ($_POST['ppp'] > 48) ? 48 : $_POST['ppp'];
    }
    $currentPage = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $totalPages = ceil($products->count() / $per_page);

    wc_set_loop_prop('total', $products->count());
    wc_set_loop_prop('per_page', $per_page);
    wc_set_loop_prop('current_page', $currentPage);
    wc_set_loop_prop('total_pages', $totalPages);

//    var_dump($products->getResults()[0]->getData());
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

//        $product_link = get_permalink((int) $productData->getId());
//        if (has_post_thumbnail($productData->getId())) {
//
//        } else {
//            echo '<img src="' . wc_placeholder_img_src() . '" alt="Placeholder" width="200px" height="200px" />';
//        }
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
        $html .= '<del><span class="woocommerce-Price-amount amount">'.$product->getRegularPrice()
                     .'<span class="woocommerce-Price-currencySymbol">din.</span></span></del>';
        if ($saved_percentage > 0) {
            $html .= '<ins><span class="woocommerce-Price-amount amount">'.$price.
                     '<span class="woocommerce-Price-currencySymbol">din.</span></span></ins>';
            $html .= '<p class="saved-sale">Ušteda: <span class="woocommerce-Price-amount amount">'.$saved_price.
                     '<span class="woocommerce-Price-currencySymbol">din.</span></span><em>'.$saved_percentage.'%</em></p>';
        }
        $html .= '</span>';
        $html .= '</li>';
    }

    $html .= '</ul>';

    echo $html;
}
add_filter( 'registration_errors', 'wpse8170_registration_errors', 10, 3 );
function wpse8170_registration_errors( $errors, $sanitized_user_login, $user_email ) {
    if ($user_email == 'test@test123.com' ) {
        $errors->add( 'myexception_code', 'This is my message' );
    }

    return $errors;
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
