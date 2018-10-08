<?php

class GF_Elastica_Setup
{
    static function createIndex(\Elastica\Client $elasticaClient) {
        $elasticaIndex = $elasticaClient->getIndex('nss');

        $elasticaIndex->create(
            array(
                'number_of_shards' => 4,
                'number_of_replicas' => 2,
                'analysis' => array(
                    'analyzer' => array(
                        'default' => array(
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => array('lowercase', 'mySnowball', 'stop') //custom_ascii_folding
                        ),
                        'search' => array(
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => array('standard', 'lowercase', 'mySnowball', 'trim') //@TODO install icu_folding
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
            ),
            ['recreate']
        );

        $elasticaType = $elasticaIndex->getType('products');
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setType($elasticaType);

        // Set mapping
        $mapping->setProperties(array(
            'postId' => array('type' => 'integer'),
            'category' => array(
                'type' => 'object',
                'properties' => array(
                    'id' => array('type' => 'integer'),
                    'name' => array('type' => 'text', 'boost' => 4)
                ),
            ),
            'attributes' => array(
                'type' => 'object',
                'properties' => array(
                    'type' => array('type' => 'text'),
                    'value' => array('type' => 'text', 'boost' => 4)
                ),
            ),
            'name' => array('type' => 'text', 'boost' => 5),
            'manufacturer' => array('type' => 'text', 'boost' => 5),
            'createdAt' => array('type' => 'date'),
            'supplierId' => array('type' => 'integer'),
            'supplierSku' => array('type' => 'text'),
            'description' => array('type' => 'text'),
            'shortDescription' => array('type' => 'text'),
            'regularPrice' => array('type' => 'integer'),
            'salePrice' => array('type' => 'integer'),
            'status' => array('type' => 'integer'),
            'stockStatus' => array('type' => 'integer'),
            'sku' => array('type' => 'text', 'boost' => 20),
            'synced' => array('type' => 'integer'),
            'viewCount' => array('type' => 'integer'),
            'rating' => array('type' => 'integer'),
            'type' => array('type' => 'text'),
            'inputPrice' => array('type' => 'integer'), // check for float
        ));

        $response = $mapping->send();
        if (!$response->isOk()) {
            var_dump($response->getError());
            die();
        }
        echo 'created index';
    }
}