<?php

// @TODO move this to class
if (defined('WP_CLI') && WP_CLI) {
    ini_set('max_execution_time', 1200);

    // elastic operations
    \WP_CLI::add_command('createElasticIndex', 'createElasticIndex');
    \WP_CLI::add_command('syncElasticIndex', 'syncElasticIndex');
    \WP_CLI::add_command('removeNonExistentProductsFromIndex', 'removeNonExistentProductsFromIndex');

    //debug
    \WP_CLI::add_command('passAllProducts', 'passAllProducts');
    \WP_CLI::add_command('passAllUsers', 'passAllUsers');
    \WP_CLI::add_command('createXmlExport', 'getItemExport');


    \WP_CLI::add_command('createFromExcell', 'createFromExcell');

    //feed processing
    $factory = new \Nss\Feed\FeedFactory();
    $feed = $factory();
    \WP_CLI::add_command('feed', $feed);

    \WP_CLI::add_command('mis', 'mis');

    \WP_CLI::add_command('daily', 'daily');

    \WP_CLI::add_command('createNalog', 'createNalog');

    \WP_CLI::add_command('createJitexItemExport', 'createJitexItemExport');

    \WP_CLI::add_command('testCron', 'testCron');

    \WP_CLI::add_command('testNl', 'testNl');
}

function testNl()
{
    $nl = new Newsletter();
    $nl->hook_newsletter();

}

/*
 * @TODO create automatic operations
 * */
add_action('parseFeed', 'parseFeed');
function parseFeed($args) {
    global $wpdb;

    $httpClient = new \GuzzleHttp\Client(['defaults' => [
        'verify' => false
    ]]);
    $redis = new \Redis();
    $redis->connect(REDIS_HOST);

    $feed = new \Nss\Feed\Feed($httpClient, $redis, $wpdb);
    $feed->parseFeed($args);
}



add_action('testCron', 'testCron');
function testCron() {
    $from = 'mailer@nonstopshop.rs';
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        "From: NonStopShop <'{$from}'>",
    ];
    $to[] = 'djavolak@mail.ru';
    $subject = 'test cron operation';
    $message = 'cron started at : '  . date('d-m-Y H:i:s');

    $send = wp_mail($to, $subject, $message, $headers);
//    $send = mail($to, $subject, $message);
    var_dump($send);
}

add_action('createNalog', 'createNalog');
function createNalog() {
    global $wpdb;

    if (ENVIRONMENT === 'production') {
        $dt = new \DateTime('now', new \DateTimeZone('Europe/Belgrade'));
        if (in_array($dt->format('D'), ['Sun', 'Sat'])) {
            return;
        }

        $backorder = new NSS_Backorder($wpdb);
        $backorder->createBackOrders();

        $sql = "SELECT backOrderId FROM wp_nss_backorder WHERE status <> 4 AND mailSent = 0";
//        $sql = "SELECT backOrderId FROM wp_nss_backorder WHERE status = 2 AND mailSent = 0 AND DATE(createdAt) = '2020-12-18'";
        foreach ($wpdb->get_results($sql) as $result) {
            $orders = $backorder->getBackOrders($result->backOrderId);
            $supplierId = $orders[0]->supplierId;
            $backorder->sendBackOrderEmail($supplierId, $orders);
        }

        $from = 'mailer@nonstopshop.rs';
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            "From: NonStopShop <'{$from}'>",
        ];
        $to[] = 'djavolak@mail.ru';
        $subject = 'Backorders created';
        $dt = new \DateTime('now', new \DateTimeZone('Europe/Belgrade'));
        $message = 'cron started at : ' . $dt->format('d/m/Y H:i:s');

        wp_mail($to, $subject, $message, $headers);
    }
}

function daily() {
    $api = new \GF\Util\DailyExpressApi();
    $api->sendAdresnice();
}

add_action('syncMis', 'mis');
function mis() {
//    $item = wc_get_product(558409);  //
//    new NSS_MIS_Item($item);
//    die();

//    $orderIds = [596937];
//    foreach ($orderIds as $orderId) {
//        $order = wc_get_order($orderId);
//        new NSS_MIS_Order($order);
//    }
//    die();

//    $order = wc_get_order(586068);
//    new NSS_MIS_Order($order);
//    die();

//    $user = get_user_by('id', 732);
//    new NSS_MIS_User($user);
//    die();

//    $dtStart = \DateTime::createFromFormat('d/m/Y', '28/4/2020');
//    $dtEnd = \DateTime::createFromFormat('d/m/Y', '12/5/2020');
//    $dt = $dtStart->getTimestamp() .'...'. $dtEnd->getTimestamp();

    $arg = array(
        'orderby' => 'date',
        'posts_per_page' => '1000',
//        'posts_per_page' => -1,
//        'date_created' => $dt,
//        'page' => 10,
    );
    $orders = WC_get_orders($arg);
//    var_dump(count($orders));
//    die();
    foreach ($orders as $order) {
        $ignoreStatuses = [
            'stornirano', 'cancelled', 'refunded', 'processing', 'reklamacija-pnns', 'pending', 'on-hold', 'failed'
        ];
        if (!in_array($order->get_status(), $ignoreStatuses)) {
            if (get_class($order) === WC_Order::class) {
                if ($order->get_meta('synced') !== '') {
//                    var_dump($order->get_status());
                } else {
                    $misOrder = new NSS_MIS_Order($order);
                    $order->add_order_note('Synced to MIS at: ' . date('d/m/Y H:i'));
                    $order->update_meta_data('synced', 1);
//                    echo $order->get_id();
//                    var_dump($order->get_meta('synced'));
                }
            } else if (get_class($order) === WC_Order_Refund::class) {
                continue;
            } else {
                var_dump($order);
                var_dump('problem ?');
                die();
            }
        }
    }
    echo 'total passed ' . count($orders);
}

add_action('createJitexItemExport', 'createJitexItemExport');
function createJitexItemExport()
{
    $csv = '';
    for ($i = 1; $i < 20; $i++) {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 3000,
            'page' => $i,
            'status' => ['publish']
        );
        $products = wc_get_products($args);
//        $itemsSaved = 0;
        /* @var $product WC_Product_Simple|WC_Product_Variable */
        foreach ($products as $key => $product) {
            try {
                if ($product->get_meta('pdv') >= 10) {
                    $taxcalc = (int) ('1' . $product->get_meta('pdv'));
                } else {
                    $taxcalc = (int) ('10' . (int)$product->get_meta('pdv'));
                }

                $csv .= iconv('utf-8', 'windows-1250//IGNORE', $product->get_sku() . "\t" . trim(mb_strtoupper($product->get_name(), 'UTF-8')) . "\t" .
                        str_replace('.', ',', $product->get_meta('pdv')) . "\t" .
                        str_replace('.', ',', round((int) $product->get_price() * 100 / (double) $taxcalc, 2)) . "\t" .
                        str_replace('.', ',', round((int) $product->get_price(), 2))) . "\r\n";
//                $itemsSaved++;

                if (get_class($product) === WC_Product_Variable::class) {
                    $passedIds = [];
                    foreach ($product->get_available_variations() as $variations) {
                        foreach ($variations['attributes'] as $variation) {
                            $itemIdSize = $product->get_sku() . $variation;
                            if (!in_array($itemIdSize, $passedIds)) {
                                $passedIds[] = $itemIdSize;
                                $csv .= iconv('utf-8', 'windows-1250//IGNORE', $itemIdSize . "\t" .
                                        trim(mb_strtoupper($product->get_name() . ' ' . $variation, 'UTF-8')) . "\t" .
                                        str_replace('.', ',', $product->get_meta('pdv')) . "\t" . str_replace('.', ',', round($product->get_price() * 100 / (double)$taxcalc, 2)) . "\t" .
                                        str_replace('.', ',', round($product->get_price(), 2))) . "\r\n";
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                var_dump($e->getMessage());
                die();
                continue;
            }
        }
    }
//    echo 'saved items: ' . $itemsSaved;

    $fileName = 'jitexItems.txt';
    $filePath = __DIR__ . '/../../uploads/feed/' . $fileName;
    file_put_contents($filePath, $csv);
}

function getItemExport() {
    global $wpdb;

    $xmlDoc = new DOMDocument('1.0', 'UTF-8');
    $root = $xmlDoc->createElement('proizvodi');

    $pages = 21;
    $limit = 1000;
    for ($i = 1; $i < $pages; $i++) {
        $products_ids = wc_get_products(array(
            'limit' => $limit,
            'return' => 'ids',
            'paged' => $i
        ));

        foreach ($products_ids as $product_id) {
            $product = wc_get_product($product_id);
            if ($product->is_in_stock()) {
                $root = createXml($xmlDoc, $product, $root);
            }
        }
    }

    $xmlDoc->appendChild($root);
    $xmlDoc->formatOutput = true;

    echo $xmlDoc->save(ABSPATH . '/wp-content/uploads/itemExport.xml');
}

function createXml(DOMDocument $xmlDoc, WC_Product $item, $root) {
    try {
        $cat = get_term_by('id', $item->get_category_ids()[0], 'product_cat');
        $pr = $xmlDoc->createElement('proizvod');
        $thumbnail = wc_placeholder_img_src();
        if (has_post_thumbnail($item->get_id())) {
            $thumbnail = get_the_post_thumbnail_url($item->get_id(), 'shop_catalog');
        }
        $product_link = get_permalink((int) $item->get_id());
        $salePrice = 0;
        if ($item->is_type('variable')){
            $regularPrice = $item->get_variation_regular_price();
        }else{
            $regularPrice = $item->get_regular_price();
        }
        $price = $regularPrice;
        if ($item->get_price() !== $regularPrice) {
            $salePrice = $item->get_price();
            $price = $salePrice;
        }

        $pr->appendChild($xmlDoc->createElement('sku'))
            ->appendChild($xmlDoc->createTextNode($item->get_sku()));
        $pr->appendChild($xmlDoc->createElement('kategorija_proizvoda'))
            ->appendChild($xmlDoc->createCDATASection($cat->name));
        $pr->appendChild($xmlDoc->createElement('naziv_proizvoda'))
            ->appendChild($xmlDoc->createCDATASection($item->get_name()));
        $pr->appendChild($xmlDoc->createElement('proizvodjac'))
            ->appendChild($xmlDoc->createTextNode(trim($item->get_meta('proizvodjac'))));
        $pr->appendChild($xmlDoc->createElement('model'))
            ->appendChild($xmlDoc->createTextNode($item->get_meta('vendor_code')));
        $pr->appendChild($xmlDoc->createElement('specifikacija'))
            ->appendChild($xmlDoc->createCDATASection(htmlspecialchars($item->get_description(), ENT_QUOTES, 'UTF-8')));
        $pr->appendChild($xmlDoc->createElement('cena'))
            ->appendChild($xmlDoc->createTextNode($price));
        $pr->appendChild($xmlDoc->createElement('nov_proizvod'))
            ->appendChild($xmlDoc->createTextNode($price));
        $pr->appendChild($xmlDoc->createElement('specifikacija'))
            ->appendChild($xmlDoc->createCDATASection(''));
        $pr->appendChild($xmlDoc->createElement('slika_url'))
            ->appendChild($xmlDoc->createTextNode($thumbnail));

        $akcija = 0;
        if ($salePrice > 0):
            $akcija = 1;
        endif;
        $pr->appendChild($xmlDoc->createElement('proizvod_na_akciji'))
            ->appendChild($xmlDoc->createTextNode($akcija));

        $pr->appendChild($xmlDoc->createElement('proizvod_url'))
            ->appendChild($xmlDoc->createTextNode($product_link));

        $root->appendChild($pr);
    } catch(Exception $e) {
        echo $e->getMessage();
        die();
    }

    return $root;
}

function passAllUsers() {
    global $wpdb;
//    $args = array(
//        'role'    => 'Customer',
//        'orderby' => 'user_nicename',
//        'order'   => 'ASC',
//        'limit' => 1,
//        'paged' => 1
//    );

    $sql = "SELECT user_email FROM wp_users u WHERE u.user_email NOT IN 
(SELECT email FROM wp_newsletter n) AND u.user_email NOT LIKE '%nonstopshop.rs' AND u.user_email NOT LIKE '!!DISABLED!!%' 
AND u.user_email <> '' AND user_email NOT LIKE '%telefonska%' LIMIT 30000";
    $emails = $wpdb->get_results($sql);

    ob_start();
    foreach ($emails as $email) {
//        $user = TNP::subscribe(['email' => $email->user_email, 'status' => 'C', 'send_emails' => false]);
//        if ($user) { // if object, should mean user is subscribed
//
//        }
        echo $email->user_email . PHP_EOL;
    }
    $csv = ob_get_clean();
    file_put_contents('csv', $csv);
}

function createFromExcell($args) {
    $cli = new \GF\Cli();

//    $cli->saleItems($args);
    $cli->createFromExcell();

//    $cli->migrateSaleItems($args);
}

function passAllProducts($args) {
    $cli = new \GF\Cli();

//    $cli->saleItems($args);
    $cli->listItems();

//    $cli->migrateSaleItems($args);
}

function removeNonExistentProductsFromIndex() {
    $cli = new \GF\Cli();
    $cli->removeNonExistentProducts();
}

function createElasticIndex() {
    $elasticaClient = new \GF\Search\Factory\ElasticClientFactory();
    $productSetupFactory = new \GF\Search\Factory\ProductSetupFactory($elasticaClient);
//    $termSetupFactory = new \GF\Search\Factory\TermSetupFactory($elasticaClient);
    $recreate = true;
    $productSetupFactory->make()->createIndex($recreate);
//    $termSetupFactory->make()->createIndex(false);
}

function syncElasticIndex() {
    ini_set('max_execution_time', 1200);
    $productConfig = new \GF\Search\Elastica\Config\Product();
    $elasticaClientFactory = new \GF\Search\Factory\ElasticClientFactory();
    $productType = $elasticaClientFactory->make()->getIndex($productConfig->getIndex())
        ->getType($productConfig->getType());
    $indexer = new \GF\Search\Elastica\Indexer($productType);
    $indexer->indexAll();
}

//add_action('woocommerce_process_product_meta', 'syncToElastic', 666, 3);
//function syncToElastic($id, WP_Post $post) {
add_action('woocommerce_update_product', 'syncToElastic', 10, 1);
function syncToElastic($id) {
    $product = wc_get_product($id);
    if ($product && strtolower($product->get_status()) != 'auto-draft' && strtolower($product->get_name()) != 'auto-draft') {
//        && $product->get_sku() != '') {

        if ($product->get_sku() == "") {
//            return;
            $product->set_sku(md5($product->get_id() . $product->get_name()));
//            $product->save();
        }
        $productConfig = new \GF\Search\Elastica\Config\Product();
        $elasticaClientFactory = new \GF\Search\Factory\ElasticClientFactory();
        $productType = $elasticaClientFactory->make()->getIndex($productConfig->getIndex())->getType($productConfig->getType());
        $indexer = new \GF\Search\Elastica\Indexer($productType);
        $indexer->indexProduct($product);
    }
}

add_action('feedCronStarter', 'nss_feed_start');
add_action('feedCronFillQueue', 'nss_feed_parse');
add_action('feedCronProcessQueue', 'nss_feed_process_queue');

//nss_feed_test();
function nss_feed_test(){
    $message = nss_feed_queue(123);
    var_dump($message);
    $message = nss_feed_process(123);
    var_dump($message);
    die();
}

function nss_feed_start() {
    $activeSuppliers = [
//        268, // vitapur
        252, // a sport
        123, // tv shop
    ];

    foreach (SUPPLIERS as $supplierId => $supplierData) {
        if (in_array($supplierId, $activeSuppliers)) {
            wp_schedule_single_event(time(), 'feedCronFillQueue', [$supplierId, $supplierData['name']]);
        }
    }
}

function nss_feed_parse($supplierId, $name) {
    \NSS_Log::log('nss_feed_parse start');
//    $supplierId = $args[0];
//    $name = $args[1];
    $from = 'mailer@nonstopshop.rs';
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        "From: NonStopShop <'{$from}'>",
    ];
    $to[] = 'djavolak@mail.ru';
    $subject = 'NSS feed cron report - parse items for: ' . SUPPLIERS[$supplierId]['name'];

    $message = nss_feed_queue($supplierId);
    wp_schedule_single_event(time(), 'feedCronProcessQueue', [$supplierId]);

    wp_mail($to, $subject, $message, $headers);
    \NSS_Log::log('nss_feed_parse done');
}

function nss_feed_process_queue($supplierId) {
//    \NSS_Log::log('nss_feed_process started');
    $from = 'mailer@nonstopshop.rs';
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        "From: NonStopShop <'{$from}'>",
    ];
    $to[] = 'djavolak@mail.ru';
    $subject = 'NSS feed cron report - process queue for: ' . SUPPLIERS[$supplierId]['name'];
    $message = nss_feed_process($supplierId);

    wp_mail($to, $subject, $message, $headers);
    \NSS_Log::log('nss_feed_process done');
}

function nss_feed_process($supplierId) {
    global $wpdb;

    $httpClient = new \GuzzleHttp\Client(['defaults' => [
        'verify' => false
    ]]);
    $redis = new \Redis();
    $redis->connect(REDIS_HOST);

//    $key = 'importFeedQueueCreate:' . SUPPLIERS[$args[0]]['name'] .':';
//    $importer = new \Nss\Feed\Importer($this->redis, $this->wpdb, $this->httpClient, $key);

    $key = 'importFeedQueueUpdate:' . SUPPLIERS[$supplierId]['name'] .':';
    $importer = new \Nss\Feed\Importer($redis, $wpdb, $httpClient, $key);

    $message = $importer->importItems(0, 1000);

    return $message;
}

function nss_feed_queue($supplierId) {
    $httpClient = new \GuzzleHttp\Client(['defaults' => [
        'verify' => false
    ]]);
    $redis = new \Redis();
    $redis->connect(REDIS_HOST);

    $parser = \Nss\Feed\ParserFactory::make(SUPPLIERS[$supplierId], $httpClient, $redis);;
    $stats = $parser->processItems();

    $message = 'Parse completed successfully.' . "\r\n";
    $message .= print_r($stats, true);
//    $message .= print_r($args, true);
    $message .= $parser->parseErrors();

    return $message;
}

