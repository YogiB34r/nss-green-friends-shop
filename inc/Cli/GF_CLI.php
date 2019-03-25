<?php

namespace GF;

class Cli
{
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

    public function listSaleItems()
    {
        $limit = 5000;
        $page = 5;
        $total = 0;
        $products_ids = wc_get_products(array(
            'limit' => $limit,
            'return' => 'ids',
            'paged' => $page
        ));
        $items = [];
        foreach ($products_ids as $product_id) {
            $product = wc_get_product($product_id);

            $vendor = $product->get_meta('supplier');
            if ($vendor == 268) {
                continue;
            }

            if ($product->get_status() == 'publish' && $product->is_on_sale() || $product->get_sale_price() > 0) {
                if (get_class($product) === \WC_Product_Variable::class) {
                    foreach ($product->get_children() as $productId) {
                        $variation = wc_get_product($productId);
                        $savingsDinars = $variation->get_regular_price() - $variation->get_sale_price();
                        $regularPrice = $variation->get_regular_price();
                        $salePrice = $variation->get_sale_price();
                    }
                } else {
                    $savingsDinars = $product->get_regular_price() - $product->get_sale_price();
                    $regularPrice = $product->get_regular_price();
                    $salePrice = $product->get_sale_price();
                }

                $savingsPercentage = 0;
//                if ($savingsDinars > 0) {
                if ($savingsDinars > 1500) {
                    if ($regularPrice == 0) {
                        var_dump('price zero');
                        var_dump($product->get_id());
                        die();
                    }
                    $savingsPercentage = number_format($savingsDinars / $regularPrice * 100);
                    if ($savingsPercentage > 30) {
                        $items['30percent'][] = [
                            'price' => $regularPrice,
                            'salePrice' => $salePrice,
                            'id' => $product->get_id(),
                            'name' => $product->get_name()
                        ];
                        continue;
                    }
                    if ($savingsPercentage > 20) {
                        $items['20percent'][] = $product;
                        continue;
                    }
                    if ($savingsPercentage > 10) {
                        $items['10percent'][] = $product;
                        continue;
                    }

                }
            }
        }

//        foreach ($items['30percent'] as $item) {
//            echo $item['id'] .','. $item['price'] .','. $item['salePrice'] .','. $item['name'] . PHP_EOL;
//        }

//        var_dump('total items: ' . count($items['30percent']));
//        var_dump('30 percent count: ' . count($items['30percent']));
//        var_dump('20 percent count: ' . count($items['20percent']));
//        var_dump('10 percent count: ' . count($items['10percent']));
    }



    public function migrateSaleItems($args)
    {
        $limit = 100;
        $page = 1;
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

            if ($product->get_date_on_sale_to() || $product->get_date_on_sale_from()) {
                if (get_class($product) === \WC_Product_Variable::class) {
                    foreach ($product->get_children() as $productId) {
                        $variation = wc_get_product($productId);
                        $variation->set_date_on_sale_from(null);  // m/d/Y
                        $variation->set_date_on_sale_to(null);
                        $variation->update_meta_data('sale_sticker_from', '20. February 2019.');
                        $variation->update_meta_data('sale_sticker_to', '4. March 2019.');
                        $variation->update_meta_data('sale_sticker_active', 'yes');
                        $variation->save();
                    }
                } else {
                    $product->set_date_on_sale_from(null);  // m/d/Y
                    $product->set_date_on_sale_to(null);
                    // @TODO check if to date is already set longer
                    var_dump($product->get_date_on_sale_to() > '31. March 2019.');
                    var_dump($product->get_date_on_sale_to() < '31. March 2019.');
                    var_dump($product->get_date_on_sale_to() == '31. March 2019.');
                    die();
                    $product->update_meta_data('sale_sticker_from', '20. February 2019.');
                    $product->update_meta_data('sale_sticker_to', '31. March 2019.');
                    $product->update_meta_data('sale_sticker_active', 'yes');
                    $product->save();
                }

                $total++;
                continue;
            }
        }

        echo 'saved ' . $total . PHP_EOL;
        echo 'from' . count($products_ids) . PHP_EOL;

        echo 'done';
    }

    public function saleItems($args)
    {
        $limit = 4000;
        $page = 1;
        if (isset($args[0])) {
            $page = $args[0];
        }
        if (isset($args[1])) {
            $limit = $args[1];
        }

        $total = 0;
        $bugged = 0;
        $products_ids = wc_get_products(array(
            'limit' => $limit,
            'return' => 'ids',
            'paged' => $page
        ));

        $httpClient = new \GuzzleHttp\Client();
        $products_ids = [412783];

        foreach ($products_ids as $product_id) {
            if (isset($args[2])) {
                $product_id = $args[2];
            }
            if (in_array($product_id, [471923])) {
                continue;
            }

            $product = wc_get_product($product_id);
            if ($product->get_price() === 0 || $product->get_sale_price() === 0) {
                echo 'wrong price';
                var_dump($product->get_id());
                die();
            }

            if ($product->get_meta('supplier') == 123) {
                continue;
            }

//            $url = str_replace('https://nonstopshop.rs', 'https://nss-devel.ha.rs', get_permalink($product_id));
//            $url = str_replace('https://nss-devel.ha.rs', 'https://nonstopshop.rs', get_permalink($product_id));
            if ($product->get_sku() == '') {
                $bugged++;
                continue;
            }
            $url = 'https://nss-devel.ha.rs/back-ajax/?action=getPrice&sku=' . $product->get_sku();
            try {
//                $response = $httpClient->send(new \GuzzleHttp\Psr7\Request('get', $url, ['allow_redirects' => true]));
                $response = $httpClient->send(new \GuzzleHttp\Psr7\Request('get', $url, ['allow_redirects' => false]));
            } catch (\Exception $e) {
                if ($e->getCode() == 404) {
                    continue;
                }
            }

            $res = json_decode($response->getBody()->getContents());
            if ($res->status == 404) {
                continue;
            }
            $backupPrice = $res->price;

//            var_dump($res->salePrice);
//            var_dump($res->regularPrice);


/*            $rule = '/(<ins>)(.*?)(<\/ins>)/';
            $matches = [];
            preg_match_all($rule, $response->getBody()->getContents(), $matches);
            if (!isset($matches[2][0])) {
                var_dump($matches);
                var_dump($url);
                die();
            }
            $string = $matches[2][0];
            $strip = '<span class="woocommerce-Price-amount amount">';
            $string = str_replace($strip, '', $string);
            $backupPrice = strstr($string, '<', true);
            $backupPrice = (int) str_replace(',', '', $backupPrice);
*/
//            $dom = new \DOMDocument();
//            $html = $response->getBody()->getContents();
//            @$dom->loadHTML($html);
//            $finder = new \DomXPath($dom);
//            $classname = "woocommerce-Price-amount amount";
//            $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
//            $backupPrice = str_replace('din.', '', $nodes->item(0)->nodeValue);
//            $backupPrice = str_replace(',', '', $backupPrice);

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
                if (get_class($product) === \WC_Product_Variable::class) {
                    foreach ($product->get_children() as $productId) {
                        $variation = wc_get_product($productId);
                        $variation->set_regular_price($testPrice);
                        $variation->set_price($backupPrice);
                        $variation->set_sale_price($backupPrice);
                        $variation->set_date_on_sale_from(strtotime('02/20/2019'));  // m/d/Y
                        $variation->set_date_on_sale_to(strtotime('03/31/2019'));
                        $variation->save();
                    }
                } else {
                    $product->set_regular_price($testPrice);
                    $product->set_price($backupPrice);
                    $product->set_sale_price($backupPrice);
                    $product->set_date_on_sale_from(strtotime('02/20/2019'));  // m/d/Y
                    $product->set_date_on_sale_to(strtotime('03/31/2019'));
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

        echo 'bugged ' . $bugged . PHP_EOL;
        echo 'saved ' . $total . PHP_EOL;
        echo 'from' . count($products_ids) . PHP_EOL;

        echo 'done';
    }


    public function listItems()
    {
//        $pages = 21;
//        $limit = 500;

        $diff = [];
        $html = '';
        $total = 0;
        $updated = [];
//        for ($i = 1; $i < 22; $i++) {
//        for ($i = $start; $i < $end; $i++) {
            $products_ids = wc_get_products(array(
                'limit' => 4000,
                'meta_key' => 'supplier',
                'meta_value' => 252,
//                'meta_value' => 123,
//                'compare' => 'IN',
                'return' => 'ids',
                'paged' => 1
            ));

//        $products_ids = [412783];
            foreach ($products_ids as $product_id) {
                $product = wc_get_product($product_id);

//                var_dump($product->get_date_on_sale_to()->format('d/m/Y') > '28/02/2019');
//                var_dump($product->get_date_on_sale_to()->format('d/m/Y') < '28/02/2019');
//                die();
                $product->set_weight(1);
                $product->save();
                $updated[] = $product_id;

//                if ($product->get_status() === 'pending') {
//                $product->set_status('pending');
//                $product->delete();
//                $product->save();

//                }

            }
//        echo 'found ' . count($products_ids) . ' items';
        echo 'found ' . count($updated) . ' items';
            var_dump($updated);
die();
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
