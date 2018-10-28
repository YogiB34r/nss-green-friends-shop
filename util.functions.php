<?php

/**
 * @TODO Some of these must be refactored and optimized
 */


function gf_insert_in_array_by_index($array, $index, $val) {
    $size = count($array); //because I am going to use this more than one time
    if (!is_int($index) || $index < 0 || $index > $size) {
        return -1;
    } else {
        $temp = array_slice($array, 0, $index);
        $temp[] = $val;
        return array_merge($temp, array_slice($array, $index, $size));
    }
}

//Testira razliku array-a order sensitive
function gf_array_reccursive_difrence(array $array1, array $array2, array $_ = null) {
    $diff = [];
    $args = array_slice(func_get_args(), 1);
    foreach ($array1 as $key => $value) {
        foreach ($args as $item) {
            if (is_array($item)) {
                if (array_key_exists($key, $item)) {
                    if (is_array($value) && is_array($item[$key])) {
                        $tmpDiff = gf_array_reccursive_difrence($value, $item[$key]);

                        if (!empty($tmpDiff)) {
                            foreach ($tmpDiff as $tmpKey => $tmpValue) {
                                if (isset($item[$key][$tmpKey])) {
                                    if (is_array($value[$tmpKey]) && is_array($item[$key][$tmpKey])) {
                                        $newDiff = array_diff($value[$tmpKey], $item[$key][$tmpKey]);
                                    } else if ($value[$tmpKey] !== $item[$key][$tmpKey]) {
                                        $newDiff = $value[$tmpKey];
                                    }

                                    if (isset($newDiff)) {
                                        $diff[$key][$tmpKey] = $newDiff;
                                    }
                                } else {
                                    $diff[$key][$tmpKey] = $tmpDiff;
                                }
                            }
                        }
                    } else if ($value !== $item[$key]) {
                        $diff[$key] = $value;

                    }
                } else {
                    $diff[$key] = $value;
                }
            }
        }
    }
    return $diff;
}

function gf_get_categories($exlcude = array()) {
    $args = array(
        'orderby' => 'name',
        'order' => 'asc',
        'hide_empty' => false,
        'exclude' => $exlcude,
    );
    $product_cats = get_terms('product_cat', $args);
    return $product_cats;
}

function gf_get_top_level_categories($exclude = array()) {
    $top_level_categories = [];
    foreach (gf_get_categories($exclude) as $category) {
        if (!$category->parent) {
            $top_level_categories[] = $category;
        }
    }
    return $top_level_categories;
}

function gf_get_second_level_categories($parent_id = null) {
    $categories = gf_get_categories();
    $top_level_ids = [];
    $second_level_categories = [];
    foreach ($categories as $category) {
        if (!$category->parent) {
            $top_level_ids[] = $category->term_id;
        }
    }
    foreach ($categories as $category) {
        if ($parent_id) {
            if ($category->parent == $parent_id) {
                $second_level_categories[] = $category;
            }
        } elseif (in_array($category->parent, $top_level_ids)) {
            $second_level_categories[] = $category;
        }
    }
    return $second_level_categories;
}

function gf_get_third_level_categories($parent_id = null) {
    $categories = gf_get_categories();
    $second_level_ids = [];
    foreach (gf_get_second_level_categories() as $cat) {
        $second_level_ids[] = $cat->term_id;
    }
    $third_level_categories = [];
    foreach ($categories as $category) {
        if ($parent_id) {
            if ($category->parent == $parent_id) {
                $third_level_categories[] = $category;
            }
        } elseif (in_array($category->parent, $second_level_ids)) {
            $third_level_categories[] = $category;
        }
    }
    return $third_level_categories;
}

function gf_check_level_of_category($cat_id) {
    $result = null;
    $top_level_ids = [];
    $second_level_ids = [];
    $third_level_ids = [];
    foreach (gf_get_top_level_categories() as $category) {
        $top_level_ids[] = $category->term_id;
    }
    foreach (gf_get_second_level_categories() as $category) {
        $second_level_ids[] = $category->term_id;
    }
    foreach (gf_get_third_level_categories() as $category) {
        $third_level_ids[] = $category->term_id;
    }
    if (in_array($cat_id, $top_level_ids)) {
        $result = 1;
    }
    if (in_array($cat_id, $second_level_ids)) {
        $result = 2;
    }
    if (in_array($cat_id, $third_level_ids)) {
        $result = 3;
    }
    return $result;
}

function gf_get_category_children_ids($slug) {
    $cat = get_term_by('slug', $slug, 'product_cat');
    $childrenIds = [];
    if ($cat) {
        $catChildren = get_term_children($cat->term_id, 'product_cat');
        $childrenIds[] = $cat->term_id;
        foreach ($catChildren as $child) {
            $childrenIds[] = $child;
        }
    }
    return $childrenIds;
}

/**
 * One time use function, in order to replace proper vendorid for products
 */
function gf_change_supplier_id_by_vendor_id() {
    global $wpdb;

    $failedMatchIds = [];
    $alreadySyncedIds = [];
//    for ($i = 0; $i < 10; $i++) {
    for ($i = 1; $i < 5; $i++) {
        $products_ids = wc_get_products(array(
            'limit' => 5000,
            'return' => 'ids',
            'paged' => $i
        ));
        foreach ($products_ids as $product_id) {
            if (get_post_meta($product_id, 'synced', true) != 1) {
                $vendorId = (int) get_post_meta($product_id, 'supplier', true);
                if ($vendorId === 0) {
                    var_dump($product_id);
                    continue;
                }

                //resolve duplicate vendors
                if ($vendorId === 142) {
                    $vendorId = 357;
                }
                if ($vendorId === 349) {
                    $vendorId = 350;
                }
                if ($vendorId === 11) {
                    $vendorId = 324;
                }
                if ($vendorId === 191) {
                    $vendorId = 192;
                }
                if ($vendorId === 356) {
                    $vendorId = 324;
                }
                if ($vendorId === 304) {
                    $vendorId = 391;
                }
                if ($vendorId === 78) {
                    $vendorId = 89;
                }
                if ($vendorId === 209) {
                    $vendorId = 208;
                }
                if ($vendorId === 287 || $vendorId === 286) {
                    $vendorId = 288;
                }
                $sql = "SELECT user_id FROM wp_usermeta WHERE meta_key = 'vendorid' AND meta_value = {$vendorId}";
                $userId = $wpdb->get_var($sql);
                if (!$userId) {
                    if (!in_array($vendorId, $failedMatchIds)) {
                        $failedMatchIds[] = $vendorId;
                    }
                    continue;
                }
                update_post_meta($product_id, 'supplier', $userId);
                add_post_meta($product_id, 'synced', true);
            } else {
                if (!in_array($product_id, $alreadySyncedIds)) {
                    $alreadySyncedIds[] = $product_id;
                }
                continue;
//                var_dump('product already synced' . );
//                die();
            }
        }
    }
    echo 'Nisu pronađeni dobavljachi za sledeće vendorid-eve: ' . count($failedMatchIds);
    echo '<ul>';
    foreach ($failedMatchIds as $failedMatchId) {
        echo '<li>' . $failedMatchId . '</li>';
    }
    echo '</ul>';

    echo 'Sledeći proizvodi su vec sinhronizovani: ' . count($alreadySyncedIds);
//    echo '<ul>';
//    foreach ($alreadySyncedIds as $syncedId) {
//        echo '<li>' . $syncedId . '</li>';
//    }
//    echo '</ul>';
}


//require(__DIR__ . "/inc/GF_CLI.php");

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    // add cli commands
    \WP_CLI::add_command('test', function() {
        $html = updateItems();
        \WP_CLI::success($html);
    });

}


function updateItems() {

    $page = 1;
    $limit = 5000;

    $diff = [];
    $html = '';
    $total = 0;
//    for ($i = 2; $i < $pages; $i++) {
    $products_ids = wc_get_products(array(
        'limit' => $limit,
        'return' => 'ids',
        'paged' => $page
    ));
    $fields = [];
    foreach ($products_ids as $product_id) {
        $total++;
        $product = wc_get_product($product_id);
        if (!$product) {
            var_dump('not found. ' . $product_id);
            die();
        }
        if ($product->get_meta('supplier') == 319) {
            continue;
        }
        if ($product->get_sku() === '') {
//                if (strstr($product->get_name(), 'rider')) {
//                    $product->set_status('draft');
//                }
            var_dump($product->get_id());
            var_dump($product->get_name());
            var_dump($product->get_meta('supplier'));
            var_dump($product->get_meta_data());
            continue;
//                die();
        }
        $data = fetchItemData($product->get_sku(), $product->get_name(), $product->get_meta('supplier'));

        if ($data->sku !== $product->get_sku()) {
            $fields['sku'] = $data->sku;
        }
        if ($data->status === 1 && $product->get_status() !== 'publish' ||
            $data->status === 0 && $product->get_status() !== 'draft') {
//                var_dump('sku differs');
            $fields['status'] = $data->status;
        }
        if ($data->stockStatus === 1 && $product->get_stock_status() !== 'instock' ||
            $data->stockStatus === 0 && $product->get_stock_status() !== 'outofstock') {
//                var_dump('sku differs');
//                $fields['stockStatus'] = $data->stockStatus;
            if ($data->stockStatus) {
                $product->set_stock_status('instock');
            } else {
                $product->set_stock_status('outofstock');
            }
        }

        $images = explode(',', $data->images);
        //has different images
        if (has_post_thumbnail($product->get_id())) {
            if (count($images) - 1 !== count($product->get_gallery_image_ids())) {
//                    $fields['images'] = 'different';
                if (!handleImage($data->images, $product->get_id())) {
                    $html .= 'failed to save image for sku ' . $product->get_sku();
//                        var_dump($data);
//                        die();
                } else {
                    $html .= '<p>Updated images for productId: '.$product->get_id().'</p>';
                }
            }
        } else {
//                $fields['images'] = 'none';
            if (!handleImage($data->images, $product->get_id())) {
                $html .= 'failed to create image for sku ' . $product->get_sku();
//                    var_dump($data);
//                    die();
            } else {
                $html .= '<p>Created images for productId: '.$product->get_id().'</p>';
            }
        }
        if ($data->pdv !== $product->get_meta('pdv')) {
            $fields['pdv'] = $data->pdv;
            $product->update_meta_data('pdv', $data->pdv);
//                var_dump('pdv differs');
        }
        if ($data->vendorId != $product->get_meta('supplier')) {
            $fields['vendorId'] = $data->vendorId;
            $product->update_meta_data('supplier', $data->vendorId);
//                var_dump('vendorId differs');
        }
        if ($data->quantity != $product->get_meta('quantity')) {
            $fields['quantity'] = $data->quantity;
            $product->update_meta_data('quantity', $data->quantity);
        }

        if (!empty($fields)) {
            $diff[$product->get_sku() .'#'. $product->get_id()] = $fields;
            $product->save();
        }

//            var_dump($data);
    }
//    }
    $html .= '<p>total of items parsed: '. $total .'</p>';
    $html .= 'Differences <br />';
    $html .= print_r($diff, true);

    return $html;
}

function handleImage($images, $postId) {
    $explodedImages = explode(',', $images);
    if ($images == '' || count($explodedImages) === 0) {
        return false;
    }
    $image_main_url = $explodedImages[0];

    if (is_object($image_main_url) && get_class($image_main_url) === \WP_Error::class) {
        $msg = 'Failed to fetch image for item: ' . $postId . PHP_EOL;
        $msg .= $image_main_url->get_error_messages();
        $msg .= $image_main_url;
        die($msg);
    }

    //Main image
    $image_main_id = \media_sideload_image($image_main_url, $postId, '', 'id');
    if (is_object($image_main_id) && get_class($image_main_id) === \WP_Error::class) {
        $msg = 'Failed to fetch image for item: ' . $postId . PHP_EOL;
        $msg .= print_r($image_main_id->get_error_messages(), true);
        $msg .= $image_main_url;
        die($msg);
    }
    $setThumbnail = \set_post_thumbnail($postId, $image_main_id);
    $updateMeta = \update_post_meta($postId, '_thumbnail_id', $image_main_id);

    //Gallery images
    $image_gallery_urls = explode(',', $images);
    $image_gallery_ids = [];
    foreach ($image_gallery_urls as $key => $url) {
        if ($key > 0) {
            $image = \media_sideload_image($url, $postId, '', 'id');
            if (is_object($image) && get_class($image) === \WP_Error::class) {
                throw new \Exception(sprintf('Could not save image for item %s. Url: %s. Error %s .',
                    $postId, $url, $image));
            }
            $image_gallery_ids[] = $image;
        }
    }

    $updateMeta = \update_post_meta($postId, '_product_image_gallery', implode(',', $image_gallery_ids));

    return true;
}

function fetchItemData($sku, $name, $supplier) {
    $url = "https://185.29.100.160/cms/work/itemsApi.php?id=" . $sku;
    $httpClient = new \GuzzleHttp\Client(['verify' => false]);
    $response = $httpClient->send(new \GuzzleHttp\Psr7\Request('GET', $url));
    $body = $response->getBody()->getContents();
    $item = json_decode($body);
    if (!isset($item->sku)) {
        echo $url . "<br />";
        echo $name . "<br />";
        echo $supplier . "<br />";
        var_dump('item not found remotely.');
        var_dump($item);
//        die();
    }

    return $item;
}
