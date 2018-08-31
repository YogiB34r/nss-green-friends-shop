<?php

$product = wc_get_product(wc_get_product_id_by_sku($id));
$url = get_permalink($id);