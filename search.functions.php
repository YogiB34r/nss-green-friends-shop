<?php


//for loged in users
add_action('wp_ajax_ajax_gf_autocomplete', 'gf_ajax_search_autocomplete');
//for logged out users
add_action('wp_ajax_nopriv_ajax_gf_autocomplete', 'gf_ajax_search_autocomplete');
function gf_ajax_search_autocomplete()
{
    if (isset($_POST['keyword'])) {
        global $wpdb;

        $query = addslashes($_POST['keyword']);

        $cache = new GF_Cache();
        $key = 'category-search-' . md5($query);
        $cat_results = unserialize($cache->redis->get($key));
        if ($cat_results === false || $cat_results === '') {
            $sql_cat = "SELECT `name`,`term_id` FROM wp_terms t JOIN wp_term_taxonomy tt USING (term_id) WHERE t.name LIKE '{$query}%' AND tt.taxonomy = 'product_cat' LIMIT 4";
            $cat_results = $wpdb->get_results($sql_cat);
            if (!empty($cat_results)) {
                $cache->redis->set($key, serialize($cat_results));
            }
        }

//        $sql_product = "SELECT `productName`, `postId` FROM wp_gf_products WHERE `productName` LIKE '%{$keyword}%' LIMIT 4";
//        $product_results = $wpdb->get_results($sql_product);
        $product_results = gf_custom_search($query, 4);

        $html = '';
        if (!empty($cat_results)) {
            $html = '<span>Kategorije</span>';
            $html .= '<ul>';
            foreach ($cat_results as $category) {
                $category_link = get_term_link((int)$category->term_id);
                $html .= '<li><a href="' . $category_link . '">' . $category->name . '</a></li>';
            }
            $html .= '</ul>';
        }

        $html .= '<span>Proizvodi</span>';
        $html .= '<ul>';
        if ($product_results) {
            foreach ($product_results->get_posts() as $post) {
                $product_link = get_permalink((int)$post->ID);
                $html .= '<li><a href="' . $product_link . '">' . $post->post_title . '</a></li>';
            }
        } else {
            $html .= '<li>Nema rezultata</li>';
        }
        $html .= '</ul>';

        echo $html;
    }
}

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
    $term = new \GF\Search\Elastica\TermSearch($client);
    $term->storeQuery($input);
    $elasticaSearch = new \GF\Search\Elastica\Search($client);
    $search = new \GF\Search\Search(new \GF\Search\Adapter\Elastic($elasticaSearch));
    $resultSet = $search->getItemsForSearch($input, $per_page, $currentPage);

    parse_search_category_aggregation($resultSet);

    wc_set_loop_prop('total', $resultSet->getTotalHits());
    wc_set_loop_prop('per_page', $per_page);
    wc_set_loop_prop('current_page', $currentPage);
    wc_set_loop_prop('total_pages', ceil($resultSet->getTotalHits() / $per_page));

    return $resultSet;
}

function parse_search_category_aggregation(\Elastica\ResultSet $resultSet)
{
    $counts = [];
    foreach ($resultSet->getAggregation('category')['buckets'] as $bucket) {
        $counts[$bucket['key']] = $bucket['doc_count'];
    }
    $args = array(
        'taxonomy'     => 'product_cat',
        'include' => array_keys($counts),
        'posts_per_page' => 50,  // ?
        'suppress_filters' => true,
        'no_found_rows' => true
    );
    $cats = [];
    foreach (get_categories($args) as $cat) {
        $cats[] = [
            'name' => $cat->name,
            'id' => $cat->term_id,
            'url' => $cat->slug,
            'count' => $counts[$cat->term_id]
        ];

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
    $elasticaSearch = new \GF\Search\Elastica\Search($client);
    $search = new \GF\Search\Search(new \GF\Search\Adapter\Elastic($elasticaSearch));
    $cat = get_term_by('slug', get_query_var('term'), 'product_cat');
    $resultSet = $search->getItemsForCategory($cat->term_id, $query, $per_page, $currentPage);

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