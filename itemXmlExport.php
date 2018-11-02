<?php
/**
 * Template Name: item export
 */
ini_set('max_execution_time', 1200);
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

    echo $xmlDoc->saveXML();
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

