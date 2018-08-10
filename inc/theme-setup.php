

<?php
add_action('after_theme_setup', 'gf_theme_setup');

/**
* Add theme support
*/
function gf_theme_setup()
{
/*
* Load Localisation files.
*
* Note: the first-loaded translation file overrides any following ones if the same translation is present.
*/

    load_theme_textdomain( 'green-friends', get_template_directory() . '/languages' );

    /**
     * Add default posts and comments RSS feed links to head.
     */
    add_theme_support( 'automatic-feed-links' );

    /*
     * Enable support for Post Thumbnails on posts and pages.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#Post_Thumbnails
     */
    add_theme_support( 'post-thumbnails' );

    /**
     * Enable support for site logo
     */
    add_theme_support( 'custom-logo', array(
        'height'      => 110,
        'width'       => 470,
        'flex-width'  => true,
    ) ) ;

// This theme uses wp_nav_menu() in two locations.
    register_nav_menus( array(
        'primary'   => __( 'Header Menus'),
        'secondary' => __( 'Footer Menus'),
        'handheld'  => __( 'Other Menus'),
    ) );

    /*
     * Switch default core markup for search form, comment form, comments, galleries, captions and widgets
     * to output valid HTML5.
     */
add_theme_support( 'html5',array(
    'search-form',
    'comment-form',
    'comment-list',
    'gallery',
    'caption',
    'widgets',
) ) ;

// Setup the WordPress core custom background feature.
    add_theme_support( 'custom-background', array(
        'default-color' => 'ffffff',
        'default-image' => '',
    ) );

    /**
     *  Add support for the Site Logo plugin and the site logo functionality in JetPack
     *  https://github.com/automattic/site-logo
     *  http://jetpack.me/
     */
//add_theme_support( 'site-logo', apply_filters( 'storefront_site_logo_args', array(
//    'size' => 'full'
//) ) );



//// Declare support for title theme feature.
//add_theme_support( 'title-tag' );

// Declare support for selective refreshing of widgets.
    add_theme_support( 'customize-selective-refresh-widgets' );

    add_theme_support( 'custom-header',array(
        'default-image' => '',
        'header-text'   => false,
        'width'         => 1950,
        'height'        => 500,
        'flex-width'    => true,
        'flex-height'   => true,
    ) );
}


add_action('wp_enqueue_scripts', 'gf_theme_scripts_init');

/**
    * Enqueue styles & scripts
    */
function gf_theme_scripts_init()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('bootstrap-popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array(), '', 'true');
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js', array(), '', 'true');
    wp_enqueue_script('ui', get_stylesheet_directory_uri() . '/inc/js/ui.js');
//    wp_enqueue_script('bootstrap-jQuery', 'https://code.jquery.com/jquery-3.3.1.slim.min.js', array(), '', 'true');
    wp_enqueue_style('bootstrap 4.1', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css');
    wp_enqueue_style('gf-style-reset', get_stylesheet_directory_uri() . '/inc/reset.css');
    wp_enqueue_style('gf-style', get_stylesheet_directory_uri() . '/style.css');
}


add_action('customize_register', 'gf_theme_customizer_setup');
/**
 * GF customizer
 */
function gf_theme_customizer_setup($wp_customize)
{

}
