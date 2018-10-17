<?php

//@TODO implement category as filter
/**
 * @param $input
 * @param int $limit
 * @return bool|WP_Query
 */
function gf_custom_search($input, $limit = 0)
{
    global $wpdb;

    $search = new \GF\Search\Search(new \GF\Search\Adapter\MySql($wpdb));
    $allIds = $search->getItemIdsForSearch($input, $limit);

    return gf_parse_post_ids_for_list($allIds);
}

function gf_elastic_search($input, $limit = 0)
{
    $config = array(
        'host' => ES_HOST,
        'port' => 9200
    );
    $elasticaSearch = new \GF\Search\Elastica\Search(new \Elastica\Client($config));
    $search = new \GF\Search\Search(new \GF\Search\Adapter\Elastic($elasticaSearch));
    $allIds = $search->getItemIdsForSearch($input, $limit);

    return gf_parse_post_ids_for_list($allIds);
}

function gf_elastic_search_with_data($input, $limit = 0)
{
    $config = array(
        'host' => ES_HOST,
        'port' => 9200
    );
    $per_page = apply_filters('loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page());
    $currentPage = (get_query_var('paged')) ? get_query_var('paged') : 1;
    if (isset($_POST['ppp'])) {
        $per_page = ($_POST['ppp'] > 48) ? 48 : $_POST['ppp'];
        $currentPage = 1;
    }
    if ($limit) {
        $per_page = $limit;
    }
    $client = new \Elastica\Client($config);
    if ($input && $input != '') {
        $term = new \GF\Search\Elastica\TermSearch($client);
        $term->storeQuery($input);
    }
    $elasticaSearch = new \GF\Search\Elastica\Search($client);
    $search = new \GF\Search\Search(new \GF\Search\Adapter\Elastic($elasticaSearch));
    $resultSet = $search->getItemsForSearch($input, $per_page, $currentPage);

    parse_search_category_aggregation($resultSet);

    wc_set_loop_prop('total', $resultSet->getTotalHits());
    wc_set_loop_prop('per_page', $per_page);
    wc_set_loop_prop('query', $input);
    wc_set_loop_prop('current_page', $currentPage);
    wc_set_loop_prop('total_pages', ceil($resultSet->getTotalHits() / $per_page));

    add_filter('woocommerce_page_title', 'applySearchPageTitle');

    return $resultSet;
}


function applySearchPageTitle($title) {
    $page_title = sprintf('Rezultati pretrage za: &ldquo;%s&rdquo;', wc_get_loop_prop('query'));

    if (wc_get_loop_prop('current_page')) {
        $page_title .= sprintf('&nbsp;&ndash; strana %s', wc_get_loop_prop('current_page'));
    }

    return $page_title;
}

function parse_search_category_aggregation(\Elastica\ResultSet $resultSet) {
    $counts = [];
    foreach ($resultSet->getAggregation('category')['buckets'] as $bucket) {
        $counts[$bucket['key']] = $bucket['doc_count'];
    }

    $args = array(
        'taxonomy'     => 'product_cat',
        'include' => array_keys($counts),
        'orderby' => 'include',
        'posts_per_page' => 50,  // ?
        'suppress_filters' => true,
        'no_found_rows' => true
    );
    $cats = [];
    $activeCategory = get_term_by('slug', get_query_var('term'), 'product_cat');
    foreach (get_categories($args) as $cat) {
        if (get_query_var('term') != '') {
            if ($cat->parent > 0 &&
                $activeCategory->term_id != $cat->term_id && //ignore current
//                get_queried_object_id()
//                $activeCategory->parent != $cat->term_id && //ignore current parent
                $activeCategory->term_id === $cat->parent // only children
            ) {
                $cats[] = [
                    'name' => $cat->name,
                    'id' => $cat->term_id,
                    'url' => get_term_link($cat->term_id, 'product_cat'),
                    'count' => $counts[$cat->term_id]
                ];
            }
        } else {
            if ($cat->parent === 0) {
                $cats[] = [
                    'name' => $cat->name,
                    'id' => $cat->term_id,
                    'url' => get_term_link($cat->term_id, 'product_cat'),
                    'count' => $counts[$cat->term_id]
                ];
            }
        }
    }

    $GLOBALS['gf-search']['facets']['category'] = $cats;
}

function get_search_category_aggregation() {
    return $GLOBALS['gf-search']['facets']['category'];
}

function gf_get_category_items_from_elastic()
{
    $config = array(
        'host' => ES_HOST,
        'port' => 9200
    );
    $per_page = apply_filters('loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page());
    $currentPage = (get_query_var('paged')) ? get_query_var('paged') : 1;
    // @TODO calculate proper page when per page param is changed
    if (isset($_POST['ppp'])) {
        $per_page = ($_POST['ppp'] > 48) ? 48 : $_POST['ppp'];
        $currentPage = 1;
    }

    $query = isset($_GET['query']) ? $_GET['query'] : null;
    $client = new \Elastica\Client($config);
    if ($query && $query != '') {
        $term = new \GF\Search\Elastica\TermSearch($client);
        $term->storeQuery($query);
    }
    $elasticaSearch = new \GF\Search\Elastica\Search($client);
    $search = new \GF\Search\Search(new \GF\Search\Adapter\Elastic($elasticaSearch));
    $cat = get_term_by('slug', get_query_var('term'), 'product_cat');
    $resultSet = $search->getItemsForCategory($cat->term_id, $query, $per_page, $currentPage);

    parse_search_category_aggregation($resultSet);

    wc_set_loop_prop('total', $resultSet->getTotalHits());
    wc_set_loop_prop('per_page', $per_page);
    wc_set_loop_prop('current_page', $currentPage);
    wc_set_loop_prop('total_pages', ceil($resultSet->getTotalHits() / $per_page));

    return $resultSet;
}

function gf_get_category_query()
{
    global $wpdb;

    $search = new \GF\Search\Search(new \GF\Search\Adapter\MySql($wpdb));
    $allIds = $search->getItemIdsForCategory(get_query_var('term'));

    return gf_parse_post_ids_for_list($allIds);
}

add_action('admin_menu', function (){
    add_menu_page('Pretraga', 'Podesavanje pretrage', 'edit_pages',
    'gf-search-settings', 'handleAdminSearchSettings');
});

function handleAdminSearchSettings() {
//    $config = array(
//        'host' => ES_HOST,
//        'port' => 9200
//    );
//    $client = new \Elastica\Client($config);
//    $term = new \GF\Search\Elastica\TermSearch($client);
//    $result = $term->getTerms();
    include(__DIR__ . "/templates/admin/search-settings.php");

}