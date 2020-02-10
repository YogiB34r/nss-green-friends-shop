<?php
namespace GF\Search\Indexer;

use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Term;

class Indexer
{
    /**
     * @var \Elastica\Client
     */
    private $client;

    private $search;

    private $totalCount;

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
        $this->search->addIndex('product')->addType('product');
    }

    public function getTotalCount()
    {
        return $this->totalCount;
    }

    public function getResults()
    {
        $perPage = 500;
        $page = (isset($_GET['paged']) && $_GET['paged'] > 0) ? $_GET['paged'] : 1;
        $categoryId = (isset($_GET['categoryId']) && $_GET['categoryId'] > 0) ? $_GET['categoryId'] : null;

        $boolQuery = new BoolQuery();
        $q = new Term();
        $q->setParam('status', 1);
        $boolQuery->addMust($q);


        $mainQuery = new Query();
//        $mainQuery->setQuery($boolQuery);
        $mainQuery = $this->setSorting($mainQuery, $boolQuery, 'default');


//        $categoryAggregation = new \Elastica\Aggregation\Terms('category');
//        $categoryAggregation->setField('category.id');
//        $categoryAggregation->setSize(50);
//        $mainQuery->addAggregation($categoryAggregation);

        $this->search->setQuery($mainQuery);
        if ($categoryId) {
            $q = new Term();
            $q->setParam('category.id', $categoryId);
            $boolQuery->addMust($q);
            $this->totalCount = $this->search->count();
        } else {
            $this->totalCount = $this->search->getClient()->getIndex('product')->getType('product')->count();
        }

        $this->search->setOption('size', $perPage);
        $this->search->setOption('from', 0);
        if ($page > 1) {
            $this->search->setOption('from', ($page - 1) * $perPage);
        }
//        var_dump(json_encode($this->search->getQuery()->toArray()));
        $resultSet = $this->search->search();

        return $resultSet;
    }

    private function setSorting(Query $mainQuery, BoolQuery $boolQuery, $order)
    {
        switch ($order) {
            case 'popularity':
                $mainQuery->addSort(['order_data.viewCount' => 'desc']);

                break;

            //@TODO add sync for ratings
            case 'rating':
                $mainQuery->addSort(['order_data.rating' => 'desc']);

                break;

            case 'date':
                $scriptCode = 'doc["order_data.default"].value';
                $ordering = 'desc';

                break;

            case 'price-desc':
                $scriptCode = 'doc["order_data.price"].value';
                $ordering = 'desc';

                break;

            case 'price':
                $scriptCode = 'doc["order_data.price"].value';
                $ordering = 'asc';

                break;

            default:
                $scriptCode = 'doc["order_data.default"].value';
                $ordering = 'desc';

                break;
        }

        $functionQuery = new \Elastica\Query\FunctionScore();
        $scoreFunction = new \Elastica\Script\Script($scriptCode);
        $functionQuery->addScriptScoreFunction($scoreFunction);
        $functionQuery->setQuery($boolQuery);
        $mainQuery->setSort(['_score' => $ordering]);
        $mainQuery->setQuery($functionQuery);
        $mainQuery->setTrackScores();

        return $mainQuery;
    }

}