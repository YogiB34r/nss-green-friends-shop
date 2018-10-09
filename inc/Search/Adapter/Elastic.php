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

    public function getIdsForStandardSearch($input, $limit = 0, $currentPage = 1)
    {
        $this->search->search($input, $limit, $currentPage, $_GET['orderby']);

        return array_keys($this->search->getIds());
    }

    /**
     * @param $input
     * @param int $limit
     * @return \Elastica\ResultSet
     */
    public function getItemsForStandardSearch($input, $limit = 0, $currentPage = 1)
    {
        $this->search->search($input, $limit, $currentPage, $_GET['orderby']);

        return $this->search->getResultSet();

//        return array_keys($products);
    }

    public function getIdsForCategory($slug)
    {
        return $this->search->getItemIdsForCategory($slug);

//        return array_merge($allIds, array_keys($productsOutOfStock));
    }
}