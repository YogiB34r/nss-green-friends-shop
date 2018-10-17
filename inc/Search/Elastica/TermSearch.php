<?php

namespace GF\Search\Elastica;


use Elastica\Document;
use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Elastica\Query\MatchAll;
use Elastica\Query\Term;

class TermSearch
{
    const TYPE_TERM = 'searchterm';
    const INDEX_TERM = 'searchterm';

    /**
     * @var \Elastica\Client
     */
    private $client;

    private $search;

    /**
     * @var Type
     */
    private $termType;

    /**
     * @var \Elastica\ResultSet
     */
    private $resultSet;

    /**
     * @TODO add logger
     *
     * Search constructor.
     * @param \Elastica\Client $elasticaClient
     */
    public function __construct(\Elastica\Client $elasticaClient)
    {
        $this->client = $elasticaClient;
        $this->search = new \Elastica\Search($elasticaClient);
        $this->search->addIndex(self::INDEX_TERM)->addType(self::TYPE_TERM);
        $this->termType = $elasticaClient->getIndex(self::INDEX_TERM)->getType(self::TYPE_TERM);
    }

    public function getRedirectFor($query)
    {
        $boolQuery = new BoolQuery();
        $q = new Term();
        $q->setParam('searchQuery', $query);
        $boolQuery->addMust($q);
//        $q = new Term();
//        $q->setParam('url', '');
        // does not work
//        $boolQuery->addMustNot($q);
//        var_dump(json_encode($boolQuery->toArray()));
//        die();
        $response = $this->search->setQuery($boolQuery)->search();

        if ($response->getTotalHits() > 0) {
            return $response;
        }

        return false;
    }

    public function getTerms()
    {
        $boolQuery = new BoolQuery();
        $q = new MatchAll();
//        $q->setParam('searchQuery', $query);
        $boolQuery->addMust($q);
        $response = $this->search->setQuery($boolQuery)->search();

        return $response->getResults();
    }

    public function updateQuery($query, $url)
    {
        $this->termExists($query);
        $data = [
            'id' => $this->resultSet->getResults()[0]->getData()['id'],
            'searchQuery' => $query,
            'url' => $url
        ];

        $response = $this->termType->updateDocument(new Document($data['id'], $data));
        if (!$response->isOk() || $response->hasError()) {
            throw new \Exception($response->getError());
        }
    }

    public function storeQuery($query)
    {
        $data = [
            'id' => md5($query),
            'searchQuery' => $query,
            'count' => 0,
            'url' => ''
        ];

        if ($this->termExists($query)) {
            $data = $this->incrementTermCount($data);
        }

        $response = $this->termType->addDocument(new Document($data['id'], $data));
        if (!$response->isOk() || $response->hasError()) {
            throw new \Exception($response->getError());
        }
    }

    private function incrementTermCount($data)
    {
        $data['count'] = $this->resultSet->getResults()[0]->getData()['count'] + 1;
        $data['id'] = $this->resultSet->getResults()[0]->getId();

        return $data;
    }

    private function termExists($query)
    {
        $boolQuery = new BoolQuery();
        $q = new Term();
        $q->setParam('searchQuery', $query);
        $boolQuery->addMust($q);
        $response = $this->search->setQuery($boolQuery)->search();

        if ($response->getTotalHits() > 0) {
            $this->resultSet = $response;

            return true;
        }

        return false;
    }
}