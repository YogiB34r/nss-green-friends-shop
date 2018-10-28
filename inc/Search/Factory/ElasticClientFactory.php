<?php

namespace Gf\Search\Factory;

/**
 * Class ElasticClientFactory
 * @package Gf\Search\Factory
 */
class ElasticClientFactory
{
    /**
     * ElasticClientFactory constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return \Elastica\Client
     */
    public function make()
    {
        $config = array(
            'host' => ES_HOST,
            'port' => 9200
        );
        return new \Elastica\Client($config);
    }
}