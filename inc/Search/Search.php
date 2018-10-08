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

    public function getItemIdsForSearch($input, $limit = 0)
    {
        return $this->adapter->getIdsForStandardSearch($input, $limit);
    }
}