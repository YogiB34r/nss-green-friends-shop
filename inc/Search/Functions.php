<?php

namespace GF\Search;


class Functions
{
    private $useElastic;

    public function __construct($useElastic)
    {
        $this->useElastic = $useElastic;
    }

    public function getResults()
    {
        /* @TODO make it better ... */
        if (get_query_var('term') !== '') {
            if ($this->useElastic) {
                $sortedProducts = gf_get_category_items_from_elastic();
            } else {
                $sortedProducts = gf_get_category_query();
            }
        } else {
            if (!isset($_GET['query'])) {
                header('Location: ' . home_url());
            }
            if ($this->useElastic) {
                $sortedProducts = gf_elastic_search_with_data($_GET['query']);
            } else {
                $sortedProducts = gf_custom_search($_GET['query']);
            }
        }

        return $sortedProducts;
    }
}