<?php

class GF_Elastica_Indexer
{
    static function index(\Elastica\Client $elasticaClient)
    {
        global $wpdb;

        $elasticaIndex = $elasticaClient->getIndex('nss');
        $elasticaType = $elasticaIndex->getType('products');
        $perPage = 500;

        $documents = [];
        for ($i = 0; $i < 42; $i++) {
            $offset = $i * $perPage;
            $sql = "SELECT ID FROM wp_posts WHERE post_type = 'product' LIMIT {$offset}, {$perPage};";
            $result = $wpdb->get_results($sql);
            foreach ($result as $value) {
                $product = wc_get_product($value->ID);
                $documents[] = static::parseWcProduct($product);
            }
        }

        $response = $elasticaType->addDocuments($documents);
        if (!$response->isOk()) {
            var_dump($response->getError());
            die();
        }
        echo sprintf('stored %s items.', 'x');
        $elasticaType->getIndex()->refresh();
    }

    static function parseWcProduct(\WC_Product $product)
    {
        $cats = [];
        foreach ($product->get_category_ids() as $category_id) {
            $cat = get_term_by('id', $category_id, 'product_cat');
            $cats[] = [
                'id' => $cat->term_id,
                'name' => $cat->name,
            ];
        }
        $attributes = [];
        if (get_class($product) === \WC_Product_Variable::class) {
            foreach ($product->get_available_variations() as $variation) {
                foreach ($variation['attributes'] as $attribute => $value) {
                    $attributes[] = [
                        'type' => ltrim($attribute, 'attribute_pa_'),
                        'value' => $value
                    ];
                }
            }
        }

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
            'shortDescription' => $product->get_short_description(),
            'regularPrice' => $product->get_regular_price(),
            'salePrice' => $product->get_sale_price(),
            'inputPrice' => $product->get_meta('input_price'),
            'stockStatus' => (int) $product->is_in_stock(),
            'status' => (int) $product->is_visible(),
            'viewCount' => 0,
            'rating' => 0,
            'sku' => $product->get_sku(),
            'synced' => 1,
            'type' => $product->get_type(),
        ];
        return new \Elastica\Document($product->get_id(), $data);
    }
}