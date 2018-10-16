<?php

namespace GF\Search\Elastica;

use Elastica\Client;
use Elastica\Index;
use Elastica\Type\Mapping;

class SetupProducts
{
    const TYPE = 'product';
    const INDEX = 'product';

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
        $productsType = $this->index->getType(self::TYPE);
        $mapping = new Mapping();
        $mapping->setType($productsType);

        $mapping->setProperties(array(
            'entity' => [
                'properties' => [
                    'postId' => array('type' => 'integer'),
                    'category' => array(
                        'type' => 'nested',
                        'properties' => array(
                            'id' => array('type' => 'integer'),
                            'parent' => array('type' => 'integer'),
                            'level' => array('type' => 'integer'),
                            'slug' => array('type' => 'text'),
                            'name' => [
                                'type' => 'text',
                                'boost' => 4,
                                'fields' => [
                                    'keyword' => [
                                        'type' => 'keyword'
                                    ]
                                ]
                            ]
                        ),
                    ),
                    'attributes' => array(
                        'type' => 'nested',
                        'properties' => array(
                            'type' => array('type' => 'text'),
                            'value' => array('type' => 'text', 'boost' => 4)
                        ),
                    ),
                    'name' => array('type' => 'text', 'boost' => 5, 'fielddata' => true),
                    'manufacturer' => array('type' => 'text', 'boost' => 5),
                    'createdAt' => array('type' => 'date'),
                    'supplierId' => array('type' => 'integer'),
                    'supplierSku' => array('type' => 'text'),
                    'thumbnail' => array('type' => 'text'),
                    'permalink' => array('type' => 'text'),
                    'description' => array('type' => 'text'),
                    'shortDescription' => array('type' => 'text'),
                    'regularPrice' => array('type' => 'integer'),
                    'salePrice' => array('type' => 'text'),
                    'status' => array('type' => 'integer'),
                    'stockStatus' => array('type' => 'integer'),
                    'sku' => array('type' => 'text', 'boost' => 20),
                    'synced' => array('type' => 'integer'),
                    'viewCount' => array('type' => 'integer'),
                    'rating' => array('type' => 'integer'),
                    'product_type' => array('type' => 'text'),
                    'inputPrice' => array('type' => 'long'),
                ]
            ],
            'order_data' => [
                'properties' => [
                    'price' => array('type' => 'integer'),
                    'rating' => array('type' => 'integer'),
                    'date' => array('type' => 'integer'),
                    'viewCount' => array('type' => 'integer'),
                    'stock' => array('type' => 'integer'),
                    'published' => array('type' => 'integer'),
                    'default' => array('type' => 'integer'),
                ]
            ],
            'search_data' => [
                'properties' => [
                    'full_text' => array('type' => 'text'),
                    'full_text_boosted' => array('type' => 'text'),
                ]
            ],
            "completion_terms" => [
                "type" => "text",
                "analyzer" => "search"
            ],
//            "suggestion_terms" => [
//                "type" => "text",
//                "index_analyzer" => "search",
//                "search_analyzer" => "search"
//            ]
        ));

        return $mapping;
    }

    /**
     * @return void
     */
    private function setupAnalyzers()
    {
        $this->index->create(
            array(
                'number_of_shards' => 4,
                'number_of_replicas' => 1,
                'analysis' => array(
                    'analyzer' => array(
                        'default' => array(
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => array('lowercase', 'stop', 'trim', 'custom_ascii_folding') //custom_ascii_folding
                        ),
                        'search' => array(
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => array('standard', 'lowercase', 'trim', 'custom_ascii_folding') //@TODO install icu_folding
                        )
                    ),
                    'filter' => array(
                        'mySnowball' => array(
                            'type' => 'snowball',
                            'language' => 'German'
                        ),
                        'custom_ascii_folding' => array(
                            'type' => 'asciifolding',
                            'preserve_original' => true
                        ),
                        'test' => array(
                            'type' => 'stemmer',
                            'language' => 'Russian'
                        ),
                    )
                )
            )
        );
    }
}