<?php
/* Template Name: redirectOldProducts */

if (isset($_GET['type']) && $_GET['type'] === 'category') {
    $id = $_GET['catId'];
    $cats = explode("\n", file_get_contents(__DIR__ . '/old.cats.map.csv'));
    $category = false;
    foreach ($cats as $catDataString) {
        if ($catData[0] === '') {
            continue;
        }
        $catData = str_getcsv($catDataString, ",", '"');

        if (isset($catData[3])) {
            if (in_array($id, explode(',', $catData[3]))) {
                $cat = get_term_by('name', trim($catData[0]), 'product_cat');
                $url = get_term_link($cat->term_id, 'product_cat');
                if (isset($catData[1])) {
                    $name = trim($catData[1]);
                    $cat = get_term_by('name', $name, 'product_cat');
                    $url = get_term_link($cat->term_id, 'product_cat');
                }
                if (isset($catData[2]) && $catData[2] != '') {
                    $name = trim($catData[2]);
                    $cat = get_term_by('name', $name, 'product_cat');
                    if (!is_object($cat)) {
                        var_dump('fali kat ?' . $name);
                        die();
                    }
                    $url = get_term_link($cat->term_id, 'product_cat');
                }

                header("HTTP/1.1 301 Moved Permanently");
                header('Location: '.  $url);
                exit();
            }
        }
    }
}

$sku = $_GET['id'];
$wcproduct = get_product_by_sku($sku);
if (!$wcproduct) {
//global $wp_query;
//  $wp_query->set_404();
//  status_header( 404 );
//  get_template_part( 404 ); exit();
    header("HTTP/1.1 302 Moved Temporary");
    header('Location: '.  home_url());
    exit();
}

$url = get_permalink($wcproduct->get_id());

header("HTTP/1.1 301 Moved Permanently");
header('Location: '.  $url);
