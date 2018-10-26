<?php

namespace GF\Search\Elastica\Config;

class Product implements ConfigInterface
{
    private $type = 'product';

    private $index = 'product';

    private $setupConfig = [
        'number_of_shards' => 4,
        'number_of_replicas' => 1,
        'analysis' => [
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
        ]
    ];

    private $mapping = [
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
    ];

    public function getMapping()
    {
        return $this->mapping;
    }

    public function getSetupConfig()
    {
        return $this->setupConfig;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getIndex()
    {
        return $this->index;
    }
}