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
    \WP_CLI::add_command('createElasticIndex', 'createElasticIndex');
    \WP_CLI::add_command('syncElasticIndex', 'syncElasticIndex');

    \WP_CLI::add_command('passAllProducts', 'passAllProducts');
    \WP_CLI::add_command('passAllUsers', 'passAllUsers');

    \WP_CLI::add_command('createXmlExport', 'getItemExport');

    \WP_CLI::add_command('fixItemVendor', 'fixItemVendor');
}

ini_set('max_execution_time', 1200);
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

function fixItemVendor() {
    $cli = new \GF\Cli();
    $cli->fixItems();
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
            $root = createXml($xmlDoc, $product, $root);
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





function gf_get_products_without_image() {
    global $wpdb;
    $sql = "SELECT ID FROM wp_posts WHERE ID NOT IN (select post_id from wp_postmeta WHERE meta_key='_thumbnail_id') AND post_type='product'";
    $result = $wpdb->get_results($sql);

    return $result;
}

function passAllUsers() {
    global $wpdb;
    $args = array(
        'role'    => 'Supplier',
        'orderby' => 'user_nicename',
        'order'   => 'ASC'
    );
    /* @var WP_User $user */
    foreach (get_users($args) as $user) {
        $userMeta = get_user_meta($user->ID);
        $sql = "SELECT gfax FROM gvendor WHERE  gvendorid = {$user->vendorid}";
        $result = $wpdb->get_results($sql);
        update_user_meta($user->ID, 'description', $result[0]->gfax);
        wp_update_user( array( 'ID' => $user->ID, 'display_name' => $userMeta['vendor_name'][0] ) );
    }
}

function passAllProducts() {
    $pages = 21;
    $limit = 1000;

    global $wpdb;

//    for ($i = 1; $i < $pages; $i++) {
        $products_ids = wc_get_products(array(
//            'limit' => $limit,
            'limit' => 2000,
            'return' => 'ids',
            'status' => 'pending',
//            'paged' => $i
        ));

        foreach ($products_ids as $product_id) {
            $product = wc_get_product($product_id);
            $product->set_status('publish');
            $product->save();
        }
        echo 'done';
//    }
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


