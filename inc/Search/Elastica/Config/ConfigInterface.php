<?php

namespace GF\Search\Elastica\Config;

interface ConfigInterface
{
    public function getMapping();

    public function getSetupConfig();

    public function getType();

    public function getIndex();
}