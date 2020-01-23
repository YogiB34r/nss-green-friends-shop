<?php
add_action('after_theme_setup', 'gf_theme_setup');

function remove_stubborn_js() {
    wp_dequeue_script('cookie');
    wp_deregister_script('cookie');
    wp_dequeue_script('grid-list-scripts');
    wp_deregister_script('grid-list-scripts');
}
//add_action('wp_print_scripts', 'remove_stubborn_js', 99999);

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
