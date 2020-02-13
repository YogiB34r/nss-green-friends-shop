<?php
namespace GF\Woocommerce;
class WooFunctions
{
    public function __construct()
    {
        $this->run();
    }

    private function run()
    {
        add_filter('woocommerce_currency_symbol', [$this, 'change_existing_currency_symbol'], 10, 2);
        add_action('woocommerce_save_account_details_errors', [$this, 'wooc_validate_custom_field'], 10, 2);
        add_action('woocommerce_before_account_navigation', [$this, 'gf_my_account_shop_button'], 1);
        add_filter('woocommerce_account_menu_items', [$this, 'gf_remove_my_account_links']);
        add_filter('woocommerce_catalog_orderby', [$this, 'wc_customize_product_sorting']);
        add_filter('woocommerce_billing_fields', [$this, 'wpb_custom_billing_fields']);

        //Cod disable
        add_filter('woocommerce_available_payment_gateways', [$this, 'restrictCod'], 10, 1);//Checks if product has Cod disabled
        add_action('woocommerce_product_options_shipping', [$this, 'disableCodCheckbox']);//Adds cod checkbox
        add_action('woocommerce_process_product_meta', [$this, 'saveCodCheckbox'], 10, 2);//Saves cod checkbox

        //Custom shipping price
        add_action('woocommerce_product_options_shipping', [$this, 'customShippingPrice']);//Adds cod checkbox
        add_action('woocommerce_process_product_meta', [$this, 'saveCustomShippingPrice'], 10, 2);//Saves cod checkbox

        //Generate sku
        add_action('save_post', [$this, 'validateSku'], 10, 2);
    }


    function change_existing_currency_symbol($currency_symbol, $currency)
    {
        return 'din.';
    }


    function wooc_validate_custom_field($args, $user)
    {
        $user_id = $user->ID;
        $user_pass_hash = get_user_by('id', $user_id)->user_pass;
        if (isset($_POST['password_current']) && !empty($_POST['password_current'])) {
            $current_pass = $_POST['password_current'];
            $passowrd_check = wp_check_password($current_pass, $user_pass_hash, $user_id);
            if (isset($_POST['password_1']) && $passowrd_check == 'true') {
                if (strlen($_POST['password_1']) < 5)
                    $args->add('error', __('Lozinka mora sadržati minimum 5 karaktera!', 'woocommerce'), '');
            }
        }
    }


    function gf_my_account_shop_button()
    {
        global $wp;
        $request = explode('/', $wp->request);
        $page = end($request);

        $user = wp_get_current_user();
        $args = array(
            'customer_id' => $user->ID,
        );
        $orders = wc_get_orders($args);
        $class = '';
        if ($page == 'narudzbine' && empty($orders)) {
            $class = 'd-none';
        }
        echo '<div class="gf-welcome-wrapper mb-3">';
        echo '<a class="gf-shop-button ' . $class . '" href="/">Kreni u kupovinu</a>';
        echo '<div class="gf_login_notice py-3 px-1 mt-0 mb-4"><p class="mb-0">Prilikom prijave možete koristiti <strong>korisničko ime</strong> ili <strong>email adresu</strong>.</p>
            <div class="mt-3 mb-1"><strong>Korisničko ime: </strong>' . $user->user_login . '</div>
            <div><strong>Email adresa: </strong>' . $user->user_email . '</div>
            </div>';
        echo '<div class="mb-2">';
        printf(
            __('Hello %1$s (not %1$s? <a href="%2$s">Log out</a>)', 'woocommerce'),
            '<strong>' . esc_html($user->display_name) . '</strong>',
            esc_url(wc_logout_url(wc_get_page_permalink('myaccount')))
        );
        echo '</div>';
        printf(
            __('From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">shipping and billing addresses</a>, and <a href="%3$s">edit your password and account details</a>.', 'woocommerce'),
            esc_url(wc_get_endpoint_url('orders')),
            esc_url(wc_get_endpoint_url('edit-address')),
            esc_url(wc_get_endpoint_url('edit-account'))
        );
        echo '</div>';
    }


    function gf_remove_my_account_links($menu_links)
    {
        unset($menu_links['dashboard']); // Addresses

        return $menu_links;
    }


    function wc_customize_product_sorting($sorting_options)
    {
        $sorting_options = array(
            'menu_order' => __('Sorting', 'woocommerce'),
            'popularity' => __('Sort by popularity', 'woocommerce'),
            'rating' => __('Sort by average rating', 'woocommerce'),
            'date' => __('Sort by newness', 'woocommerce'),
            'price' => __('Sort by price: low to high', 'woocommerce'),
            'price-desc' => __('Sort by price: high to low', 'woocommerce'),
        );

        return $sorting_options;
    }

    function wpb_custom_billing_fields($fields = array())
    {
        unset($fields['billing_state']);

        return $fields;
    }

    /**
     * Checks if disable cod is active for product
     */

    function restrictCod($available_gateways)
    {
        // Not in backend (admin)
        if (is_admin())
            return $available_gateways;

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = wc_get_product($cart_item['product_id']);
            $disableCod = $product->get_meta('disableCod', true);


            if ($disableCod === 'yes')
                unset($available_gateways['cod']); // unset 'cod'

        }
        return $available_gateways;
    }

    /**
     * Adds checkbox to product shipping tab for disabling cod
     */
    function disableCodCheckbox()
    {
        echo '<div class="options_group">';

        woocommerce_wp_checkbox(array(
            'id' => 'disableCod',
            'value' => get_post_meta(get_the_ID(), 'disableCod', true),
            'label' => 'Disable COD',
            'desc_tip' => true,
            'description' => 'If checked disables COD payment options when this product is in cart',
        ));

        echo '</div>';
    }

    /**
     * Saves checkbox for disabling cod
     */
    function saveCodCheckbox($id, $post)
    {
        update_post_meta($id, 'disableCod', $_POST['disableCod']);
    }

    /**
     * Creates input for custom shipping price in shipping tab on product edit page
     */
    function customShippingPrice()
    {
        echo '<div class="options_group">';

        woocommerce_wp_text_input(array(
            'id' => 'customShippingPrice',
            'value' => get_post_meta(get_the_ID(), 'customShippingPrice', true),
            'label' => 'Custom shipping price',
            'desc_tip' => true,
            'description' => 'This field changes product shipping price',
        ));

        echo '</div>';
    }

    /**
     * Saves custom shipping price value
     */
    function saveCustomShippingPrice($id, $post)
    {
        update_post_meta($id, 'customShippingPrice', $_POST['customShippingPrice']);
    }


    public function validateSku($id, $post)
    {
        if (isset($_POST['_sku'])) {
            if ($_POST['_sku'] === "") {
                $this->autoGenerateSku();
            } else {
                $productDb = wc_get_product_id_by_sku($_POST['_sku']);
                if ($productDb === $_POST['post_ID']){
                    $product = wc_get_product($_POST['post_ID']);
                    $skuOld = wc_get_product($productDb)->get_sku();
                    $skuNew = $product->get_sku();
                    if ($skuOld === $skuNew){
                        return;
                    } else {
                        $this->preventDuplicateSku();
                    }
                }
            }
        }
    }

    private function autoGenerateSku ()
    {
        $exit = false;
        $i = 0;
        while ($exit === false) {
            if ($i > 0) {
                $counter = $i;
            } else {
                $counter = '';
            }

            if (strlen(wc_get_product_id_by_sku($_POST['post_ID'] . $counter) === 0)) {
                $_POST['_sku'] = $_POST['post_ID'];
                $exit = true;
            } else {
                $_POST['_sku'] = $_POST['post_ID'] . $i;
                $i++;
            }
        }
    }

    private function preventDuplicateSku()
    {
        $exit = false;
        $i = 0;
        while ($exit === false) {
            if ($i > 0) {
                $counter = $i;
            } else {
                $counter = '';
            }

            if (strlen(wc_get_product_id_by_sku($_POST['_sku'] . $counter) === 0)) {
                $exit = true;
            } else {
                $_POST['_sku'] = $_POST['_sku'] . $i;
                $i++;
            }
        }
    }
}