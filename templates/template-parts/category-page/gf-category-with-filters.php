<?php
$queriedObjectId = get_queried_object_id();
$sexyShopCats = gf_get_category_children_ids('sexy-shop');
if ($sexyShopCats){
    if ((in_array($queriedObjectId, $sexyShopCats) || in_array($queriedObjectId, $sexyShopCats)) && !in_array('nss-sex-shop-agreement', $_COOKIE)): ?>
        <script type="text/javascript">
            if (confirm('Da bi ste videli sadržaj ovog odeljka morate se složiti sa uslovima i prihvatiti da imate preko 18 godina.')
                == true) {
                var expiryDate = new Date();
                expiryDate.setMonth(expiryDate.getMonth() + 6);
                document.cookie = 'name = nss-sex-shop-agreement; path=/; expires =' + expiryDate;
                document.getElementsByTagName("BODY")[0].style.display = "block";
            } else {
                document.location.href = "/";
            }
        </script>
    <?php endif;
}

/**
 * Has to be called on top in order to properly set all required filters
 */
$sortedProducts = false;
$useElastic = true;

/* @TODO make it better ... */
if (get_query_var('term') !== '') {
    if ($useElastic) {
        $sortedProducts = gf_get_category_items_from_elastic();
    } else {
        $sortedProducts = gf_get_category_query();
    }
} else {
    if (!isset($_GET['query'])) {
        header('Location: ' . home_url());
    }
    if ($useElastic) {
        $sortedProducts = gf_elastic_search_with_data($_GET['query']);
    } else {
        $sortedProducts = gf_custom_search($_GET['query']);
    }
}
?>
<div class="row">
    <div class="col-3 list-unstyled gf-sidebar">
        <div class="gf-left-sidebar-wrapper">
            <div class="gf-wrapper-before">
                <div class="gf-category-sidebar-toggle">Kategorije</div>
                <span class="fas fa-angle-up"></span>
            </div>
            <?php dynamic_sidebar('gf-category-sidebar') ?>
        </div>
    </div>
    <div class="gf-content-wrapper col-md-9 col-sm-12">
        <?php
        /**
         * The Template for displaying product archives, including the main shop page which is a post type archive
         *
         * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
         *
         * HOWEVER, on occasion WooCommerce will need to update template files and you
         * (the theme developer) will need to copy the new files to your theme to
         * maintain compatibility. We try to do this as little as possible, but it does
         * happen. When this occurs the version of the template file will be bumped and
         * the readme will list any important changes.
         *
         * @see https://docs.woocommerce.com/document/template-structure/
         * @package WooCommerce/Templates
         * @version 3.4.0
         */

        defined('ABSPATH') || exit;

        get_header('shop');

        /**
         * Hook: woocommerce_before_main_content.
         *
         * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
         * @hooked woocommerce_breadcrumb - 20
         * @hooked WC_Structured_Data::generate_website_data() - 30
         */
        do_action('woocommerce_before_main_content');
        ?>
        <header class="woocommerce-products-header">
            <?php if (apply_filters('woocommerce_show_page_title', true)) : ?>
                <h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
            <?php endif; ?>

            <div class="gf-product-filters-mobile-wrapper">
                <?php dynamic_sidebar('gf-category-sidebar-product-filters'); ?>
            </div>

            <?php
            /**
             * Hook: woocommerce_archive_description.
             *
             * @hooked woocommerce_taxonomy_archive_description - 10
             * @hooked woocommerce_product_archive_description - 10
             */
            do_action('woocommerce_archive_description');
            ?>
        </header>
        <?php
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

            woocommerce_product_loop_start();
            if (get_class($sortedProducts) === \Elastica\ResultSet::class) {
                gf_custom_shop_loop($sortedProducts);
            } else {
                gf_custom_search_output($sortedProducts);
            }

            woocommerce_product_loop_end();

            /**
             * Hook: woocommerce_after_shop_loop.
             *
             * @hooked woocommerce_pagination - 10
             */
            echo '<div class="gf-product-controls gf-product-controls--bottom">';
            do_action('woocommerce_after_shop_loop');
            echo '</div>';
        } else {
            /**
             * Hook: woocommerce_no_products_found.
             *
             * @hooked wc_no_products_found - 10
             */
            do_action('woocommerce_no_products_found');
        }

        /**
         * Hook: woocommerce_after_main_content.
         *
         * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
         */
        do_action('woocommerce_after_main_content');; ?>
    </div>
</div>
