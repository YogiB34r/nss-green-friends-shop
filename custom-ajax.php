<?php
/* Template Name: custom ajax */

global $wpdb;

if (isset($_GET['viewCount'])) {
    gf_ajax_view_count((int) $_GET['postId']);
    exit();
}

if (isset($_GET['import'])) {
//    require (__DIR__ . '/../../plugins/nss-feed-import/classes/Importer.php');
    if(!function_exists('wp_get_current_user')) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $perPage = 100;
    $offset = $_POST['page'] * $perPage;
    $supplierId = 666;
    $supplierId = 308;
    $stats = gf_start_import($wpdb, $supplierId, $offset, $perPage);
    if ($stats['keyRemoveCount'] === 0 && $stats['importCount'] === 0) {
        //nothing to do
        echo 0;
    } else {
        echo 1;
    }
    exit();
}

//$sw = new \Symfony\Component\Stopwatch\Stopwatch();
//$sw->start('gfmain');

if (isset($_POST['query'])) {
    $query = addslashes($_POST['query']);

    $cache = new GF_Cache();
    $key = 'category-search#' . md5($query);
    $cat_results = unserialize($cache->redis->get($key));
    if ($cat_results === false || $cat_results === '') {
        $sql_cat = "SELECT `name`,`term_id`, `count` FROM wp_terms t JOIN wp_term_taxonomy tt USING (term_id) 
        WHERE t.name LIKE '%{$query}%' AND tt.taxonomy = 'product_cat' ORDER BY `count` DESC LIMIT 4";
        $cat_results = $wpdb->get_results($sql_cat);
        if (!empty($cat_results)) {
            $cache->redis->set($key, serialize($cat_results));
        }
    }

//    $product_results = gf_custom_search($query, 4);
    $product_results = gf_elastic_search($query, 4);
    /* @var \Elastica\ResultSet $product_results */
    $product_results = gf_elastic_search_with_data($query, 4);

    $html = '';
    if (!empty($cat_results)) {
        $html = '<span>Kategorije</span>';
        $html .= '<ul>';
        foreach ($cat_results as $category) {
            $category_link = get_term_link((int) $category->term_id);
            $html .= '<li><a href="' . $category_link . '">' . $category->name . ' ('.$category->count.')</a></li>';
        }
        $html .= '</ul>';
    }

    $html .= '<span>Proizvodi</span>';
    $html .= '<ul>';
    if ($product_results) {
//        foreach ($product_results->get_posts() as $post) {
//            $product_link = get_permalink((int) $post->ID);
//            $html .= '<li><a href="' . $product_link . '">' . $post->post_title . '</a></li>';
//        }
        foreach ($product_results->getResults() as $result) {
            $product_link = get_permalink((int) $result->getId());
            $html .= '<li><a href="' . $product_link . '">' . $result->getData()['name'] . '</a></li>';
        }
    } else {
        $html .= '<li>Nema rezultata</li>';
    }
    $html .= '</ul>';

    echo $html;
    exit();
}


include("inc/Elastica/GF_Elastica_Search.php");
include("inc/Elastica/GF_Elastica_Indexer.php");
include("inc/Elastica/GF_Elastica_Setup.php");

if (isset($_GET['action'])) {
    $config = array(
        'host' => 'localhost',
        'port' => 9200
    );
    $elasticaClient = new \Elastica\Client($config);

    switch ($_GET['action']) {
        case 'createIndex':
            GF_Elastica_Setup::createIndex($elasticaClient);

            break;

        case 'getList':
            $keywords = $_GET['query'];
            $search = new GF_Elastica_Search($elasticaClient);
            $search->search($keywords);
            $search->printDebug();

            break;


            break;
        case 'syncIndex':
            GF_Elastica_Indexer::index($elasticaClient);

            break;
    }

}


?>
<a href="/gf-ajax/?action=createIndex">(re) create index</a>
<a href="/gf-ajax/?action=syncIndex">sync index</a>
<a href="/gf-ajax/?action=getList">test</a>
