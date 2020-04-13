<?php

namespace GF\Search\Elastica;

use Elastica\Type;


class Indexer
{
    private $elasticaType;

    public function __construct(Type $elasticaType)
    {
        $this->elasticaType = $elasticaType;
    }

    public function indexProduct(\WC_Product $product)
    {
        try {
            $response = $this->elasticaType->addDocument($this->parseWcProduct($product));
            if (!$response->isOk() || $response->hasError()) {
                var_dump($response->getError());
                die();
            }
            unset($response);
            $this->elasticaType->getIndex()->refresh();
        } catch (\Exception $e) {
            \NSS_Log::log($e->getMessage(), \NSS_Log::LEVEL_ERROR);
        }
    }

    /**
     * Batch index
     */
    public function indexAll()
    {
        global $wpdb;

        $perPage = 500;
        $totalItems = 0;

        for ($i = 0; $i < 60; $i++) {
            $offset = $i * $perPage;

            $sql = "SELECT ID FROM wp_posts WHERE post_type = 'product' 
            AND post_status = 'publish'
LIMIT {$offset}, {$perPage};";
//            $sql = "SELECT ID FROM wp_posts WHERE post_type = 'product'
// AND ID IN (397944,419590,401317,391140,427106,413564,426681,405142)
// LIMIT {$offset}, {$perPage};";
            $products = $wpdb->get_results($sql);

//            $products = wc_get_products(array(
//                'category' => array('muska-obuca'), //muska-obuca
//                'posts_per_page' => $perPage, //muska-obuca
//                'page' => $i+1
//            ));

            $wpdb->flush();
            if (count($products) > 0) {
                $documents = [];
                foreach ($products as $value) {
                    $product = wc_get_product($value->ID);
//                    $product = $value;
                    if (!$product) {
                        var_dump($product);
                        var_dump('Could not find product for postId : ', $value->ID);
                        die();
                    }
                    if ($product->get_name() === "AUTO-DRAFT") {
                        continue;
                    }
                    if ($product->get_status() !== 'publish') {
                        //@TODO removing of products....
//                        $this->elasticaType->deleteDocument($this->parseWcProduct($product));
                    } else {
                        $documents[] = $this->parseWcProduct($product);
                    }
                }
                try {
                    $response = $this->elasticaType->addDocuments($documents);
                    if (!$response->isOk() || $response->hasError()) {
                        var_dump($response->getError());
                        die();
                    }
                    $totalItems += count($documents);
                    $documents = [];
//                    echo sprintf('stored %s items.', $response->count());
                    unset($response);
                    $this->elasticaType->getIndex()->refresh();
                } catch (\Exception $e) {
                    var_dump($e->getMessage());
                }
            } else {
                break;
            }
        }
        echo "sync complete. $totalItems items indexed.";
    }

    private function parseWcProduct(\WC_Product $product)
    {
        global $wpdb;

        $cats = [];
        foreach ($product->get_category_ids() as $category_id) {
            $cat = get_term_by('id', $category_id, 'product_cat');
            if ($cat->parent === 0){
                $cat_lvl = 1;
            } else {
                if (get_term($cat->parent, 'product_cat')->parent === 0){
                    $cat_lvl = 2;
                } else{
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
        $salePrice = $product->get_sale_price();
        $price = $regularPrice = $product->get_regular_price();
        if (get_class($product) === \WC_Product_Variable::class) {
            $regularPrice = $product->get_variation_regular_price();
            $salePrice = $product->get_variation_sale_price();
            foreach ($product->get_available_variations() as $variation) {
                foreach ($variation['attributes'] as $attribute => $value) {
                    $attributes[] = [
                        'type' => ltrim($attribute, 'attribute_pa'),
                        'value' => $value
                    ];
                }
            }
        }
        if ($product->get_price() !== 0 && $product->get_price() !== $regularPrice) {
            $salePrice = $price = $product->get_price();
        }
        if ((int) $price === 0) {
//            $product->set_status('draft');
//            $product->save();
        }


        // @TODO solve better. when no sku detected, use post id.
        if ($product->get_sku() == "") {
//            $product->set_sku(md5($product->get_id() . $product->get_name()));
            $product->set_sku('0' . $product->get_id());
        }

        $thumbnail = '<img src="' . wc_placeholder_img_src() . '" alt="Placeholder" width="200px" height="200px" />';
        if (has_post_thumbnail($product->get_id())) {
            $thumbnail = '<img src="'.get_the_post_thumbnail_url($product->get_id(), 'shop_catalog').'" width="200" height="200" '.
            ' class="attachment-post-thumbnail size-post-thumbnail wp-post-image"  alt="'.$product->get_title().'"  />';
        }
        $product_link = get_permalink((int) $product->get_id());
        $sql = "SELECT * FROM wp_gf_products WHERE postId = {$product->get_id()}";
        $viewCount = 0;
        if (!isset($wpdb->get_results($sql)[0])) {
            $data = sprintf('%s: %s', date('Y:m:d H:i:s'), 'could not find gf product for ' . $product->get_id());
            $filePath = LOG_PATH . 'debug-cli.log';
            file_put_contents($filePath, $data . PHP_EOL, FILE_APPEND);
        } else {
            $gfProduct = $wpdb->get_results($sql)[0];
            $viewCount = $gfProduct->viewCount;
        }
        $rating = $product->get_meta('rating');

        $ordering = $this->calculateOrderingRating($product);

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
            'salePrice' => $salePrice,
            'inputPrice' => $product->get_meta('input_price'),
            'stockStatus' => (int) $product->is_in_stock(),
            'status' => (int) ($product->get_status() === 'publish'),
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
                'price' => $price,
//                'price-asc' => $ordering['price-asc'],
//                'price-desc' => $ordering['price-desc'],
                'rating' => $rating,
                'date' => strtotime($product->get_date_created()),
                'viewCount' => $viewCount,
                'stock' => (int) $product->is_in_stock(),
                'published' => (int) ($product->get_status() === 'publish'),
                'default' => $ordering['default'],
            ]
        ];
        return new \Elastica\Document($product->get_id(), $data);
    }

    /**
     * @param \WC_Product $product
     * @return array
     */
    private function calculateOrderingRating(\WC_Product $product)
    {
        $ponder = 1;
        $menuOrder = (int) $product->get_menu_order();
        if ($product->get_sale_price() > 0 && $product->get_regular_price()) {
            $ponder = 10000;
        }
        if (!$product->is_in_stock()) {
            $ponder = -1;
        }

        /* @var \WC_Product_Variable $product */
        if (get_class($product) === \WC_Product_Variable::class) {
            if ($product->get_variation_sale_price() > 0 && $product->get_variation_sale_price() < $product->get_variation_regular_price()) {
                $ponder = 10000;
            }
            $stock = false;
            foreach ($product->get_available_variations() as $variation) {
                if ($variation['is_in_stock']) {
                    $stock = true;
                }
            }
            if (!$stock) {
                $ponder = -1;
            }
        }

        // sort items according to sort order set in admin
        if ($menuOrder === 0) {
            $menuOrder = 100000;
        }
        $menuOrder = 200000 - $menuOrder;

        return [
            'default' => $ponder * $menuOrder,
//            'price-desc' => $priceDescPonder,
//            'price-asc' => $priceAscPonder
        ];
    }

    /**
     * @param \WC_Product $product
     * @param $attributes
     * @param $cats
     * @return string
     */
    private function extractFullTextBoostedFields(\WC_Product $product, $attributes, $cats)
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

    /**
     * @param \WC_Product $product
     * @param $attributes
     * @param $cats
     * @return string
     */
    private function extractFullTextFields(\WC_Product $product, $attributes, $cats)
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
