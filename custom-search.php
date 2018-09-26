<?php
/* Template Name: custom search */

global $wpdb;

$sw = new \Symfony\Component\Stopwatch\Stopwatch();
$sw->start('gfmain');

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

//        $sql_product = "SELECT `productName`, `postId` FROM wp_gf_products WHERE `productName` LIKE '%{$keyword}%' LIMIT 4";
//        $product_results = $wpdb->get_results($sql_product);
    $product_results = gf_custom_search($query, 4);

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
        foreach ($product_results->get_posts() as $post) {
            $product_link = get_permalink((int) $post->ID);
            $html .= '<li><a href="' . $product_link . '">' . $post->post_title . '</a></li>';
        }
    } else {
        $html .= '<li>Nema rezultata</li>';
    }
    $html .= '</ul>';

    echo $html;
    exit();
}

wp_reset_query();

/**
 * Set custom body class in order to load proper woo commerce templates
 */
add_filter('body_class', 'custom_body_class');
function custom_body_class($classes) {
    $classesToRemove = [
        'page', 'page-template', 'page-template-custom-search', 'page-template-custom-search-php'
    ];
    $classesToAdd = [
        'archive', 'tax-product_cat', 'woocommerce', 'woocommerce-page'
    ];

    return array_merge(array_diff($classes, $classesToRemove), $classesToAdd);
}
$sw->start('heder');
get_header();
$sw->stop('heder');
//$sw->stopSection('gfheader');
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <div class="row">
            <div class="col-3 list-unstyled gf-sidebar">
                <div class="gf-left-sidebar-wrapper">
                    <div class="gf-wrapper-before">
                        <div class="gf-category-sidebar-toggle">Kategorije</div>
                        <span class="fas fa-angle-up"></span>
                    </div>
                    <?php $sw->start('gfsidebar'); ?>
                    <?php dynamic_sidebar('gf-category-sidebar')?>
                    <?php $sw->stop('gfsidebar'); ?>
                </div>
            </div>
            <div class="gf-content-wrapper col-md-9 col-sm-12">
        <?php
        /**
         * Hook: woocommerce_before_main_content.
         *
         * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
         * @hooked woocommerce_breadcrumb - 20
         * @hooked WC_Structured_Data::generate_website_data() - 30
         */
        do_action( 'woocommerce_before_main_content' );

        ?>
        <header class="woocommerce-products-header">
            <h1 class="woocommerce-products-header__title page-title">Pretraga</h1>

            <?php
            /**
             * Hook: woocommerce_archive_description.
             *
             * @hooked woocommerce_taxonomy_archive_description - 10
             * @hooked woocommerce_product_archive_description - 10
             */
            do_action( 'woocommerce_archive_description' );
            ?>
        </header>
        <?php
            $sortedProducts = gf_custom_search($_GET['query']);
            if ($sortedProducts) {
                /**
                 * Hook: woocommerce_before_shop_loop.
                 *
                 * @hooked wc_print_notices - 10
                 * @hooked woocommerce_result_count - 20
                 * @hooked woocommerce_catalog_ordering - 30
                 */
                echo '<div class="gf-product-controls">';
                do_action('woocommerce_before_shop_loop');
                echo '</div>';

                $sw->start('searchoutput');
                gf_custom_search_output($sortedProducts);
                $sw->stop('searchoutput');


                /**
                 * Hook: woocommerce_after_shop_loop.
                 *
                 * @hooked woocommerce_pagination - 10
                 */
                echo '<div class="gf-product-controls gf-product-controls--bottom">';
                do_action( 'woocommerce_after_shop_loop' );
                echo '</div>';
            } else {
            /**
             * Hook: woocommerce_no_products_found.
             *
             * @hooked wc_no_products_found - 10
             */
            do_action( 'woocommerce_no_products_found' );
        }

        /**
         * Hook: woocommerce_after_main_content.
         *
         * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
         */
        do_action( 'woocommerce_after_main_content' );
        ?>
            </div>
        </div>
    </main>
</div>
<?php
$sw->stop('gfmain');
/* @var \Symfony\Component\Stopwatch\Section $section */
/* @var \Symfony\Component\Stopwatch\StopwatchEvent $event */
/* @var \Symfony\Component\Stopwatch\StopwatchPeriod $period */
foreach ($sw->getSections() as $section) {
    foreach ($section->getEvents() as $name => $event) {
        var_dump($name);
        foreach ($event->getPeriods() as $period) {
//            echo '<p>start time : ' . $period->getStartTime() . '</p>';
//            echo '<p>end time : ' . $period->getEndTime() . '</p>';
            echo '<p>duration : ' . ($period->getEndTime() - $period->getStartTime()) . '</p>';
        }
    }
}
//var_dump($sw->getSections());
?>
<?php get_footer(); ?>