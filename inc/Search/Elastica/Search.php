<?php

namespace GF\Search\Elastica;

class Search
{
    /**
     * @var \Elastica\Client
     */
    private $client;

    /**
     * @var \Elastica\ResultSet
     */
    private $resultSet;

    public function __construct(\Elastica\Client $elasticaClient)
    {
        $this->client = $elasticaClient;
    }

    public function search($keywords, $limit = 0, $currentPage = 1, $order = '') {
        $search = new \Elastica\Search($this->client);

        $search
            ->addIndex('nss')
            ->addType('products');

//        $qb = new \Elastica\QueryBuilder();


//        $query->setQuery(
//            $qb->query()->bool()->addMust(
//                $qb->query()->term(['name' => $keywords])
//            )->addMust(
//                $qb->query()->term(['status' => 1])
//            )
//        );



        $boolQuery = new \Elastica\Query\BoolQuery();

        $q = new \Elastica\Query\Term();
        $q->setParam('status', 1);
        $boolQuery->addMust($q);

//        $q = new \Elastica\Query\Term();
//        $q->setParam('stockStatus', 1);
//        $boolQuery->addMust($q);

//        $q = new \Elastica\Query\Term();
//        $q->setParam('category.name', $keywords);
//        $boolQuery->addShould($q);

//        $q = new \Elastica\Query\Term();
//        $q->setParam('attributes.value', $keywords);
//        $boolQuery->addShould($q);

//        $q = new \Elastica\Query\MultiMatch();
//        $q->setFields(['name', 'description', 'attributes.value', 'category.name']);
//        $q->setFields(['name', 'description', 'attributes.value', 'category.name']);
//        $q->setQuery($keywords);
//        $q->setFuzziness(1);
//        $q->setOperator('or');
//        $boolQuery->addMust($q);

        $q = new \Elastica\Query\Match();
        $q->setFieldQuery('search_data.full_text', $keywords);
        $q->setFieldOperator('search_data.full_text', 'and');
        $q->setFieldFuzziness('search_data.full_text', 1);
        $q->setFieldBoost('search_data.full_text', 10);
        $boolQuery->addMust($q);

        $q = new \Elastica\Query\Match();
        $q->setFieldQuery('search_data.full_text_boosted', $keywords);
//        $q->setFieldFuzziness('search_data.full_text_boosted', 1);
//        $q->setFieldOperator('search_data.full_text_boosted', 'and');
        $q->setFieldBoost('search_data.full_text_boosted', 16);
        $boolQuery->addMust($q);

        $q = new \Elastica\Query\Match();
        $q->setFieldQuery('entity.category.name', $keywords);
//        $q->setFieldFuzziness('entity.category.name', 1);
        $q->setFieldBoost('entity.category.name', 15);
        $boolQuery->addShould($q);

        $q = new \Elastica\Query\Match();
        $q->setFieldQuery('entity.attribute.value', $keywords);
//        $q->setFieldFuzziness('entity.attribute.value', 1);
        $q->setFieldBoost('entity.attribute.value', 15);
        $boolQuery->addShould($q);

        switch ($order) {
            case 'popularity':
                $orderBy = [
                    'order_data.viewCount' => 'desc'
                ];

                break;

            //@TODO add sync for ratings
            case 'rating':
                $orderBy = [
                    'order_data.rating' => 'desc'
                ];

                break;

            case 'date':
                $orderBy = [
//                    'order_data.default' => 'desc'
                    '_score'
                ];

                break;

            case 'price-desc':
                $orderBy = [
                    'order_data.price' => 'desc'
                ];

                break;

            case 'price':
                $orderBy = [
                    'order_data.price' => 'asc'
                ];

                break;

            default:
                $orderBy = [
                    'order_data.default' => 'desc'
                ];

                break;
        }

        $query = new \Elastica\Query();
        $functionQuery = new \Elastica\Query\FunctionScore();
//        $scoreFunction = new \Elastica\Script\Script('_score * boostFactor', ['boostFactor' => 4]);
        $scoreFunction = new \Elastica\Script\Script('_score * doc["order_data.default"].value');
        $functionQuery->addScriptScoreFunction($scoreFunction);
//        $functionQuery->setScoreMode('sum');
//        $functionQuery->setBoostMode('replace');
        $functionQuery->setQuery($boolQuery);

        $query->setQuery($functionQuery);


//        $query->setSort($orderBy);
//        $query->addSort(['order_data.default' => 'desc']);
//        $query->addSort(['_score' => 'asc']);

        $search->setQuery($query);
        $search->setOption('size', 10000);
//        $search->addOption('sort', [
//            'name' => 'desc'
//        ]);
        if ($limit) {
            $search->setOption('size', $limit);
        }
        $search->setOption('from', 0);
        if ($currentPage > 1) {
            $search->setOption('from', ($currentPage - 1) * $limit);
        }
        $this->resultSet = $search->search();
    }

    public function getIds()
    {
        $ids = [];
        foreach ($this->resultSet->getResults() as $result) {
            $ids[] = $result->getDocument()->getId();
        }

        return $ids;
    }

    /**
    * @return \Elastica\ResultSet
    */
    public function getResultSet()
    {
        return $this->resultSet;
    }

    public function printDebug()
    {
        $totalResults = $this->resultSet->getTotalHits();
        var_dump('total results: ' . $totalResults);
        var_dump('paged results: ' . count($this->resultSet->getResults()));

        echo '<table><tr><th>score</th><th>name</th><th width="150px">status</th><th>desc</th><th>cat</th><th>attr</th></tr>';
        /* @var \Elastica\Result $result */
        foreach ($this->resultSet->getResults() as $result) {
            echo '<tr>';
            echo '<td>' . $result->getScore() . '</td>';
            echo '<td>' . $result->getDocument()->getData()['name'] . '</td>';
            echo '<td>' . $result->getDocument()->getData()['description'] . '</td>';
            echo '<td>' . $result->getDocument()->getData()['status'] . '</td>';
            echo '<td>';
            foreach ($result->getDocument()->getData()['category'] as $cat) {
                echo $cat['name'] . ', ';
            }
            echo '</td>';
            echo '<td>';
            foreach ($result->getDocument()->getData()['attributes'] as $cat) {
                echo $cat['type'] .' - '. $cat['value'];
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}