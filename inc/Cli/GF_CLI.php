<?php

namespace GF;

class Cli
{
    public function fixMisPrices($args)
    {
        $limit = 1000;
        $page = 1;
        if (isset($args[0])) {
            $page = $args[0];
        }
        if (isset($args[1])) {
            $limit = $args[1];
        }

        $total = 0;
        $updated = [];
        $products_ids = wc_get_products(array(
            'limit' => $limit,
            'return' => 'ids',
            'paged' => $page
        ));
        require_once(__DIR__ . "/../../../../plugins/nss-mis/classes/NSS_MIS_Item.php");
        require_once(__DIR__ . "/../../../../plugins/nss-mis/classes/Pricelist.php");
        require_once(__DIR__ . "/../../../../plugins/nss-mis/classes/NSS_MIS_Client.php");
        require_once(__DIR__ . "/../../../../plugins/nss-mis/classes/NSS_Log.php");

//        $itemId = 0;

        $failedIds = [];
        foreach ($products_ids as $itemId) {
            if (isset($args[2])) {
                $itemId = $args[2];
            }

            $item = wc_get_product($itemId);
            echo 'syncing item ' . $item->get_id();
            $syncItem = new \NSS_MIS_Item($item);
            if ($syncItem->getSync() !== true) {
                $failedIds['sync'] = $syncItem->getSync();
                continue;
            }

            echo 'syncing item price for ' . $item->get_id();
            if (get_class($item) === \WC_Product_Variable::class) {
                foreach ($item->get_children() as $productId) {
                    $variation = wc_get_product($productId);
                    $price = $variation->get_sale_price();
                    if ($price == 0 || $price == "") {
                        $price = $variation->get_price();
                    }
                    $syncPrice = new \NSS\MIS\Pricelist($item->get_sku(), $price);
                    if ($syncPrice->getStatus() !== true) {
                        $failedIds['price'] = $syncPrice->getStatus();
                    }
                }
            } else {
                $price = $item->get_sale_price();
                if ($price == 0 || $price == "") {
                    $price = $item->get_price();
                }
                $syncPrice = new \NSS\MIS\Pricelist($item->get_sku(), $price);
                if ($syncPrice->getStatus() !== true) {
                    $failedIds['price'] = $syncPrice->getStatus();
                }
            }

//            $syncPrice = new \NSS\MIS\Pricelist($item->get_sku(), $price);

        }

        echo 'failed ids';
        var_dump($failedIds);
    }

    public function saleItems($args)
    {
        $limit = 1000;
        $page = 1;
        if (isset($args[0])) {
            $page = $args[0];
        }
        if (isset($args[1])) {
            $limit = $args[1];
        }

        $total = 0;
        $updated = [];
        $products_ids = wc_get_products(array(
            'limit' => $limit,
            'return' => 'ids',
            'paged' => $page
        ));

        $httpClient = new \GuzzleHttp\Client();

        foreach ($products_ids as $product_id) {
            if (isset($args[2])) {
                $product_id = $args[2];
            }

            $product = wc_get_product($product_id);

            $url = str_replace('https://nonstopshop.rs', 'https://nss-devel.ha.rs', get_permalink($product_id));
//            $url = str_replace('https://nss-devel.ha.rs', 'https://nonstopshop.rs', get_permalink($product_id));

            try {
                $response = $httpClient->send(new \GuzzleHttp\Psr7\Request('get', $url));
            } catch (\Exception $e) {
                if ($e->getCode() == 404) {
                    continue;
                }
            }

            $dom = new \DOMDocument();
            @$dom->loadHTML($response->getBody()->getContents());
            $finder = new \DomXPath($dom);
            $classname = "woocommerce-Price-amount amount";
            $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
            $backupPrice = str_replace('din.', '', $nodes->item(0)->nodeValue);
            $backupPrice = str_replace(',', '', $backupPrice);
            $testPrice = $product->get_price();
            if (get_class($product) === \WC_Product_Variable::class) {
                foreach ($product->get_children() as $productId) {
                    $variation = wc_get_product($productId);
                    $testPrice = $variation->get_price();
                }
            }

//            var_dump($product_id);
//            var_dump(get_permalink($product_id));
//            var_dump($product->get_date_on_sale_to());
//            var_dump($backupPrice);
//            var_dump($testPrice);
//            die();

            if ($backupPrice == $testPrice) {
                continue;
            }

            if ($testPrice > $backupPrice) {
                if (get_class($product) === \WC_Product_Variable::class) {
                    foreach ($product->get_children() as $productId) {
                        $variation = wc_get_product($productId);
                        $variation->set_regular_price($testPrice);
                        $variation->set_price($backupPrice);
                        $variation->set_sale_price($backupPrice);
                        $variation->set_date_on_sale_from(strtotime('01/01/2019'));  // m/d/Y
                        $variation->set_date_on_sale_to(strtotime('03/01/2019'));
                        $variation->save();
                    }
                } else {
                    $product->set_regular_price($testPrice);
                    $product->set_price($backupPrice);
                    $product->set_sale_price($backupPrice);
                    $product->set_date_on_sale_from(strtotime('01/01/2019'));  // m/d/Y
                    $product->set_date_on_sale_to(strtotime('03/01/2019'));
                    $product->save();
                }

                var_dump(get_permalink($product_id));
                $total++;
                continue;
            }

            if (!$product->is_in_stock()) {
                continue;
            }

            if ($testPrice === "") {
                var_dump('no test price ');
                var_dump($product_id);
//                var_dump(get_permalink($product_id));
                var_dump($product->get_date_on_sale_to());
                var_dump($backupPrice);
                var_dump($testPrice);
                die();
                $product->set_price($backupPrice);
                $product->save();
                continue;
            }

//            var_dump($product_id);
//            var_dump(get_permalink($product_id));
//            var_dump($product->get_date_on_sale_to());
//            var_dump($backupPrice);
//            var_dump($testPrice);
//            die();
        }

        echo 'saved ' . $total . PHP_EOL;
        echo 'from' . count($products_ids) . PHP_EOL;

        echo 'done';
    }


    public function fixItems()
    {
//        $pages = 21;
//        $limit = 500;

//        $increment = 1;
//        $start = 70;
//        $end = $start + 80;

        $diff = [];
        $html = '';
        $total = 0;
        $updated = [];
//        for ($i = 1; $i < 22; $i++) {
//        for ($i = $start; $i < $end; $i++) {
            $products_ids = wc_get_products(array(
                'limit' => 2000,
                'meta_key' => 'supplier',
//                'meta_value' => 268,
                'meta_value' => 198,
//                'compare' => 'IN',
                'return' => 'ids',
                'paged' => 1
            ));

            foreach ($products_ids as $product_id) {
                $product = wc_get_product($product_id);
//                $product->update_meta_data('quantity', 0);
//                $product->set_weight(0.5);
//                $product->set_status('pending');
//                $product->save();

                if ($product->get_status() === 'pending') {
                    $product->delete();
                    $product->save();
                    $updated[] = $product_id;
                }

            }
//        }
//        echo 'found ' . count($products_ids) . ' items';
        echo 'deleted ' . count($updated) . ' items';
die();
            $fields = [];
            foreach ($products_ids as $product_id) {
                $product = wc_get_product($product_id);
                $total++;

                $data = $this->fetchItemData($product->get_sku(), $product->get_name(), $product->get_meta('supplier'));
//                $user = get_users(array('meta_key' => 'vendorid', 'meta_value' => $data->vendorId));
                if (!$data) {
                    $updated[] = $product->get_id();
                    echo 'item not found remotely : ';
                    var_dump($product->get_name());
                    var_dump($product->get_id());
                    var_dump($product->get_meta('supplier'));
                    die();
                    $product->update_meta_data('supplier', 27);
                    $product->save();
//                    die();
                    continue;
                }
                $user = get_users(array('meta_key' => 'description', 'meta_value' => $data->vendorEmail));
                if (!$user) {
                    echo 'vendor not found by email, retry';
                    die();

                    // trouble ahead
                    if ((int) $product->get_meta('supplier') !== $user1[0]->ID) {
                        $updated[] = $product->get_id();
                        //update by vendor email
                        echo 'update by email';
                        $product->update_meta_data('supplier', $user1[0]->ID);
                        $product->save();
//                        var_dump($product->get_id());
//                        var_dump($data);
//                        die();
                    }
                } else {
                    //trouble ahead
                    if ((int) $product->get_meta('supplier') !== $user[0]->ID) {
                        $updated[] = $product->get_id();
                        //update by old vendor
                        echo 'update by vendor email to id : ' . $user[0]->ID;
                        var_dump($product->get_name());
                        die();
//                        if ($user[0]->ID !== 252) {
//                            echo 'wrong vendor ?';
//                            var_dump($product->get_name());
//                            var_dump($user[0]->ID);
//                            var_dump($product->get_id());
//                            die();
//                        }

                        $product->update_meta_data('supplier', $user[0]->ID);
                        $product->save();
                    }
                }

                if (!empty($fields)) {
                    $diff[$product->get_sku() .'#'. $product->get_id()] = $fields;
//                    $product->save();
                }
            }
//        }
        $html .= 'total of items parsed: '. $total . PHP_EOL;
        $html .= 'total of items updated: '. count($updated) . PHP_EOL;
        $html .= 'Differences' . PHP_EOL;
        $html .= print_r($diff, true);
        echo $html;
//        var_dump($updated);

        \WP_CLI::success($html);
    }

    private function handleImage($images, $postId) {
        $explodedImages = explode(',', $images);
        if ($images == '' || count($explodedImages) === 0) {
            return false;
        }
        $image_main_url = $explodedImages[0];

        //Main image
        $image_main_id = \media_sideload_image($image_main_url, $postId, '', 'id');
        if (is_object($image_main_id) && get_class($image_main_id) === \WP_Error::class) {
            $msg = 'Failed to fetch image for item: ' . $postId . PHP_EOL;
            $msg .= print_r($image_main_id->get_error_messages(), true);
            $msg .= $image_main_url;
            echo $msg;
            return false;
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

    private function fetchItemData($sku, $name, $supplier) {
        $url = "https://185.29.100.160/cms/work/itemsApi.php?id=" . $sku;
        $httpClient = new \GuzzleHttp\Client(['verify' => false]);
        $response = $httpClient->send(new \GuzzleHttp\Psr7\Request('GET', $url));
        $body = $response->getBody()->getContents();
        $item = json_decode($body);
        if (!isset($item->sku)) {
//            echo $url . PHP_EOL;
//            echo $name . PHP_EOL;
//            echo $supplier . PHP_EOL;
//            var_dump('item not found remotely.');
//            var_dump($item);
            return false;
        }

        return $item;
    }
}
