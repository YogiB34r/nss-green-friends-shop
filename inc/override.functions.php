<?php

/**
 * custom breadcrumbs based on wc breadcrumbs
 *
 * @param array $args
 */
function woocommerce_breadcrumb($args = array()) {
    $args = wp_parse_args($args, apply_filters('woocommerce_breadcrumb_defaults', array(
        'delimiter' => '&nbsp;&#47;&nbsp;',
        'wrap_before' => '<nav class="woocommerce-breadcrumb" ' . (is_single() ? 'itemprop="breadcrumb"' : '') . '>',
        'wrap_after' => '</nav>',
        'before' => '',
        'after' => '',
        'home' => _x('Home', 'breadcrumb', 'woocommerce')
    )));
    $breadcrumbs = new gf_breadcrumbs();

    if ($args['home']) {
        $breadcrumbs->add_crumb($args['home'], apply_filters('woocommerce_breadcrumb_home_url', home_url()));
    }
    $args['breadcrumb'] = $breadcrumbs->generate();

    wc_get_template('global/breadcrumb.php', $args);
}

function woocommerce_pagination() {
    $args = array(
        'total' => wc_get_loop_prop('total_pages'),
        'current' => wc_get_loop_prop('current_page'),
        'base' => esc_url_raw(add_query_arg('product-page', '%#%', false)),
        'format' => '?product-page=%#%',
    );

    if (!wc_get_loop_prop('is_shortcode')) {
        $args['format'] = '';
        $args['base'] = esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false))));
    }
    wc_get_template('loop/pagination.php', $args);
}


