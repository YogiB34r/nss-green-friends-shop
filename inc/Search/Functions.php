<?php

namespace GF\Search;

class Functions
{
    /**
     * @var bool
     */
    private $useElastic;

    /**
     * @var \wpdb
     */
    private $wpdb;

    public function __construct($wpdb, $useElastic)
    {
        $this->wpdb = $wpdb;
        $this->useElastic = $useElastic;
    }

    public function ajaxViewCount($postId)
    {
        $key = 'post-view-count#' . $postId;
        $cache = new \GF_Cache();
        $count = (int) $cache->redis->get($key);
        if ($count == 10) {
            $this->wpdb->query("UPDATE wp_gf_products SET viewCount = viewCount + {$count} WHERE postId = {$postId}");
            $cache->redis->set($key, 0);
        } else {
            $count++;
            $cache->redis->set($key, $count);
        }
        echo 1;
    }

    public function getResults($queryVar, $query)
    {
        if ($queryVar !== '') {
            if ($this->useElastic) {
                $sortedProducts = $this->getCategoryItemsFromElastic();
            } else {
                $search = new \GF\Search\Search(new \GF\Search\Adapter\MySql($this->wpdb));
                $allIds = $search->getItemIdsForCategory(get_query_var('term'));

                $sortedProducts = $this->parsePostIdsForList($allIds);
            }
        } else {
            // @TODO
            if (!isset($query)) {
                header('Location: ' . home_url());
            }
            if ($this->useElastic) {
                $sortedProducts = $this->elasticSearch($query);
            } else {
                $sortedProducts = $this->mysqlSearch($query);
            }
        }

        return $sortedProducts;
    }

    /**
     * Parses array of post ids and fetches them via wp query to prepare for loop.
     *
     * @param $allIds
     * @return bool|\WP_Query
     */
    public function parsePostIdsForList($allIds)
    {
        $per_page = apply_filters('loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page());
        if (isset($_POST['ppp'])) {
            $per_page = ($_POST['ppp'] > 48) ? 48 : $_POST['ppp'];
        }
        $currentPage = (get_query_var('paged')) ? get_query_var('paged') : 1;

        $resultCount = count($allIds);
        if ($resultCount === 0) {
            return false;
        }
        $totalPages = ceil($resultCount / $per_page);
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        $args = array(
            'post_type' => 'product',
            'orderby' => 'post__in',
            'post__in' => $allIds,
            'posts_per_page' => $per_page,
            'paged' => $currentPage,
            'suppress_filters' => true,
            'no_found_rows' => true
        );

        wc_set_loop_prop('total', $resultCount);
        wc_set_loop_prop('per_page', $per_page);
        wc_set_loop_prop('current_page', $currentPage);
        wc_set_loop_prop('total_pages', $totalPages);
        $sortedProducts = new \WP_Query($args);

        return $sortedProducts;
    }

    /**
     * Get category items from elastic. User for frontend ajax search.
     *
     * @return \Elastica\ResultSet
     */
    public function getCategoryItemsFromElastic()
    {
        $config = array(
            'host' => ES_HOST,
            'port' => 9200
        );
//    $per_page = apply_filters('loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page());
        $per_page = 24;
        $currentPage = (get_query_var('paged')) ? get_query_var('paged') : 1;
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
        //sets data in GLOBALS
        $this->parseCategoryAggregation($resultSet);

        wc_set_loop_prop('total', $resultSet->getTotalHits());
        wc_set_loop_prop('per_page', $per_page);
        wc_set_loop_prop('current_page', $currentPage);
        wc_set_loop_prop('total_pages', ceil($resultSet->getTotalHits() / $per_page));

        return $resultSet;
    }

    //@TODO implement category as filter
    private function mysqlSearch($input, $limit = 0)
    {
        $search = new \GF\Search\Search(new \GF\Search\Adapter\MySql($this->wpdb));
        $allIds = $search->getItemIdsForSearch($input, $limit);

        return $this->parsePostIdsForList($allIds);
    }

    private function elasticSearch($input, $limit = 0)
    {
        $config = array(
            'host' => ES_HOST,
            'port' => 9200
        );
        $per_page = 24;
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
        $search = new \GF\Search\Search(new \GF\Search\Adapter\Elastic(new \GF\Search\Elastica\Search($client)));
        $resultSet = $search->getItemsForSearch($input, $per_page, $currentPage);

        //sets data in GLOBALS
        $this->parseCategoryAggregation($resultSet);

        wc_set_loop_prop('total', $resultSet->getTotalHits());
        wc_set_loop_prop('per_page', $per_page);
        wc_set_loop_prop('query', $input);
        wc_set_loop_prop('current_page', $currentPage);
        wc_set_loop_prop('total_pages', ceil($resultSet->getTotalHits() / $per_page));

        add_filter('woocommerce_page_title', [$this, 'applySearchPageTitle']);

        return $resultSet;
    }

    public function applySearchPageTitle($title) {
        $page_title = sprintf('Rezultati pretrage za: &ldquo;%s&rdquo;', wc_get_loop_prop('query'));

//        if (wc_get_loop_prop('current_page')) {
//            $page_title .= '&nbsp;' . wc_get_loop_prop('current_page');
//        }

        return $page_title;
    }

    /**
     * Parses aggregations from resultSet, and sets it in GLOBALS array
     *
     * @param \Elastica\ResultSet $resultSet
     * @return void
     */
    public function parseCategoryAggregation(\Elastica\ResultSet $resultSet) {
        $counts = [];
        foreach ($resultSet->getAggregation('category')['buckets'] as $bucket) {
            $counts[$bucket['key']] = $bucket['doc_count'];
        }
        $catIds = array_keys($counts);
        if (count($catIds) === 0) {
            $GLOBALS['gf-search']['facets']['category'] = [];
            return;
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

    /**
     * Custom loop that works with wp query
     *
     * @param \WP_Query $sortedProducts
     */
    public function customSearchOutput(\WP_Query $sortedProducts)
    {
        if ($sortedProducts->have_posts()):
            wc_setup_loop();
            woocommerce_product_loop_start();
            while ($sortedProducts->have_posts()) :
                $sortedProducts->the_post();
                wc_get_template_part('content', 'product');
            endwhile;
            wp_reset_postdata();
            woocommerce_product_loop_end();
        endif;
    }

    /**
     * @param \Elastica\ResultSet $products
     */
    public function customShopLoop(\Elastica\ResultSet $products)
    {
        global $stickers;
        $html = '';
        $i = 0;
        $dtNow = time();
        foreach ($products->getResults() as $productData) {
            $productId = $productData->postId;
            $product = new \Nss\Feed\Product($productData->getData());
            $price = $product->getRegularPrice();
            $saved_percentage = 0;
            $showSalePrice = false;
            if ($productData->getData()['salePriceStart'] !== '' && $dtNow > $productData->getData()['salePriceStart'] && $dtNow < $productData->getData()['salePriceEnd']) {
                $showSalePrice = true;
            } elseif ($productData->getData()['salePriceStart'] == '' && $product->getSalePrice() > 0) {
                $showSalePrice = true;
            }

            if ($showSalePrice) {
                $saved_price = $product->getRegularPrice() - $product->getSalePrice();
                if ($product->getSalePrice() > 0) {
                    $price = $product->getSalePrice();
                }
                if ($saved_price > 0 && $product->getSalePrice() > 0) {
                    $saved_percentage = number_format($saved_price * 100 / $product->getRegularPrice(), 2);
                }
            }

            $classes = '';
            if ($saved_percentage > 0 && $product->getStockStatus() !== 0) {
                $classes .= ' sale ';
            }
            if ($product->getStockStatus() == 0) {
                $classes .= ' outofstock ';
            }
            // klase koje mozda zatrebaju za <li> 'instock sale shipping-taxable purchasable product-type-simple'
            $classes .= " instock ";
            if (!$product->getStockStatus()) {
                $classes = " outofstock ";
            }
            if ($product->getSalePrice() > 0) {
                $classes .= " sale ";
            }
            if ($i === 0) {
                $classes .= " first ";
            }

            $classes .= " product type-product status-publish has-post-thumbnail shipping-taxable purchasable  ";
            $html .= '<li class="product-type-' . $product->getType() . $classes . '">';
            $html .= '<a href=" ' . $product->dto['permalink'] . ' " title=" ' . $product->getName() . ' ">';
            $html .= $stickers->addStickerToSaleProducts($classes, $productId);
//        woocommerce_show_product_sale_flash('', '', '', $classes);
//            ob_start();
//            $stickers::addStickersToNewProducts(null, $product->getStockStatus(), $product->getPostId());
//            $html .= ob_get_clean();
//        add_stickers_to_products_new($product);
            $html .= $product->dto['thumbnail'];
            $html .= $stickers->addStickerForSoldOutProducts($classes, $product->dto['stockStatus']);
            $html .= '</a>';
//            $html .= '<a href="' . $product->dto['permalink'] . '" title="' . $product->getName() . '">';
            $html .= '<a href="' . $product->dto['permalink'] . '" title="' . $product->getName() . '">';
//            $html .= '<h3>' . $product->dto['stockStatus'] .' # '. $product->getName() . '</h3>';
//            $html .= '<h3>' . $productData->getScore() .' # '. $product->getName() . '</h3>';
            $html .= '<h3>'. $product->getName() .'</h3>';
            $html .= '</a>';
            $html .= '<span class="price">';
            if ($saved_percentage > 0) {
                $html .= '<del><span class="woocommerce-Price-amount amount">' . $product->getRegularPrice()
                    . '<span class="woocommerce-Price-currencySymbol">din.</span></span></del>';
                $html .= '<ins><span class="woocommerce-Price-amount amount">' . $price .
                    '<span class="woocommerce-Price-currencySymbol">din.</span></span></ins>';
                $html .= '<p class="saved-sale">Ušteda: <span class="woocommerce-Price-amount amount">' . $saved_price .
                    '<span class="woocommerce-Price-currencySymbol">din.</span></span> <em> (' . $saved_percentage . '%)</em></p>';
            } else {
                $html .= '<ins><span class="woocommerce-Price-amount amount">' . $product->getRegularPrice()
                    . '<span class="woocommerce-Price-currencySymbol">din.</span></span></ins>';
            }
            $html .= '</span>';
            $html .= '<p class="loop-short-description">' . $product->getShortDescription() . '</p>';
            $html .= '</li>';
            $i++;
        }
        $html .= '</ul>';

        echo $html;
    }
}