<?php

class GF_Elastica_Search
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

    public function search($keywords) {
        $search = new Elastica\Search($this->client);

        $search
            ->addIndex('nss')
            ->addType('products');

        $qb = new \Elastica\QueryBuilder();
        $query = new Elastica\Query();

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
//        $q->setParam('category.name', $keywords);
//        $boolQuery->addShould($q);

//        $q = new \Elastica\Query\Term();
//        $q->setParam('attributes.value', $keywords);
//        $boolQuery->addShould($q);

        $q = new \Elastica\Query\MultiMatch();
//        $q->setFields(['name', 'description', 'attributes.value', 'category.name']);
        $q->setFields(['name', 'description', 'attributes.value', 'category.name']);
        $q->setQuery($keywords);
//        $q->setFuzziness(1);
        $q->setOperator('or');
//        $boolQuery->addMust($q);

        $q = new \Elastica\Query\Match();
        $q->setFieldQuery('name', $keywords);
        $q->setFieldFuzziness('name', 1);
//        $q->setFieldBoost('name', 10);
        $boolQuery->addMust($q);

        $q = new \Elastica\Query\Match();
        $q->setFieldQuery('category.name', $keywords);
//        $q->setFieldFuzziness('description', 1);
        $boolQuery->addShould($q);

        $q = new \Elastica\Query\Match();
        $q->setFieldQuery('attributes.value', $keywords);
        $boolQuery->addShould($q);

        $q = new \Elastica\Query\Match();
        $q->setFieldQuery('manufacturer', $keywords);
        $boolQuery->addShould($q);

        $q = new \Elastica\Query\Match();
        $q->setFieldQuery('description', $keywords);
//        $q->setFieldFuzziness('description', 1);
        $boolQuery->addShould($q);

        $search->setQuery($boolQuery);
        $search->setOption('size', 10000);
        $search->setOption('from', 0);
//        $search->setOption()

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