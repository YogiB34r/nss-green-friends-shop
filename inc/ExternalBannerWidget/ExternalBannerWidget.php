<?php

namespace GF\ExternalBannerWidget;

//load_plugin_textdomain('gf-externalItemBannersWidget', '', plugins_url() . '/gf-externalItemBannersWidget/languages');

class ExternalBannerWidget
{
    const CACHE_KEY = 'nss#Carousel#Widget';

    private $wpdb;

    private $cache;

    public function __construct(\wpdb $wpdb)
    {
        $this->cache = new \GF_Cache();
        $this->wpdb = $wpdb;
    }

    public function render_html($template = 'vertical', $source = 'blic.rs')
    {
        $key = sprintf('%s-%s-%s', self::CACHE_KEY, $template, $source);
        $html = $this->cache->redis->get($key);
        $html = false;
        if ($html === false) {
            $get_items_sql = "SELECT * FROM `wp_nss_external_banners_widget`";
            $data = $this->wpdb->get_results($get_items_sql);

            ob_start();
            require(get_stylesheet_directory() . "/templates/externalBannersWidget/layout.phtml");
            $html = ob_get_clean();
            $this->cache->redis->set($key, $html);
        }
        echo $html;
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

            case 'form':
                $this->form();

                break;

            case 'delete':
                $this->delete();
                $this->list();

                break;

            case 'preview':
                $this->render_html(@$_GET['template'], @$_GET['source']);

                break;

            default:
                $this->list();

                break;
        }
    }

    public function form()
    {
        $product = null;
        if (isset($_GET['itemId'])) {
            $sql = "SELECT * FROM `wp_nss_external_banners_widget` WHERE itemId = {$_GET['itemId']}";
            $product = $this->wpdb->get_results($sql)[0];
        }

        if (!empty($_POST)) {
            if (!$this->validate()) {
                echo '<div class="notice notice-error is-dismissible"><p>Morate popuniti sva polja</p></div>';
                $errors = $this->validationErrors;

                require (get_template_directory() . "/templates/externalBannersWidget/adminForm.phtml");

                return;
            }

            if (isset($_POST['articleCreate'])) {
                if ($this->create()) {
                    echo '<div class="notice notice-success is-dismissible"><p>Proizvod sacuvan!</p></div>';
                    $this->list();

                    return;
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Doslo je do greske prilikom kreiranja</p></div>';
                }
            } else {
                if ($this->update()) {
                    echo '<div class="notice notice-success is-dismissible"><p>Proizvod sacuvan!</p></div>';
                    $this->list();

                    return;
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>Doslo je do greske prilikom snimanja</p></div>';
                }
            }
        }

        require (get_template_directory() . "/templates/externalBannersWidget/adminForm.phtml");
    }

    public function list()
    {
        $get_items_sql = "SELECT * FROM `wp_nss_external_banners_widget`";
        $data = $this->wpdb->get_results($get_items_sql);

        require (get_template_directory() . "/templates/externalBannersWidget/adminList.phtml");
    }

    private function validate()
    {
        $errors = [];
        if ($_POST['title'] == '') {
            $errors['title'] = 'Polje naslov je obavezno';
        }

        if ($_POST['description'] == '') {
            $errors['description'] = 'Polje opis je obavezno';
        }
        if ($_POST['salePrice'] == '') {
            $errors['salePrice'] = 'Polje cena je obavezno';
        }
        if ($_POST['categoryUrl'] == '') {
            $errors['categoryUrl'] = 'Polje url kategorije je obavezno';
        }

        if (count($errors)) {
            $this->validationErrors = $errors;

            return false;
        }

        return true;
    }

    public function update()
    {
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
            echo $sql_update = "UPDATE wp_nss_external_banners_widget
            SET itemId = $itemId, title = '{$title}', description = '{$description}', salePrice = '{$salePrice}', 
            regularPrice = '{$regularPrice}', categoryUrl = '{$categoryUrl}', itemUrl = '{$itemUrl}', imageSrc = '{$imageSrc}'
            WHERE itemId LIKE $itemId";
            if ($this->wpdb->query($sql_update)) {
                return true;
            } else {
                var_dump($this->wpdb->last_error);

                return false;
            }
        }
    }

    public function create()
    {
        $title = htmlspecialchars($_POST['title']);
        $description = htmlspecialchars($_POST['description']);
        $salePrice = str_replace(',', '',  $_POST['salePrice']);
        $regularPrice = str_replace(',', '',  $_POST['regularPrice']);
        $categoryUrl = $_POST['categoryUrl'];
        $itemUrl = $_POST['itemUrl'];

        if (isset($_POST['articleCreate'])){
            $sql_insert = "INSERT INTO wp_nss_external_banners_widget (carouselitemId, title, description, salePrice, regularPrice, categoryUrl, itemUrl, imageSrc, sku, itemId, image)
            VALUES ('', '{$title}', '{$description}', '{$salePrice}', '{$regularPrice}', '{$categoryUrl}', '{$itemUrl}', '{$_POST['imageSrc']}', '{$_POST['sku']}', '{$_POST['itemId']}', '{$_POST['image']}')";
            $insert = $this->wpdb->query($sql_insert);
            if (!$insert) {
                echo 'nisam snimio';
                var_dump($this->wpdb->last_error);

                return false;
            }
            return true;
        }
    }

    protected function delete()
    {
        $itemId = $_GET['itemId'];
        $sql_delete = "DELETE FROM wp_nss_external_banners_widget WHERE itemId = {$itemId} LIMIT 1";
        $delete = $this->wpdb->query($sql_delete);
        if (!$delete) {
            var_dump($this->wpdb->last_error);
        }
        echo '<div class="notice notice-success is-dismissible"><p>Proizvod obrisan.</p></div>';
    }
}

