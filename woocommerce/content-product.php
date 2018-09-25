<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
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

defined('ABSPATH') || exit;
$list_grid = new WC_List_Grid;
global $product;
// Ensure visibility.
if (empty($product) || !$product->is_visible()) {
    return;
}
?>
<li <?php wc_product_class(); ?>>
    <a href="<?php echo get_permalink($product->get_id()) ?>"
       title="<?php echo esc_attr($product->get_title() ? $product->get_title() : $product->get_id()); ?>">
        <?php woocommerce_show_product_sale_flash('', $product);
        add_stickers_to_products_new();
        add_stickers_to_products_soldout()
        ?>
        <?php if (has_post_thumbnail($product->get_id())) echo get_the_post_thumbnail($product->get_id(), 'shop_catalog'); else echo '<img src="' . wc_placeholder_img_src() . '" alt="Placeholder" width="300px" height="300px" />'; ?>
    </a>
    <a href="<?php echo get_permalink($product->get_id()) ?>"
       title="<?php echo esc_attr($product->get_title() ? $product->get_title() : $product->get_id()); ?>">
        <h5><?php the_title(); ?></h5>
    </a>
    <span class="price"><?php echo $product->get_price_html(); ?></span>
    <div class="loop-short-description"><?=$product->get_short_description()?></div>
    <!-- ADD to cart button if needed, also hidden in css just in case -->
    <?php //$list_grid->gridlist_buttonwrap_open()?>
    <?php //woocommerce_template_loop_add_to_cart($product); ?>
    <?php //$list_grid->gridlist_buttonwrap_close()?>
</li>
