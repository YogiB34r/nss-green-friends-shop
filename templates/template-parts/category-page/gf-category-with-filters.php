<?php
$sexyShopCat = get_term_by('slug', 'sexy-shop', 'product_cat');
$queriedObject = get_queried_object_id();
if ($sexyShopCat){
    $sexyShopChildren = get_term_children($sexyShopCat->term_id, 'product_cat');
    if (($queriedObject == $sexyShopCat->term_id || in_array($queriedObject, $sexyShopChildren)) && !in_array('nss-sex-shop-agreement', $_COOKIE)): ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                function deleteCookie(name) {
                    document.cookie = name + "=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;";
                }

                function getCookie(name) {
                    var match = document.cookie.match(RegExp('(?:^|;\\s*)' + name + '=([^;]*)'));
                    return match ? match[1] : null;
                }

                function deleteCookie(name) {
                    document.cookie = name + "=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;";
                }

                function hideBody() {
                    document.getElementsByTagName("BODY")[0].style.display = "none";
                }
            });
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
} ?>
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
        if (woocommerce_product_loop()) {
            if (wc_get_loop_prop('total')) {
                if (isset($_GET['s'])) {
                    $sortedProducts = gf_custom_search();
                } else {
//                    custom_woo_product_loop();
                }
            }


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
            ?>
            <?php
            /**
             * Hook: woocommerce_before_shop_loop.
             *
             * @hooked wc_print_notices - 10
             * @hooked woocommerce_result_count - 20
             * @hooked woocommerce_catalog_ordering - 30
             */
            woocommerce_product_loop_start();
            if (wc_get_loop_prop('total')) {
                if (isset($_GET['s'])) {
                    gf_custom_search_output($sortedProducts);
                } else {
                    custom_woo_product_loop();
                }
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
