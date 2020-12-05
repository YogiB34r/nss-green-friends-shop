<?php

//------------------------------------------------------------
//*********** REMOVED FUNCTIONS FROM functions.php ***********
//------------------------------------------------------------
add_filter('post_date_column_time', 'gf_custom_post_date_column_time', 10, 2);
function gf_custom_post_date_column_time($h_time, $post)
{

    return get_the_time(__('d/m/Y', 'woocommerce'), $post);
}

//add_filter( "views_edit-shop_order" , 'gfCacheStatusCounts', -PHP_INT_MAX);
function gfCacheStatusCounts($views)
{
    global $current_screen;
    $cache = new \GF_Cache();

//    var_dump($views);
//    die();

    switch ($current_screen->id) {
//        case 'edit-post':
//            $views = wpse_30331_manipulate_views( 'post', $views );
//            break;
        case 'edit-shop_order':
            $key = 'ordersStatusCounts';
            $cachedViews = $cache->redis->get($key);
            if ($cachedViews === false) {
                if (!empty($views)) {
                    $cache->redis->set($key, serialize($views), 60 * 5);
                } else {
                    add_filter("views_edit-shop_order", 'gfCacheStatusCounts', 10, 10);
                }
            } else {
                echo 'cached';
                $views = unserialize($cachedViews);
                remove_all_filters('views_edit-shop_order');
            }

            break;
    }
    return $views;
}

//admin order list - date column
add_action('manage_posts_custom_column', 'gf_date_clmn');
function gf_date_clmn($column_name)
{
    global $post;
    if ($column_name === 'order_date') {
        $t_time = get_the_time(__('d/m/Y H:i', 'woocommerce'), $post);
        echo $t_time . '<br />';
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
        'customActions' => 'Actions',
    );
}

add_action('manage_shop_order_posts_custom_column', 'gf_get_order_payment_method_column');
function gf_get_order_payment_method_column($colname)
{
    global $the_order; // the global order object

    if ($colname === 'payment_method_column') {
        echo $the_order->get_payment_method_title();
    }
    if ($colname === 'order_phone_column') {
        $via = 'WWW';
        if ($the_order->get_created_via() === 'admin') {
            $via = 'PHONE';
        }
        echo $via;
//        echo $the_order->get_meta('gf_order_created_method');
    }
    if ($colname === 'order_shipping_price_column') {
        echo $the_order->get_shipping_total() . 'din.';
    }
    if ($colname === 'customActions') {
        $jitexDoneStyle = '';
        $adresnicaDoneStyle = '';
        $misDoneStyle = '';
        if ($the_order->get_meta('jitexExportCreated')) {
            $jitexDoneStyle = 'style="color:white;background-color:gray;font-style:italic;"';
        }
        if ($the_order->get_meta('adresnicaCreated')) {
            $adresnicaDoneStyle = 'style="color:white;background-color:gray;font-style:italic;"';
        }
        if ($the_order->get_meta('synced')) {
            $misDoneStyle = 'style="color:white;background-color:gray;font-style:italic;"';
        }
//        echo '<a class="button" href="/back-ajax/?action=printOrder&id='. $the_order->get_id() .'" title="Print racuna" target="_blank">Racun</a>';
        echo '&nbsp;';
        echo '<a class="button" href="/back-ajax/?action=printPreorder&id=' . $the_order->get_id() . '" title="Print predracuna" target="_blank">Predracun</a>';
        echo '&nbsp;';
        echo '<a class="button nssOrderJitexExport" ' . $jitexDoneStyle . ' href="/back-ajax/?action=exportJitexOrder&id=' . $the_order->get_id() . '" title="Export za Jitex" target="_blank">Export</a>';
        echo '&nbsp;';
        echo '<a class="button nssOrderAdresnica" ' . $adresnicaDoneStyle . ' href="/back-ajax/?action=adresnica&id=' . $the_order->get_id() . '" title="Kreiraj adresnicu" target="_blank">Adresnica</a>';
        $orderNote = $the_order->get_customer_note();
        if (strlen($orderNote) > 0) {
            echo '&nbsp;';
            echo '<a class="button " style="background-color:yellow;" ' . ' href="#" title="' . $orderNote . '"  target="_blank">Napomena</a>';
        }
        $user = wp_get_current_user();
        if ($user->ID === 1) {
            echo '&nbsp;';
            echo '<a class="button nssOrderMis" ' . $misDoneStyle . ' href="/back-ajax/?action=mis&type=order&id=' . $the_order->get_id() . '" title="Sinkuj na mis" target="_blank">Mis</a>';
        }
//        echo $the_order->get_meta('gf_order_created_method');
    }
}

add_action('woocommerce_admin_order_data_after_order_details', 'gf_admin_phone_order_field');
<<<<<<< HEAD
function gf_admin_phone_order_field(WC_Order $order) {
=======
function gf_admin_phone_order_field(WC_Order $order)
{
>>>>>>> ce0cffd661d2dd9e67eda35244b37dafdf48ccf4
    $type = 'Telefonska';
    if ($order->get_created_via() === 'checkout') {
        $type = 'WWW';
    }
    echo '<br />';
    echo '<p class="form-field form-field-wide">Tip porudžbenice: ' . $type . '</p>';
}

add_action('save_post', 'redirect_page');
function redirect_page()
{
    switch (get_post_type()) {
        case "shop_order":
            $url = admin_url() . 'edit.php?post_type=shop_order';
            wp_redirect($url);
            exit;
            break;
    }
}

add_action('woocommerce_before_order_itemmeta', 'addItemStatusToOrderItemList', 10, 3);
function addItemStatusToOrderItemList($itemId, $item, $c)
{
    /* @var WC_Order_Item_Product $item */
    if (isset($_GET['post']) && $_GET['post'] && get_class($item) === WC_Order_Item_Product::class) {
        global $wpdb;

        $sql = "SELECT * FROM wp_nss_backorderItems WHERE orderId = {$_GET['post']} AND itemId = {$item->get_product_id()}";
        $result = $wpdb->get_results($sql);
        require(__DIR__ . "/../templates/admin/order/product-status.phtml");
    }
}

// ADDING A CUSTOM COLUMN TITLE TO ADMIN PRODUCTS LIST
add_filter('manage_edit-product_columns', 'gf_supplier_product_list_column', 11);
function gf_supplier_product_list_column($columns)
{
    $columns['stockStatus'] = __('Lager', 'woocommerce'); // title
    $columns['supplier'] = __('Dobavljač', 'woocommerce'); // title

    return $columns;
}

add_action('manage_product_posts_custom_column', 'gf_supplier_product_list_column_content', 10, 2);
function gf_supplier_product_list_column_content($column, $product_id)
{
    global $metaCache, $product;

//    $supplier_id = get_post_meta($product_id, 'supplier', true);

    switch ($column) {
        case 'supplier':
            $supplierId = $metaCache->getMetaFor($product_id, 'product', 'supplier');
            if ($supplierId) {
//                echo get_user_by('ID', $supplierId)->display_name;
                echo $metaCache->getMetaFor($product_id, 'product', 'supplierName', true);
            }
            break;
        case 'stockStatus':
            $supplierId = $metaCache->getMetaFor($product_id, 'product', 'supplier');
            if ($supplierId) {
                $stockStatus = false;
                if (get_class($product) === WC_Product_Variable::class) {
                    foreach ($product->get_available_variations() as $available_variation) {
                        $variation = wc_get_product($available_variation['variation_id']);
                        if ($variation->is_in_stock()) {
                            $stockStatus = true;
                        }
                    }
                } else {
                    if ($product->is_in_stock()) {
                        $stockStatus = true;
                    }
                }

                if ($stockStatus) {
                    echo 'Na stanju ' . $product->get_meta('quantity') . ' komada';
                } else {
                    echo 'Nema na stanju';
                }
            }
            break;
    }
}

/**
 * @return array
 */
function gf_get_order_dates()
{
    global $wpdb;

    $cache = new \GF_Cache();
    $key = 'orderDateFilterHtml';
    $orderDates = $cache->redis->get($key);
    if ($orderDates === false) {
        $sql = "SELECT distinct DATE(post_date) as postDate FROM wp_posts WHERE post_type = 'shop_order' ORDER BY post_date DESC";
        $dates = $wpdb->get_results($sql);
        $orderDates = [];
        foreach ($dates as $date) {
            $orderDates[] = date('d/m/Y', strtotime($date->postDate));
        }
        $cache->redis->set($key, serialize($orderDates), 60 * 60 * 4); // 4 hours ttl, could be whole day ?
    } else {
        $orderDates = unserialize($orderDates);
    }

    return $orderDates;
}

add_filter('query_vars', 'gf_order_date_register_query_vars');
function gf_order_date_register_query_vars($qvars)
{
    $qvars[] = 'gf_order_date';
    $qvars[] = 'gf_created_via';
    return $qvars;
}

add_action('restrict_manage_posts', 'gf_print_order_date_picker_admin_list');
function gf_print_order_date_picker_admin_list()
{
    global $typenow;
    $order_dates = gf_get_order_dates();
    if ($typenow == 'shop_order') {
        $selected = get_query_var('gf_order_date');
        $output = "<select name='gf_order_date' class='postform'>";
        $output .= '<option ' . selected($selected, 0, false) . ' value="">Date picker</option>';
        if (!empty($order_dates)) {
            foreach ($order_dates as $order_date):
                $output .= "<option value='{$order_date}' " . selected($selected, $order_date,
                        false) . '>' . $order_date . '</option>';
            endforeach;
        }
        $output .= "</select>";

<<<<<<< HEAD
//        $selected = get_query_var('gf_created_via');
//        $output .= "<select name='gf_created_via' class='postform'>";
//        $output .= '<option>Phone / WWW</option>';
//        $output .= '<option ' . selected($selected, 'phone', false) . ' value="phone">Phone</option>';
//        $output .= '<option ' . selected($selected, 'www', false) . ' value="www">WWW</option>';
//        $output .= "</select>";
=======
        $selected = get_query_var('gf_created_via');
        $output .= "<select name='gf_created_via' class='postform'>";
        $output .= '<option>Phone / WWW</option>';
        $output .= '<option ' . selected($selected, 'phone', false) . ' value="phone">Phone</option>';
        $output .= '<option ' . selected($selected, 'www', false) . ' value="www">WWW</option>';
        $output .= "</select>";
>>>>>>> ce0cffd661d2dd9e67eda35244b37dafdf48ccf4

        echo $output;
    }
}

add_action('pre_get_posts', 'gf_order_date_apply_filter');
function gf_order_date_apply_filter($query)
{
    $order_date_str = $query->get('gf_order_date');
    $exploded_date = explode('/', $order_date_str);
    if (!empty($order_date_str)) {
        $meta_query = $query->get('meta_query');
        if (empty($meta_query)) {
            $meta_query = array();
        }

//        $meta_query[] = array(
//            'key' => 'post_date',
//            'value' => $order_date_str,
//            'compare' => 'LIKE',
//            'type' => 'DATE'
//        );
//        $query->set('meta_query',$meta_query);
        $query->set('day', $exploded_date[0]);
        $query->set('monthnum', $exploded_date[1]);
        $query->set('year', $exploded_date[2]);
    }
}

add_filter('bulk_actions-edit-product', 'register_gf_product_list_bulk_action');
function register_gf_product_list_bulk_action($bulk_actions)
{
    if (isset($_GET['post_type']) && $_GET['post_type'] == 'product' && isset($_GET['filter_action']) && isset($_GET['product_cat'])) {
        $bulk_actions['remove_product_from_sliders'] = 'Ukloni iz kategorije: ' . $_GET['product_cat'];
    }

    return $bulk_actions;
}

add_filter('handle_bulk_actions-edit-product', 'gf_product_list_bulk_action_handler', 10, 3);
function gf_product_list_bulk_action_handler($redirect_to, $doaction, $post_ids)
{
    if ($doaction !== 'remove_product_from_sliders') {
        return $redirect_to;
    }
    $category_slug = $_GET['product_cat'];
<<<<<<< HEAD
    $specialPromoId = get_term_by('slug', 'specijalne-promocije','product_cat')->term_id;
    $taxonomyParentId = wp_get_term_taxonomy_parent_id(get_term_by( 'slug', $category_slug, 'product_cat' ),
        'product_cat');

=======
>>>>>>> ce0cffd661d2dd9e67eda35244b37dafdf48ccf4

    /*Specijalne promocije is parent category for slider cats, so if filtered cat is aforementioned cat
      get all child cats and remove them from product
     **/
    if ($category_slug === 'specijalne-promocije'){
        $category = get_term_by( 'slug', $category_slug, 'product_cat' );
        $childCats = get_terms(['parent' => $category->term_id, 'taxonomy' => 'product_cat' , 'number' => 50]);
    }
    foreach ($post_ids as $post_id) {
        if (count($childCats ) > 0) {
            foreach ($childCats as $cat){
                wp_remove_object_terms($post_id, $cat->term_id, 'product_cat');
            }
        }
<<<<<<< HEAD
        //If parent cat is specijalne-promocije, also remove specijalne promocije cat from product
        if( $taxonomyParentId === $specialPromoId){
            wp_remove_object_terms($post_id, 'specijalne-promocije', 'product_cat');
        }
=======
>>>>>>> ce0cffd661d2dd9e67eda35244b37dafdf48ccf4
        wp_remove_object_terms($post_id, $category_slug, 'product_cat');
    }
    $redirect_to = add_query_arg('bulk_remove_product_from_sliders', count($post_ids), $redirect_to);
    return $redirect_to;
}

//admin product list filter by supplier *** START ***
add_filter('woocommerce_product_filters', 'gf_admin_product_list_supplier_filter', 10, 1);
function gf_admin_product_list_supplier_filter($output)
{
    $cache = new \GF_Cache();
    $key = 'orderSuppliersFilterHtml';
    $html = $cache->redis->get($key);
    if ($html === false) {
        $suppliers = get_users([
            'role' => 'supplier',
            'orderby' => 'display_name',
            'order' => 'ASC'
        ]);
        $html = '<select name="product_supplier_filter">';
        $html .= '<option value="">Filtriraj po dobavljaču </option>';
        foreach ($suppliers as $supplier) {
            $html .= '<option value="' . $supplier->ID . '">' . $supplier->display_name . '</option>';
        }
        $html .= '</select>';
        $cache->redis->set($key, $html, 60 * 60);
    }

    return $html . $output;
}

add_filter('parse_query', 'gf_featured_products_admin_filter_query');
function gf_featured_products_admin_filter_query($query)
{
    global $typenow, $wp_query;

    if ($typenow == 'product' && !empty($_GET['product_supplier_filter'])) {
        $query->query_vars['meta_key'] = 'supplier';
        $query->query_vars['meta_value'] = $_GET['product_supplier_filter'];
    }
}

//admin product list filter by supplier *** END ***


add_filter('bulk_actions-edit-shop_order', 'bulkAdresniceExport', 20, 1);
function bulkAdresniceExport($actions)
{
    $actions['adresniceExport'] = __('Adresnice', 'woocommerce');
    $actions['jitexExport'] = __('Jitex Export', 'woocommerce');

    return $actions;
}

add_filter('handle_bulk_actions-edit-shop_order', 'handleBulkAdresniceExport', 10, 3);
function handleBulkAdresniceExport($redirect_to, $action, $orderIds)
{
    switch ($action) {
        case 'adresniceExport':
            $zipArchive = new ZipArchive();
            $zipPath = generateUploadsPath() . date('Ymdhis') . '-adresnice-' . md5(serialize($orderIds)) . '.zip';
            $open = $zipArchive->open($zipPath, ZipArchive::CREATE);
            if ($open !== true) {
                var_dump($open);
                die('cannot open');
            }

            foreach ($orderIds as $orderId) {
                $order = wc_get_order($orderId);
                $path = \Gf\Util\Adresnica::createAdresnicaPdf($order);
                if (file_exists($path) && is_readable($path)) {
                    $add = $zipArchive->addFile($path, basename($path));
                } else {
                    throw new \Exception('there was a problem reading file: ' . $path);
                }
                if ($add !== true) {
                    throw new \Exception('could not add file to archive');
                }
            }
            if ($zipArchive->close() !== true) {
                throw new \Exception('could not close archive.');
            }
            $path = str_replace('public_html', '', str_replace(strstr($zipPath, 'public_html', true), '', $zipPath));

            return $redirect_to = add_query_arg(array(
                'adresniceExport' => '1',
                'processed_count' => count($orderIds),
                'zipPath' => $path,
            ), $redirect_to);
            break;

        case 'jitexExport':
            $zipArchive = new ZipArchive();
            $zipPath = generateUploadsPath() . date('Ymdhis') . '-export-' . md5(serialize($orderIds)) . '.zip';
            $open = $zipArchive->open($zipPath, ZipArchive::CREATE);
            foreach ($orderIds as $orderId) {
                $order = wc_get_order($orderId);
                $csvText = \Gf\Util\Jitex::parseJitexDataFromOrder($order);
                $add = $zipArchive->addFromString($order->get_order_number() . '.txt', $csvText);
            }
            if ($zipArchive->close() !== true) {
                var_dump('could not close archive.');
                die();
            }
            $path = str_replace('public_html', '', str_replace(strstr($zipPath, 'public_html', true), '', $zipPath));

            return $redirect_to = add_query_arg(array(
                'jitexExport' => '1',
                'processed_count' => count($orderIds),
                'zipPath' => $path,
            ), $redirect_to);

            break;

        default:
            return $redirect_to;
            break;
    }
}

// The results notice from bulk action on orders
add_action('admin_notices', 'bulkAdresniceAdminNotice');
function bulkAdresniceAdminNotice()
{
    if (!isset($_REQUEST['processed_count'])) {
        return;
    }
    $count = (int)$_REQUEST['processed_count'];

    if (!empty($_REQUEST['adresniceExport'])) {
        echo '<div id="message" class="updated fade">
        <p>' . sprintf('Ukupno %s porudžbina obrađeno za <b>adresnice</b>.', $count) . '</p>
        <p>Adresnice možete preuzeti <a href="' . $_REQUEST['zipPath'] . '">ovde</a></p>
        </div>';
    }
    if (!empty($_REQUEST['jitexExport'])) {
        echo '<div id="message" class="updated fade">
        <p>' . sprintf('Ukupno %s porudžbina obrađeno za <b>jitex export</b>.', $count) . '</p>
        <p>Jitex export možete preuzeti <a href="' . $_REQUEST['zipPath'] . '">ovde</a></p>
        </div>';
    }
}

function printPreorder(WC_Order $order)
{
    ob_start();
    require(__DIR__ . '/../templates/orders/printPredracun.phtml');
    $html = ob_get_clean();

    return $html;
}

add_action('woocommerce_product_options_general_product_data', 'addStickerInfoToProductTabs');
function addStickerInfoToProductTabs()
{
    echo '<div class="options_group">';
    $isActive = get_post_meta(get_the_ID(), 'sale_sticker_active', true);
    $class = "";
    if ($isActive !== 'yes') {
        $class = "hidden";
    }
    woocommerce_wp_checkbox(array(
        'id' => 'sale_sticker_active',
        'value' => get_post_meta(get_the_ID(), 'sale_sticker_active', true),
        'label' => 'Sale sticker',
        'desc_tip' => true,
        'description' => 'Add a sale sticker to this product.',
    ));

    echo '<div class="' . $class . ' saleStickerOptionContainer">';

    $dateFrom = get_post_meta(get_the_ID(), 'sale_sticker_from', true);
    woocommerce_wp_text_input([
        'id' => 'sale_sticker_from',
        'class' => 'datepicker',
        'value' => ((int)$dateFrom > 0) ? date('d/m/Y', (int)$dateFrom) : '',
        'label' => 'Start date',
        'description' => 'Select start date',
    ]);

    $dateTo = get_post_meta(get_the_ID(), 'sale_sticker_to', true);
    woocommerce_wp_text_input([
        'id' => 'sale_sticker_to',
        'class' => 'datepicker',
        'value' => ((int)$dateTo > 0) ? date('d/m/Y', (int)$dateTo) : '',
        'label' => 'End date',
        'description' => 'Select end date.',
    ]);
    echo '</div>';
    echo '</div>';
}

add_action('woocommerce_process_product_meta', 'saveStickerInfo', 10, 2);
function saveStickerInfo($id, $post)
{
    update_post_meta($id, 'sale_sticker_from', strtotime($_POST['sale_sticker_from']));
    update_post_meta($id, 'sale_sticker_to', strtotime($_POST['sale_sticker_to']));
    update_post_meta($id, 'sale_sticker_active', $_POST['sale_sticker_active']);
}

add_action('woocommerce_order_item_add_line_buttons', 'pd_admin_order_items_headers');
function pd_admin_order_items_headers()
{
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css"/>

    <button type="button" class="button add-order-item-custom">Dodaj proizvod (novo)</button>

    <div id="custom-add" class="modal" title="Dodaj proizvod" data-order-id="" data-security="">
        <div class="content">
            <div class="custom-add-row">
                <select class="item-list" style="width: 80%"></select>

                <label>kol</label>
                <input type="number" value="1" class="item-qty" style="width: 50px"/>
            </div>
        </div>

        <button type="button" class="button save-items">Dodaj</button>

        <div style="display: none;" class="custom-add-template">
            <div class="custom-add-row">
                <select class="item-list" style="width: 80%"></select>

                <label>kol</label>
                <input type="number" value="1" class="item-qty" style="width: 50px"/>
            </div>
        </div>
    </div>
    <?php
}