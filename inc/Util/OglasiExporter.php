<?php

namespace GF\Util;


use Elastica\Document;
use Elastica\ResultSet;

class OglasiExporter
{

    public function getProductList($categoryId, $page = 1, $perPage = 100)
    {
        $client = new \Elastica\Client([
            'host' => ES_HOST,
            'port' => 9200
        ]);
        $search = new \GF\Search\Search(new \GF\Search\Adapter\Elastic(new \GF\Search\Elastica\Search($client)));
        $resultSet = $search->getItemsForCategory($categoryId, null, $perPage, $page);
        $items = $this->parseProduct($resultSet);
//        var_dump(count($resultSet->getDocuments()));
//        var_dump($resultSet->getDocuments()[0]->getData()['postId']);

        return [
            'token' => $page+1,
            'count' => count($items),
            'items' => $items,
            'last' => (count($resultSet->getDocuments()) < $perPage) ? true : false
        ];
    }

    public function parseProduct(ResultSet $resultSet)
    {
        $items = [];
        /* @var Document $document */
        foreach ($resultSet->getDocuments() as $document) {
            $data = $document->getData();
            $price = $data['regularPrice'];
            if ($data['salePrice'] > 0 && $data['salePrice'] < $price) {
                $price = $data['salePrice'];
            }
            if ($data['status'] !== 1) {
                continue;
            }
            if ($data['stockStatus'] !== 1) {
                continue;
            }
            $src = wp_get_attachment_image_src(get_post_thumbnail_id($data['postId']));
            if ($src) {
                $images = [$src[0]];
            } else {
                $images = [];
//                continue;
            }
            $gallery = get_metadata('_product_image_gallery', $data['postId']);
            if (count($gallery)) {
                foreach ($gallery as $image) {
                    var_dump($image);
                    die();
                }
            }

            $items[] = [
                'id' => $data['postId'],
                'name' => $data['name'],
                'description' => $data['description'],
                'images' => $images,
                'actionUrl' => $data['permalink'],
                'price' => $price,
                'type' => $data['type'],
                'sku' => $data['sku'],
            ];
        }
        return $items;
    }

    public function parseCategory(\WP_Term $cat, $withItems = true)
    {
        $data = [
            'categoryId' => $cat->term_id,
            'name' => $cat->name,
            'slug' => $cat->slug,
            'productCount' => $cat->count,
            'childrenCount' => count(get_term_children($cat->term_id, 'product_cat')),
        ];
        if (!$withItems) {
            return $data;
        }
        $data['items'] = $this->getChildren($cat->term_id);

        return $data;
    }

    public function getChildren($id)
    {
        $items = [];
        foreach (get_term_children($id, 'product_cat') as $child) {
            $items[] = $this->parseCategory(get_term($child));
        }
        return $items;
    }

    public function getCategory(int $id)
    {
        $cat = get_term($id);
        if (!$cat) {
            throw new \Exception('Category not found: ' . $id);
        }
        return $this->parseCategory($cat);
    }

    public function getRootCategories()
    {
        $cats = [];
        //uncategorized
        foreach (CategoryFunctions::gf_get_top_level_categories([3152]) as $cat) {
//            var_dump($cat);
//            die();
            $cats[] = $this->parseCategory($cat, false);
        }
        return $cats;
    }
}