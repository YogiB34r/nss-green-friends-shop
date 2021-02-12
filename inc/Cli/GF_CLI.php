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
            'port' => ES_PORT
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

    /**
     * removes indexed products no longer in database
     */
    public function removeNonExistentProducts()
    {
        /* @var \Elastica\Client $elasticaClient */
        $factory = new \GF\Search\Factory\ElasticClientFactory();
        $elasticaClient = $factory->make();
        $products = wc_get_products(array(
            'limit' => -1,
            'return' => 'ids',
            'status' => 'publish',
            'meta_key' => 'supplier',
            'meta_value' => 296,
        ));
        $removed = 0;
        $diff = array_diff($this->getIndexedIds(), $products);
        foreach ($diff as $postId) {
            $p = wc_get_product($postId);
            if (!$p) {
                $response = $elasticaClient->getIndex('product')->getType('product')->deleteById($postId);
                $removed++;
                if (!$response->isOk()) {
                    var_dump($response);
                    die();
                }
            }
        }
        echo sprintf('Removed %s products from index', $removed);
    }

    public function getIndexedIds()
    {
        /* @var \Elastica\Client $elasticaClient */
        $factory = new \GF\Search\Factory\ElasticClientFactory();
        $elasticaClient = $factory->make();
        $search = new \GF\Search\Elastica\Search($elasticaClient);

        $search->search('');
        $ids = [];
        $limit = 10000;

        $res = $elasticaClient->getIndex('product')->getType('product')->search(
            '', ['scroll' => '1m', 'size' => $limit]);
        $scrollId = $res->getResponse()->getScrollId();
        foreach ($res->getResults() as $term) {
            $ids[] = $term->getData()['postId'];
        }
        $total = $elasticaClient->getIndex('product')->getType('product')->count();

        while (count($ids) < $total) {
            $r = $elasticaClient->getIndex('product')->getType('product')->search(
                '', ['scroll_id' => $scrollId, 'scroll' => '1m']);

            $scrollId = $r->getResponse()->getData()['_scroll_id'];
            foreach ($r->getResults() as $term) {
                $ids[] = $term->getData()['postId'];
            }
        }

        return $ids;
    }

    public function createFromExcell()
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
        $reader->setReadDataOnly(true);
        $file = $reader->load(__DIR__ . '/Torbe za Plažu.xlsx');
        $worksheet = $file->getActiveSheet()
            ->rangeToArray(
                'A2:G' . $file->getActiveSheet()->getHighestRow(),
                null,
                true,
                true,
                false
            );
        foreach ($worksheet as $key => $item) {
            $this->createProduct($item);
        }

    }

    public function createProduct($item)
    {
        $product = new \WC_Product();
        $product->set_name('Pulse ' . str_replace('pulse ', '', mb_strtolower($item[3])));
        $product->set_status('draft');
        $price = 100;
        $product->set_catalog_visibility('visible');
        $product->set_short_description($item[6]);
        $product->set_description($item[6] . '<p>Garancija: '. $item[5] .'</p>');
        $product->set_weight(2);
        $product->set_reviews_allowed(1);
        $product->set_price($price);
        $product->set_regular_price($price);
        $product->update_meta_data('vendor_code', $item[0]);
        $product->update_meta_data('supplier', 296);
        $product->set_category_ids([3023, 1734]);
        $product->save();
        $product->set_sku($product->get_id());
        if (!$product->save()) {
            echo 'there was an error';
            var_dump($item);
        } else {
            echo 'created item ' . $item[0];
        }

        return true;
    }

    public function listItems()
    {
        $total = 0;
        $products = wc_get_products(array(
            'limit' => -1,
            'meta_key' => 'supplier',
            'meta_value' => 296,
            'paged' => 1
        ));

        /* @var \WC_Product $product */
        foreach ($products as $key => $product) {
            $status = ($product->get_status() === 'publish') ? 1:2;
            if ($product->get_stock_status() === 'outofstock') {
                $status = 3;
            }
            echo '"' . $product->get_sku() . '",' . $product->get_meta('inputprice', true) . ',' . $product->get_regular_price()
                . ',' . $product->get_sale_price() . ',' . $status . ',' . '"' . $product->get_meta('vendor_code', true) .'"'. PHP_EOL;
        }
        die();

        $products = wc_get_products(array(
            'limit' => -1,
            'meta_key' => 'supplier',
            'meta_value' => 237223,
        ));

        /* @var \WC_Product $wcProduct */
        foreach ($products as $wcProduct) {
                $wcProduct->set_status('draft');
                $wcProduct->save();
                $total++;
        }

        echo 'drafted ' . $total . ' items';
    }
}
