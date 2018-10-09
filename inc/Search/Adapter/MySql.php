<?php

namespace GF\Search\Adapter;

class MySql implements \GF\Search\AdapterInterface
{
    private $wpdb;

    public function __construct(\wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
    }

    private function parseOrderBy($search = false)
    {
        $order = (isset($_GET['orderby'])) ? $_GET['orderby'] : 'date';
        switch ($order) {
            case 'popularity':
                $orderBy = " ORDER BY viewCount DESC ";

                break;

            //@TODO add sync for ratings
            case 'rating':
//            $orderBy = " ORDER BY rating DESC ";
                $orderBy = " createdAt DESC ";

                break;

            case 'date':
                $orderBy = " createdAt DESC ";
                if ($search) {
                    $orderBy = " ORDER BY o DESC, createdAt DESC ";
                }

                break;

            case 'price-desc':
                $orderBy = " priceOrder DESC ";

                break;

            case 'price':
                $orderBy = " priceOrder ";

                break;

            default:
                $orderBy = " createdAt DESC ";

                break;
        }
        return $orderBy;
    }

    public function getIdsForStandardSearch($input, $limit = 0)
    {
        $input = addslashes($input);
        $searchCondition = "";
        $customOrdering = "";
        $explodedInput = explode(' ', $input);
        $attributes = parseAttributes();
        $gradeCount = 0;
        foreach ($explodedInput as $key => $word) {
            if (strlen($word) > 2) {
                $gradeCount++;
                //query is attribute
                if (in_array(rtrim($word, 'aeiou'), $attributes)) {
                    if ($key > 0) {
                        $searchCondition .= " OR ";
                        $customOrdering .= " + ";
                    }
                    $searchCondition .= " attributes LIKE '%{$word}%' ";
                    $customOrdering .= "
                CASE
                    WHEN productName LIKE '%{$word}%' THEN 15
                    ELSE 0
                END +
                CASE WHEN description LIKE '%{$word}%' THEN 10 ELSE 0 END 
                ";
                } else {
                    $word = rtrim($word, 'aeiou');
                    if ($key > 0) {
                        $searchCondition .= " OR ";
                        $customOrdering .= " + ";
                    }
                    $searchCondition .= " productName LIKE '%{$word}%' OR description LIKE '%{$word}%' 
                OR attributes LIKE '%{$word}%' OR categories LIKE '%{$word}%'";
                    $customOrdering .= "
                CASE
                    WHEN productName LIKE '% {$word} %' THEN 16
                    WHEN productName LIKE '{$word} %' THEN 15
                    WHEN productName LIKE '{$word}%' THEN 12
                    WHEN productName LIKE '%{$word}%' THEN 9
                    ELSE 0
                END
                + CASE
                    WHEN categories LIKE '%{$word}%' THEN 14 ELSE 0
                END
                + CASE
                    WHEN description LIKE '%{$word}%' THEN 4 ELSE 0
                END
                + CASE WHEN attributes LIKE '%{$word}%' THEN 13 ELSE 0 END ";
                }
            }
        }
        $priceCondition = "";
        if (isset($_GET['min_price'])) {
            $minPrice = (int)$_GET['min_price'];
            $maxPrice = (int)$_GET['max_price'];
            $priceCondition = " AND priceOrder >= {$minPrice} AND priceOrder <= {$maxPrice} ";
        }
        $excludeCategories = " 1=1 ";
        foreach (gf_get_category_children_ids('sexy-shop') as $catId) {
            $excludeCategories .= " AND categoryIds NOT LIKE '%{$catId}%' ";
        }

        $gradeCount = $gradeCount * 7;
        $priceOrdering = " CASE
        WHEN salePrice > 0 THEN salePrice
        ELSE regularPrice 
     END as priceOrder ";

        $sql = "SELECT 
        postId,
        {$customOrdering} as o,
        {$priceOrdering}
        FROM wp_gf_products
        WHERE stockStatus = 1 
        AND status = 1
        AND {$excludeCategories}
        AND ({$searchCondition}) 
        HAVING o > {$gradeCount}
        {$priceCondition}
        {$this->parseOrderBy(true)}";
        if ($limit) {
            $sql .= " LIMIT {$limit} ";
        }

//    echo $sql;
        $products = $this->wpdb->get_results($sql, OBJECT_K);

        return array_keys($products);
    }

    public function getIdsForCategory($slug)
    {
        $cat = get_term_by('slug', $slug, 'product_cat');
        $orderBy = $this->parseOrderBy();
        $searchCondition = " 1=1 ";
        $customOrdering = " 1=1 ";
        if (isset($_GET['query'])) {
            $searchCondition = "";
            $customOrdering = "";
            $input = addslashes($_GET['query']);
            $explodedInput = explode(' ', $input);
            $gradeCount = 0;
            foreach ($explodedInput as $key => $word) {
                if ($key > 0) {
                    $searchCondition .= " OR ";
                    $customOrdering .= " + ";
                }
                $searchCondition .= " productName LIKE '%{$word}%' OR description LIKE '%{$word}%' 
                OR attributes LIKE '%{$word}%'";
                $customOrdering .= "
                CASE
                    WHEN productName LIKE '% {$word} %' THEN 16
                    WHEN productName LIKE '{$word} %' THEN 15
                    WHEN productName LIKE '{$word}%' THEN 12
                    WHEN productName LIKE '%{$word}%' THEN 9
                    ELSE 0
                END
                + CASE
                    WHEN categories LIKE '%{$word}%' THEN 14 ELSE 0
                END
                + CASE
                    WHEN description LIKE '%{$word}%' THEN 4 ELSE 0
                END
                + CASE WHEN attributes LIKE '%{$word}%' THEN 13 ELSE 0 END ";
            }
            $customOrdering .= " as o ";
            $orderBy .= " , o DESC ";
            $gradeCount = $gradeCount * 7;
        }

        $priceOrdering = " CASE
                WHEN salePrice > 0 THEN salePrice
                ELSE regularPrice 
            END as priceOrder ";

        $priceCondition = "";
        if (isset($_GET['min_price'])) {
            $minPrice = (int)$_GET['min_price'];
            $maxPrice = (int)$_GET['max_price'];
            $priceCondition = " HAVING priceOrder >= {$minPrice} AND priceOrder <= {$maxPrice} ";
        }

        $excludeCategories = " 1=1 ";
        if (!in_array($cat->term_id, gf_get_category_children_ids('sexy-shop'))) {
            foreach (gf_get_category_children_ids('sexy-shop') as $catId) {
                $excludeCategories .= " AND categoryIds NOT LIKE '%{$catId}%' ";
            }
        }

        $sql = "SELECT postId, {$priceOrdering}, {$customOrdering} FROM wp_gf_products WHERE salePrice > 0 AND stockStatus = 1 AND status = 1
                AND categories LIKE '%{$cat->name}%' AND categoryIds LIKE '%{$cat->term_id}%' AND {$excludeCategories}
                AND ({$searchCondition})
                {$priceCondition} 
                ORDER BY $orderBy ";
        $productsSale = $this->wpdb->get_results($sql, OBJECT_K);

        $sql = "SELECT postId, {$priceOrdering}, {$customOrdering} FROM wp_gf_products WHERE salePrice = 0 AND stockStatus = 1 AND status = 1
                AND categories LIKE '%{$cat->name}%' AND categoryIds LIKE '%{$cat->term_id}%' AND {$excludeCategories}
                AND ({$searchCondition})
                {$priceCondition} ORDER BY $orderBy ";
        $productsNotOnSale = $this->wpdb->get_results($sql, OBJECT_K);
        $allIds = array_merge(array_keys($productsSale), array_keys($productsNotOnSale));

        $sql = "SELECT postId, {$priceOrdering}, {$customOrdering} FROM wp_gf_products WHERE stockStatus = 0 AND status = 1
                AND categories LIKE '%{$cat->name}%' AND categoryIds LIKE '%{$cat->term_id}%' AND {$excludeCategories}
                AND ({$searchCondition})
                {$priceCondition} ORDER BY $orderBy ";

        $productsOutOfStock = $this->wpdb->get_results($sql, OBJECT_K);

        return array_merge($allIds, array_keys($productsOutOfStock));
    }
}