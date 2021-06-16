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
        add_filter('wp_insert_post_data', [$this, 'productSlugPrepareRedirect'], 99, 2);
//        add_action('woocommerce_process_product_meta', [$this, 'detectCategoryChange'], 99, 2);
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

    /**
     * @param array $data
     * @param array $postarr
     * @return array
     * @todo create redirect
     */
    public function productSlugPrepareRedirect(array $data, array $postarr): array
    {
        //If is doing auto-save
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $data;
        }
        //If is doing auto-save via AJAX
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return $data;
        }
        //We make redirect only for published post
        if ($postarr['post_status'] !== 'publish') {
            return $data;
        }
        //Only trigger on update of products
        if (!$postarr['save']) {
            return $data;
        }

        if (count($postarr) > 0) {
            unset($postarr['tax_input']['product_cat'][0]);  //It is always 0 and useless
            if ($postarr['post_type'] === 'product') {
                $product = wc_get_product($postarr['ID']);
                $oldPermalink = $product->get_permalink();
                $case = null;
                if ($product->get_slug() !== $data['post_name']) {
                    $newPermalink = str_replace($product->get_slug(), $data['post_name'], $oldPermalink);
                }
                if (count(array_diff($postarr['tax_input']['product_cat'], $product->get_category_ids())) > 0) {
                    $newPermalink = get_home_url();
                    $cats = get_terms([
                        'taxonomy' => 'product_cat',
                        'include' => $postarr['tax_input']['product_cat'],
                        'fields' => 'slugs'
                    ]);
                    //To avoid creating urls with specijalne promocije or akcija cats we use switch instead of array_key_last
                    switch (count($cats)){
                        case 0:
                            $this->redirectLog('Proizvod id=' . $product->get_id() . ' je snimljen bez kategorije');
                            $newPermalink.= '/uncategorized/' . $data['post_name'] . '/';
                            break;
                        case 1:
                            $newPermalink.= '/'.$cats[0].'/'.$data['post_name'].'/';
                            break;
                        case 2:
                            $newPermalink.= '/'.$cats[1].'/'.$data['post_name'].'/';
                            break;
                        default:
                            $newPermalink.= '/'.$cats[2].'/'.$data['post_name'].'/';
                    }
                }
            }
        }
        return $data;
    }

    public function redirectLog($msg): void
    {
        $file = WP_CONTENT_DIR . '/uploads/redirect.log';
        $msg = date('Y/m/d H:i:s') . ' : ' . $msg;
        $data = file_get_contents($file) . PHP_EOL . PHP_EOL . $msg;
        file_put_contents($file, $data);
    }
}

$product = new Product();