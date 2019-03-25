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
            echo printPreorder($order);

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

        case 'mis':
            if ($_GET['type'] === 'order') {
                $order = wc_get_order($_GET{'id'});
                new NSS_MIS_Order($order);
            }

            break;

        case 'findBySku':
            $item = get_product_by_sku($_GET['sku']);
            if ($item) {
                $catUrl = '';
                foreach ($item->get_category_ids() as $category_id) {
                    $cat = get_term_by('id', $category_id, 'product_cat');
                    if ($cat->parent === 0 && (!in_array($category_id, [3142])) ) {
                        $catUrl = get_term_link($category_id, 'product_cat');
                    }
                }

                $salePrice = $item->get_sale_price();
                $regularPrice = $item->get_regular_price();
                if (get_class($item) === WC_Product_Variable::class) {
                    //@TODO check all items when different prices for variations are implemented and used
                    $variation = wc_get_product($item->get_children()[0]);
                    $salePrice = $variation->get_sale_price();
                    $regularPrice = $variation->get_regular_price();
                }

                echo json_encode([
                    'sku' => $item->get_sku(),
                    'id' => $item->get_id(),
                    'title' => $item->get_title(),
                    'description' => $item->get_description(),
                    'itemUrl' => get_permalink($item->get_id()),
                    'categoryUrl' => $catUrl,
                    'regularPrice' => $regularPrice,
                    'salePrice' => $salePrice,
                    'imageSrc' => get_the_post_thumbnail_url($item->get_id()), // 'shop_catalog' => 200x200
                    'image' => get_the_post_thumbnail_url($item->get_id(), 'shop_catalog'),
                ]);
            }

            break;

        case 'getPrice':
            $productId = wc_get_product_id_by_sku($_GET['sku']);
            $product = wc_get_product($productId);

            $response = [
                'status' => 404,
                'price' => 0,
                'salePrice' => 0,
                'regularPrice' => 0
            ];

            if ($product) {
                $response = [
                    'status' => 200,
                    'price' => $product->get_price(),
                    'salePrice' => $product->get_sale_price(),
                    'regularPrice' => $product->get_regular_price()
                ];
            }

            echo json_encode($response);
            exit();

            break;

        case 'getAdresnice':
            $fileName = 'adresnica-' . date('dmy') . '.csv';
            $adresnicaPath = ABSPATH . '../wp-content/uploads/adresnice/' . $fileName;
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header("Content-Transfer-Encoding: binary");
            header('Content-type: text/csv');
            header('Expires: 0');
            header('Pragma: no-cache');
            echo file_get_contents($adresnicaPath);

            break;

        // served it's purpose, if everything ok delete this
        case 'fixAdresnice':
            ini_set('max_execution_time', 600);
            $zipArchive = new ZipArchive();
            $zipPath = generateUploadsPath() . date('Ymd') .'-adresnice-fix.zip';
            $open = $zipArchive->open($zipPath, ZipArchive::CREATE);
            if ($open !== true) {
                var_dump($open);
                die('cannot open');
            }

            $orderIds = [];
            $sors = file_get_contents(__DIR__ . '/adresnica-200219.csv');
            foreach (explode("\n", $sors) as $key => $line) {
                if ($key === 0) {
                    continue;
                }
                $orderNo = explode('|', $line)[0];
                if (!isset(explode('-', $orderNo)[1])) {
                    continue;
                }
                $orderId = explode('-', $orderNo)[1];
                if ($orderId === '') {
                    continue;
                }
                $order = wc_get_order($orderId);
                if (!$order) {
                    var_dump($orderId);
                    var_dump($order);
                    die();
                }

                $path = createAdresnicaPdf($order);
                if (file_exists($path) && is_readable($path)) {
                    $add = $zipArchive->addFile($path, basename($path));
                } else {
                    var_dump('there was a problem reading file: ' . $path);
                    die();
                }
                if ($add !== true) {
                    var_dump('could not add file to archive');
                }
            }
            if ($zipArchive->close() !== true) {
                var_dump('could not close archive.');
                die();
            }
            $path = str_replace('public_html', '', str_replace(strstr($zipPath, 'public_html', true), '', $zipPath));
            echo $path;

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
                        }
                    }
                }
            }
        }
    }

    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header('Content-type: text/plain');
    header("Content-Disposition: attachment; filename=".date('d-m-Y H:i:s').'.txt"');
    header('Content-Transfer-Encoding: binary');

    echo $csv;
}

function createAdresnica($orderId) {
    $order = wc_get_order($orderId);
    $path = createAdresnicaPdf($order);

    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header('Content-type: text/plain');
    header("Content-Disposition: attachment; filename=".basename($path));
    header('Content-Transfer-Encoding: binary');

    echo file_get_contents($path);
}

function exportJitexOrder(WC_Order $order) {
    $csvText = parseJitexDataFromOrder($order);

    header('Content-Disposition: attachment; filename="' . $order->get_order_number() . '.txt' . '"');
    header("Content-Transfer-Encoding: binary");
    header('Expires: 0');
    header('Pragma: no-cache');

//    print iconv('utf-8','windows-1250',str_replace(array('Ð', 'ð'), array('Đ', 'đ'), $csvText));
    $csvText = fixJitexCharacters($csvText);
    echo $csvText;
}

function fixJitexCharacters($str) {

    return str_replace(
        ['ć', 'Ć', 'č', 'Č', 'š', 'Š', 'đ', 'Đ', 'ž', 'Ž'],
        ['c', 'C', 'c', 'C', 's', 'S', 'd', 'D', 'z', 'Z'],
        $str
    );
}

function printOrder(WC_Order $order) {
    require (__DIR__ . '/templates/orders/printRacun.phtml');
}
