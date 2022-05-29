<?php
/* Template Name: back ajax */

global $wpdb;
//$sw = new \Symfony\Component\Stopwatch\Stopwatch();
//$sw->start('gfmain');
use GF\Esir\Actions\FiskalAdvanceInvoice;
use GF\Esir\Actions\SendAdvanceInvoice;
use GF\Esir\Actions\SendAdvanceRefund;
use GF\Esir\Actions\SendNormalInvoice;
use GF\Esir\Actions\SendNormalRefund;
use GF\Esir\EsirIntegration;
use GF\Esir\EsirIntegrationLogHandler;
use GuzzleHttp\Exception\GuzzleException;
use League\Flysystem\FilesystemException;

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
        case 'sendNormalInvoice':
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            $sent = 0;
            try {
                $json = getEsirFileContentsFromDropbox($_GET['id']);
                EsirIntegrationLogHandler::saveResponse($_GET['id'], $json, 'getFile', EsirIntegrationLogHandler::STATUS_WAITING);
                $json = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
                $sendNormalInvoice = new SendNormalInvoice($json, $_GET['id']);
                $sendNormalInvoice();
            } catch (\Exception|FilesystemException|GuzzleException $e) {
                echo $e->getMessage();
                return;
            }
            echo 'Racun poslat na fiskalizaciju, bićete preusmereni nazad za 5 sekundi.';
            echo '<script>
                     setTimeout(function(){
                         history.back();
                     }, 5000);
                    </script>';
            break;
        case 'sendNormalInvoiceFromAdvance':
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            $sent = 0;
            try {
                $json = getEsirFileContentsFromDropbox($_GET['id']);
                EsirIntegrationLogHandler::saveResponse($_GET['id'], $json, 'getFile', EsirIntegrationLogHandler::STATUS_WAITING);
                $json = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
                $sendNormalInvoiceFromAdvance = new FiskalAdvanceInvoice($json, $_GET['id']);
                $sendNormalInvoiceFromAdvance();
            } catch (\Exception|FilesystemException|GuzzleException $e) {
                echo $e->getMessage();
                return;
            }
            echo 'Racun poslat na fiskalizaciju, bićete preusmereni nazad za 5 sekundi.';
            echo '<script>
                     setTimeout(function(){
                         history.back();
                     }, 5000);
                    </script>';
            break;
        case 'sendAdvanceInvoice':
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            $sent = 0;
            try {
                $json = \GF\Esir\EsirIntegration::createJsonForAdvanceInvoice($_GET['id']);
                EsirIntegrationLogHandler::saveResponse($_GET['id'], $json, 'createAdvanceInvoice', EsirIntegrationLogHandler::STATUS_WAITING);
                $json = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
                $sendAdvanceInvoice = new SendAdvanceInvoice($json, $_GET['id']);
                $sendAdvanceInvoice();
            } catch (\Exception|FilesystemException|GuzzleException $e) {
                echo $e->getMessage();
                return;
            }
            echo 'Avansni racun poslat na fiskalizaciju, bićete preusmereni nazad za 5 sekundi.';
            echo '<script>
                     setTimeout(function(){
                         history.back();
                     }, 5000);
                    </script>';
            break;
        case 'sendNormalRefund':
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            $sent = 0;
            try {
                $json = getEsirFileContentsFromDropbox($_GET['id']);
                EsirIntegrationLogHandler::saveResponse($_GET['id'], $json, 'getFile', EsirIntegrationLogHandler::STATUS_WAITING);
                $json = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
                $sendNormalRefund = new SendNormalRefund($json, $_GET['id']);
                $sendNormalRefund();
            } catch (\Exception|FilesystemException|GuzzleException $e) {
                echo $e->getMessage();
                return;
            }
            echo 'Racun poslat na refundaciju, bićete preusmereni nazad za 5 sekundi.';
            echo '<script>
                     setTimeout(function(){
                         history.back();
                     }, 5000);
                    </script>';
            break;
        case 'sendAdvanceRefund':
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            $sent = 0;
            try {
                $json = getEsirFileContentsFromDropbox($_GET['id']);
                EsirIntegrationLogHandler::saveResponse($_GET['id'], $json, 'getFile', EsirIntegrationLogHandler::STATUS_WAITING);
                $json = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
                $sendAdvanceRefund = new SendAdvanceRefund($json, $_GET['id']);
                $sendAdvanceRefund();
            } catch (\Exception|FilesystemException|GuzzleException $e) {
                echo $e->getMessage();
                return;
            }
            echo 'Avansni racun poslat na refundaciju, bićete preusmereni nazad za 5 sekundi.';
            echo '<script>
                     setTimeout(function(){
                         history.back();
                     }, 5000);
                    </script>';
            break;
        case 'prihvatiFiskalizovanRacun':
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
            $response = file_get_contents('php://input');
            \GF\Esir\EsirIntegration::errorLog($response);
            try {
                \GF\Esir\EsirIntegration::processEsirResponse($response);
            } catch (\Exception $e) {
                \GF\Esir\EsirIntegration::errorLog($e->getMessage());
            }
            break;
        case 'printajFiskalizovanRacun':
            $data = EsirIntegrationLogHandler::getEsirResponse($_GET['id']);
            if ($data !== []) {
                $wcOrder = wc_get_order($_GET['id']);
                echo '<pre><p>Broj narudžbenice #<b>'.$wcOrder->get_order_number().'</b></p>' . json_decode($data[0]->response)->journal . '</pre>';
                echo '<img width="300px" src="'.home_url() . '/wp-content/uploads/qrinvoices/' . $wcOrder->get_order_number() .'.jpg" />';
                break;
            }
        case 'manualRefund':
            try {
                //@todo test this
                $referentDocumentNumber = $_GET['refDocNumber'];
                $json = getEsirFileContentsFromDropbox($_GET['id']);
                EsirIntegrationLogHandler::saveResponse($_GET['id'], $json, 'getFile', EsirIntegrationLogHandler::STATUS_WAITING);
                $json = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
                $json->referentDocumentNumber = $referentDocumentNumber;
                EsirIntegration::sendJsonToEsir($json);
            } catch (GuzzleException|JsonException|FilesystemException $e) {
                var_dump($e->getMessage());
                die();
            }
            break;
    }
}


/**
 * Returns JSON ready for ESIR
 *
 * @param $orderId
 * @return string
 * @throws JsonException
 * @throws FilesystemException
 */
function getEsirFileContentsFromDropbox($orderId): string
{
    $order = wc_get_order($orderId);
    $orderNumber = $order->get_order_number();
    $dropbox = new \GF\DropBox\DropboxApi();
    $dropbox->setupFileSystem();
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