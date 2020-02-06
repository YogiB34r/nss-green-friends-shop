<?php

namespace GF\Util;


class AdminMenu
{
    public function init()
    {
        add_menu_page('NSS Panel', 'NSS Panel', 'administrator', 'nss-panel', function() {
            if (function_exists('getTabs')) {
                getTabs();
            }
        }, '', 3);
        remove_submenu_page('nss-panel','nss-panel');

        add_action('admin_menu', function() {
            global $wpdb;
            $widget = new \GF\ExternalBannerWidget\ExternalBannerWidget($wpdb);
            add_submenu_page('nss-panel', 'Carousel za partnerske sajtove', 'Carousel za partnere',
                'administrator', 'external_item_banners_widget', function () use ($widget) {
                $widget->admin();
            }, 20);
//            add_menu_page('Carousel za partnerske sajtove', 'Carousel za partnere', 'administrator', 'external_item_banners_widget', function () use ($widget) {
//                $widget->admin();
//            }, null, 666);
        });

        add_action('admin_menu', function (){
            add_submenu_page(
                'nss-panel', 'Import cenovnika', 'Import cenovnika', 'administrator', 'pricelist-import', function() {
                $pricelist = new \GF\Util\Pricelist();
                $pricelist->init();
            }, 25);
        });

        add_action('admin_menu', function (){
            add_submenu_page('nss-panel', 'Podešavanje pretrage', 'Podešavanje pretrage', 'edit_pages','gf-search-settings',
                function() {
                    $config = array(
                        'host' => ES_HOST,
                        'port' => 9200
                    );
                    $client = new \Elastica\Client($config);
                    $termSearch = new \GF\Search\Elastica\TermSearch($client);
//    $result = $term->getTerms();
                    require(get_stylesheet_directory() . "/templates/admin/search-settings.php");
            });
        });
    }

}