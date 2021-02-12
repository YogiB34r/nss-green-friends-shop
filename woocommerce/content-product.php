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
 *
 * @var WC_Product $product
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
global $stickers;
?>
<li <?=$classes?>>
    <a href="<?php echo get_permalink($product->get_id()) ?>"
       title="<?php echo esc_attr($product->get_title() ? $product->get_title() : $product->get_id()); ?>">
        <?php

        if (method_exists($stickers,'addStickerToSaleProducts')) {
            echo $stickers->addStickerToSaleProducts($classes, $product->get_id());
        }
        if (method_exists($stickers,'addStickersToNewProducts')) {
            $stickers->addStickersToNewProducts($product);
        }
        if (method_exists($stickers,'addStickerForSoldOutProducts')) {
            $stickers->addStickerToSaleProducts($classes, $product->get_id());
        }
        ?>
        <?php if (has_post_thumbnail($product->get_id())): ?>
            <img src="<?=get_the_post_thumbnail_url($product->get_id(), 'shop_catalog')?>" class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
                 alt="<?=$product->get_title()?>" width="200" height="200" />
<?php
        else:
            echo '<img src="' . wc_placeholder_img_src() . '" alt="Placeholder" width="300px" height="300px" />';
        endif; ?>
    </a>
    <a href="<?php echo get_permalink($product->get_id()) ?>"
       title="<?php echo esc_attr($product->get_title() ? $product->get_title() : $product->get_id()); ?>">
        <h3><?php the_title();?></h3>
    </a>
    <span class="price"><?php echo $product->get_price_html(); ?></span>
    <div class="loop-short-description"><?=strip_tags($product->get_short_description())?></div>
</li>
