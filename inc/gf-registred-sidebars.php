<?php
add_action( 'widgets_init', 'gf_register_sidebars' );
function gf_register_sidebars() {
    $theme= wp_get_theme();
    $my_sidebars = array(
        array(
            'name'          => __('Header Text/Registracija/Prijava', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-header-row-1',
            'description'   => '',
        ),
        array(
            'name'          => __('Header Logo', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-header-row-2-col-1',
            'description'   => '',
        ),
        array(
            'name'          => __('Header Search form (desktop)', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-header-row-2-col-2',
            'description'   => '',
        ),
        array(
            'name'          => __('Search form mobile', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-search-form-mobile',
            'description'   => '',
        ),
        array(
            'name'          => __('Header cart/account/mobile navigation', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-header-row-2-col-3',
            'description'   => '',
        ),
        array(
            'name'          => __('Homepage images slider/banners', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-homepage-row-1',
            'description'   => '',
        ),
        array(
            'name'          => __('Homepage product sliders', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-homepage-row-2',
            'description'   => '',

        ),
        array(
            'name'          => __('Footer Newsletter', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-footer-row-1-column-1',
            'description'   => 'First footer row column 1',
        ),
        array(
            'name'          => __('Footer O kompaniji', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-footer-row-1-column-2',
            'description'   => 'First footer row column 2',
        ),
        array(
            'name'          => __('Footer Podrška', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-footer-row-1-column-3',
            'description'   => 'First footer row column 3',
        ),
        array(
            'name'          => __('Footer Uslovi korišćenja', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-footer-row-1-column-4',
            'description'   => 'First footer row column 4',
        ),
        array(
            'name'          => __('Footer row 2 column 1', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-footer-row-2-column-1',
            'description'   => 'Second footer row column 1',
        ),
        array(
            'name'          => __('Footer row 2 column 2', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-footer-row-2-column-2',
            'description'   => 'Second footer row column 2',
        ),
        array(
            'name'          => __('Footer Cards Images', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-footer-row-3-column-1',
            'description'   => 'Third footer row column 1',
        ),
        array(
            'name'          => __('Left sidebar', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-left-sidebar',
            'description'   => 'Left sidebar',

        ),
        array(
            'name'          => __('Category Sidebar', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-category-sidebar',
            'description'   => 'Category page sidebar',
        ),
        array(
            'name'          => __('Category Sidebar (product filters for mobile)', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-category-sidebar-product-filters',
            'description'   => 'Category page sidebar (product filters for mobile)',
        ),
        array(
            'name'          => __('Sidebar (single product page)', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-sidebar-single-productpage',
            'description'   => 'Sidebar (single product page)',
        ),
    );


    foreach( $my_sidebars as $args ) {
        register_sidebar($args);
    }
}