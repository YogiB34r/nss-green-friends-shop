<?php

namespace GF\Search;

interface AdapterInterface
{
    public function getIdsForStandardSearch($input, $limit = 0);

    public function getIdsForCategory($slug);

    public function getItemsForStandardSearch($slug);
}