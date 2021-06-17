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

    public function createSexyFeed()
    {
        ini_set('display_errors', '1');
        $items = [];
        /* @var \WC_Product $product */
        foreach (wc_get_products([
            'limit' => -1,
            'status' => 'published',
            'tax_query' => [[
                'taxonomy'      => 'product_cat',
                'terms'         => 2690,
            ]],
            'paged' => 1
        ]) as $key => $product) {

            foreach ($product->get_category_ids() as $catId) {
                if ($catId === 2690) {
                    continue;
                }
                $subCat = '';
                $cat = get_term($catId, 'product_cat');
                if ($cat->parent === 2690) {
                    $mainCat = $cat->name;
                } else {
                    $subCat = $cat->name;
                }
            }
            $images = [];
            $images[] = wp_get_attachment_url($product->get_image_id());
            foreach ($product->get_gallery_image_ids() as $imageId) {
                $images[] = wp_get_attachment_url($imageId);
            }

            $items[] = [
                "title" => trim($product->get_title()),
                "description" => trim($product->get_description()),
                "mainCategory" => trim($mainCat),
                "subCategory" => trim($subCat),
                "price" => $product->get_price(),
                "salePrice" => $product->get_sale_price(),
                "weight" => $product->get_weight(),
                "status" => $product->get_stock_status(),
                "productId" => $product->get_id(),
                "images" => $images,
                "sku" => $product->get_sku()
            ];
        }
        file_put_contents(WP_CONTENT_DIR . '/uploads/sexyExport.json', json_encode($items));
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
            'status' => 'draft',
            'meta_key' => 'supplier',
            'meta_value' => 27,
        ));
        $removed = 0;
//        $diff = array_diff($this->getIndexedIds(), $products);
        $diff = array_intersect($products, $this->getIndexedIds());
        foreach ($diff as $postId) {
            $p = wc_get_product($postId);
            if (!$p || ($p && $p->get_status() !== 'publish')) {
//                var_dump($p->get_status());
//                var_dump($p);
//                die();
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
//            if ($key == 0) {
//                continue;
//            }
            $this->createProduct($item);
        }

    }

    public function createProduct($item)
    {
//        $products = wc_get_products(array(
//            'limit' => 1,
//            'status' => 'published',
//            'meta_key' => 'vendor_code',
//            'meta_value' => $item[0],
//        ));

//        if (count($products)) {
//            return false;
//        }

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
//        $subscibers = new \Subscriber\Repository\Subscriber(new \Subscriber\Mapper\Subscriber());
//        $data = file_get_contents(WP_CONTENT_DIR . '/uploads/subscribers.txt');
//        foreach (explode(PHP_EOL, $data) as $datum) {
//            $subscibers->create(['email' => $datum, 'emailStatus' => 'confirmed']);
//        }
//        die();

//        $total = 0;

//        $order = wc_get_order(544649);

//    $dtStart = \DateTime::createFromFormat('d/m/Y', '1/5/2020');
//    $dtEnd = \DateTime::createFromFormat('d/m/Y', '15/7/2020');
//    $dt = $dtStart->getTimestamp() .'...'. $dtEnd->getTimestamp();

//        $arg = array(
//            'orderby' => 'date',
//            'posts_per_page' => '30',
//        'posts_per_page' => -1,
//        'date_created' => $dt,
//        'page' => 10,
//        );
//        $orders = WC_get_orders($arg);


//        $ids = [553708,553615,553770,554054,554049,554050,554136,554137,554138,553686,554051,554052,387437,554134,554135,554060,554058,554059,554043,553788,554185,554186,554187,554182,554183,554184,554179,554180,554181,554176,554178,554173,554174,554170,554171,554172,554133,554130,554132,554127,554128,554129,554124,554125,554126,554123,554085,554083,553740,379588,554154,554155,554153,554106,554045,553758,553600,554044,554143,554092,554091,553663,554113,554114,554115,554112,554077,554075,554076,553734,554159,554160,554157,554158,554156,554142,554144,554140,554141,554109,554107,554108,554095,554071,554061,554041,553683,553622,553580,554139,554093,554094,553763,554167,554168,554169,554164,554165,554166,554163,554122,554119,554120,554121,554116,554117,554118,554081,554080,554078,553631,554145,554146,554097,554098,554096,554067,554068,553725,554162,554161,554111,554110,554074,554073,553784,553695,553691,553613,554069,553652,554151,554152,554148,554149,554150,554147,554103,554104,554100,554101,554102,554099,554063,554064,553656,554188,554177,554175,553778,554353,554354,553749,553639,554359,554355,554357,554358,553680,553756,553773,553650,554230,554231,554232,554227,554228,554229,554226,553605,554404,554405,554401,554402,554403,554399,554400,554396,554397,554398,554393,554394,554395,554391,554392,554389,554390,554386,554387,554388,554384,554385,554383,554373,554374,554370,554371,554372,554367,554368,554369,554365,554366,554362,554363,554364,554361,554349,554350,554341,554342,554327,554324,554325,554326,554321,554322,554323,554320,554316,554314,554310,554311,554312,554308,554306,554303,554304,554301,554302,554298,554299,554300,554295,554296,554292,554293,554294,554290,554291,554285,554286,554287,554288,554282,554283,554284,554280,554281,554277,554278,554279,554274,554275,554276,554272,554273,554269,554270,554271,554267,554268,554256,554225,554222,554223,554224,554220,554221,554217,554218,554219,554213,554214,554215,554216,554210,554211,554212,554207,554209,554205,554206,553787,553772,553769,553728,553722,553717,553676,553661,553654,553647,553641,553629,553596,553587,554347,554344,554345,554346,554339,554340,554336,554337,554338,554334,554335,554332,554333,554329,554330,554331,554328,554318,554319,554315,554309,554307,554204,554201,554202,554203,554198,554199,554200,554195,554196,554197,554193,553776,553752,553706,553618,553603,553713,553704,553782,553715,553592];
//        foreach ($ids as $id) {
//            $product = get_product_by_sku($id);
//            if (!$product) {
//                var_dump('product not found' . $id);
//                die();
//            }
//            if ($product->get_meta('supplier') != 296) {
//                echo $product->get_id() .' - ' . $product->get_name() . PHP_EOL;
//            }
//        }
//        die();

        $products = wc_get_products(array(
            'limit' => -1,
//            'status' => 'published',
            'meta_key' => 'supplier',
            'meta_value' => 296,
            'paged' => 1
        ));

        /* @var \WC_Product $product */
        foreach ($products as $key => $product) {
//            $name = str_replace('pulse ', '', mb_strtolower($product->get_name()));
//            $name = 'Pulse ' . $name;

//            if ($product->get_sku()[0] == 0) {
//                $sku = substr($product->get_sku(), 1);
//                $p = get_product_by_sku($sku);
//                var_dump($p->get_id());
//                var_dump($p->get_price());
//                var_dump($p->get_title());
//                echo '"' . $p->get_sku() . '",' . $p->get_id() . ',' . $p->get_regular_price(). PHP_EOL;
//            }


//            $product->set_name($name);
//            $product->save();


            $status = ($product->get_status() === 'publish') ? 1:2;
            if ($product->get_stock_status() === 'outofstock') {
                $status = 3;
            }
            echo '"' . $product->get_sku() . '",' . $product->get_meta('inputprice', true) . ',' . $product->get_regular_price()
                . ',' . $product->get_sale_price() . ',' . $status . ',' . '"' . $product->get_meta('vendor_code', true) .'"'. PHP_EOL;
        }

//        var_dump($order->get_payment_method_title() === 'Pouzećem');
//        var_dump($order->payment_complete());
//        var_dump($order->get_payment_method_title());
//        var_dump($order->needs_payment());
//        var_dump($order->needs_processing());
//        var_dump($order->get_date_paid());
//        var_dump($order->is_paid());
        die();


//        $drafted = 0;
//        $vendor = [];
//        $items = [];
        $products = wc_get_products(array(
            'limit' => -1,
//            'status' => 'published',
            'meta_key' => 'supplier',
            'meta_value' => 237223,
//                'meta_value' => 123,
//                'compare' => 'IN',
//            'return' => 'ids',
//            'paged' => 1
        ));

//        var_dump(count($products));
//        die();

//        $products_ids = [412783];
        /* @var \WC_Product $wcProduct */
        foreach ($products as $wcProduct) {
//            if (get_class($wcProduct) == \WC_Product_Variable::class) {
                $wcProduct->set_status('draft');
                $wcProduct->save();
                $total++;
//            }
        }

//        echo 'new ' . $newItems . ' items' . "\r\n";
        echo 'drafted ' . $total . ' items';
    }
}
