<?php
add_action('widgets_init', 'gf_register_sidebars');
function gf_register_sidebars()
{
    $theme = wp_get_theme();
    $my_sidebars = array(
        array(
            'name' => __('Header Text/Registracija/Prijava', '' . $theme->get('TextDomain') . ''),
            'id' => 'gf-header-row-1',
            'description' => '',
        ),
        array(
            'name' => __('Header Logo', '' . $theme->get('TextDomain') . ''),
            'id' => 'gf-header-row-2-col-1',
            'description' => '',
        ),
        array(
            'name' => __('Header Search form (desktop)', '' . $theme->get('TextDomain') . ''),
            'id' => 'gf-header-row-2-col-2',
            'description' => '',
        ),
        array(
            'name' => __('Home images slider/banners(desktop)', '' . $theme->get('TextDomain') . ''),
            'id' => 'gf-homepage-row-1',
            'description' => '',
        ),
        array(
            'name' => __('Homepage product sliders', '' . $theme->get('TextDomain') . ''),
            'id' => 'gf-homepage-row-2',
            'description' => '',
        ),
        array(
            'name' => __('Footer Newsletter', '' . $theme->get('TextDomain') . ''),
            'id' => 'gf-footer-row-1-column-1',
            'description' => 'First footer row column 1',
        ),
        array(
            'name' => __('Left sidebar', '' . $theme->get('TextDomain') . ''),
            'id' => 'gf-left-sidebar',
            'description' => 'Left sidebar',

        ),
        array(
            'name' => __('Category Sidebar', '' . $theme->get('TextDomain') . ''),
            'id' => 'gf-category-sidebar',
            'description' => 'Category page sidebar',
        ),
        array(
            'name' => __('Sidebar (single product page)', '' . $theme->get('TextDomain') . ''),
            'id' => 'gf-sidebar-single-productpage',
            'description' => 'Sidebar (single product page)',
        ),

        array(
            'name' => __('Header Search form (mobile)', '' . $theme->get('TextDomain') . ''),
            'id' => 'gf-search-form-mobile',
            'description' => '',
        ),
        array(
            'name' => __('Header cart/account/mobile navigation', '' . $theme->get('TextDomain') . ''),
            'id' => 'gf-header-row-2-col-3',
            'description' => '',
        ),
        array(
            'name' => __('Home images slider/banners(mobile)', '' . $theme->get('TextDomain') . ''),
            'id' => 'gf-homepage-row-1-mobile',
            'description' => '',
        ),
        array(
            'name' => __('Homepage product sliders (mobile)', '' . $theme->get('TextDomain') . ''),
            'id' => 'gf-homepage-row-3',
            'description' => '',
        ),
        array(
            'name' => __('Category Sidebar (product filters for mobile)', '' . $theme->get('TextDomain') . ''),
            'id' => 'gf-category-sidebar-product-filters',
            'description' => 'Category page sidebar (product filters for mobile)',
        ),
    );


    foreach ($my_sidebars as $args) {
        register_sidebar($args);
    }
}