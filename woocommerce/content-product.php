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
ob_start();
wc_product_class();
$classes = ob_get_clean();
?>
<li <?=$classes?>>
    <a href="<?php echo get_permalink($product->get_id()) ?>"
       title="<?php echo esc_attr($product->get_title() ? $product->get_title() : $product->get_id()); ?>">
        <?= add_stickers_to_products_on_sale($classes);
        add_stickers_to_products_new($product);
        echo add_stickers_to_products_soldout($classes);
        ?>
        <?php if (has_post_thumbnail($product->get_id())):
//            $image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'single-post-thumbnail');
//            get_the_post_thumbnail_url($product->get_id(), 'single-post-thumbnail');
            ?>
            <img src="<?=get_the_post_thumbnail_url($product->get_id(), 'shop_catalog')?>" class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
                 alt="<?=$product->get_title()?>" width="200" height="200" />
<?php // srcset="https://nss.ha.rs/wp-content/uploads/2018/10/02/34/2960630.jpg 500w, https://nss.ha.rs/wp-content/uploads/2018/10/02/34/2960630-150x150.jpg 150w, https://nss.ha.rs/wp-content/uploads/2018/10/02/34/2960630-300x300.jpg 300w, https://nss.ha.rs/wp-content/uploads/2018/10/02/34/2960630-100x100.jpg 100w" sizes="(max-width: 500px) 100vw, 500px" ';
//            echo get_the_post_thumbnail($product->get_id());
        else:
            echo '<img src="' . wc_placeholder_img_src() . '" alt="Placeholder" width="300px" height="300px" />';
        endif; ?>
    </a>
    <a href="<?php echo get_permalink($product->get_id()) ?>"
       title="<?php echo esc_attr($product->get_title() ? $product->get_title() : $product->get_id()); ?>">
        <h5><?php the_title();?></h5>
    </a>
    <span class="price"><?php echo $product->get_price_html(); ?></span>
    <div class="loop-short-description"><?=strip_tags($product->get_short_description())?></div>
   <?php // ADD to cart button if needed, also hidden in css just in case?>
    <?php //$list_grid->gridlist_buttonwrap_open()?>
    <?php //woocommerce_template_loop_add_to_cart($product); ?>
    <?php //$list_grid->gridlist_buttonwrap_close()?>
</li>
