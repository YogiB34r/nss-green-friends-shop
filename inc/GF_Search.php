<?php

class GF_Search
{
    public function getIdsForStandardSearch($input, $limit = 0)
    {
        global $wpdb;

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

        switch (get_query_var('orderby')) {
            case 'popularity':
                $orderBy = " ORDER BY viewCount DESC ";
//            $orderBy = " ORDER BY createdAt DESC ";

                break;

            //@TODO add sync for ratings
            case 'rating':
//            $orderBy = " ORDER BY rating DESC ";
                $orderBy = " ORDER BY createdAt DESC ";

                break;

            case 'date':
                $orderBy = " ORDER BY o DESC, createdAt DESC ";

                break;

            case 'price-desc':
                $orderBy = " ORDER BY priceOrder DESC ";

                break;

            case 'price':
                $orderBy = " ORDER BY priceOrder ";

                break;

            default:
                $orderBy = " ORDER BY o DESC, createdAt DESC ";

                break;
        }

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
        {$orderBy}";
        if ($limit) {
            $sql .= " LIMIT {$limit} ";
        }

//    echo $sql;
        $products = $wpdb->get_results($sql, OBJECT_K);
        return array_keys($products);
    }
}