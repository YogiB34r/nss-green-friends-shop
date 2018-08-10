

<?php
add_action( 'widgets_init', 'gf_register_sidebars' );
function gf_register_sidebars() {
    $theme= wp_get_theme();
    $my_sidebars = array(
        array(
            'name'          => __('Header row 1', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-header-row-1',
            'description'   => '',
        ),
        array(
            'name'          => __('Header row 2 col 1', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-header-row-2-col-1',
            'description'   => '',
        ),
        array(
            'name'          => __('Header row 2 col 2', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-header-row-2-col-2',
            'description'   => '',
        ),
        array(
            'name'          => __('Header row 2 col 3', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-header-row-2-col-3',
            'description'   => '',
        ),
        array(
            'name'          => __('Homepage row 1', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-homepage-row-1',
            'description'   => '',
        ),
        array(
            'name'          => __('Homepage row 2', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-homepage-row-2',
            'description'   => '',

        ),
        array(
            'name'          => __('Footer row 1 column 1', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-footer-row-1-column-1',
            'description'   => 'First footer row column 1',
        ),
        array(
            'name'          => __('Footer row 1 column 2', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-footer-row-1-column-2',
            'description'   => 'First footer row column 2',
        ),
        array(
            'name'          => __('Footer row 1 column 3', ''.$theme->get('TextDomain').''),
            'id'            => 'gf-footer-row-1-column-3',
            'description'   => 'First footer row column 3',
        ),
        array(
            'name'          => __('Footer row 1 column 4', ''.$theme->get('TextDomain').''),
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
            'name'          => __('Footer row 3 column 1', ''.$theme->get('TextDomain').''),
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
    );


    foreach( $my_sidebars as $args ) {
        register_sidebar($args);
    }
}