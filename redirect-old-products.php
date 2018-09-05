<?php
/* Template Name: redirectOldProducts */

$sku = $_GET['id'];
$product = get_product_by_sku($sku);
$url = get_permalink($product->get_id());
//echo $id = wc_get_product_id_by_sku($id);
//echo $url = get_permalink($id);

header("HTTP/1.1 301 Moved Permanently");
header('Location: '.  $url);

function get_product_by_sku( $sku ) {
    global $wpdb;

    $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );
    if ($product_id){
	 return new WC_Product( $product_id );
    }

    return null;
}
