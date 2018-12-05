<?php

namespace GF\ExternalBannerWidget;

//load_plugin_textdomain('gf-externalItemBannersWidget', '', plugins_url() . '/gf-externalItemBannersWidget/languages');

class ExternalBannerWidget
{
    private $wpdb;

    public function __construct(\wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
    }

    public function render_html()
    {
        $get_items_sql = "SELECT * FROM `wp_nss_external_banners_widget`";
        $data = $this->wpdb->get_results($get_items_sql);

        $template = 'vertical';
        $ref = 'blic';
        if (isset($_GET['template']) && $_GET['template'] === 'horizontal') {
            $template = 'horizontal';
        }
        if (isset($_GET['ref'])) {
            $ref = $_GET['ref'];
        }

        require(get_stylesheet_directory() . "/templates/externalBannersWidget/layout.phtml");

    }

    public function register_widget_options()
    {
        register_setting('gf-external-item-banners-widget-group', 'external-item-banners-widget-articles');
    }

    public function admin()
    {
        switch ($_GET['action']) {
            case 'create':
                $this->create();

                break;

            case 'update':
                $this->update();

                break;
        }

        $this->list();
    }

    public function list()
    {
        $get_items_sql = "SELECT * FROM `wp_nss_external_banners_widget`";
        $data = $this->wpdb->get_results($get_items_sql);

        require (get_template_directory() . "/templates/externalBannersWidget/adminList.phtml");
    }

    public function update()
    {
        if (
            isset($_POST['itemId']) && !empty($_POST['itemId']) &&
            isset($_POST['title']) && !empty($_POST['title']) &&
            isset($_POST['description']) && !empty($_POST['description']) &&
            isset($_POST['salePrice']) &&
            isset($_POST['regularPrice']) && !empty($_POST['regularPrice']) &&
            isset($_POST['categoryUrl']) && !empty($_POST['categoryUrl'])
        ) {
            $itemId = $_POST['itemId'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            $salePrice = str_replace(',', '',  $_POST['salePrice']);
            $regularPrice = str_replace(',', '',  $_POST['regularPrice']);
            $categoryUrl = $_POST['categoryUrl'];
            $product_id = wc_get_product_id_by_sku( $itemId );
            $itemUrl = get_permalink($product_id);
            $imageSrc = get_the_post_thumbnail_url($product_id, 'shop_catalog');

            if (isset($_POST['articleUpdate'])){
                $sql_update = "UPDATE wp_nss_external_banners_widget
                SET itemId = $itemId, title = '{$title}', description = '{$description}', salePrice = '{$salePrice}', 
                regularPrice = '{$regularPrice}', categoryUrl = '{$categoryUrl}', itemUrl = '{$itemUrl}', imageSrc = '{$imageSrc}'
                WHERE itemId LIKE $itemId";
                if ($this->wpdb->query($sql_update)) {
                    echo '<div class="notice notice-success is-dismissible"><p>Article updated!</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Failed to save article</p></div>';
                }
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>You must fill in all required field fields</p></div>';
        }
    }

    public function create()
    {
        //create
        if (isset($_POST['articleCreate']) || isset($_POST['articleUpdate'])) {
            if (
                isset($_POST['itemId']) && !empty($_POST['itemId']) &&
                isset($_POST['title']) && !empty($_POST['title']) &&
                isset($_POST['description']) && !empty($_POST['description']) &&
                isset($_POST['salePrice']) &&
                isset($_POST['regularPrice']) && !empty($_POST['regularPrice']) &&
                isset($_POST['categoryUrl']) && !empty($_POST['categoryUrl'])
            ) {
                $itemId = $_POST['itemId'];
                $title = $_POST['title'];
                $description = $_POST['description'];
                $salePrice = str_replace(',', '',  $_POST['salePrice']);
                $regularPrice = str_replace(',', '',  $_POST['regularPrice']);
                $categoryUrl = $_POST['categoryUrl'];
                $product_id = wc_get_product_id_by_sku( $itemId );
                $itemUrl = get_permalink($product_id);
                $imageSrc = get_the_post_thumbnail_url($product_id, 'shop_catalog');

                if (isset($_POST['articleCreate'])){
                    $sql_insert = "INSERT INTO wp_nss_external_banners_widget (itemId, title, description, salePrice, regularPrice, categoryUrl, itemUrl, imageSrc)
                    VALUES ({$itemId}, '{$title}', '{$description}', '{$salePrice}', '{$regularPrice}', '{$categoryUrl}', '{$itemUrl}', '{$imageSrc}')";
                    $insert = $this->wpdb->query($sql_insert);
                    echo '<div class="notice notice-success is-dismissible"><p>Article created!</p></div>';
                }

            } else {
                echo '<div class="notice notice-error is-dismissible"><p>You must fill in all required field fields</p></div>';
            }
        }
    }

    public function delete()
    {
        if(isset($_POST['articleDelete'])) {
            if(isset($_POST['itemId']) && !empty($_POST['itemId'])) {
                $itemId = $_POST['itemId'];
                $sql_delete = "DELETE FROM wp_nss_external_banners_widget WHERE itemId = {$itemId} LIMIT 1";
                $delete = $this->wpdb->query($sql_delete);
                echo '<div class="notice notice-success is-dismissible"><p>Article deleted!</p></div>';
            }
        }
    }
}

