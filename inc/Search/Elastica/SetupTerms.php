<?php

namespace GF\Search\Elastica;

use Elastica\Client;
use Elastica\Index;
use Elastica\Type\Mapping;

class SetupTerms
{
    const TYPE = 'searchterm';
    const INDEX = 'searchterm';

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
        $this->index = $this->client->getIndex(self::INDEX);
    }

    public function createIndex($recreate = false)
    {
        if ($recreate) {
            $this->index->delete();
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
        $type = $this->index->getType(self::TYPE);
        $mapping = new Mapping();
        $mapping->setType($type);

        $mapping->setProperties([
            'entity' => [
                'properties' => [
                    'searchQuery' => ['type' => 'text']
                ]
            ]
        ]);

        return $mapping;
    }

    /**
     * @return void
     */
    private function setupAnalyzers()
    {
        $this->index->create([
            'number_of_shards' => 2,
            'number_of_replicas' => 1,
        ]);
    }
}