<?php


namespace GF\Util;


class FilterProductsByMeta
{
    public function init()
    {
        $this->setupMenuPage();
        add_action('wp_ajax_gfGetProducts', [$this, 'getProducts']);
        add_action('wp_ajax_bulkActionRemoveMeta', [$this, 'removeMeta']);
    }

    public function setupMenuPage()
    {
        add_action('admin_menu', function (){
            add_submenu_page(
                'nss-panel', 'Filtriraj proizvode', 'Filtriraj proizvode',
                'administrator', 'product-filters', [$this, 'menuPageView']);
        });
    }

    public function menuPageView()
    {
        include 'templates/list.phtml';
    }

    public function getProducts()
    {
        global $wpdb;
        $sql = "SELECT ID,post_name FROM $wpdb->posts WHERE ID IN(SELECT post_id FROM $wpdb->postmeta 
        WHERE meta_key = '{$_GET['metaField']}' AND meta_value = 'yes') LIMIT {$_GET['length']} OFFSET {$_GET['start']}";
        $products = $wpdb->get_results($sql,ARRAY_A);
        $totalCount = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts
        WHERE ID IN(SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '{$_GET['metaField']}' AND meta_value = 'yes')");

        foreach ($products as $key => $product) {
            $products[$key]['id'] = sprintf('<input class="individualCheckbox" type="checkbox" data-id="%s">',
                $product['ID']);
            $products[$key]['post_name'] = sprintf('<a href="%s">%s</a>',get_edit_post_link($product['ID']), $product['post_name']);
            $products[$key]['sku'] = get_post_meta($product['ID'],'_sku', true);
        }
        $data = [
            'draw' => (int)$_GET['draw'],
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $totalCount,
            'data' => $products
        ];
        echo wp_json_encode($data);
        wp_die();
    }

    public function removeMeta(): void
    {
       switch ($_POST['bulkAction']){
           case 'soloInCartRemove':
               foreach ($_POST['productIds'] as $productId){
                   update_post_meta($productId,'soloInCart','no');
               }
               break;
           case 'disableCodRemove':
               foreach ($_POST['productIds'] as $productId){
                   update_post_meta($productId,'disableCod','no');
               }
               break;
           case 'disableIgnorePriceUpdates':
               foreach ($_POST['productIds'] as $productId){
                   update_post_meta($productId,'ignoreFeedPriceUpdate','no');
               }
               break;
       }
    }
}

$filterProducts = new FilterProductsByMeta();
$filterProducts->init();