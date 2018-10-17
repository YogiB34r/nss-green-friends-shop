<?php

namespace GF\Search\Elastica;

class Indexer
{
    static function index(\Elastica\Client $elasticaClient)
    {
        global $wpdb;
        ini_set('max_execution_time', '600');

        $elasticaIndex = $elasticaClient->getIndex('nss');
        $elasticaType = $elasticaIndex->getType('products');
        $perPage = 5000;
//        $perPage = 50;

        for ($i = 2; $i < 5; $i++) {
//        for ($i = 0; $i < 35; $i++) {
//        for ($i = 0; $i < 1; $i++) {
            $offset = $i * $perPage;
            $sql = "SELECT ID FROM wp_posts WHERE post_type = 'product' LIMIT {$offset}, {$perPage};";
            $result = $wpdb->get_results($sql);
            $wpdb->flush();
            if (count($result) > 0) {
                $documents = [];
                foreach ($result as $value) {
                    $product = wc_get_product($value->ID);
                    if (!$product) {
                        var_dump($product);
                        var_dump('Could not find product for postId : ', $value->ID);
                        continue;
                    }
                    $documents[] = static::parseWcProduct($product);
                }
                unset($result);

                $response = $elasticaType->addDocuments($documents);
                $documents = [];
                if (!$response->isOk() || $response->hasError()) {
                    var_dump($response->getError());
                    die();
                }
                echo sprintf('stored %s items.', $response->count());
                unset($response);
                $elasticaType->getIndex()->refresh();
            }
        }
        echo 'sync complete';
    }

    static function parseWcProduct(\WC_Product $product)
    {
        global $wpdb;

        $cats = [];
        foreach ($product->get_category_ids() as $category_id) {
            $cat = get_term_by('id', $category_id, 'product_cat');
            if($cat->parent === 0){
                $cat_lvl = 1;
            }else{
                if (get_term($cat->parent, 'product_cat')->parent === 0){
                    $cat_lvl = 2;
                }else{
                    $cat_lvl = 3;
                }
            }
            $cats[] = [
                'id'    => $cat->term_id,
                'name'  => $cat->name,
                'slug'  => $cat->slug,
                'parent'=> $cat->parent,
                'level' => $cat_lvl
            ];
        }
        $attributes = [];
        if (get_class($product) === \WC_Product_Variable::class) {
            foreach ($product->get_available_variations() as $variation) {
                foreach ($variation['attributes'] as $attribute => $value) {
                    $attributes[] = [
                        'type' => ltrim($attribute, 'attribute_pa'),
                        'value' => $value
                    ];
                }
            }
        }

        $thumbnail = '<img src="' . wc_placeholder_img_src() . '" alt="Placeholder" width="200px" height="200px" />';
        if (has_post_thumbnail($product->get_id())) {
            $thumbnail = '<img src="'.get_the_post_thumbnail_url($product->get_id(), 'shop_catalog').'" width="200" height="200" '.
            ' class="attachment-post-thumbnail size-post-thumbnail wp-post-image"  alt="'.$product->get_title().'"  />';
        }
        $product_link = get_permalink((int) $product->get_id());
        $salePrice = 0;
        if ($product->is_type('variable')){
            $regularPrice = $product->get_variation_regular_price();
        }else{
            $regularPrice = $product->get_regular_price();
        }
        $price = $regularPrice;
        if ($product->get_price() !== $regularPrice) {
            $salePrice = $product->get_price();
            $price = $salePrice;
        }
        $sql = "SELECT * FROM wp_gf_products WHERE postId = {$product->get_id()}";
        if (!isset($wpdb->get_results($sql)[0])) {
            throw new \Exception('could not find gf product for ' . $product->get_id());
        }
        $gfProduct = $wpdb->get_results($sql)[0];

        $data = [
            'postId' => $product->get_id(),
            'category' => $cats,
            'attributes' => $attributes,
            'name' => $product->get_name(),
            'manufacturer' => $product->get_meta('pa_proizvodjac'),
            'createdAt' => strtotime($product->get_date_created()),
            'supplierId' => $product->get_meta('supplier'),
            'supplierSku' => $product->get_meta('vendor_code'),
            'description' => $product->get_description(),
            'thumbnail' => $thumbnail,
            'permalink' => $product_link,
            'shortDescription' => $product->get_short_description(),
            'regularPrice' => $regularPrice,
            'salePrice' => (string) $salePrice,
            'inputPrice' => $product->get_meta('input_price'),
            'stockStatus' => (int) $product->is_in_stock(),
            'status' => (int) $product->is_visible(),
            'viewCount' => 0,
            'rating' => 0,
            'sku' => $product->get_sku(),
            'synced' => 1,
            'type' => $product->get_type(),
            'search_data' => [
                'full_text' => static::extractFullTextFields($product, $attributes, $cats),
                'full_text_boosted' => static::extractFullTextBoostedFields($product, $attributes, $cats),
            ],
            'order_data' => [
                'price' => (int) $price,
                'rating' => $product->get_meta('rating'),
                'date' => strtotime($product->get_date_created()),
                'viewCount' => $gfProduct->viewCount,
                'stock' => (int) $product->is_in_stock(),
                'published' => (int) $product->is_visible(),
                'default' => static::calculateOrderingRating($product),
            ]
        ];
        return new \Elastica\Document($product->get_id(), $data);
    }

    static function calculateOrderingRating(\WC_Product $product)
    {
        $ponder = 10;
        if ($product->is_on_sale()) {
            $ponder = 100;
        }
        if (!$product->is_in_stock()) {
            $ponder = 1;
        }


        return $ponder;


        $actionPonder = 50;
        $statusPonder = 20;
        $stockPonder = -100;

        return ((int) $product->is_on_sale() * $actionPonder) +
        ((int) $product->is_visible() * $statusPonder) +
        ((int) !$product->is_in_stock() * $stockPonder);
    }

    static function extractFullTextBoostedFields(\WC_Product $product, $attributes, $cats)
    {
        $text = $product->get_name();
        $text .= ' ' . $product->get_meta('pa_proizvodjac');
        $text .= ' ' . $product->get_meta('vendor_code');
//        $text .= ' ' . $product->get_sku();
        $attr = '';
        if (count($attributes) > 0) {
            foreach ($attributes as $attribute) {
                $attr .= ' ' . implode(' ', $attribute) . ' ';
            }
        }
        $categories = '';
        if (count($cats) > 0) {
            foreach ($cats as $cat) {
                $categories .= ', '. $cat['name'];
            }
        }
        $text .= ' ' . $attr .' '. $categories;

        return $text;
    }

    static function extractFullTextFields(\WC_Product $product, $attributes, $cats)
    {
        $text = $product->get_name();
        $text .= ' ' . $product->get_meta('pa_proizvodjac');
        $text .= ' ' . $product->get_meta('vendor_code');
        $text .= ' ' . strip_tags($product->get_description());
        $text .= ' ' . strip_tags($product->get_short_description());
        $text .= ' ' . $product->get_sku();
        $attr = '';
        if (count($attributes) > 0) {
            foreach ($attributes as $attribute) {
                $attr .= implode(' ', $attribute);
            }
        }
        $categories = '';
        if (count($cats) > 0) {
            foreach ($cats as $cat) {
                $categories .= ', '. $cat['name'];
            }
        }
        $text .= ' ' . $attr .' '. $categories;

        return $text;
    }


}