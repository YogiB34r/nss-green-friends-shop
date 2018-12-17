<?php
add_action('woocommerce_admin_order_totals_after_tax', 'custom_admin_order_totals_after_tax', 10, 1);
function custom_admin_order_totals_after_tax($orderid)
{
    $order = wc_get_order($orderid);
    $totalWeight = 0;
    foreach ($order->get_items() as $item_id => $item_data) {
        $totalWeight += $item_data->get_product()->get_weight() * $item_data->get_quantity();
    }

    $price = 0;
    if ($totalWeight > 0 and $totalWeight <= 0.5) {
        $price = 175;
    } elseif ($totalWeight > 0.5 and $totalWeight <= 2) {
        $price = 200;
    } elseif ($totalWeight > 2 and $totalWeight <= 5) {
        $price = 230;
    } elseif ($totalWeight > 5 and $totalWeight <= 10) {
        $price = 270;
    } elseif ($totalWeight > 10 and $totalWeight <= 20) {
        $price = 360;
    } elseif ($totalWeight > 20 and $totalWeight <= 30) {
        $price = 470;
    } elseif ($totalWeight > 30 and $totalWeight <= 50) {
        $price = 500;
    } elseif ($totalWeight > 50) {
        $newWeight = $totalWeight - 50;
        $price = 500 + ($newWeight * 10);
    }
//    if (isset(array_keys($order->get_shipping_methods())[0])) {
//        $order->remove_item(array_keys($order->get_shipping_methods())[0]);
//        $order->set_shipping_total(0);
//        $order->save();
//    }

    if (isset($_POST['items']) && array_search('free_shipping', \GuzzleHttp\Psr7\parse_query(urldecode($_POST['items'])))) {
        $order->remove_item(array_keys($order->get_shipping_methods())[0]);
        $shipping = new WC_Order_Item_Shipping();
        $shipping->set_name('Besplatna dostava');
        $shipping->set_total(0);
        $order->add_item($shipping);
        $order->set_shipping_total(0);
        $order->save();
    }

    if ($price > 0 && $order->get_shipping_total() == 0) {
        if($order->get_shipping_method() !== 'Besplatna dostava') {
            $shipping = new WC_Order_Item_Shipping();
            $shipping->set_total($price);
            $order->add_item($shipping);
            $order->set_shipping_total($price);
            $order->save();
        }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'woocommerce_remove_order_item') {
        $order->remove_item(array_keys($order->get_shipping_methods())[0]);
        $order->save();
    }

}



//$freeShipping = false;
//if (isset($_POST['items']) && array_search('free_shipping', \GuzzleHttp\Psr7\parse_query(urldecode($_POST['items'])))) {
//    $freeShipping = true;
//    $order->remove_item(array_keys($order->get_shipping_methods())[0]);
//    $order->set_shipping_total(0);
//    $order->save();
//}
//
////    var_dump($price);
////    var_dump($freeShipping);
////    var_dump($_POST['action']);
//
//if ((isset($_POST['action']) && $_POST['action'] == 'woocommerce_calc_line_taxes') && $price > 0 && !$freeShipping) {
//    $order->remove_item(array_keys($order->get_shipping_methods())[0]);
//    $order->set_shipping_total(0);
//    $order->save();
//
//    $shipping = new WC_Order_Item_Shipping();
//    $shipping->set_total($price);
//    $order->add_item($shipping);
//    $order->set_shipping_total($price);
//    $order->save();
//    echo 'saved';
//}


//------------------------------------------------------------
//*********** REMOVED FUNCTIONS FROM functions.php ***********
//------------------------------------------------------------
add_filter('post_date_column_time', 'gf_custom_post_date_column_time', 10, 2);
function gf_custom_post_date_column_time($h_time, $post)
{

    $h_time = get_the_time(__('d/m/Y', 'woocommerce'), $post);

    return $h_time;
}


//admin order list - date column
add_action('manage_posts_custom_column', 'gf_date_clmn');
function gf_date_clmn($column_name)
{
    global $post;
    if ($column_name == 'order_date') {
        $t_time = get_the_time(__('d/m/Y H:i', 'woocommerce'), $post);
        echo $t_time . '<br />';
    }
}

//***** ORDERS - admin *****
//add_filter('manage_edit-shop_order_columns', 'gf_order_payment_method_column');
//function gf_order_payment_method_column($order_columns) {
//    $order_columns['payment_method_column'] = "Način plaćanja";
//    $order_columns['order_phone_column'] = "Telefonom / www";
//    $order_columns['order_shipping_price_column'] = "Dostava";
//
//    return $order_columns;
//}

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

    if ($colname == 'payment_method_column') {
        echo $the_order->get_payment_method_title();
    }
    if ($colname == 'order_phone_column') {
        $via = 'WWW';
        if ($the_order->get_created_via() == 'admin') {
            $via = 'PHONE';
        }
        echo $via;
//        echo $the_order->get_meta('gf_order_created_method');
    }
    if ($colname == 'order_shipping_price_column') {
        echo $the_order->get_shipping_total() . 'din.';
    }
    if ($colname == 'customActions') {
        $jitexDoneStyle = '';
        $adresnicaDoneStyle = '';
        if ($the_order->get_meta('jitexExportCreated')) {
            $jitexDoneStyle = 'style="color:white;background-color:gray;font-style:italic;"';
        }
        if ($the_order->get_meta('adresnicaCreated')) {
            $adresnicaDoneStyle = 'style="color:white;background-color:gray;font-style:italic;"';
        }
//        echo '<a class="button" href="/back-ajax/?action=printOrder&id='. $the_order->get_id() .'" title="Print racuna" target="_blank">Racun</a>';
        echo '&nbsp;';
        echo '<a class="button" href="/back-ajax/?action=printPreorder&id=' . $the_order->get_id() . '" title="Print predracuna" target="_blank">Predracun</a>';
        echo '&nbsp;';
        echo '<a class="button nssOrderJitexExport" ' . $jitexDoneStyle . ' href="/back-ajax/?action=exportJitexOrder&id=' . $the_order->get_id() . '" title="Export za Jitex" target="_blank">Export</a>';
        echo '&nbsp;';
        echo '<a class="button nssOrderAdresnica" ' . $adresnicaDoneStyle . ' href="/back-ajax/?action=adresnica&id=' . $the_order->get_id() . '" title="Kreiraj adresnicu" target="_blank">Adresnica</a>';
//        echo $the_order->get_meta('gf_order_created_method');
    }
}

add_action('woocommerce_admin_order_data_after_order_details', 'gf_admin_phone_order_field');
function gf_admin_phone_order_field($order)
{
    $checked = true;
    if ($order->get_meta('gf_order_created_method') == 'WWW') {
        $checked = false;
    }
    woocommerce_form_field('gf_phone_order', array(
        'type' => 'checkbox',
        'class' => array('gf-admin-phone-order'),
        'label' => __('Poručivanje telefonom'),
        'required' => false,
    ), $checked);
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
        require("templates/admin/order/product-status.phtml");
    }
}


// ADDING A CUSTOM COLUMN TITLE TO ADMIN PRODUCTS LIST
add_filter('manage_edit-product_columns', 'gf_supplier_product_list_column', 11);
function gf_supplier_product_list_column($columns)
{
    //add columns
    $columns['supplier'] = __('Dobavljač', 'woocommerce'); // title
    return $columns;
}

// ADDING THE DATA FOR EACH PRODUCTS BY COLUMN (EXAMPLE)
add_action('manage_product_posts_custom_column', 'gf_supplier_product_list_column_content', 10, 2);
function gf_supplier_product_list_column_content($column, $product_id)
{
    global $post;

    $supplier_id = get_post_meta($product_id, 'supplier', true);
    if ($supplier_id) {
        switch ($column) {
            case 'supplier' :
                echo get_user_by('ID', $supplier_id)->display_name;
                break;
        }
    }

}

function gf_get_order_dates()
{
    $query = new WC_Order_Query(array(
        'limit' => -1,
        'return' => 'ids'
    ));
    $order_ids = $query->get_orders();

    $same_date = '';
    $order_dates = [];
    foreach ($order_ids as $order_id) {
        $date = get_the_time(__('d/m/Y', 'woocommerce'), $order_id);
        if ($date !== $same_date) {
            $order_dates[] = $date;
            $same_date = $date;
        }
    }

    return $order_dates;
}

add_filter('query_vars', 'gf_order_date_register_query_vars');
function gf_order_date_register_query_vars($qvars)
{
    $qvars[] = 'gf_order_date';
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
                $output .= "<option value='{$order_date}' " . selected($selected, $order_date, false) . '>' . $order_date . '</option>';
            endforeach;
        }
        $output .= "</select>";
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
        if (empty($meta_query))
            $meta_query = array();


        $meta_query[] = array(
            'key' => 'post_date',
            'value' => $order_date_str,
            'compare' => 'LIKE',
            'type' => 'DATE'
        );
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
    foreach ($post_ids as $post_id) {
        wp_remove_object_terms($post_id, $category_slug, 'product_cat');
    }
    $redirect_to = add_query_arg('bulk_remove_product_from_sliders', count($post_ids), $redirect_to);
    return $redirect_to;
}


//add_action('wp_enqueue_scripts', function(){
//    wp_enqueue_style( 'select2_css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
//    wp_register_script( 'select2_js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery'), '4.0.3', true );
//    wp_enqueue_script('select2_js');
//});

//admin product list filter by supplier *** START ***
add_filter('woocommerce_product_filters', 'gf_admin_product_list_supplier_filter', 10, 1);
function gf_admin_product_list_supplier_filter($output)
{
    $args = array(
        'role' => 'supplier',
        'orderby' => 'display_name',
        'order' => 'ASC'
    );
    $suppliers = get_users($args);
    $html = '<select name="product_supplier_filter">';
    $html .= '<option value="">Filtriraj po dobavljaču </option>';
    foreach ($suppliers as $supplier) {
        $html .= '<option value="' . $supplier->ID . '">' . $supplier->display_name . '</option>';
    }
    $html .= '</select>';

    echo $html;

    return $output;
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