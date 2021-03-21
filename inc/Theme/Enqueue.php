<?php


namespace GF;


class Enqueue
{

    public function init()
    {
        add_action('wp_enqueue_scripts', [$this, 'addStyles']);
        add_action('wp_enqueue_scripts', [$this, 'addScripts']);
        add_action('admin_enqueue_scripts',[$this, 'addAdminStyles']);
        add_action('admin_enqueue_scripts',[$this, 'addAdminScripts']);
        $this->hardCodedActionsAndHooks();
    }

    /**
     * @TODO test this out
     */
    private function hardCodedActionsAndHooks()
    {
        add_action('wp_print_scripts', function() {
            wp_dequeue_script('wc-password-strength-meter');
        }, 10);

        // prevent bug with members plugin
        add_filter('members_check_parent_post_permission', function () { return false; });
    }

    public function addStyles()
    {
        wp_enqueue_style('font-awesome', 'https://use.fontawesome.com/releases/v5.1.1/css/all.css');
        wp_enqueue_style('bootstrap 4.1', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css');
// loads anyway
//        wp_enqueue_style('woocommerce-layout');
//        wp_enqueue_style('woocommerce-general');

        wp_enqueue_style('gf-style-reset', get_stylesheet_directory_uri() . '/assets/css/reset.css');
        wp_enqueue_style('gf-style', get_stylesheet_directory_uri() . '/style.css', ['woocommerce-layout'], '7e853');
        wp_enqueue_style( 'grid-list-layout', plugins_url( '/woocommerce-grid-list-toggle/assets/css/style.css'));
        wp_enqueue_style( 'grid-list-button', plugins_url( '/woocommerce-grid-list-toggle/assets/css/button.css'));
    }

    public function addScripts()
    {
        wp_enqueue_script('jquery', '', [], false, true);
        wp_enqueue_script('swiper-js', 'https://unpkg.com/swiper/swiper-bundle.min.js', array(), '1.0.0', true);
        wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js', array(), '', 'true');
        wp_enqueue_script('bootstrap-popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array(), '', 'true');
        wp_enqueue_script('clamp', get_stylesheet_directory_uri() . '/assets/js/3rd-party/clamp.min.js');
        wp_enqueue_script('cookie', get_stylesheet_directory_uri() . '/assets/js/jquery.cookie.js');
//        wp_enqueue_script('flexslider', plugins_url() . '/woocommerce/assets/js/flexslider/jquery.flexslider.min.js');
        wp_enqueue_script('zoom', plugins_url() . '/woocommerce/assets/js/zoom/jquery.zoom.min.js');
        wp_enqueue_script('photoswipe', plugins_url() . '/woocommerce/assets/js/photoswipe/jquery.photoswipe.min.js');
        wp_enqueue_script('photoswipe-ui-default', plugins_url() . '/woocommerce/assets/js/photoswipe/jquery.photoswipe-ui-default.min.js');
        wp_enqueue_script('wc-single-product', plugins_url() . '/woocommerce/assets/js/frontend/single-product.min.js', ['photoswipe']);

        wp_enqueue_script('grid-list-scripts', plugins_url( '/woocommerce-grid-list-toggle/assets/js/jquery.gridlistview.min.js'), ['jquery-ui-tabs']);
        wp_enqueue_script('gf-front-js', get_stylesheet_directory_uri() . '/assets/js/gf-front.js', [], '', true);

        //required in order for ajax to work !?
        wp_localize_script( 'gf-front-js', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php')));
    }

    public function addAdminStyles ()
    {
        wp_enqueue_style('gf-admin-style', get_stylesheet_directory_uri() . '/admin.css');
    }

    public function addAdminScripts ()
    {
        wp_enqueue_script('gf-admin-js', get_stylesheet_directory_uri() . '/assets/js/gf-admin.js');
    }
}