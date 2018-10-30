<?php
//ini_set('upload_max_size', '128M');
//ini_set('post_max_size', '128M');
//ini_set('max_execution_time', '80');
ini_set('max_execution_time', '40');

require(__DIR__ . DIRECTORY_SEPARATOR . "user.functions.php");
require(__DIR__ . DIRECTORY_SEPARATOR . "search.functions.php");
require(__DIR__ . DIRECTORY_SEPARATOR . "util.functions.php");
require(__DIR__ . DIRECTORY_SEPARATOR . "cron.functions.php");

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

require(__DIR__ . "/inc/Search/AdapterInterface.php");
require(__DIR__ . "/inc/Search/Adapter/MySql.php");
require(__DIR__ . "/inc/Search/Adapter/Elastic.php");
require(__DIR__ . "/inc/Search/Search.php");
require(__DIR__ . "/inc/Search/Elastica/Search.php");
require(__DIR__ . "/inc/Search/Elastica/TermSearch.php");
require(__DIR__ . "/inc/CheckoutHelper/CheckoutHelper.php");

add_action('after_setup_theme', 'require_on_init');

add_filter('woocommerce_currency_symbol', 'change_existing_currency_symbol', 10, 2);
function change_existing_currency_symbol($currency_symbol, $currency)
{
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
function upload_dir_filter($uploads)
{
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

/**
 * print all enqued styles
 *
 * @return array
 */
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


function gf_custom_shop_loop(\Elastica\ResultSet $products)
{
    $html = '';

    $i = 0;
    foreach ($products->getResults() as $productData) {
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
        $html .= add_stickers_to_products_on_sale($classes);
//        woocommerce_show_product_sale_flash('', '', '', $classes);
//        add_stickers_to_products_new($product);
        $html .= $product->dto['thumbnail'];
        ob_start();
        add_stickers_to_products_soldout($classes);
        $html .= ob_get_clean();
//        $html .= add_stickers_to_products_soldout($classes);
        $html .= '</a>';
        $html .= '<a href="' . $product->dto['permalink'] . '" title="' . $product->getName() . '">';
        $html .= '<h5>' . $product->getName() . '</h5>';
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


function iconic_remove_password_strength()
{
    wp_dequeue_script('wc-password-strength-meter');
}

add_action('wp_print_scripts', 'iconic_remove_password_strength', 10);


add_action('woocommerce_save_account_details_errors', 'wooc_validate_custom_field', 10, 2);

function wooc_validate_custom_field($args, $user)
{
    $user_id = $user->ID;
    $user_pass_hash = get_user_by('id', $user_id)->user_pass;
    if (isset($_POST['password_current']) && !empty($_POST['password_current'])) {
        $current_pass = $_POST['password_current'];
        $passowrd_check = wp_check_password($current_pass, $user_pass_hash, $user_id);
        if (isset($_POST['password_1']) && $passowrd_check == 'true') {
            if (strlen($_POST['password_1']) < 5)
                $args->add('error', __('Lozinka mora sadržati minimum 5 karaktera!', 'woocommerce'), '');
        }
    }
}

add_action('woocommerce_before_account_navigation', 'gf_my_account_shop_button', 1);
function gf_my_account_shop_button()
{
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    global $wp;
    $request = explode('/', $wp->request);
    $page = end($request);

    $user = wp_get_current_user();
    $args = array(
        'customer_id' => $user->ID,
    );
    $orders = wc_get_orders($args);
    $class = '';
    if ($page == 'narudzbine' && empty($orders)) {
        $class = 'd-none';
    }
    echo '<div class="gf-welcome-wrapper mb-3">';
    echo '<a class="gf-shop-button ' . $class . '" href="/">Kreni u kupovinu</a>';
    echo '<div class="gf_login_notice py-3 px-1 mt-0 mb-4"><p class="mb-0">Prilikom prijave možete koristiti <strong>korisničko ime</strong> ili <strong>email adresu</strong>.</p>
            <div class="mt-3 mb-1"><strong>Korisničko ime: </strong>' . $user->user_login . '</div>
            <div><strong>Email adresa: </strong>' . $user->user_email . '</div>
            </div>';
    echo '<div class="mb-2">';
    printf(
        __('Hello %1$s (not %1$s? <a href="%2$s">Log out</a>)', 'woocommerce'),
        '<strong>' . esc_html($user->display_name) . '</strong>',
        esc_url(wc_logout_url(wc_get_page_permalink('myaccount')))
    );
    echo '</div>';
    printf(
        __('From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">shipping and billing addresses</a>, and <a href="%3$s">edit your password and account details</a>.', 'woocommerce'),
        esc_url(wc_get_endpoint_url('orders')),
        esc_url(wc_get_endpoint_url('edit-address')),
        esc_url(wc_get_endpoint_url('edit-account'))
    );
    echo '</div>';
}

add_action('woocommerce_before_checkout_shipping_form', 'gf_checkout_shipping_notice');
function gf_checkout_shipping_notice() {
    echo '<div class ="gf-checkout-shipping-notice p-3" >Ukoliko se adresa za dostavu razlikuje od navedene u detaljima naplate, popunite sledeća polja:</div>';
}

add_action('wp_footer', 'gf_cart_refresh_update_qty');
function gf_cart_refresh_update_qty() {
    if (is_cart()) {
        ?>
        <script type="text/javascript">
            jQuery('div.woocommerce').on('click', 'input.qty', function () {
                jQuery("[name='update_cart']").trigger("click");
            });
            jQuery('div.woocommerce').on('change', 'input.qty', function () {
                jQuery("[name='update_cart']").trigger("click");
            });
        </script>
        <?php
    }
}

add_filter('woocommerce_account_menu_items', 'gf_remove_my_account_links');
function gf_remove_my_account_links($menu_links) {

    unset($menu_links['dashboard']); // Addresses

    //unset( $menu_links['dashboard'] ); // Dashboard
    //unset( $menu_links['payment-methods'] ); // Payment Methods
    //unset( $menu_links['orders'] ); // Orders
    //unset( $menu_links['downloads'] ); // Downloads
    //unset( $menu_links['edit-account'] ); // Account details
    //unset( $menu_links['customer-logout'] ); // Logout

    return $menu_links;
}

add_filter('post_date_column_time', 'gf_custom_post_date_column_time', 10, 2);
function gf_custom_post_date_column_time($h_time, $post) {

    $h_time = get_the_time(__('d/m/Y', 'woocommerce'), $post);

    return $h_time;
}

add_action('woocommerce_cart_collaterals', 'gf_cart_page_extra_buttons');
function gf_cart_page_extra_buttons() {
    if (!is_user_logged_in()) {
        echo '<a class="gf-cart-extra-buttons d-block p-3 mb-3" href="/moj-nalog">REGISTRUJ SE</a>
              <a class="gf-cart-extra-buttons d-block p-3" href="/placanje">NASTAVI KUPOVINU BEZ REGISTRACIJE</a>';
    }
}

//Migrate comments from old site
function gf_migrate_comments() {
    $rows = array_map('str_getcsv', file(__DIR__ . '/reviews.csv'));
    $header = array_shift($rows);
    $csv = array();
    foreach ($rows as $row) {
        $csv[] = array_combine($header, $row);
    }
    $successfulComments = [];
    $emptySkus = [];
    $emptyUsers = [];
    foreach ($csv as $comment) {
        $postId = wc_get_product_id_by_sku($comment['sku']);
        if (!$postId) {
            $emptySkus[] = $comment['sku'];
            continue;
        }
        $user = get_user_by('email', $comment['Email']);
        if (!$user) {
            $emptyUsers[] = $comment['Email'];
            continue;
        }
        $commentAuthor = $user->get('display_name');
        $commentAuthorEmail = $user->get('user_email');
        $commentAuthorUrl = $user->get('user_url');
        $commentContent = $comment['comment'];
        $userId = $user->get('ID');
        $commentDate = $comment['date'];


        $data = array(
            'comment_post_ID' => $postId,
            'comment_author' => $commentAuthor,
            'comment_author_email' => $commentAuthorEmail,
            'comment_author_url' => $commentAuthorUrl,
            'comment_content' => $commentContent,
            'comment_date' => $commentDate,
            'comment_date_gmt' => $commentDate,
            'comment_approved' => 1,
            'user_id' => $userId,
        );
        $comment_id = wp_insert_comment($data);
        $successfulComments[] = $comment_id;
        update_comment_meta($comment_id, 'migrated', '1');
    } //foreach comments

    $skuLogFile = fopen(LOG_PATH . '/skuLog.csv', 'w');
    fwrite($skuLogFile, implode(',', $emptySkus));
    fclose($skuLogFile);

    $userLogFile = fopen(LOG_PATH . '/usersLog.csv', 'w');
    fwrite($userLogFile, implode(',', $emptyUsers));
    fclose($userLogFile);
    echo '<p>Uspešno importovano ' . count($successfulComments) . ' komentara</p>';
}


function gf_unrequire_wc_state_field($fields)
{
    $fields['shipping_state']['required'] = false;
    return $fields;
}

add_filter('woocommerce_shipping_fields', 'gf_unrequire_wc_state_field');

//admin order list - date column
add_action('manage_posts_custom_column', 'misha_date_clmn');
function misha_date_clmn($column_name)
{
    global $post;
    if ($column_name == 'order_date') {
        $t_time = get_the_time(__('m/d/Y H:i', 'woocommerce'), $post);
        echo $t_time . '<br />';
    }

}


//***** ORDERS *****

add_filter('manage_edit-shop_order_columns', 'gf_order_payment_method_column');
function gf_order_payment_method_column($order_columns)
{
    $order_columns['payment_method_column'] = "Način plaćanja";
    return $order_columns;
}

add_action('manage_shop_order_posts_custom_column', 'gf_get_order_payment_method_column');
function gf_get_order_payment_method_column($colname)
{
    global $the_order; // the global order object

    if ($colname == 'payment_method_column') {
        echo $the_order->get_payment_method_title();
    }
}

add_filter('manage_edit-shop_order_columns', 'gf_order_phone_column');
function gf_order_phone_column($order_columns)
{
    $order_columns['order_phone_column'] = "Telefonom / www";
    return $order_columns;
}

add_action('manage_shop_order_posts_custom_column', 'gf_get_order_phone_column');
function gf_get_order_phone_column($colname)
{
    global $the_order;

    if ($colname == 'order_phone_column') {
        echo $the_order->get_meta('gf_order_created_method');
    }
}

add_filter('manage_edit-shop_order_columns', 'gf_order_shipping_price_column');
function gf_order_shipping_price_column($order_columns)
{
    $order_columns['order_shipping_price_column'] = "Dostava";
    return $order_columns;
}

add_action('manage_shop_order_posts_custom_column', 'gf_get_order_shipping_price_column');
function gf_get_order_shipping_price_column($colname)
{
    global $the_order;

    if ($colname == 'order_shipping_price_column') {
        echo $the_order->get_shipping_total() . 'din.';
    }
}

add_filter('manage_edit-shop_order_columns', 'gf_custom_column_ordering_for_admin_list_order');
function gf_custom_column_ordering_for_admin_list_order($product_columns)
{

    return array(
        'cb' => '<input type="checkbox" />', // checkbox for bulk actions
        'order_number' => 'Narudžbina',
        'payment_method_column' => 'Način plaćanja',
        'order_phone_column' => 'Telefonom / WWW',
        'order_date' => 'Datum',
        'order_shipping_price_column' => 'Dostava',
        'order_total' => 'Ukupno',
        'order_status' => 'Status',
    );

}

add_action('woocommerce_admin_order_data_after_order_details', 'gf_admin_phone_order_field');
function gf_admin_phone_order_field()
{
//    echo '<p class="form-field form-field-wide">Poručeno telefonom?</p>
//            <input type="radio" name="phone_order"><label for="">Da</label>';
    woocommerce_form_field('gf_phone_order', array(
        'type' => 'checkbox',
        'class' => array('gf-admin-phone-order'),
        'label' => __('Poručivanje telefonom'),
        'required' => false,
    ), true);
}

add_action('save_post_shop_order', 'gf_manual_order_created', 10, 3);
function gf_manual_order_created($post_id, $post, $update)
{
    $order = new WC_Order($post_id);

    // For testing purpose
    $trigger_status = get_post_meta($post_id, '_hook_is_triggered', true);

    // 1. Fired the first time you hit create a new order (before saving it)
    if (!$update)
        update_post_meta($post_id, '_hook_is_triggered', 'Create new order'); // Testing

    if ($update) {
        // 2. Fired when saving a new order
        if ('Create new order' == $trigger_status) {
            update_post_meta($post_id, '_hook_is_triggered', 'Save the new order'); // Testing
            $phone_order_value = $_POST['gf_phone_order'];
            if ($phone_order_value == 1) {
                update_post_meta($post_id, 'gf_order_created_method', 'Telefonom'); // Testing
            } else {
                update_post_meta($post_id, 'gf_order_created_method', 'WWW'); // Testing
            }
        } // 3. Fired when Updating an order
        else {
            update_post_meta($post_id, '_hook_is_triggered', 'Update  order'); // Testing
            $phone_order_value = isset($_POST['gf_phone_order']) ? $_POST['gf_phone_order'] : 0;
            if ($phone_order_value == 1) {
                update_post_meta($post_id, 'gf_order_created_method', 'Telefonom'); // Testing
            } else {
                update_post_meta($post_id, 'gf_order_created_method', 'WWW'); // Testing
            }
        }
    }
}

add_action('woocommerce_review_order_before_submit', 'gf_add_www_field_on_checkout');
function gf_add_www_field_on_checkout($checkout)
{
    woocommerce_form_field('gf_www_orders', array(
        'type' => 'hidden',
    ), true);
}

add_action('woocommerce_checkout_update_order_meta', 'gf_custom_checkout_field_update_order_meta_created_method');

function gf_custom_checkout_field_update_order_meta_created_method($order_id)
{
    if ($_POST['gf_www_orders']) update_post_meta($order_id, 'gf_order_created_method', 'WWW');
}

add_filter('manage_edit-shop_order_columns', 'gf_add_order_print');
function gf_add_order_print($order_columns)
{
    $order_columns['customActions'] = "Actions";

    return $order_columns;
}

add_action('manage_shop_order_posts_custom_column', 'gf_get_order_print_url');
function gf_get_order_print_url($colname)
{
    global $the_order;

    if ($colname == 'customActions') {
//        echo '<a class="button" href="/back-ajax/?action=printOrder&id='. $the_order->get_id() .'" title="Print racuna" target="_blank">Racun</a>';
        echo '&nbsp;';
        echo '<a class="button" href="/back-ajax/?action=printPreorder&id=' . $the_order->get_id() . '" title="Print predracuna" target="_blank">Predracun</a>';
        echo '&nbsp;';
        echo '<a class="button" href="/back-ajax/?action=exportJitexOrder&id=' . $the_order->get_id() . '" title="Export za Jitex" target="_blank">Export</a>';
        echo '&nbsp;';
        echo '<a class="button" href="/back-ajax/?action=adresnica&id=' . $the_order->get_id() . '" title="Kreiraj adresnicu" target="_blank">Adresnica</a>';
//        echo $the_order->get_meta('gf_order_created_method');
    }
}

// ako zatreba za neki prevod koji ne mozemo da nadjemo
//add_filter( 'gettext', 'theme_sort_change', 20, 3 );
//function theme_sort_change( $translated_text, $text, $domain ) {
//
//    if ( is_woocommerce() ) {
//
//        switch ( $translated_text ) {
//
//            case 'Sort by latest' :
//
//                $translated_text = __( 'Sortiraj po najnovijem', 'theme_text_domain' );
//                break;
//        }
//
//    }
//
//    return $translated_text;
//}

add_filter('woocommerce_catalog_orderby', 'wc_customize_product_sorting');

function wc_customize_product_sorting($sorting_options){
    $sorting_options = array(
        'menu_order' => __( 'Sorting', 'woocommerce' ),
        'popularity' => __( 'Sort by popularity', 'woocommerce' ),
        'rating'     => __( 'Sort by average rating', 'woocommerce' ),
        'date'       => __( 'Sort by newness', 'woocommerce' ),
        'price'      => __( 'Sort by price: low to high', 'woocommerce' ),
        'price-desc' => __( 'Sort by price: high to low', 'woocommerce' ),
    );

    return $sorting_options;
}