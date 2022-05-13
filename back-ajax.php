<?php
/* Template Name: back ajax */

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
            \Gf\Util\Jitex::exportJitexOrder($order);

            break;

        case 'adresnica':
            ini_set('display_errors', 1);
            \Gf\Util\Adresnica::createAdresnica($_GET['id']);

            break;

        case 'dailyExpressCsv': // wc-spz-slanje
            $arg = array('orderby' => 'date', 'status' => ['spz-pakovanje', 'spz-slanje'], 'posts_per_page' => '1000');
            $orders = WC_get_orders($arg);
            createDailyExport($orders);

            break;

        case 'jitexItemExport':
            \Gf\Util\Jitex::getJitexExport();

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

        case 'backendProductSearch':
            backendProductSearch();

            break;

        case 'dropboxAuth':
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            try {
                $dropboxApi = new \GF\DropBox\DropboxApi();
                $dropboxApi->dropBoxAuthConsent();
            } catch (\Exception $e) {
                var_dump($e->getMessage());
                die();
            }

            break;
        case 'dropboxAuthComplete':
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            try {
                $dropboxApi = new \GF\DropBox\DropboxApi();
                $dropboxApi->saveRefreshToken();
                wp_redirect(get_home_url() . '/wp-admin/admin.php?page=order-analytics');
            } catch (\Exception $e) {
                var_dump($e->getMessage());
                die();
            }

            break;
        case 'fiskalniRacun':
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            $logTitle = 'Sending JSON to ESIR for orderid ' . $_GET['id'];
            $sent = 0;
            try {
                $json = getEsirFileContentsFromDropbox($_GET['id']);
                \GF\Esir\EsirIntegrationLogHandler::saveDropoxResponse($_GET['id'], $json);
                if (\GF\Esir\EsirIntegration::sendJsonToEsir($json, $logTitle)) {
                    $sent = 1;
                }
            } catch (\League\Flysystem\FilesystemException $e) {
//                \WP_Logging::add($logTitle . ' has FAILED', $e->getMessage());
                echo $e->getMessage();
                die();
            } catch (\Exception $e) {
//                \WP_Logging::add($logTitle . ' has FAILED', $e->getMessage());
                echo $e->getMessage();
                die();
            }
            if ($sent) {
                echo 'Racun poslat na fiskalizaciju.';
            } else {
                echo 'Doslo je do greske prilikom slanja racuna na fiskalizaciju.';
            }
            break;

        case 'prihvatiFiskalizovanRacun':
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
            $response = file_get_contents('php://input');
            $response = '[{"orderID":"05052028-663073","merchantTin":"109837860","fiscalizationDate":"2022-05-12 17:28:58","verificationUrl":"https://sandbox.suf.purs.gov.rs/v/?vl=A0Y3Q0JaTUxRRHQxT3YxbzA9CAAABAcAAEADXwMAAAAAAAABgLjjL%2FwAAACp%2BLAGwIkAP97oyEBD2DC8DLO69E1ePPvYUjziQO%2Fi2dxTl1zep%2FuO9s7yDvB5ha8vilpY9RQvplsAKF9DZriiiIwl1JBQH9TbbFk0cy%2BiflwY7xpbCgLw%2BQ6iTge%2Fwk1as51JWIHdJogHpfAcb%2FuvbKWLaejL5S5M9Z1LlGqum46e1%2FT%2FvOldLiTZdF8c%2FXfcoUeqAE9AFOPq8Vr2O4r5ZbpT0zymgpJN05vFYsSPLe9PxgJTqY0XSFGCK3byh1fJZxlvbUZ5OkjI1Ub8BMknFiUslU9KeZeh5evI2eYpkhJ4onvrb3%2Bskf9ml%2B9MFMew00DQsYgvRtXyI2nCpwTG6A%2FUQmiiQYh0rmwbSJMeoplOcGwE9%2FUMXCbve8%2FRRytQGkCMAXngYqfERDNlxSd8r6Cv9Y8%2B3BYhka3AWp2zu9jkRENs1qrTAGztIEh50FdeRHuXKuj0a5hCTDx9keoVh85lXDZCl%2Fx%2FYw0EFywbCFHiCruI1gdvQcpFjKQ5CK%2BDJHCuDHO5ocLPRV2gSVfOx26es4boWHsSpzI%2Fm9iKszQ6awzdeZsikWOIZTxIbRKDWd3pPAQcyGS51a9EWSIyWtuxPnICFqFdWSC2NF3T6VqVF%2B4wr1%2FXpZUE9oRS2uPw5nKqsVH8EcTWM0ShupiK5QHASZSAbFs5OZ1%2FJKWOEDMKCpPl3Jtst6bZDX6cq9I%3D","invoiceNumber":"F7CBZMLQ-Dt1Ov1o0-2109","journal":"============ ФИСКАЛНИ РАЧУН ============\r\nПИБ:                           112591486\r\nПредузеће:                    Loads King\r\nМесто продаје:                Loads King\r\nАдреса:                   Mlatisumina 5.\r\nОпштина:                          Врачар\r\nКасир:                                  \r\nЕСИР број:                   283/1.0.0.0\r\n-------------ПРОМЕТ ПРОДАЈА-------------\r\nАртикли\r\n========================================\r\nНазив   Цена         Кол.         Укупно\r\nFLASTERI ZA SMANJENJE APETITA I PODRŠKA \r\nPRI MRŠAVLJENJU - GO (Kom.) (C)         \r\n     2.914,17          1        2.914,17\r\nGREEN FIT BILJNE KAPI ZA MRŠAVLJENJE 2+1\r\n GRATIS (Kom.) (C)                      \r\n     2.500,00          1        2.500,00\r\nTROŠKOVI DOSTAVE (Kom.) (C)             \r\n       241,67          1          241,67\r\n----------------------------------------\r\nУкупан износ:                   5.655,84\r\nГотовина:                       5.655,84\r\nПовраћај:                           0.00\n========================================\r\nОзнака       Име      Стопа        Порез\r\nC        VAT-EXCL    0,00%          0,00\r\n----------------------------------------\r\nУкупан износ пореза:                0,00\r\n========================================\r\nПФР време:          12.05.2022. 17:28:58\r\nПФР број рачуна:  F7CBZMLQ-Dt1Ov1o0-2109\r\nБројач рачуна:               1796/2109ПП\r\n========================================\r\n======== КРАЈ ФИСКАЛНОГ РАЧУНА =========\r\n","messages":"Success","invoiceCounter":"1796/2109ПП","invoiceType":"NORMAL","transactionType":"SALE"}]';
            \GF\Esir\EsirIntegration::processEsirResponse($response);

            break;

        case 'printajFiskalizovanRacun':

            break;
    }
}


/**
 * Returns JSON ready for ESIR
 *
 * @param $orderId
 * @return string
 * @throws JsonException
 */
function getEsirFileContentsFromDropbox($orderId) {
    $order = wc_get_order($orderId);
    $orderNumber = $order->get_order_number();
    $dropbox = new \GF\DropBox\DropboxApi();
    $dropbox->setupFileSystem();
    $orderNumber = '05052022-663073'; // 04052022-662935

    return $dropbox->getOrderFileContents($orderNumber);
}

function appendItemForBackendSearch(WC_Product $product) {
    $data = [];

    if ($product->get_status() == 'publish' && $product->get_stock_status() == 'instock') {
        $data[] = [
            'id' => $product->get_id(),
            'text' => $product->get_name()
        ];
        if (get_class($product) == \WC_Product_Variable::class) {
            foreach ($product->get_available_variations() as $available_variation) {
                $variation = wc_get_product($available_variation['variation_id']);
                $data[] = [
                    'id' => $available_variation['variation_id'],
                    'text' => $variation->get_name()
                ];
            }
        }
    }

    return $data;
}

function backendProductSearch() {
    global $searchFunctions;

    $data = [];
    $query = $_GET['query'];
    $productId = wc_get_product_id_by_sku($query);
    if ($productId) {
        $product = wc_get_product($productId);
        if ($product) {
            $data = array_merge($data, appendItemForBackendSearch($product));
        }
    } else {
        $product = wc_get_product($query);
    }
    if ($product) {
        $data = array_merge($data, appendItemForBackendSearch($product));
    }

    $results = $searchFunctions->getResults('', $_GET['query']);
    foreach ($results->getResults() as $result) {
        $product = wc_get_product($result->getData()['postId']);
        if ($product->is_purchasable()) {
            $data = array_merge($data, appendItemForBackendSearch($product));
        }
    }

    echo json_encode([
        'security' => wp_create_nonce(),
        'results' => $data,
        'pagination' => [
            'more' => false
        ]
    ]);
}

function printOrder(WC_Order $order) {
    require (__DIR__ . '/templates/orders/printRacun.phtml');
}