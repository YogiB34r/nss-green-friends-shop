<?php
/* Template Name: back ajax */


//ini_set('display_errors', 1);

global $wpdb;

//$sw = new \Symfony\Component\Stopwatch\Stopwatch();
//$sw->start('gfmain');

if (isset($_GET['action'])) {
    ini_set('max_execution_time', 1200);
    error_reporting(E_ALL);
    switch ($_GET['action']) {
        case 'printPreorder':
            $printMenu = false;
            $order = wc_get_order($_GET['id']);
            printPreorder($order);

            break;

        case 'printOrder':
            $printMenu = false;
            $order = wc_get_order($_GET['id']);
            printOrder($order);

            break;

        case 'exportJitexOrder':
            $printMenu = false;
            $order = wc_get_order($_GET['id']);
            exportJitexOrder($order);

            break;

        case 'adresnica':
            createAdresnica($_GET['id']);

            break;

        case 'dailyExpressCsv': // wc-spz-slanje
            $arg = array('orderby' => 'date', 'status' => ['spz-pakovanje', 'spz-slanje'], 'posts_per_page' => '500');
            $orders = WC_get_orders($arg);
            createDailyExport($orders);

            break;

        case 'jitexItemExport':
            ini_set('memory_limit', '1500M');
            ini_set('max_execution_time', '300');
            createJitexItemExport();

            break;

        case 'saveOrderItemStatus':
            $sql = "UPDATE wp_nss_backorderItems SET status = {$_GET['status']} WHERE backOrderId = {$_GET['backOrderId']}
            AND orderId = {$_GET['orderId']} AND itemId = {$_GET['itemId']}";
            $wpdb->query($sql);
            echo 1;

            break;

    }
}

function createJitexItemExport() {
    $csv = '';
    for ($i=1; $i<15; $i++) {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 2000,
            'page' => $i,
            'status' => 'publish'
        );
        $products = wc_get_products($args);

        /* @var $product WC_Product_Simple|WC_Product_Variable */
        foreach($products as $product) {
            if($product->get_meta('pdv') >= 10) {
                $taxcalc = (int) ('1' . $product->get_meta('pdv'));
            } else {
                $taxcalc = (int) ('10' . (int) $product->get_meta('pdv'));
            }

            $csv .= @iconv('utf-8','windows-1250',  $product->get_sku()."\t".trim(mb_strtoupper($product->get_name(), 'UTF-8'))."\t".
                    str_replace('.', ',', $product->get_meta('pdv'))."\t".str_replace('.', ',', round($product->get_price() * 100 / (double) $taxcalc, 2))."\t".
                    str_replace('.', ',', round($product->get_price(), 2)))."\r\n";

            if (get_class($product) === WC_Product_Variable::class) {
                $passedIds = [];
                foreach ($product->get_available_variations() as $variations) {
                    foreach ($variations['attributes'] as $variation) {
                        $itemIdSize = $product->get_sku() . $variation;
                        if (!in_array($itemIdSize, $passedIds)) {
                            $passedIds[] = $itemIdSize;
                            $csv .= iconv('utf-8','windows-1250',  $itemIdSize."\t".
                                    trim(mb_strtoupper($product->get_name() . ' ' . $variation, 'UTF-8'))."\t".
                                    str_replace('.',',',$product->get_meta('pdv'))."\t".str_replace('.', ',', round($product->get_price() * 100 / (double) $taxcalc, 2))."\t".
                                    str_replace('.', ',', round($product->get_price(), 2)))."\r\n";
//                                var_dump($product->get_sku() . $variation);
                        }
                    }
                }
            }
        }
    }

    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header('Content-type: text/plain');
    header("Content-Disposition: attachment; filename=".date('d-m-Y H:i:s').'.txt');
    header('Content-Transfer-Encoding: binary');

    echo $csv;
}

function createAdresnica($orderId) {
    $order = wc_get_order($orderId);
    $html = '';
    $order->update_status('spz-slanje');
    $order->update_meta_data('adresnicaCreated', 1);
    $order->save();

    require (__DIR__ . '/templates/orders/adresnica.phtml');

//    $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'en', true, 'Windows-1252');
    $html2pdf = new \Spipu\Html2Pdf\Html2Pdf();
    $html2pdf->writeHTML($html);
    $name = 'Adresnica-'.$order->get_order_number().'.pdf';
    $html2pdf->output($name, 'D');
}

function exportJitexOrder(
    WC_Order $order,
    $test,
    $tes12,
    $test123
) {
    $string = '';
    foreach ($order->get_items() as $item) {
        $p = wc_get_product($item->get_product()->get_id());

        $variation = '';
        if (get_class($p) === WC_Product_Variation::class) {
            foreach (array_values($p->get_variation_attributes())[0] as $value) {
                if (strstr($item->get_name(), $value)) {
                    $variation = $value;
                }
            }
        }

        if ($p->get_parent_id()) {
            $p = wc_get_product($p->get_parent_id());
        }
        $name = $order->get_billing_first_name() .' '. $order->get_billing_last_name();
        if ($order->get_meta('_billing_pib') != '') {
            $name = $order->get_billing_company() .' '. $order->get_meta('_billing_pib');
        }

        $variantId = $p->get_sku() . $variation;
        $variantName = $item->get_name();
        $date = $order->get_date_created()->format('d.m.Y');
        $modifier = (float) '1' .'.'. (int) number_format($p->get_meta('pdv'));
        $priceNoPdv = number_format((int) $p->get_price() / $modifier, 2, ',', '.');
        $priceFormated =number_format($p->get_price(), 2, ',', '.');
        $string .= $name."\t".$order->get_billing_address_1()."\t".$order->get_billing_postcode()."\t".$order->get_billing_city()."\t"."Srbija"."\t".
        $order->get_billing_phone()."\t".$order->get_order_number()."\t".$date."\t".$order->get_payment_method_title()."\t".$variantId."\t".$variantName."\t".
            $item->get_quantity()."\t".$priceNoPdv."\t".$priceFormated."\t".$order->get_billing_company()."\r\n";
    }
    $order->update_meta_data('jitexExportCreated', 1);
    $order->save();
    $shippingNoPdv = number_format($order->get_shipping_total() / 1.2, 2, ',', '.');

    $string .= $name."\t".$order->get_billing_address_1()."\t".$order->get_billing_postcode()."\t".$order->get_billing_city()."\t"."Srbija"."\t".
        $order->get_billing_phone()."\t".$order->get_order_number()."\t".$date."\t".$order->get_payment_method_title()."\t9999\tDostava\t1\t".
        $shippingNoPdv."\t".number_format($order->get_shipping_total(), 2, ',', '.')."\t".$order->get_billing_company();

    header('Content-Disposition: attachment; filename="' . $order->get_order_number() . '.txt' . '"');
    header("Content-Transfer-Encoding: binary");
    header('Expires: 0');
    header('Pragma: no-cache');
    print iconv('utf-8','windows-1250',str_replace(array('Ð', 'ð'), array('Đ', 'đ'), $string));
}

function printOrder(WC_Order $order) {
    require (__DIR__ . '/templates/orders/printRacun.phtml');
}
function printPreorder(WC_Order $order) {

    ob_start();
    require (__DIR__ . '/templates/orders/printPredracun.phtml');
    $html = ob_get_clean();

    echo $html;

//    $html2pdf = new \Spipu\Html2Pdf\Html2Pdf();
//    $html2pdf->writeHTML($html);
//    $name = 'Racun-'.$order->get_order_number().'.pdf';
//    $html2pdf->output($name, 'D');
}




