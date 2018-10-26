<?php
/* Template Name: feed-test */
//require (ABSPATH . 'wp-content/plugins/nss-feed-import/classes/Parser/ParserInterface.php');
//require (ABSPATH . 'wp-content/plugins/nss-feed-import/classes/Parser/Vitapur.php');
//require (ABSPATH . 'wp-content/plugins/nss-feed-import/classes/Parser/Asport.php');
//require (ABSPATH . 'wp-content/plugins/nss-feed-import/classes/Parser/Nss.php');
//require (ABSPATH . 'wp-content/plugins/nss-feed-import/classes/ParserFactory.php');
//require (ABSPATH . 'wp-content/plugins/nss-feed-import/classes/Product.php');
//require (ABSPATH . 'wp-content/plugins/nss-feed-import/classes/Importer.php');
//require ('classes/NSS_Log.php');

ini_set('max_execution_time', 1200);
ini_set('display_errors', 1);
error_reporting(E_ALL);

gf_feed1();

function gf_feed1() {
    global $wpdb;

    $route = isset($_GET['tab']) ? $_GET['tab'] : '';
    $supplierId = $_GET['supplierId'];
    $supplierId = 666;

    set_time_limit(0);
    ini_set('max_execution_time', 60 * 60 * 6); // 6 hrs

    switch ($route) {
        case 'parseFeed':
            $supplierId = 666;
            gf_start_parsing1($supplierId);

            break;

        case 'resetQueue':
            $supplierId = 666;
            gf_reset_queue1($wpdb, $supplierId);

            break;

        case 'importItems':
            $supplierId = 666;
            $counts = gf_start_import1($wpdb, $supplierId);
            $msg = '<p>updated total of items: ' . count($counts['updated']) . '</p>';
            $msg .= '<p>created total of items: ' . count($counts['created']) . '</p>';
            $msg .= '<p>from a total of items: ' . $counts['total'] . '</p>';
            NSS_Log::log($msg, NSS_Log::LEVEL_DEBUG);
            echo $msg;

            break;

        default:

            break;

    }
    renderActions1();
}

function renderActions1() {
    echo '<a href="/feed-test/?tab=parseFeed">Parse feed</a><br />';
    echo '<a href="/feed-test/?tab=importItems">Import items</a><br />>';
    echo '<a href="/feed-test/?tab=resetQueue">Reset queue</a><br />';
    echo '<a id="import" href="#">TEST</a>';
    ?>
    <script>
        var running = 0;
        jQuery(document).ready(function() {
            jQuery('#import').click(function() {
                running++;
                startImport(running);
            });
        });
        function startImport(page) {
            jQuery.ajax({
                type: "POST",
                url: '/gf-ajax/?import=true',
                data:{'page': page},
                minLength: 0,
                success: function(response){
                    if (response) {
                        page++;
                        startImport(page);
                    }
                }
            });
        }
    </script>

    <?php
}

function gf_start_import1($wpdb, $supplierId, $offset = 0, $limit = 100) {


    $httpClient = new \GuzzleHttp\Client();
    $redis = new Redis();
    $redis->connect(REDIS_HOST);
    $limit = 5000;

    $key = 'importFeedQueue:' . SUPPLIERS[$supplierId]['name'] .':';
    $importer = new Nss\Feed\Importer($redis, $wpdb, $httpClient, $key);

    var_dump($importer->getCount());

    return $importer->importItems($offset, $limit);
}

function gf_start_parsing1($supplierId) {
    $httpClient = new \GuzzleHttp\Client(['timeout' => 0]);
    $redis = new Redis();
    $redis->connect(REDIS_HOST);

    $parser = Nss\Feed\ParserFactory::make(SUPPLIERS[$supplierId], $httpClient, $redis);
    $parser->processItems();
}

function gf_reset_queue1($wpdb, $supplierId) {
    $httpClient = new \GuzzleHttp\Client();
    $redis = new Redis();
    $redis->connect(REDIS_HOST);

    $key = 'importFeedQueue:' . SUPPLIERS[$supplierId]['name'] .':';
    $importer = new Nss\Feed\Importer($redis, $wpdb, $httpClient, $key);
    $importer->resetQueue();
}


