<?php

namespace GF;

class Cli
{
    public function fixItems()
    {
//        $pages = 21;
        $limit = 100;

//        $increment = 1;
        $start = 50;
        $end = $start + 50;

        $diff = [];
        $html = '';
        $total = 0;
        $updated = 0;
//        for ($i = 1; $i < $pages; $i++) {
        for ($i = $start; $i < $end; $i++) {
            $products_ids = wc_get_products(array(
                'limit' => $limit,
                'return' => 'ids',
                'paged' => $i
            ));

            $fields = [];
            foreach ($products_ids as $product_id) {
                if ($total % $limit === 0) {
                    echo 'passed '.$total.' items' . PHP_EOL;
                }
                $total++;
                $product = wc_get_product($product_id);
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

//                if ($data->sku !== $product->get_sku()) {
//                    $fields['sku'] = $data->sku;
//                }
//
//                if ($data->status === 1 && $product->get_status() !== 'publish' ||
//                    $data->status === 0 && $product->get_status() !== 'draft') {
//                    $fields['status'] = $data->status;
//                }
//                if ($data->stockStatus === 1 && $product->get_stock_status() !== 'instock' ||
//                    $data->stockStatus === 0 && $product->get_stock_status() !== 'outofstock') {
//                    $fields['stockStatus'] = $data->stockStatus;
//                    $children = get_posts(array(
//                        'post_parent'   => $product->get_id(),
//                        'posts_per_page'=> -1,
//                        'post_type'   => 'product_variation'
//                    ));
//                    if ($data->stockStatus) {
//                        $stockStatus = 'instock';
//                    } else {
//                        $stockStatus = 'outofstock';
//                    }
//                    $product->set_stock_status($stockStatus);
//                    foreach ($children as $child) {
//                        $variation = wc_get_product($child->ID);
//                        $variation->set_stock_status($stockStatus);
//                        $variation->save();
//                    }
//                }

//                $images = explode(',', $data->images);
                //has different images
//                if (has_post_thumbnail($product->get_id())) {
//                    if (count($images) - 1 !== count($product->get_gallery_image_ids())) {
    //                    $fields['images'] = 'different';
//                        if (!$this->handleImage($data->images, $product->get_id())) {
//                            $html .= 'failed to save image for sku ' . $product->get_sku();
//                        } else {
//                            $html .= '<p>Updated images for productId: '.$product->get_id().'</p>';
//                        }
//                    }
//                } else {
    //                $fields['images'] = 'none';
//                    if (!$this->handleImage($data->images, $product->get_id())) {
//                        $html .= 'failed to create image for sku ' . $product->get_sku();
//                    } else {
//                        $html .= '<p>Created images for productId: '.$product->get_id().'</p>';
//                    }
//                }
//                if ($data->pdv !== $product->get_meta('pdv')) {
//                    $fields['pdv'] = $data->pdv;
//                    $product->update_meta_data('pdv', $data->pdv);
//                    var_dump('pdv differs');
//                }


//                $user = get_users(array('meta_key' => 'vendorid', 'meta_value' => $data->vendorId));
//                $user = get_user_by('description', $data->vendorEmail);
//                $user = get_users(array('meta_key' => 'description', 'meta_value' => $data->vendorEmail));
                $user = get_users(array('meta_key' => 'vendorid', 'meta_value' => $data->vendorId));
//                $user1 = get_users(array('meta_key' => 'description', 'meta_value' => $data->vendorEmail));
                if (!$user) {
//                    echo 'retry';
                    $user1 = get_users(array('meta_key' => 'description', 'meta_value' => trim($data->vendorEmail)));

                    if (!$user1) {
                        $user1 = get_user_by('description', $data->vendorEmail);
//                        if ($data->vendorEmail === 'rajko.djuric@ringier.rs') {
//                            $updated++;
//                            $product->update_meta_data('supplier', 373);
//                            $product->save();
//                        } else {
                            var_dump($data->vendorEmail);
                            var_dump($user1);
                            echo 'vendor not found : ';
                            var_dump($data);
                            die();
//                        }

                    }

                    // trouble ahead
                    if ((int) $product->get_meta('supplier') !== $user1[0]->ID) {
                        $updated++;
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
                        $updated++;
                        $user1 = get_users(array('meta_key' => 'description', 'meta_value' => trim($data->vendorEmail)));
                        if ($user[0]->ID !== $user1[0]->ID) {
                            if (strstr($data->vendorEmail, 'rajko.djuric@ringier.rs')) {
                                $product->update_meta_data('supplier', 373);
                                $product->save();
                            } elseif ($product->get_meta('supplier') == 207) {
                                echo 'updated fantazija item';
                                $product->update_meta_data('supplier', 230);
                                $product->save();
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
        $html .= 'total of items updated: '. $updated . PHP_EOL;
        $html .= 'Differences' . PHP_EOL;
        $html .= print_r($diff, true);
        echo $html;

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
