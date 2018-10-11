<?php

namespace GF\Search\Adapter;

use GF\Search\Elastica\Search;

class Elastic implements \GF\Search\AdapterInterface
{
    /**
     * @var Search
     */
    private $search;

    public function __construct(Search $search)
    {
        $this->search = $search;
    }

    public function getIdsForStandardSearch($input, $limit = 0, $currentPage = 1)
    {
        $order = (isset($_GET['orderby'])) ? $_GET['orderby'] : 'date';
        $this->search->search($input, $limit, $currentPage, $order);

        return array_keys($this->search->getIds());
    }

    /**
     * @param $input
     * @param int $limit
     * @param int $currentPage
     * @return \Elastica\ResultSet
     */
    public function getItemsForStandardSearch($input, $limit = 0, $currentPage = 1)
    {
        $order = (isset($_GET['orderby'])) ? $_GET['orderby'] : 'date';
        $this->search->search($input, $limit, $currentPage, $order);

        return $this->search->getResultSet();
    }

    /**
     * @param string $category
     * @param string $input
     * @param int $limit
     * @param int $currentPage
     * @return \Elastica\ResultSet
     */
    public function getItemsForCategory($category, $input = null, $limit = 0, $currentPage = 1)
    {
        $order = (isset($_GET['orderby'])) ? $_GET['orderby'] : 'date';
        $this->search->category($category, $input, $limit, $currentPage, $order);

        $resultSet = $this->search->getResultSet();
        if ($resultSet->getTotalHits() > $limit) {
            wc_get_loop_prop('is_paginated', true);
        }

        return $resultSet;
    }

    public function getIdsForCategory($slug)
    {
        return $this->search->getItemIdsForCategory($slug);

//        return array_merge($allIds, array_keys($productsOutOfStock));
    }
}