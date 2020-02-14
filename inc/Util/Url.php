<?php

namespace GF\Util;


class Url
{

    public function __construct()
    {

    }

    public function init()
    {
        add_filter('parse_request', [$this, 'customRequestOverride']);
        add_filter('post_type_link', [$this, 'custom_post_link'], PHP_INT_MAX, 2);
        add_filter('term_link', [$this, 'term_link_filter'], 10, 3);
        add_action('rewrite_rules_array', [$this, 'rewriteRules']);

// @TODO check if this triggers upon new category creation, will the new url work ?
//        add_action('init', [$this, 'flushRules']);
    }

    /**
     * Enables custom URL structure to work for single product: /last-category/product-slug
     *
     * @param \WP $wp
     */
    public static function customRequestOverride(\WP $wp)
    {
        if (isset($wp->query_vars['pagename'])) {
            $params = explode('/', $wp->query_vars['pagename']);
            if (count($params) === 2) {
                $pageName = $params[1];

                /* @var \WP_Post $p */
                $p = get_page_by_path($pageName, OBJECT, 'product');
                if ($p) {
                    $wp->query_vars = [
                        'post_type' => 'product',
// @TODO test this out. it seems to be required in order for rewrites to work.
                        'product' => $pageName,
                        'name' => $pageName
                    ];
                }
            }
        }
    }

    public static function custom_post_link( $permalink, $post ) {
        if ($post->post_type === 'product') {
            return \Gf\Util\CategoryFunctions::getProductUrl($post, $permalink);
        }

        return $permalink;
    }

    public static function term_link_filter( $url, $term, $taxonomy ) {
        if ($taxonomy === 'product_cat') {
            $url = '/' . \Gf\Util\CategoryFunctions::buildTermPath($term);
        }

        return $url;
    }

    public static function rewriteRules($rules) {
        $terms = get_categories(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ));

        foreach ($terms as $term) {
            $slug = \Gf\Util\CategoryFunctions::buildTermPath($term);
//        add_rewrite_rule("{$slug}/?\$", 'index.php?product_cat=' . $term->slug, 'top');
            $customRules["{$slug}/?\$"] = 'index.php?product_cat=' . $term->slug;
        }

        return $customRules + $rules;
    }

    public static function flushRules() {
        flush_rewrite_rules();
    }

}