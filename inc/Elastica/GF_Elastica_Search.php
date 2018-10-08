<?php

class GF_Elastica_Search
{
    static function search($keywords, \Elastica\Client $elasticaClient) {
        $elasticaIndex = $elasticaClient->getIndex('nss');
        $search = new Elastica\Search($elasticaClient);

        $keywords = $_GET['query'];

        $search
            ->addIndex('nss')
//            ->addIndex($elasticaIndex) // $indexUS instanceof Elastica\Index

            ->addType('products');
//            ->addType($elasticaIndex->getType('products')); // $typeTweet instanceof Elastica\Type

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
        $search->setOption('size', 80);
        $search->setOption('from', 0);
        $resultSet = $search->search();

//        foreach ($search->scanAndScroll() as $scrollId => $resultSet) {
            // ... handle Elastica\ResultSet
//        }

        $results = $resultSet->getResults();
        $totalResults = $resultSet->getTotalHits();

        var_dump('total results: ' . $totalResults);
//        var_dump('total results: ' . count($results));

        echo '<table><tr><th>name</th><th>status</th><th>cat</th><th>attr</th></tr>';
        /* @var \Elastica\Result $result */
        foreach ($results as $result) {
            echo '<tr>';
            echo '<td>' . $result->getDocument()->getData()['name'] . '</td>';
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