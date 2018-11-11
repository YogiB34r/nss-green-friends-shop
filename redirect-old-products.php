<?php
/* Template Name: redirectOldProducts */

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
//echo $id = wc_get_product_id_by_sku($id);
//echo $url = get_permalink($id);

header("HTTP/1.1 301 Moved Permanently");
header('Location: '.  $url);
