<?php
/* Template Name: back ajax */

ini_set('max_execution_time', 1200);
ini_set('display_errors', 1);
error_reporting(E_ALL);

global $wpdb;

//$sw = new \Symfony\Component\Stopwatch\Stopwatch();
//$sw->start('gfmain');

if (isset($_GET['action'])) {
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

function createDailyExport($orders) {
    ini_set('display_errors', 1);
    $adresnicaFields = [
        'ReferenceID','SBranchID','SName','SAddress','STownID','STown','SCName','SCPhone','PuBranchID','PuName',
        'PuAddress','PuTownID','PuTown','PuCName','PuCPhone','RBranchID','RName','RAddress','RTownID','RTown','RCName',
        'RCPhone','DlTypeID','PaymentBy','PaymentType','BuyOut','Value','Mass','ReturnDoc','SMS_Sender','Packages','Note', 'Content'
    ];

    $tmpFile = fopen(ABSPATH . '/wp-content/uploads/daily.csv', 'wa');

    //insert csv header
    if (!fputcsv($tmpFile, $adresnicaFields, '|')) {
        throw new Exception('Doslo je do greske prilikom generisanja csv izvestaja.');
    }

    /* @var \WC_Order $order */
    foreach ($orders as $order) {
        $billingName = $order->get_billing_first_name() .' '. $order->get_billing_last_name();
        if ($order->get_shipping_address_1() !== ''):
            $shippingName = $order->get_shipping_first_name() .' '. $order->get_shipping_last_name();
            $shippingAddress = $order->get_shipping_address_1();
            $shippingZip = $order->get_shipping_postcode();
            $shippingCity = $order->get_shipping_city();
        else:
            $shippingName = $order->get_billing_first_name() .' '. $order->get_billing_last_name();
            $shippingAddress = $order->get_billing_address_1();
            $shippingZip = $order->get_billing_postcode();
            $shippingCity = $order->get_billing_city();
        endif;
        $otkupnina = 0;
        if ($order->get_payment_method_title() == 'Pouzećem') {
            $otkupnina = number_format($order->get_total(), 0, '', '');
            $otkupnina = $otkupnina * 100;
        }
        $weight = 0;
        $category = '';
        /* @var WC_Order_Item_Product $item */
        foreach ($order->get_items() as $item) {
            $weight += $item->get_product()->get_weight();
            if (isset($item->get_product()->get_category_ids()[0])) {
                $cat = get_term_by('id', $item->get_product()->get_category_ids()[0], 'product_cat');
                $category = $cat->name;
            }
        }

        $csvArray = array(
            'gpoid' => $order->get_order_number(),

            'ID nalogodavca' => 'UK17357',
            'Naziv nalogodavca' => 'Non Stop Shop​ d.o.o.',
            'Adresa nalogodavca' => 'Žorža Klemansoa 19',
            'ID naselja nalogodavca' => 11000,
            'Naziv naselja/mesta nalogodavca' => 'Beograd',
            'Kontakt osoba nalogodavca' => 'NonStopShop',
            'Kontakt telefon nalogodavca' => '011/3334773',

            'ID posaljioca' => 'UK17357',
            'Naziv posaljioca' => 'Non Stop Shop​ d.o.o.',
            'Adresa mesta preuzimanja' => 'Žorža Klemansoa 19',
            'ID naselja mesta preuzimanja' => 11000,
            'Naziv naselja/mesta preuzimanja' => 'Beograd',
            'Kontakt osoba mesta preuzimanja' => 'NonStopShop',
            'Kontakt telefon mesta preuzimanja' => '011/3334773',

            'ID primaoca' => '',
            'Naziv primaoca' => $shippingName,
            'Adresa primaoca' => $shippingAddress,
            'ID naselja primaoca' => $shippingZip,
            'Naziv naselja/mesta primaoca' => $shippingCity,
            'Kontakt osoba primaoca' => $billingName,
            'Kontakt telefon primaoca' => $order->get_billing_phone(),

            'Tip isporuke' => 2,
            'Ko plaća' => 1,
            'Način plaćanja' => 2,
            'Otkupnina' => $otkupnina,
            'Vrednost' => 0,
            'Masa' => $weight * 1000,
            'Povratna dokumentacija' => '',
            'SMS pošiljaoca' => '',
            'Paketi' => 'SS'.preg_replace('/2018/', '', str_replace('-', '', $order->get_order_number()), 1), // i.e. 2018 -> '' , 2012 -> ''
            'Napomena' => '',
            'Content' => $category,
        );

        if (!fputcsv($tmpFile, $csvArray, '|')) {
            die('eerrorr');
        }
        $order->update_status('poslato');
    }

    echo 'done. exported '.count($orders).' orders.';
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

function exportJitexOrder(WC_Order $order) {
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
    require (__DIR__ . '/templates/orders/printPredracun.phtml');
}