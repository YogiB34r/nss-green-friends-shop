<?php

namespace GF;

class Cli
{
    public function collectSkus()
    {
        $limit = 1000;
        $start = 1;
        $end = $start + 50;

        $ids = [];
        for ($i = 1; $i < 21; $i++) {
//        for ($i = $start; $i < $end; $i++) {
            $products_ids = wc_get_products(array(
                'limit' => $limit,
//                'return' => 'ids',
                'paged' => $i
            ));
            foreach ($products_ids as $product) {
                $ids[] = $product->get_sku();
            }

//            $ids = array_merge($products_ids, $ids);
        }
        echo implode(',', $ids);
    }

    public function fixItems()
    {
//        $pages = 21;
        $limit = 100;

//        $increment = 1;
        $start = 70;
        $end = $start + 80;

        $diff = [];
        $html = '';
        $total = 0;
        $updated = [];
//        for ($i = 1; $i < $pages; $i++) {
        for ($i = $start; $i < $end; $i++) {
            $products_ids = wc_get_products(array(
                'limit' => $limit,
                'return' => 'ids',
                'paged' => $i
            ));

            $fields = [];
            foreach ($products_ids as $product_id) {
                if (in_array($product_id, [399196, 434830])) {
                    continue;
                }
                $product = wc_get_product($product_id);
                if (in_array((int) $product->get_meta('supplier'), [192655])) {
                    continue;
                }
                if ($total % $limit === 0) {
                    echo 'passed '.$total.' items' . PHP_EOL;
                }
                $total++;

                if (!$product) {
                    throw new \Exception('not found. ' . $product_id);
                }
                // a sport
                if ($product->get_meta('supplier') == 319) {
                    continue;
                }
                if ($product->get_sku() === '') {
                    $product->set_sku($product->get_id());
                }
                $data = $this->fetchItemData($product->get_sku(), $product->get_name(), $product->get_meta('supplier'));

                if (!$data) {
//                    $product->set_status('draft');
//                    $fields['drafted'] = $product->;
//                    $product->save();
                    continue;
                }

//                $user = get_users(array('meta_key' => 'vendorid', 'meta_value' => $data->vendorId));
//                $user = get_user_by('description', $data->vendorEmail);
//                $user = get_users(array('meta_key' => 'description', 'meta_value' => $data->vendorEmail));
                $user = get_users(array('meta_key' => 'vendorid', 'meta_value' => $data->vendorId));
//                $user1 = get_users(array('meta_key' => 'description', 'meta_value' => $data->vendorEmail));
                if (!$user) {
//                    echo 'retry';
                    $user1 = get_users(array('meta_key' => 'description', 'meta_value' => trim($data->vendorEmail)));

                    if (!$user1[0]) {
                        $user1 = get_user_by('description', $data->vendorEmail);
                        if ($data->vendorEmail === 'rajko.djuric@ringier.rs') {
//                            $updated[] = $product->get_id();
//                            $product->update_meta_data('supplier', 373);
//                            $product->save();
                        } else {
                            var_dump($data->vendorEmail);
                            var_dump($user1);
                            echo 'vendor not found : ';
                            var_dump($data);
                            die();
                        }
                    }

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
                        $user1 = get_users(array('meta_key' => 'description', 'meta_value' => trim($data->vendorEmail)));
                        if ($user[0]->ID !== $user1[0]->ID) {
                            if (strstr($data->vendorEmail, 'rajko.djuric@ringier.rs')) {
//                                echo 'updated rajko item';
//                                $product->update_meta_data('supplier', 373);
//                                $product->save();
                            } else {
                                var_dump('wrong data');
                                var_dump($data);
                                var_dump($product->get_id());
                                var_dump($product->get_meta('supplier'));
                                var_dump($user[0]->ID);
                                var_dump($user[1]->ID);
                                die();
                            }
                        } else {
                            //just update by old vendor id
                            echo 'update by old vendor id';
//                            var_dump($data->vendorEmail);
//                            var_dump($product->get_id());
//                            var_dump($product->get_meta('supplier'));
//                            var_dump($user[0]->ID);
                            $product->update_meta_data('supplier', $user[0]->ID);
                            $product->save();
                        }

                    }
                }

//                if ($data->vendorId != $product->get_meta('supplier')) {
//                    $fields['vendorId'] = $data->vendorId;
//                    $product->update_meta_data('supplier', $data->vendorId);
//                }

//                if ($data->quantity != $product->get_meta('quantity')) {
//                    $fields['quantity'] = $data->quantity;
//                    $product->update_meta_data('quantity', $data->quantity);
//                }

                if (!empty($fields)) {
                    $diff[$product->get_sku() .'#'. $product->get_id()] = $fields;
                    $product->save();
                }
            }
        }
        $html .= 'total of items parsed: '. $total . PHP_EOL;
        $html .= 'total of items updated: '. count($updated) . PHP_EOL;
        $html .= 'Differences' . PHP_EOL;
        $html .= print_r($diff, true);
        echo $html;
        var_dump($updated);

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
