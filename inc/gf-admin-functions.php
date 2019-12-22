<?php
add_action('woocommerce_admin_order_totals_after_tax', 'custom_admin_order_totals_after_tax', 10, 1);
function custom_admin_order_totals_after_tax($orderid) {
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
        $price = 600;
    } elseif ($totalWeight > 50) {
        $newWeight = $totalWeight - 50;
        $price = 600 + ($newWeight * 10);
    }

    if (isset($_POST['items']) && array_search('free_shipping', \GuzzleHttp\Psr7\parse_query(urldecode($_POST['items'])))) {
        $order->remove_item(array_keys($order->get_shipping_methods())[0]);
        $shipping = new WC_Order_Item_Shipping();
        $shipping->set_name('Besplatna dostava');
        $shipping->set_total(0);
        $order->add_item($shipping);
        $order->set_shipping_total(0);
        $order->save();
    }

    if ($price > 0 && $order->get_shipping_method() !== 'Besplatna dostava') {
        $shipping = new WC_Order_Item_Shipping();
        $shipping->set_total($price);
        if ($order->get_shipping_total() != 0) {
            $order->remove_item(array_keys($order->get_shipping_methods())[0]);
        }
        $order->add_item($shipping);
        $order->set_shipping_total($price);
        $order->save();
    }
    if (isset($_POST['action']) && $_POST['action'] === 'woocommerce_remove_order_item') {
        if (isset(array_keys($order->get_shipping_methods())[0])) {
//            $order->remove_item(array_keys($order->get_shipping_methods())[0]);
//            $order->save();
//            echo 'removed shipping';
        }
    }
}

//------------------------------------------------------------
//*********** REMOVED FUNCTIONS FROM functions.php ***********
//------------------------------------------------------------
add_filter('post_date_column_time', 'gf_custom_post_date_column_time', 10, 2);
function gf_custom_post_date_column_time($h_time, $post) {

    return get_the_time(__('d/m/Y', 'woocommerce'), $post);
}


//admin order list - date column
add_action('manage_posts_custom_column', 'gf_date_clmn');
function gf_date_clmn($column_name) {
    global $post;
    if ($column_name === 'order_date') {
        $t_time = get_the_time(__('d/m/Y H:i', 'woocommerce'), $post);
        echo $t_time . '<br />';
    }
}

add_filter('manage_edit-shop_order_columns', 'gf_custom_column_ordering_for_admin_list_order');
function gf_custom_column_ordering_for_admin_list_order($product_columns) {
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
function gf_get_order_payment_method_column($colname) {
    global $the_order; // the global order object

    if ($colname === 'payment_method_column') {
        echo $the_order->get_payment_method_title();
    }
    if ($colname === 'order_phone_column') {
        $via = 'WWW';
        if ($the_order->get_created_via() == 'admin') {
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
            echo '<a class="button " style="background-color:yellow;" ' . ' href="#" title="'.$orderNote.'"  target="_blank">Napomena</a>';
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
function gf_admin_phone_order_field($order) {
    $checked = true;
    if ($order->get_meta('gf_order_created_method') === 'WWW') {
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
function redirect_page() {
    switch (get_post_type()) {
        case "shop_order":
            $url = admin_url() . 'edit.php?post_type=shop_order';
            wp_redirect($url);
            exit;
            break;
    }
}

add_action('woocommerce_before_order_itemmeta', 'addItemStatusToOrderItemList', 10, 3);
function addItemStatusToOrderItemList($itemId, $item, $c){
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
function gf_supplier_product_list_column($columns){
    $columns['stockStatus'] = __('Lager', 'woocommerce'); // title
    $columns['supplier'] = __('Dobavljač', 'woocommerce'); // title

    return $columns;
}

add_action('manage_product_posts_custom_column', 'gf_supplier_product_list_column_content', 10, 2);
function gf_supplier_product_list_column_content($column, $product_id){
    global $post;

    $supplier_id = get_post_meta($product_id, 'supplier', true);
    if ($supplier_id) {
        switch ($column) {
            case 'supplier' :
                echo get_user_by('ID', $supplier_id)->display_name;
                break;
            case 'stockStatus':
                $product = wc_get_product($product_id);
                $stockStatus = false;
                if (get_class($product) == WC_Product_Variable::class) {
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
                    echo 'Na stanju '. $product->get_meta('quantity') .' komada';
                } else {
                    echo 'Nema na stanju';
                }
                break;
        }
    }
}

/**
 * @TODO check this out
 * @return array
 */
function gf_get_order_dates() {
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
    global $wpdb;

    $sql = "SELECT distinct DATE(post_date) as postDate FROM wp_posts WHERE post_type = 'shop_order' ORDER BY post_date DESC";
    $dates = $wpdb->get_results($sql);

    $orderDates = [];
    foreach ($dates as $date){
        $orderDates[] = date('d/m/Y', strtotime($date->postDate));
    }

    return $orderDates;
}

add_filter('query_vars', 'gf_order_date_register_query_vars');
function gf_order_date_register_query_vars($qvars){
    $qvars[] = 'gf_order_date';
    return $qvars;
}

add_action('restrict_manage_posts', 'gf_print_order_date_picker_admin_list');
function gf_print_order_date_picker_admin_list(){
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
function gf_order_date_apply_filter($query){
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
function register_gf_product_list_bulk_action($bulk_actions){
    if (isset($_GET['post_type']) && $_GET['post_type'] == 'product' && isset($_GET['filter_action']) && isset($_GET['product_cat'])) {
        $bulk_actions['remove_product_from_sliders'] = 'Ukloni iz kategorije: ' . $_GET['product_cat'];
    }

    return $bulk_actions;
}

add_filter('handle_bulk_actions-edit-product', 'gf_product_list_bulk_action_handler', 10, 3);
function gf_product_list_bulk_action_handler($redirect_to, $doaction, $post_ids){
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

//admin product list filter by supplier *** START ***
add_filter('woocommerce_product_filters', 'gf_admin_product_list_supplier_filter', 10, 1);
function gf_admin_product_list_supplier_filter($output) {
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
function gf_featured_products_admin_filter_query($query) {
    global $typenow, $wp_query;

    if ($typenow == 'product' && !empty($_GET['product_supplier_filter'])) {
        $query->query_vars['meta_key'] = 'supplier';
        $query->query_vars['meta_value'] = $_GET['product_supplier_filter'];
    }
}
//admin product list filter by supplier *** END ***

// EXTERNAL ITEM BANNERS WIDGET OPTIONS
add_action('admin_menu', 'gf_external_item_banners_widget_options_create_menu');
function gf_external_item_banners_widget_options_create_menu() {
    global $wpdb;
    $widget = new \GF\ExternalBannerWidget\ExternalBannerWidget($wpdb);
    //create new top-level menu
    add_menu_page('Carousel za partnerske sajtove', 'Carousel za partnere', 'administrator', 'external_item_banners_widget', function () use ($widget) {
        $widget->admin();
    }, null, 666);

    //call register settings function
    add_action('admin_init', function () use ($widget) {
        $widget->register_widget_options();
    });
}

add_filter('bulk_actions-edit-shop_order', 'bulkAdresniceExport', 20, 1);
function bulkAdresniceExport($actions) {
    $actions['adresniceExport'] = __('Adresnice', 'woocommerce');
    $actions['jitexExport'] = __('Jitex Export', 'woocommerce');

    return $actions;
}

function generateUploadsPath() {
    return __DIR__ . '/../../../uploads/'. date('Y') .'/'. date('m') .'/'. date('d') . '/';
}

add_filter('handle_bulk_actions-edit-shop_order', 'handleBulkAdresniceExport', 10, 3);
function handleBulkAdresniceExport($redirect_to, $action, $orderIds) {
    switch ($action) {
        case 'adresniceExport':
            $zipArchive = new ZipArchive();
            $zipPath = generateUploadsPath() . date('Ymdhis') .'-adresnice-'. md5(serialize($orderIds)) . '.zip';
            $open = $zipArchive->open($zipPath, ZipArchive::CREATE);
            if ($open !== true) {
                var_dump($open);
                die('cannot open');
            }

            foreach ($orderIds as $orderId) {
                $order = wc_get_order($orderId);
                $path = createAdresnicaPdf($order);
                if (file_exists($path) && is_readable($path)) {
                    $add = $zipArchive->addFile($path, basename($path));
                } else {
                    var_dump('there was a problem reading file: ' . $path);
                    die();
                }
                if ($add !== true) {
                    var_dump('could not add file to archive');
                    die();
                }
            }
            if ($zipArchive->close() !== true) {
                var_dump('could not close archive.');
                die();
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
            $zipPath = generateUploadsPath() . date('Ymdhis') .'-export-'. md5(serialize($orderIds)) . '.zip';
            $open = $zipArchive->open($zipPath, ZipArchive::CREATE);
            foreach ($orderIds as $orderId) {
                $order = wc_get_order($orderId);
                $csvText = parseJitexDataFromOrder($order);
                $add = $zipArchive->addFromString($order->get_order_number() . '.txt', $csvText);
            }
            if ($zipArchive->close() !== true) {
                var_dump('could not close archive.');
                die();
            }
            $path = str_replace('public_html', '', str_replace(strstr($zipPath, 'public_html', true), '', $zipPath));

            return $redirect_to = add_query_arg( array(
                'jitexExport' => '1',
                'processed_count' => count($orderIds),
                'zipPath' => $path,
            ), $redirect_to );

            break;

        default:
            return $redirect_to;
            break;
    }
}

// The results notice from bulk action on orders
add_action('admin_notices', 'bulkAdresniceAdminNotice');
function bulkAdresniceAdminNotice() {
    if (!isset($_REQUEST['processed_count'])) {
        return;
    }
    $count = (int) $_REQUEST['processed_count'];

    if (!empty($_REQUEST['adresniceExport'])) {
        echo '<div id="message" class="updated fade">
        <p>' . sprintf('Ukupno %s porudžbina obrađeno za <b>adresnice</b>.', $count) . '</p>
        <p>Adresnice možete preuzeti <a href="'.$_REQUEST['zipPath'].'">ovde</a></p>
        </div>';
    }
    if (!empty($_REQUEST['jitexExport'])) {
        echo '<div id="message" class="updated fade">
        <p>' . sprintf('Ukupno %s porudžbina obrađeno za <b>jitex export</b>.', $count) . '</p>
        <p>Jitex exporte možete preuzeti <a href="'.$_REQUEST['zipPath'].'">ovde</a></p>
        </div>';
    }
}

function parseJitexDataFromOrder(WC_Order $order) {
    $string = '';
    /* @var \WC_Order_Item_Product $item */
    foreach ($order->get_items() as $item) {
        $p = wc_get_product($item->get_product()->get_id());
        $variation = '';
        if (get_class($p) === WC_Product_Variation::class) {
            foreach ($p->get_variation_attributes() as $value) {
                $variation = $value;
            }
        }

        if ($p->get_parent_id()) {
            $p = wc_get_product($p->get_parent_id());
        }
        $name = $order->get_billing_first_name() .' '. $order->get_billing_last_name();
        if ($order->get_meta('_billing_pib') != '') {
            $name = $order->get_billing_company();
        }
        $variantId = $p->get_sku() . $variation;
        $variantName = str_replace('-', '', $item->get_name());
        $date = $order->get_date_created()->format('d.m.Y');
        $itemPrice = (int) $item->get_total() / $item->get_quantity();
        $modifier = (float) '1' .'.'. (int) number_format($p->get_meta('pdv'));
        $priceNoPdv = number_format($itemPrice / $modifier, 2, ',', '.');
        $priceFormated = number_format($itemPrice, 2, ',', '.');
        $string .= $name."\t".$order->get_billing_address_1()."\t".$order->get_billing_postcode()."\t".$order->get_billing_city()."\t"."Srbija"."\t".
            $order->get_billing_phone()."\t".$order->get_order_number()."\t".$date."\t".$order->get_payment_method_title()."\t".$variantId."\t".$variantName."\t".
            $item->get_quantity()."\t".$priceNoPdv."\t".$priceFormated."\t".$order->get_billing_company()."\t".$order->get_meta('_billing_pib')."\r\n";
    }
    $order->update_meta_data('jitexExportCreated', 1);
    $order->save();
    $shippingNoPdv = number_format($order->get_shipping_total() / 1.2, 2, ',', '.');

    $string .= $name."\t".$order->get_billing_address_1()."\t".$order->get_billing_postcode()."\t".$order->get_billing_city()."\t"."Srbija"."\t".
        $order->get_billing_phone()."\t".$order->get_order_number()."\t".$date."\t".$order->get_payment_method_title()."\t9999\tDostava\t1\t".
        $shippingNoPdv."\t".number_format($order->get_shipping_total(), 2, ',', '.')."\t".$order->get_billing_company();

    return $string;
}

function createAdresnicaPdf(WC_Order $order) {
    $name = 'Adresnica-'.$order->get_order_number().'.pdf';
//    $uploadsDir = generateUploadsPath();
//    if (file_exists($uploadsDir . $name)) {
//        return $uploadsDir . $name;
//    }

    if (in_array($order->get_status(), ['spz-pakovanje'])) {
        $order->update_status('spz-slanje');
    }
    $order->update_meta_data('adresnicaCreated', 1);
    $order->save();

    $html = '';
    require (__DIR__ . '/../templates/orders/adresnica.phtml');

    //test dir structure
    $uploadsDir = __DIR__ . '/../../../uploads/'. date('Y');
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir);
    }
    $uploadsDir .= '/'. date('m');
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir);
    }
    $uploadsDir .= '/'. date('d');
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir);
    }
    $uploadsDir .= '/';
    $filePath = $uploadsDir . $name;

    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->render();
    file_put_contents($filePath, $dompdf->output());

//    $pdf = new \Spipu\Html2Pdf\Html2Pdf();
//    $pdf->writeHTML($html);
//    $pdf->output($uploadsDir . $name, 'F');

    return $filePath;
}

function printPreorder(WC_Order $order) {
    ob_start();
    require (__DIR__ . '/../templates/orders/printPredracun.phtml');
    $html = ob_get_clean();

    return $html;
}

add_action('woocommerce_product_options_general_product_data', 'addStickerInfoToProductTabs');
function addStickerInfoToProductTabs() {
    echo '<div class="options_group">';
    $isActive = get_post_meta(get_the_ID(), 'sale_sticker_active', true);
    $class = "";
    if ($isActive !== 'yes') {
        $class = "hidden";
    }
    woocommerce_wp_checkbox( array(
        'id'      => 'sale_sticker_active',
        'value'   => get_post_meta(get_the_ID(), 'sale_sticker_active', true),
        'label'   => 'Sale sticker',
        'desc_tip' => true,
        'description' => 'Add a sale sticker to this product.',
    ) );

    echo '<div class="'. $class .' saleStickerOptionContainer">';

    $dateFrom = get_post_meta(get_the_ID(), 'sale_sticker_from', true);
    woocommerce_wp_text_input([
        'id' => 'sale_sticker_from',
        'class' => 'datepicker',
        'value'   => ((int) $dateFrom > 0) ? date('d/m/Y', (int) $dateFrom) : '',
        'label'   => 'Start date',
        'description' => 'Select start date',
    ]);

    $dateTo = get_post_meta(get_the_ID(), 'sale_sticker_to', true);
    woocommerce_wp_text_input([
        'id' => 'sale_sticker_to',
        'class' => 'datepicker',
        'value'   => ((int) $dateTo > 0) ? date('d/m/Y', (int) $dateTo) : '',
        'label'   => 'End date',
        'description' => 'Select end date.',
    ]);
        echo '</div>';
    echo '</div>';
}

add_action( 'woocommerce_process_product_meta', 'saveStickerInfo', 10, 2 );
function saveStickerInfo($id, $post) {
    update_post_meta( $id, 'sale_sticker_from', strtotime($_POST['sale_sticker_from']));
    update_post_meta( $id, 'sale_sticker_to', strtotime($_POST['sale_sticker_to']));
    update_post_meta( $id, 'sale_sticker_active', $_POST['sale_sticker_active']);
}

add_action( 'woocommerce_order_item_add_line_buttons', 'pd_admin_order_items_headers' );
function pd_admin_order_items_headers(){
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css" />

    <button type="button" class="button add-order-item-custom">Dodaj proizvod (test)</button>

    <div id="custom-add" class="modal" title="Dodaj proivod" data-order-id="" data-security="">
        <div class="content">
            <div class="custom-add-row">
<!--                <label style="float: left">Select product</label>-->
                <select class="item-list" style="width: 80%"></select>

                <label>Qty</label>
                <input type="number" value="1" class="item-qty" style="width: 50px" />
            </div>
        </div>

        <button type="button" class="button save-items">Add</button>

        <div style="display: none;" class="custom-add-template">
            <div class="custom-add-row">
                <select class="item-list" style="width: 80%"></select>

                <label>Qty</label>
                <input type="number" value="1" class="item-qty" style="width: 50px" />
            </div>
        </div>
    </div>
    <?php
}

/*
add_action( 'woocommerce_admin_order_item_values', 'pd_admin_order_item_values', 3 );
function pd_admin_order_item_values( $product, $item, $item_id ) {
    //Get what you need from $product, $item or $item_id
    ?>
    <td class="line_customtitle">
        <?php //your content here ?>
    </td>
    <?php
}*/