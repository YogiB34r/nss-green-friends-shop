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

function remove_stubborn_assets() {
    wp_dequeue_style('newsletter');
    wp_deregister_style('newsletter');
}
add_action('wp_print_styles', 'remove_stubborn_assets', 99999);

add_action('wp_enqueue_scripts', 'gf_theme_and_plugins_frontend_scripts_and_styles');
function gf_theme_and_plugins_frontend_scripts_and_styles()
{

//    wp_enqueue_script('jquery', '', [], false, true);
//    wp_enqueue_script('bootstrap-popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array(), '', 'true');
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js', array(), '', 'true');
//    wp_enqueue_script('clamp', get_stylesheet_directory_uri() . '/assets/js/3rd-party/clamp.min.js');
//    wp_enqueue_script('jQuery-ui', 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js');
//    wp_enqueue_script('gf-ajax', get_stylesheet_directory_uri() . '/assets/js/ajax.js');

//    wp_enqueue_style('bootstrap 4.1', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css');
//    wp_enqueue_style('gf-style-reset', get_stylesheet_directory_uri() . '/assets/css/reset.css');
    wp_enqueue_style('gf-style-compiled', get_stylesheet_directory_uri() . '/assets/css/compiled.css');
    wp_enqueue_style('gf-style', get_stylesheet_directory_uri() . '/style.css');

    wp_dequeue_style('searchandfilter');
    wp_deregister_style('searchandfilter');
    wp_dequeue_style('to-top');
    wp_deregister_style('to-top');
    wp_dequeue_style('woocommerce-general');
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');


    wp_enqueue_script('gf-front-js', get_stylesheet_directory_uri() . '/assets/js/gf-front.js', [], '', true);
    //required in order for ajax to work !?
    wp_localize_script( 'gf-front-js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php')));
}
add_action('admin_enqueue_scripts','gf_add_theme_and_plugins_backend_scripts_and_styles');
function gf_add_theme_and_plugins_backend_scripts_and_styles() {
    wp_enqueue_script('gf-admin-js', get_stylesheet_directory_uri() . '/assets/js/gf-admin.js');
    wp_enqueue_style('gf-admin-style', get_stylesheet_directory_uri() . '/admin.css');
}

function add_async_attribute($tag, $handle) {
    $scripts_to_defer = array('gf-front-js');
    foreach($scripts_to_defer as $defer_script) {
        if ($defer_script === $handle) {
            return str_replace(' src', ' async="async" src', $tag);
        }
    }
    return $tag;
}
add_filter('script_loader_tag', 'add_async_attribute', 10, 2);

add_action('customize_register', 'gf_theme_customizer_setup');
/**
 * GF customizer
 * @todo Add support for color changing etc from customizer
 */
function gf_theme_customizer_setup($wp_customize)
{

}
