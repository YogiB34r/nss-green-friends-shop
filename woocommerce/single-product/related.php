<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (is_singular('product')) {
    global $post;
// get categories

    $parent_id = get_term_by('slug', 'specijalne-promocije', 'product_cat')->term_id;
    $slider_sub_cats_args = array(
        'parent' => $parent_id,
    );
    $slider_sub_cats = get_terms('product_cat', $slider_sub_cats_args);
    $exclude_cats_ids = [];

    foreach ($slider_sub_cats as $slider_sub_cat) {
        $exclude_cats_ids[] = $slider_sub_cat->term_id;
    }

    $parent_cats_args = array(
        'parent' => 0
    );
    $parent_cats = get_terms('product_cat', $parent_cats_args);
    foreach ($parent_cats as $parent_cat) {
        $exclude_cats_ids[] = $parent_cat->term_id;
    }

    $args = array(
        'exclude' => $exclude_cats_ids,
    );
    $terms = wp_get_post_terms($post->ID, 'product_cat', $args);
    $cats_array = [];
    foreach ($terms as $term) {
        $children = get_term_children($term->term_id, 'product_cat');
        if (!sizeof($children))
            $cats_array[] = $term->term_id;
    }

    $query_args = array(
        'orderby' => 'rand',
        'post__not_in' => array($post->ID),
        'posts_per_page' => 4,
        'no_found_rows' => 1,
        'post_status' => 'publish',
        'post_type' => 'product',
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'id',
                'terms' => $cats_array,
                'parent' => 0,
                'exclude' => $exclude_cats_ids,
            )),
        'meta_query' => array(array(
            'key' => '_stock_status',
            'value' => 'outofstock',
            'compare' => 'NOT IN'
        )));

    $r = new WP_Query($query_args);
    
    if ($r->have_posts()) { ?>


        <div class="related products">
            <h2><?php _e('Slični proizvodi', 'woocommerce'); ?></h2>

            <?php woocommerce_product_loop_start();
            while ($r->have_posts()) : $r->the_post();

                global $product;

                wc_get_template_part('content', 'product');


            endwhile; // end of the loop.

            woocommerce_product_loop_end(); ?>

        </div>

        <?php

        wp_reset_query();
    };
}