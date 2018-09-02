<?php
/* Template Name: redirectOldProducts */

$sku = $_GET['id'];
$id = wc_get_product_id_by_sku($id);
$url = get_permalink($id);

header("HTTP/1.1 301 Moved Permanently");
header('Location: '.  $url);

