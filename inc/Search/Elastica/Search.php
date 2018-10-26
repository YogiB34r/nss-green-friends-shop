<?php

namespace GF\Search\Elastica;

use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Term;

class Search
{
    /**
     * @var \Elastica\Client
     */
    private $client;

    private $search;

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
        $this->search->addIndex('product')->addType('product');
    }

    public function category($categoryId, $keywords = null, $limit = 0, $currentPage = 1, $order = '')
    {
        $boolQuery = new BoolQuery();
        $q = new Term();
        $q->setParam('status', 1);
        $boolQuery->addMust($q);
        $q = new Term();
        $q->setParam('category.id', $categoryId);
        $boolQuery->addMust($q);

        if ($keywords) {
            $q = new \Elastica\Query\Match();
            $q->setFieldQuery('search_data.full_text', $keywords);
            $q->setFieldOperator('search_data.full_text', 'and');
            $q->setFieldFuzziness('search_data.full_text', 1);
            $q->setFieldBoost('search_data.full_text', 15);
            $boolQuery->addMust($q);

            $q = new \Elastica\Query\Match();
            $q->setFieldQuery('search_data.full_text_boosted', $keywords);
            $q->setFieldFuzziness('search_data.full_text_boosted', 1);
            $q->setFieldBoost('search_data.full_text_boosted', 20);
            $boolQuery->addMust($q);
        }

        // get price range for this query, and set global values
        $this->setPriceRange($boolQuery);
        // set filters, price, etc
        $boolQuery = $this->setFilters($boolQuery);

        $this->performSearch($boolQuery, $limit, $currentPage, $order);
    }

    public function search($keywords, $limit = 0, $currentPage = 1, $order = '')
    {
//        $qb = new \Elastica\QueryBuilder();
//        $query->setQuery(
//            $qb->query()->bool()->addMust(
//                $qb->query()->term(['name' => $keywords])
//            )->addMust(
//                $qb->query()->term(['status' => 1])
//            )
//        );
        $boolQuery = new BoolQuery();

        $q = new Term();
        $q->setParam('status', 1);
        $boolQuery->addMust($q);

//        $q = new \Elastica\Query\Term();
//        $q->setParam('stockStatus', 1);
//        $boolQuery->addMust($q);

        $q = new \Elastica\Query\Match();
        $q->setFieldQuery('search_data.full_text', $keywords);
        $q->setFieldOperator('search_data.full_text', 'and');
        $q->setFieldFuzziness('search_data.full_text', 1);
        $q->setFieldBoost('search_data.full_text', 15);
        $boolQuery->addMust($q);

        $q = new \Elastica\Query\Match();
        $q->setFieldQuery('search_data.full_text_boosted', $keywords);
        $q->setFieldFuzziness('search_data.full_text_boosted', 1);
//        $q->setFieldOperator('search_data.full_text_boosted', 'and');
        $q->setFieldBoost('search_data.full_text_boosted', 20);
        $boolQuery->addMust($q);

        $q = new \Elastica\Query\Match();
        $q->setFieldQuery('entity.category.name', $keywords);
        $q->setFieldFuzziness('entity.category.name', 1);
        $q->setFieldBoost('entity.category.name', 20);
        $boolQuery->addShould($q);

        $q = new \Elastica\Query\Match();
        $q->setFieldQuery('entity.attribute.value', $keywords);
//        $q->setFieldFuzziness('entity.attribute.value', 1);
        $q->setFieldBoost('entity.attribute.value', 20);
        $boolQuery->addShould($q);

        // exclude sex shop categories
        foreach (gf_get_category_children_ids('sexy-shop') as $catId) {
            $q = new Term();
            $q->setTerm('category.id', $catId);
            $boolQuery->addMustNot($q);
        }

        // set filters, price, etc
        $boolQuery = $this->setFilters($boolQuery);
        // get price range for this query, and set global values
        $this->setPriceRange($boolQuery);

        $this->performSearch($boolQuery, $limit, $currentPage, $order);
    }

    /**
     * @param BoolQuery $boolQuery
     * @return BoolQuery
     */
    private function setFilters(BoolQuery $boolQuery)
    {
        if (isset($_GET{'min_price'})) {
            $q = new \Elastica\Query\Range();
            $q->addField('order_data.price', [
                'gte' => (int) $_GET{'min_price'},
                'lte' => (int) $_GET{'max_price'}
            ]);
            $boolQuery->addMust($q);
        }
        $q = new Query\Range();
        $q->addField('order_data.price', [
            'gt' => 0
        ]);

        return $boolQuery;
    }

    /**
     * @param BoolQuery $boolQuery
     */
    private function setPriceRange(BoolQuery $boolQuery)
    {
        $mainQuery = new Query();
        $mainQuery->setQuery($boolQuery);
        $maxPriceAggregation = new \Elastica\Aggregation\Max('max_price');
        $maxPriceAggregation->setField('order_data.price');
        $minPriceAggregation = new \Elastica\Aggregation\Min('min_price');
        $minPriceAggregation->setField('order_data.price');
        $mainQuery->addAggregation($maxPriceAggregation);
        $mainQuery->addAggregation($minPriceAggregation);
        $search = $this->search->setQuery($mainQuery)->search();
        $GLOBALS['gf_price_filter'] = [
            'max_price' => (int) $search->getAggregation('max_price')['value'],
            'min_price' => (int) $search->getAggregation('min_price')['value']
        ];
    }

    /**
     * @param \Elastica\Query $mainQuery
     * @param $boolQuery
     * @param $order
     * @return \Elastica\Query
     */
    private function setSorting(Query $mainQuery, $boolQuery, $order)
    {
        switch ($order) {
            case 'popularity':
                $mainQuery->setSort(['order_data.viewCount' => 'desc']);

                break;

            //@TODO add sync for ratings
            case 'rating':
                $mainQuery->setSort(['order_data.rating' => 'desc']);

                break;

            case 'date':
                $functionQuery = new \Elastica\Query\FunctionScore();
                $scoreFunction = new \Elastica\Script\Script('_score * doc["order_data.default"].value');
                $functionQuery->addScriptScoreFunction($scoreFunction);
                $functionQuery->setQuery($boolQuery);
                $mainQuery->setQuery($functionQuery);
                $mainQuery->setSort(['_score' => 'desc']);
//        $functionQuery->setScoreMode('sum');
//        $functionQuery->setBoostMode('replace');

                break;

            case 'price-desc':
                $mainQuery->setSort(['order_data.price' => 'desc']);

                break;

            case 'price':
                $mainQuery->setSort(['order_data.price' => 'asc']);

                break;

            default:
                $mainQuery->setSort(['order_data.default' => 'desc']);

                break;
        }

        return $mainQuery;
    }

    /**
     * @return array
     */
    public function getIds()
    {
        $ids = [];
        foreach ($this->resultSet->getResults() as $result) {
            $ids[] = $result->getDocument()->getId();
        }

        return $ids;
    }

    /**
     * @param BoolQuery $boolQuery
     * @param $limit
     * @param $page
     * @param $order
     */
    private function performSearch(BoolQuery $boolQuery, $limit, $page, $order)
    {
        $mainQuery = new Query();
        $mainQuery->setQuery($boolQuery);
        $mainQuery = $this->setSorting($mainQuery, $boolQuery, $order);

        $categoryAggregation = new \Elastica\Aggregation\Terms('category');
        $categoryAggregation->setField('category.id');
        $categoryAggregation->setSize(50);
        $mainQuery->addAggregation($categoryAggregation);

        $this->search->setQuery($mainQuery);
        $this->search->setOption('size', 10000);
        if ($limit) {
            $this->search->setOption('size', $limit);
        }
        $this->search->setOption('from', 0);
        if ($page > 1) {
            $this->search->setOption('from', ($page - 1) * $limit);
        }
//        var_dump(json_encode($this->search->getQuery()->toArray()));
        $this->resultSet = $this->search->search();
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

        echo '<table><tr><th></th><th>score</th><th width="250px">name</th><th width="150px">desc</th><th>cat</th><th width="250px">attr</th>
            <th>price</th><th>regular</th><th>sale price</th></tr>';
        /* @var \Elastica\Result $result */
        $i=0;
        foreach ($this->resultSet->getResults() as $result) {
//            var_dump($result); die();
            $i++;
            echo '<tr>';
            echo '<td>' . $i . '</td>';
            echo '<td>' . $result->getScore() . '</td>';
            echo '<td>' . $result->getDocument()->getData()['name'] . '</td>';
            echo '<td><textarea>' . $result->getDocument()->getData()['description'] . '</textarea></td>';
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
            echo '<td>'.$result->getDocument()->getData()['order_data']['price'].'</td>';
            echo '<td>'.$result->getDocument()->getData()['regularPrice'].'</td>';
            echo '<td>'.$result->getDocument()->getData()['salePrice'].'</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
}