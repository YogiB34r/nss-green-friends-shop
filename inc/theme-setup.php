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

function remove_stubborn_js() {
    wp_dequeue_script('cookie');
    wp_deregister_script('cookie');
    wp_dequeue_script('grid-list-scripts');
    wp_deregister_script('grid-list-scripts');
}
//add_action('wp_print_scripts', 'remove_stubborn_js', 99999);

add_action('wp_enqueue_scripts', 'gf_theme_and_plugins_frontend_scripts_and_styles');
function gf_theme_and_plugins_frontend_scripts_and_styles()
{
    wp_enqueue_style('font-awesome', 'https://use.fontawesome.com/releases/v5.1.1/css/all.css');

    wp_enqueue_script('jquery', '', [], false, true);
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js', array(), '', 'true');
    wp_enqueue_script('bootstrap-popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array(), '', 'true');
    wp_enqueue_script('clamp', get_stylesheet_directory_uri() . '/assets/js/3rd-party/clamp.min.js');
    wp_enqueue_script('cookie', get_stylesheet_directory_uri() . '/assets/js/jquery.cookie.js');
    wp_enqueue_script('flexslider', plugins_url() . '/woocommerce/assets/js/flexslider/jquery.flexslider.min.js');
    wp_enqueue_script('zoom', plugins_url() . '/woocommerce/assets/js/zoom/jquery.zoom.min.js');
    wp_enqueue_script('photoswipe', plugins_url() . '/woocommerce/assets/js/photoswipe/jquery.photoswipe.min.js');
    wp_enqueue_script('photoswipe-ui-default', plugins_url() . '/woocommerce/assets/js/photoswipe/jquery.photoswipe-ui-default.min.js');
    wp_enqueue_script('wc-single-product', plugins_url() . '/woocommerce/assets/js/frontend/single-product.min.js', ['photoswipe']);
//    wp_enqueue_script('cookie-notice-front', plugins_url('/cookie-notice/js/front.js'), array('jquery', 'cookie', 'gf-front-js'));

    wp_enqueue_style('bootstrap 4.1', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css');
    wp_enqueue_style('woocommerce-layout');
//    wp_enqueue_style('woocommerce-smallscreen');
    wp_enqueue_style('woocommerce-general');
    wp_enqueue_style('gf-style-reset', get_stylesheet_directory_uri() . '/assets/css/reset.css');
    wp_enqueue_style('gf-style', get_stylesheet_directory_uri() . '/style.css', ['woocommerce-layout']);
    wp_enqueue_style( 'grid-list-layout', plugins_url( '/woocommerce-grid-list-toggle/assets/css/style.css'));
    wp_enqueue_style( 'grid-list-button', plugins_url( '/woocommerce-grid-list-toggle/assets/css/button.css'));

    wp_enqueue_script('grid-list-scripts', plugins_url( '/woocommerce-grid-list-toggle/assets/js/jquery.gridlistview.min.js'), ['jquery-ui-tabs']);
    wp_enqueue_script('gf-front-js', get_stylesheet_directory_uri() . '/assets/js/gf-front.js', [], '', true);
    //required in order for ajax to work !?
    wp_localize_script( 'gf-front-js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php')));
}
add_action('admin_enqueue_scripts','gf_add_theme_and_plugins_backend_scripts_and_styles');
function gf_add_theme_and_plugins_backend_scripts_and_styles() {
    wp_enqueue_script('gf-admin-js', get_stylesheet_directory_uri() . '/assets/js/gf-admin.js');
    wp_enqueue_style('gf-admin-style', get_stylesheet_directory_uri() . '/admin.css');
}

// @TODO create option from admin to reset assets
$compileOverrideActive = true;
$userData = get_userdata(get_current_user_id());
//$userData = false;
if ($userData && in_array('administrator', $userData->roles)) {

} else {
//    add_action('wp_print_styles', function() use ($compileOverrideActive) { merge_all_styles($compileOverrideActive); }, 999999);
//    add_action('wp_enqueue_scripts', function() use ($compileOverrideActive) { merge_all_scripts($compileOverrideActive); }, 999999);
}

function merge_all_styles($compileOverrideActive) {
    global $wp_styles;
    /**
        #1. Reorder the handles based on its dependency,
            The result will be saved in the to_do property ($wp_scripts->to_do)
    */
    $wp_styles->all_deps($wp_styles->queue);
    $version = 2;
    $version = time();
    $ignoredStyles = ['font-awesome', 'woocommerce-smallscreen'];
    $fileName = "uploads/compiled.css";
    if (wp_is_mobile()) {
        $ignoredStyles = ['font-awesome'];
        $fileName = "uploads/compiled-mobile.css";
    } else {
        wp_dequeue_style('woocommerce-smallscreen');
        wp_deregister_style('woocommerce-smallscreen');
    }

    $merged_file_location = ABSPATH . '/wp-content/' . $fileName;
    if (file_exists($merged_file_location) && !$compileOverrideActive) {
        foreach($wp_styles->to_do as $handle) {
            if (in_array($handle, $ignoredStyles)) {
                continue;
            }
            wp_dequeue_style($handle);
            wp_deregister_style($handle);
        }
        wp_enqueue_style('merged-styles',  get_stylesheet_directory_uri() . '/../../' . $fileName, [], $version);
        return;
    }
    $merged_script	= '';
    $httpClient = new GuzzleHttp\Client();
    foreach($wp_styles->to_do as $handle) {
        if (in_array($handle, $ignoredStyles)) {
            continue;
        }
        // Clean up url
        $src = strtok($wp_styles->registered[$handle]->src, '?');
        $js_file_path = $src;
        $merged_script .= PHP_EOL .'/** '. $handle .' */'. PHP_EOL;
        if (strpos($src, 'http') !== false || strpos($src, '//') !== false) {
            $site_url = site_url();
            if (strpos($src, $site_url) !== false) {
                $js_file_path = str_replace($site_url, '', $src);
                if (file_exists(ABSPATH . $js_file_path)) {
                    $merged_script .= PHP_EOL . file_get_contents(ABSPATH .'..'. $js_file_path) . PHP_EOL;
                } else {
                    throw new \Exception('file not found. ' . $js_file_path);
                }
            } else {
                $response = $httpClient->send(new \GuzzleHttp\Psr7\Request('GET', $js_file_path));
                $merged_script .= PHP_EOL . $response->getBody()->getContents() . PHP_EOL;
            }
        } else {
            if (file_exists(ABSPATH . $js_file_path)) {
                $merged_script .= PHP_EOL . file_get_contents(ABSPATH . $js_file_path) . PHP_EOL;
            } else {
                throw new \Exception('file not found. ' . $js_file_path);
            }
        }
        wp_dequeue_style($handle);
        wp_deregister_style($handle);
    }

    file_put_contents($merged_file_location, str_replace('  ', ' ', $merged_script));
    wp_enqueue_style('merged-styles',  get_stylesheet_directory_uri() . '/../../' . $fileName, [], $version);
}

function merge_all_scripts($compileOverrideActive) {
    global $wp_scripts, $wc_queued_js;

    /*
        #1. Reorder the handles based on its dependency,
            The result will be saved in the to_do property ($wp_scripts->to_do)
    */
    $ignoredScripts = [
        'jquery-ui-core', 'jquery-core', 'admin-bar', 'query-monitor', 'jquery-ui-widget', 'wc-add-to-cart',
        'wp-util', 'wc-add-to-cart-variation', 'jquery', 'cookie-notice-front', 'wc-single-product',
    ];
    $version = 3;
    $version = time();
    $wp_scripts->all_deps($wp_scripts->queue);
    $targetFile = "uploads/compiled.js";
    $merged_file_location = ABSPATH . '/wp-content/' . $targetFile;
    if (file_exists($merged_file_location) && !$compileOverrideActive) {
        foreach($wp_scripts->to_do as $handle) {
            if (in_array($handle, $ignoredScripts)) {
                continue;
            }
            wp_dequeue_script($handle);
            wp_deregister_script($handle);
        }
        wp_enqueue_script('merged-script',  get_stylesheet_directory_uri() . '/../../' . $targetFile, [], $version, true);
        return;
    }
    $merged_script	= '';
    $httpClient = new GuzzleHttp\Client();

    // Loop javascript files and save to $merged_script variable
    foreach($wp_scripts->to_do as $handle) {
        if (in_array($handle, $ignoredScripts)) {
            continue;
        }
        // Clean up url
        $src = strtok($wp_scripts->registered[$handle]->src, '?');
        $merged_script .= PHP_EOL .'/** '. $handle .' */'. PHP_EOL;

        if (strpos($src, 'http') !== false) {
            $site_url = site_url();

            $js_file_path = $src;
            if (strpos($src, $site_url) !== false) {
                $js_file_path = str_replace($site_url, '', $src);
                // Check for wp_localize_script
                $localize = '';
                if (@key_exists('data', $wp_scripts->registered[$handle]->extra)) {
                    $localize =  $wp_scripts->registered[$handle]->extra['data'] . ';';
                }
                if (file_exists(ABSPATH . $js_file_path)) {
                    $merged_script .= PHP_EOL . $localize . file_get_contents(ABSPATH .'..'. $js_file_path) . PHP_EOL;
                } else {
                    throw new \Exception('file not found. ' . $js_file_path);
                }
            } else {
                $response = $httpClient->send(new \GuzzleHttp\Psr7\Request('GET', $js_file_path));
                $merged_script .= PHP_EOL . $response->getBody()->getContents() . PHP_EOL;
            }
        } else {
            $js_file_path = ltrim($src, '/');
            // Check for wp_localize_script
            $localize = '';
            if (@key_exists('data', $wp_scripts->registered[$handle]->extra)) {
                $localize =  $wp_scripts->registered[$handle]->extra['data'] . ';';
            }
            if (file_exists(ABSPATH . $js_file_path)) {
                $merged_script .= PHP_EOL . $localize . file_get_contents(ABSPATH . $js_file_path) . PHP_EOL;
            } else {
                throw new \Exception('file not found. ' . $js_file_path);
            }

        }
        wp_dequeue_script($handle);
        wp_deregister_script($handle);
    }
    $merged_script .= $wc_queued_js . PHP_EOL;
    $wc_queued_js = '';

    file_put_contents($merged_file_location, str_replace('  ', '', $merged_script));
    wp_enqueue_script('merged-script',  get_stylesheet_directory_uri() . '/../../' . $targetFile, [], $version, true);
}

function add_async_attribute($tag, $handle) {
    $scripts_to_defer = array('merged-script');
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
