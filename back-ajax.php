<?php
/* Template Name: back ajax */

ini_set('max_execution_time', 1200);
ini_set('display_errors', 1);
error_reporting(E_ALL);

global $wpdb;

$config = array(
    'host' => ES_HOST,
    'port' => 9200
);


//$sw = new \Symfony\Component\Stopwatch\Stopwatch();
//$sw->start('gfmain');

//if (isset($_GET['testVendor'])) {
//    gf_change_supplier_id_by_vendor_id();
//}

//include(__DIR__ . "/inc/Search/Elastica/Search.php");
include(__DIR__ . "/inc/Search/Elastica/Indexer.php");
include(__DIR__ . "/inc/Search/Elastica/Config/ConfigInterface.php");
include(__DIR__ . "/inc/Search/Elastica/Config/Product.php");
include(__DIR__ . "/inc/Search/Elastica/Config/Term.php");
include(__DIR__ . "/inc/Search/Elastica/Setup.php");

if (isset($_GET['action'])) {
    $config = array(
        'host' => ES_HOST,
        'port' => 9200
    );
    $elasticaClient = new \Elastica\Client($config);

    switch ($_GET['action']) {
        case 'createIndex':
//            $products = new \GF\Search\Elastica\SetupProducts($elasticaClient);
            $productSetup = new \GF\Search\Elastica\Setup($elasticaClient, new \GF\Search\Elastica\Config\Product());
            $termSetup = new \GF\Search\Elastica\Setup($elasticaClient, new \GF\Search\Elastica\Config\Term());
            $recreate = true;
            $productSetup->createIndex($recreate);
            $termSetup->createIndex($recreate);

            break;

        case 'getList':
            $keywords = (isset($_GET['query'])) ? $_GET['query'] : 'test';
            $search = new \GF\Search\Elastica\Search($elasticaClient);
            $search->search($keywords);
            $search->printDebug();

            break;

        case 'syncIndex':
            \GF\Search\Elastica\Indexer::index($elasticaClient, new \GF\Search\Elastica\Config\Product());

            break;
    }
}
?>
<a href="/back-ajax/?action=createIndex">(re) create index</a>
<a href="/back-ajax/?action=syncIndex">sync index</a>
<a href="/back-ajax/?action=getList">test</a>
