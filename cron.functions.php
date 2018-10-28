<?php

// @TODO move this to class

include(__DIR__ . "/inc/Cli/GF_CLI.php");
include(__DIR__ . "/inc/Search/Elastica/Indexer.php");
include(__DIR__ . "/inc/Search/Elastica/Config/ConfigInterface.php");
include(__DIR__ . "/inc/Search/Elastica/Config/Product.php");
include(__DIR__ . "/inc/Search/Elastica/Config/Term.php");
include(__DIR__ . "/inc/Search/Elastica/Setup.php");
include(__DIR__ . "/inc/Search/Factory/ElasticClientFactory.php");
include(__DIR__ . "/inc/Search/Factory/ProductSetupFactory.php");
include(__DIR__ . "/inc/Search/Factory/TermSetupFactory.php");

if (defined('WP_CLI') && WP_CLI) {
    // add cli commands
    \WP_CLI::add_command('fixItems', 'fixItems');

    \WP_CLI::add_command('createElasticIndex', 'createElasticIndex');
    \WP_CLI::add_command('syncElasticIndex', 'syncElasticIndex');
}



function fixItems() {
    ini_set('max_execution_time', 1200);
    $cli = new \GF\Cli();

    $cli->fixItems();
}

function createElasticIndex() {
    $elasticaClient = new \GF\Search\Factory\ElasticClientFactory();
    $productSetupFactory = new \GF\Search\Factory\ProductSetupFactory($elasticaClient);
    $termSetupFactory = new \GF\Search\Factory\TermSetupFactory($elasticaClient);
    $recreate = true;
    $productSetupFactory->make()->createIndex($recreate);
    $termSetupFactory->make()->createIndex($recreate);
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

//@TODO save sku operation does not work !?!?!?!
//add_action('save_post_product', 'setNewProductSku', 10, 3);
function setNewProductSku($id, $post, $update) {
    var_dump('setNewProductSku');
    $product = wc_get_product($id);
    if ($product->get_sku() == "") {
        $product->set_sku($product->get_id());
        $product->save();
    }
}

add_action('woocommerce_process_product_meta', 'syncToElastic', 666, 3);
function syncToElastic($id, WP_Post $post) {
    $product = wc_get_product($id);
    if ($product && strtolower($product->get_status()) != 'auto-draft' && strtolower($product->get_name()) != 'auto-draft'
    ) {
//        && $product->get_sku() != '') {

        $productConfig = new \GF\Search\Elastica\Config\Product();
        $elasticaClientFactory = new \GF\Search\Factory\ElasticClientFactory();
        $productType = $elasticaClientFactory->make()->getIndex($productConfig->getIndex())
            ->getType($productConfig->getType());
        $indexer = new \GF\Search\Elastica\Indexer($productType);
        $indexer->indexProduct($product);
    }
}


