<?php

namespace GF\Search;

class Search
{
    /**
     * @var \GF\Search\AdapterInterface
     */
    private $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function getItemIdsForCategory($slug)
    {
        return $this->adapter->getIdsForCategory($slug);
    }

    public function getItemsForCategory($categoryId, $input = null, $limit = 0, $currentPage = 1)
    {
        return $this->adapter->getItemsForCategory($categoryId, $input, $limit, $currentPage);
    }

    public function getItemIdsForSearch($input, $limit = 0)
    {
        return $this->adapter->getIdsForStandardSearch($input, $limit);
    }


    public function getItemsForSearch($input, $limit = 0, $currentPage = 1)
    {
        return $this->adapter->getItemsForStandardSearch($input, $limit, $currentPage);
    }
}