<?php
$sexyShopCats = gf_get_category_children_ids('sexy-shop');
$product_cats = wc_get_product(get_queried_object_id())->get_category_ids();
$result = false;
foreach ($product_cats as $product_cat){
    if (in_array($product_cat,$sexyShopCats)){
        $result = true;
        break;
    }
}
    if ($result && !in_array('nss-sex-shop-agreement', $_COOKIE)): ?>
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
    <?php endif;?>
<div class="row">
    <div class="col-3 list-unstyled gf-sidebar">
      <div class="gf-left-sidebar-wrapper">
        <div class="gf-wrapper-before">
          <div class="gf-category-sidebar-toggle">Kategorije</div>
          <span class="fas fa-angle-up"></span>
        </div>
        <?php dynamic_sidebar('gf-sidebar-single-productpage')?>
      </div>
    </div>
    <div class="gf-content-wrapper col-md-9 col-sm-12">
        <?php
        /**
         * The template for displaying product content in the single-product.php template
         *
         * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
         *
         * HOWEVER, on occasion WooCommerce will need to update template files and you
         * (the theme developer) will need to copy the new files to your theme to
         * maintain compatibility. We try to do this as little as possible, but it does
         * happen. When this occurs the version of the template file will be bumped and
         * the readme will list any important changes.
         *
         * @see     https://docs.woocommerce.com/document/template-structure/
         * @package WooCommerce/Templates
         * @version 3.4.0
         */

        defined( 'ABSPATH' ) || exit;
        /**
         * Hook: woocommerce_before_single_product.
         *
         * @hooked wc_print_notices - 10
         */
        do_action( 'woocommerce_before_single_product' );

        if ( post_password_required() ) {
            echo get_the_password_form(); // WPCS: XSS ok.
            return;
        }
        ?>
        <div id="product-<?php the_ID(); ?>" <?php wc_product_class(); ?>>

            <?php
            /**
             * Hook: woocommerce_before_single_product_summary.
             *
             * @hooked woocommerce_show_product_sale_flash - 10
             * @hooked woocommerce_show_product_images - 20
             */
            do_action( 'woocommerce_before_single_product_summary' );
            ?>

            <div class="summary entry-summary">
                <?php
                /**
                 * Hook: woocommerce_single_product_summary.
                 *
                 * @hooked woocommerce_template_single_title - 5
                 * @hooked woocommerce_template_single_rating - 10
                 * @hooked woocommerce_template_single_price - 10
                 * @hooked woocommerce_template_single_excerpt - 20
                 * @hooked woocommerce_template_single_add_to_cart - 30
                 * @hooked woocommerce_template_single_meta - 40
                 * @hooked woocommerce_template_single_sharing - 50
                 * @hooked WC_Structured_Data::generate_product_data() - 60
                 */
                do_action( 'woocommerce_single_product_summary' );
                ?>
            </div>

            <?php
            /**
             * Hook: woocommerce_after_single_product_summary.
             *
             * @hooked woocommerce_output_product_data_tabs - 10
             * @hooked woocommerce_upsell_display - 15
             * @hooked woocommerce_output_related_products - 20
             */
            do_action( 'woocommerce_after_single_product_summary' );
            ?>
        </div>

        <?php do_action( 'woocommerce_after_single_product' ); ?>
    </div>
</div>
