<?php

namespace GF;

class Cli
{
    public function getCategoryList()
    {
        echo 'catId,name,parentCatId,parentCat';
        foreach (gf_get_categories() as $cat) {
            $parent = get_term_by('id', $cat->parent, 'product_cat');
            if ($parent) {
                echo $cat->term_id .','. $cat->name .','. $cat->parent .','. $parent->name . "\r\n";
            } else {
                echo $cat->term_id .','. $cat->name .',0,none' . "\r\n";
            }

        }
    }

    public function cleanupIndex()
    {
        $limit = 5000;
        $page = 1;

        $config = array(
            'host' => ES_HOST,
            'port' => 9200
        );
        $esClient = new \Elastica\Client($config);
        $elasticaSearch = new \GF\Search\Elastica\Search($esClient);
        $search = new \GF\Search\Adapter\Elastic($elasticaSearch);

        $args = array(
            'taxonomy'   => "product_cat",
        );
        $product_categories = get_terms($args);
        $limit = 10;
        foreach ($product_categories as $key => $category) {
            $items = $search->getItemsForCategory($category->term_id);
            $deleted = [];
            foreach ($items as $item) {
                $productId = $item->getData()['postId'];
                $product = wc_get_product($productId);
                if (!$product instanceof \WC_Product || $product->get_status() === 'trash') {
                    try {
                        $esClient->getIndex('product')->getType('product')->deleteById($productId);
                        $deleted[] = $product;
                    } catch(\Exception $e) {
                        var_dump($e->getMessage());
                    }
                }
            }

            echo 'deleted';
            var_dump(count($deleted));
            echo 'from';
            var_dump(count($items));

        }
    }

    public function addSticker()
    {
        $limit = 12000;
        $page = 2;
        $total = 0;
        $products_ids = wc_get_products(array(
            'limit' => $limit,
            'return' => 'ids',
            'paged' => $page
        ));

        $ids = gf_get_category_children_ids('specijalne-promocije');

        foreach ($products_ids as $product_id) {
            $product = wc_get_product($product_id);
            if (!empty(array_intersect($product->get_category_ids(), $ids)) && (int) get_post_meta($product->get_id(), 'sale_sticker_to', true) == 0) {
                var_dump($product->get_id());

                $dt = new \DateTime();
                $dt->modify('+30 day');
                update_post_meta($product->get_id(), 'sale_sticker_from', time());
                update_post_meta($product->get_id(), 'sale_sticker_to', $dt->getTimestamp());
                update_post_meta($product->get_id(), 'sale_sticker_active', "yes");
                $product->set_date_on_sale_to('');
                $product->set_date_on_sale_from('');

                $total++;
            }
        }

        echo 'saved ' . $total . PHP_EOL;
        echo 'from' . count($products_ids) . PHP_EOL;
        echo 'done';
    }

    public function migrateSaleItems($args)
    {
        $limit = 4000;
        $page = 6;
        if (isset($args[0])) {
            $page = $args[0];
        }
        if (isset($args[1])) {
            $limit = $args[1];
        }

        $total = 0;
        $products_ids = wc_get_products(array(
            'limit' => $limit,
            'return' => 'ids',
            'paged' => $page
        ));
//        $products_ids = [471779];

        foreach ($products_ids as $product_id) {
            $product = wc_get_product($product_id);
            if (in_array($product->get_meta('supplier'), [123, 268, 252, 198])) {
                continue;
            }

            if (!$product->is_on_sale()) {
                $httpClient = new \GuzzleHttp\Client();
                $url = 'https://nss-devel.ha.rs/back-ajax/?action=getPrice&sku=' . $product->get_sku();
                try {
//                $response = $httpClient->send(new \GuzzleHttp\Psr7\Request('get', $url, ['allow_redirects' => true]));
                    $response = $httpClient->send(new \GuzzleHttp\Psr7\Request('get', $url, ['allow_redirects' => false]));
                } catch (\Exception $e) {
                    continue;
                }

                $res = json_decode($response->getBody()->getContents());
                if ($res->status == 404) {
                    continue;
                }
                $backupPrice = $res->price;

                $testPrice = $product->get_price();
                if (get_class($product) === \WC_Product_Variable::class) {
                    foreach ($product->get_children() as $productId) {
                        $variation = wc_get_product($productId);
                        $testPrice = $variation->get_price();
                    }
                }

                if ($backupPrice == $testPrice) {
                    continue;
                }

                if ($testPrice > $backupPrice && $backupPrice > 0) {

                    var_dump('updating price for: ' . get_permalink($product->get_id()));
                    $total++;

                    if (get_class($product) === \WC_Product_Variable::class) {
                        foreach ($product->get_children() as $productId) {
                            $variation = wc_get_product($productId);
                            $variation->set_regular_price($testPrice);
                            $variation->set_price($backupPrice);
                            $variation->set_sale_price($backupPrice);
                            $variation->save();
                        }
                    } else {
                        $product->set_regular_price($testPrice);
                        $product->set_price($backupPrice);
                        $product->set_sale_price($backupPrice);
                        $product->save();
                    }

                    continue;
                }

                if (!$product->is_in_stock()) {
                    continue;
                }

                if ($testPrice === "") {
                    var_dump('no test price ');
                    var_dump($product->get_id());
                    var_dump($product->get_date_on_sale_to());
                    var_dump($backupPrice);
                    var_dump($testPrice);
                    die();
                }
            }
        }

//        foreach ($products_ids as $product_id) {
//            $product = wc_get_product($product_id);
//            if ($product->get_date_on_sale_from() || $product->get_date_on_sale_to()) {
//                if ($product->get_date_on_sale_to()->format('d/m/Y') === '31/03/2019' ||
//                    $product->get_date_on_sale_to()->format('d/m/Y') === '31/12/2019' ||
//                    $product->get_date_on_sale_to()->format('d/m/Y') === '30/12/2019' ||
//                    $product->get_date_on_sale_to()->format('d/m/Y') === '03/04/2019' ||
//                    $product->get_date_on_sale_to()->format('d/m/Y') === '29/11/2019'
//                ) {
//                    $this->migrateToNewAttributes($product);
//                    $total++;
//                } elseif ($product->get_date_on_sale_to()->format('d/m/Y') === '30/03/2019') {
//                    // lost sale price, fetch from devel
//                    if (!$this->fetchAndUpdateBadPrice($product)) {
//                        //there was no need to update price
//                    }
//                    //we can now update new attributes
//                    $this->migrateToNewAttributes($product);
//                    $total++;
//
//                } else {
//                    var_dump('dif date');
//                    var_dump($product->get_id());
//                    var_dump($product->get_date_on_sale_from()->format('d/m/Y'));
//                    var_dump($product->get_date_on_sale_to()->format('d/m/Y'));
//                    die();
//                }
//            } else {
//                continue;
//            }
//        }

        echo 'saved ' . $total . PHP_EOL;
        echo 'from' . count($products_ids) . PHP_EOL;

        echo 'done';
    }

    private function migrateToNewAttributes(\WC_Product $product)
    {
        var_dump($product->get_id());
        $dt = new \DateTime();
        $dt->modify('+30 day');
        update_post_meta($product->get_id(), 'sale_sticker_from', time());
        update_post_meta($product->get_id(), 'sale_sticker_to', $dt->getTimestamp());
        update_post_meta($product->get_id(), 'sale_sticker_active', "yes");
        $product->set_date_on_sale_to('');
        $product->set_date_on_sale_from('');

        return ($product->save());
    }

    public function saleItems($args)
    {
        $limit = 5000;
        $page = 5;

        $total = 0;
        $bugged = 0;
        $products_ids = wc_get_products(array(
            'limit' => $limit,
            'return' => 'ids',
            'paged' => $page
        ));



        foreach ($products_ids as $product_id) {
            if (get_post_meta($product_id, 'sale_sticker_active', true)) {
                $dt = new \DateTime();
                $dt->modify('+'. rand(40, 65) .' day');
                update_post_meta($product_id, 'sale_sticker_from', time());
                update_post_meta($product_id, 'sale_sticker_to', $dt->getTimestamp());
                $total++;
            }

//            var_dump($product_id);
//            var_dump(get_permalink($product_id));
//            var_dump($product->get_date_on_sale_to());
//            var_dump($backupPrice);
//            var_dump($testPrice);
//            die();
        }

//        echo 'bugged ' . $bugged . PHP_EOL;
        echo 'saved ' . $total . PHP_EOL;
        echo 'from' . count($products_ids) . PHP_EOL;

        echo 'done';
    }

    public function listItems()
    {
        $total = 0;
        $missingImg = 0;
        $vendor = [];
        $items = [];
        $products_ids = wc_get_products(array(
            'limit' => 5000,
            'status' => 'published',
//            'meta_key' => 'supplier',
//            'meta_value' => 252,
//                'meta_value' => 123,
//                'compare' => 'IN',
            'return' => 'ids',
            'paged' => 1
        ));

//        $products_ids = [412783];
        foreach ($products_ids as $product_id) {
            $product = wc_get_product($product_id);

            if ($product->is_in_stock() && strlen($product->get_image_id()) === 0) {
                $sup = $product->get_meta('supplier');
                if (!array_key_exists($sup, $vendor)) {
                    $vendor[$sup] = 1;
                } else {
                    $vendor[$sup]++;
                }

                $missingImg++;
                $items[] = $product_id;

                if ($missingImg > 20) {
                    var_dump(implode(',', $items));
                    die();
                }
            }
        }

        echo 'found ' . $missingImg . ' items' . "\r\n";
        echo 'from' . count($products_ids) . ' items';
        var_dump(implode(',', $items));
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
