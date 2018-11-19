<?php

namespace GF;

class Cli
{
    public function saleItems()
    {
//        $pages = 21;
        $limit = 5000;

        $total = 0;
        $updated = [];
        $products_ids = wc_get_products(array(
            'limit' => $limit,
            'return' => 'ids',
            'paged' => 1
        ));

        foreach ($products_ids as $product_id) {
            $product = wc_get_product($product_id);
            if ($product->is_on_sale()) {
                $total++;
                $product->set_date_on_sale_from('11/2/18');
                $product->set_date_on_sale_to('12/1/18');
                $product->save();
            }
        }
        echo $total;
    }


    public function fixItems()
    {
//        $pages = 21;
        $limit = 500;

//        $increment = 1;
        $start = 70;
        $end = $start + 80;


        $diff = [];
        $html = '';
        $total = 0;
        $updated = [];
//        for ($i = 1; $i < $pages; $i++) {
//        for ($i = $start; $i < $end; $i++) {
            $products_ids = wc_get_products(array(
                'limit' => $limit,
                'meta_key' => 'supplier',
                'meta_value' => 319,
                'return' => 'ids',
                'paged' => 1
            ));

            $fields = [];
            foreach ($products_ids as $product_id) {
//                if (in_array($product_id, [399196, 434830])) {
//                    continue;
//                }
                $product = wc_get_product($product_id);
                $total++;

                $data = $this->fetchItemData($product->get_sku(), $product->get_name(), $product->get_meta('supplier'));
//                $user = get_users(array('meta_key' => 'vendorid', 'meta_value' => $data->vendorId));
                if (!$data) {
                    if (in_array($product->get_id(), [451028])) {
                        continue;
                    }
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
                    echo 'not found by email, retry';
                    die();
                    $user1 = get_users(array('meta_key' => 'description', 'meta_value' => trim($data->vendorEmail)));

                    if (!$user1[0]) {
                        $user1 = get_user_by('description', $data->vendorEmail);
                        var_dump($data->vendorEmail);
                        var_dump($user1);
                        echo 'vendor not found : ';
                        var_dump($data);
                        die();
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
                        //update by old vendor
                        echo 'update by vendor email to id : ' . $user[0]->ID;
                        var_dump($product->get_name());
//                        die();
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
