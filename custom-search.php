<?php
/* Template Name: custom search */

global $wpdb;

//$sw = new \Symfony\Component\Stopwatch\Stopwatch();
//$sw->start('gfmain');

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
get_header();
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
                    <?php dynamic_sidebar('gf-category-sidebar')?>
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
        if ($sortedProducts->have_posts()) {
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

//                $sw->start('searchoutput');
                gf_custom_search_output($sortedProducts);
//                $sw->stop('searchoutput');


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
//$sw->stop('gfmain');

//performanceDebug($sw);

function performanceDebug($sw) {
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
}

get_footer(); ?>