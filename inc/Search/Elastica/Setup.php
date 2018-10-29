<?php

namespace GF\Search\Elastica;

use Elastica\Client;
use Elastica\Type\Mapping;
use GF\Search\Elastica\Config\ConfigInterface as Config;

class Setup
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Config
     */
    private $config;

    /**
     * Setup constructor.
     * @param Client $elasticaClient
     * @param Config $config
     */
    public function __construct(Client $elasticaClient, Config $config)
    {
        $this->client = $elasticaClient;
        $this->config = $config;
    }

    /**
     * @param bool $recreate
     */
    public function createIndex($recreate = false)
    {
        if ($recreate) {
            try {
                $this->client->getIndex($this->config->getIndex())->delete();
            } catch (\Exception $e) {
                if (!strstr($e->getMessage(), 'no such index')) {
                    var_dump($e->getMessage());
                }
            }
        }
        $this->setupAnalyzers();
        $mapping = $this->setupMapping();
        $response = $mapping->send();
        if (!$response->isOk()) {
            var_dump($response->getError());
            exit();
        }
        $msg = $mapping->getType()->getName() . ' index created.' . PHP_EOL;
        if ($recreate) {
            $msg = $mapping->getType()->getName() . ' index recreated.';
        }

        echo $msg;
    }

    /**
     * @return Mapping
     */
    private function setupMapping()
    {
        $type = $this->client->getIndex($this->config->getIndex())->getType($this->config->getType());
        $mapping = new Mapping();
        $mapping->setType($type);
        $mapping->setProperties($this->config->getMapping());

        return $mapping;
    }

    /**
     * @return void
     */
    private function setupAnalyzers()
    {
        $this->client->getIndex($this->config->getIndex())->create($this->config->getSetupConfig());
    }
}