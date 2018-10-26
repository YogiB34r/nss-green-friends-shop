<?php

namespace GF\Search\Elastica\Config;

class Term implements ConfigInterface
{
    private $type = 'searchterm';

    private $index = 'searchterm';

    private $setupConfig = [
        'number_of_shards' => 2,
        'number_of_replicas' => 1,
    ];

    private $mapping = [
        'searchQuery' => ['type' => 'text'],
        'url' => ['type' => 'text']
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