<?php

namespace GF\Search\Elastica;

use Elastica\Client;
use Elastica\Index;
use Elastica\Type\Mapping;
use GF\Search\Elastica\Config\Product as Config;

class SetupProducts
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Index
     */
    private $index;

    /**
     * Setup constructor.
     * @param Client $elasticaClient
     */
    public function __construct(Client $elasticaClient)
    {
        $this->client = $elasticaClient;
        $this->index = $this->client->getIndex(Config::INDEX);
    }

    public function createIndex($recreate = true)
    {
        if ($recreate) {
            $response = $this->index->delete();
            var_dump($response->getData());
        }
        $this->setupAnalyzers();
        $mapping = $this->setupMapping();
        $response = $mapping->send();
        if (!$response->isOk()) {
            throw new \Exception($response->getError());
        }
        $msg = $mapping->getType()->getName() . ' type mapping created.' . PHP_EOL;
        if ($recreate) {
            $msg = 'index recreated.';
        }

        echo $msg;
    }

    private function setupMapping()
    {
        $productsType = $this->index->getType(Config::TYPE);
        $mapping = new Mapping();
        $mapping->setType($productsType);
        $mapping->setProperties(Config::$mapping);

        return $mapping;
    }

    /**
     * @return void
     */
    private function setupAnalyzers()
    {
        $this->index->create(Config::$setupConfig);
    }
}