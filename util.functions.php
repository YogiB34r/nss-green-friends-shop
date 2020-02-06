<?php

function pricelist_import_page() {
    $updater = new \GF\Util\PricelistUpdate();
    if (isset($_FILES['cenovnik'])) {
        echo $updater->updatePricesAndStatuses($_FILES['cenovnik']);
    } else {
        echo $updater->getUploadForm();
    }
}

//Migrate comments from old site
function gf_migrate_comments()
{
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

add_action('createJitexItemExport', 'createJitexItemExport');
function createJitexItemExport()
{
    $csv = '';
    for ($i = 1; $i < 15; $i++) {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 2000,
            'page' => $i,
            'status' => 'publish'
        );
        $products = wc_get_products($args);

        /* @var $product WC_Product_Simple|WC_Product_Variable */
        foreach ($products as $product) {
            if ($product->get_meta('pdv') >= 10) {
                $taxcalc = (int)('1' . $product->get_meta('pdv'));
            } else {
                $taxcalc = (int)('10' . (int)$product->get_meta('pdv'));
            }

            $csv .= @iconv('utf-8', 'windows-1250', $product->get_sku() . "\t" . trim(mb_strtoupper($product->get_name(), 'UTF-8')) . "\t" .
                    str_replace('.', ',', $product->get_meta('pdv')) . "\t" . str_replace('.', ',', round($product->get_price() * 100 / (double)$taxcalc, 2)) . "\t" .
                    str_replace('.', ',', round($product->get_price(), 2))) . "\r\n";

            if (get_class($product) === WC_Product_Variable::class) {
                $passedIds = [];
                foreach ($product->get_available_variations() as $variations) {
                    foreach ($variations['attributes'] as $variation) {
                        $itemIdSize = $product->get_sku() . $variation;
                        if (!in_array($itemIdSize, $passedIds)) {
                            $passedIds[] = $itemIdSize;
                            $csv .= iconv('utf-8', 'windows-1250', $itemIdSize . "\t" .
                                    trim(mb_strtoupper($product->get_name() . ' ' . $variation, 'UTF-8')) . "\t" .
                                    str_replace('.', ',', $product->get_meta('pdv')) . "\t" . str_replace('.', ',', round($product->get_price() * 100 / (double)$taxcalc, 2)) . "\t" .
                                    str_replace('.', ',', round($product->get_price(), 2))) . "\r\n";
                        }
                    }
                }
            }
        }
    }

    $fileName = 'jitexItems.txt';
    $filePath = __DIR__ . '/../../uploads/feed/' . $fileName;
    file_put_contents($filePath, $csv);
}
function getJitexExport() {
    $fileName = 'jitexItems.txt';
    $filePath = __DIR__ . '/../../uploads/feed/' . $fileName;

    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header('Content-type: text/plain');
    header("Content-Disposition: attachment; filename=".$fileName.'"');
    header('Content-Transfer-Encoding: binary');

    echo file_get_contents($filePath);
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





add_action('admin_menu', function (){
    add_menu_page('Pretraga', 'Podesavanje pretrage', 'edit_pages',
        'gf-search-settings', 'handleAdminSearchSettings');
});

function handleAdminSearchSettings() {
    $config = array(
        'host' => ES_HOST,
        'port' => 9200
    );
    $client = new \Elastica\Client($config);
    $termSearch = new \GF\Search\Elastica\TermSearch($client);
//    $result = $term->getTerms();
    require(__DIR__ . "/templates/admin/search-settings.php");

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

