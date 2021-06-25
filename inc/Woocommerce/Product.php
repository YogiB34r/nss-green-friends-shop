<?php


namespace GF\Woocommerce;


class Product
{

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->hooksAndFilters();
    }

    private function hooksAndFilters()
    {
//        add_filter('woocommerce_product_data_tabs', [$this,'feedProductTab']);//Registers new tab
//        add_action('woocommerce_product_data_panels', [$this,'feedImportSettings']); //Populate tab with inputs
        add_action('woocommerce_product_options_general_product_data', [$this, 'ignoreFeedPriceUpdatesCheckbox']);
        add_action('woocommerce_process_product_meta', [$this, 'saveFeedImportSettings'], 10,
            2);//Handle saving of inputs
    }

    public function feedProductTab($tabs)
    {
        $tabs['my-custom-tab'] = [
            'label' => 'Feed podešavanja',
            'target' => 'feedImportSettings',
        ];
        return $tabs;
    }

    public function ignoreFeedPriceUpdatesCheckbox()
    {
        echo '<div class="options_group">';
        woocommerce_wp_checkbox([
            'id' => 'ignoreFeedPriceUpdate',
            'label' => 'Ignoriši promenu cene',
            'description' => 'Ako je ovo štiklirano promene cena sa feed importa neće biti uvažene',
        ]);
        echo '</div>';
    }

    public function saveFeedImportSettings($id, $post)
    {
        if ($_POST['ignoreFeedPriceUpdate'] === 'yes') {
            update_post_meta($id, 'ignoreFeedPriceUpdate', 'yes');
        } else {
            update_post_meta($id, 'ignoreFeedPriceUpdate', 'no');
        }
    }
}

$product = new Product();