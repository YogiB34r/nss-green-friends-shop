<?php

add_action('woocommerce_before_cart_table', 'gf_cart_display_tax_notice', 10);
function gf_cart_display_tax_notice()
{
    echo '<div class="gf-seller-info">
           <p>' . _e('Na ovoj stranici možete izabrati koliko komada nekog proizvoda želite da naručite a pomoću dugmeta "x" možete određeni proizvod izbaciti iz korpe. Sve prikazane cene su sa PDV-om. Troškovi dostave se dodatno plaćaju i prikazani su u poslednjem koraku kreiranja narudžbenice.', 'green-fiends') . '</p>
          </div>';
}

add_action('woocommerce_before_cart_table', 'gf_cart_display_seller_info', 11);
function gf_cart_display_seller_info()
{
    echo '<div class="gf-seller-info">
           <p>' . _e('Prodavac:', 'green-fiends') . ' 
                <span class="gf-seller-info-title">Non Stop Shop</span>
                <img src="/wp-content/themes/nss-green-friends-shop/assets/images/logo.png" alt="Non Stop Shop">
           </p>
          </div>';
}


/*
 * Display total cart weight on cart & order page
 */
add_action('woocommerce_review_order_before_shipping', 'bbloomer_print_cart_weight');
add_action('woocommerce_cart_totals_before_shipping', 'bbloomer_print_cart_weight');
function bbloomer_print_cart_weight($posted)
{
    global $woocommerce;
    $html =
        '<tr class="shipping">
			<th>' . __('Težina korpe') . '</th>
				<td data-title="Tezina">
					<span class="woocommerce-Price-amount amount">' . $woocommerce->cart->cart_contents_weight . '<span class="woocommerce-Price-currencySymbol">' . get_option('woocommerce_weight_unit') . '</span></span>
				</td>
		</tr>';

    if (is_cart() or is_checkout()) {
        echo $html;
    }
}


remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);

add_action('woocommerce_proceed_to_checkout', 'gf_button_proceed_to_checkout', 20);
function gf_button_proceed_to_checkout()
{
    echo '<div class="gf-button-proceed-to-checkout">
            <a href="' . wc_get_checkout_url() . '">
                <img src="/wp-content/themes/nss-green-friends-shop/assets/images/btn_order.png" alt="">
            </a>
          </div>';
}

add_filter('wc_empty_cart_message', 'custom_wc_empty_cart_message');
function custom_wc_empty_cart_message()
{
    $custum_html = '<div id="core" class="borderedWrapper">
                            <form name="allfrm" method="post" action="//www.nonstopshop.rs/cms/identification.php">
                                                                    
                                    <p class="titleSmall">' . __('Proizvodi u korpi:') . '</p>
    
                                    <p class="cartText3"><strong>' . __('U Vašoj korpi trenutno nema proizvoda.') . '</strong></p>
                                    
                                    <p class="cartText3">' . __('Da biste naručili proizvod(e) potrebno je da ih prethodno dodate u korpu.') . ' <br>
                                        ' . __('Proizvod se dodaje u korpu klikom na dugme "Stavi u korpu" koje se nalazi na stranici
                                        svakog proizvoda.') . ' </p><br>
                                    
                                    <img src="/wp-content/themes/nss-green-friends-shop/assets/images/btn_add_to_cart.png" alt="dodaj u korpu">
                                                                    
                                    <p class="cartText3 intro-text"><strong>napomena:</strong><br>
                                        ' . __('Pre nego što započnete sa naručivanjem potrebno je da se "registrujete". Link za registraciju se 
                                        nalazi na vrhu svake stranice. Registracija se obavlja samo jednom nakon čega će vas sistem 
                                        automatski prepoznati svaki sledeći put kada posetite sajt.') . '</p>
                            </form> 
                            </div>';
    echo $custum_html;
}

add_filter('woocommerce_return_to_shop_redirect', 'wc_empty_cart_redirect_url');
function wc_empty_cart_redirect_url()
{
    return get_home_url();
}


//They only way to translate shipping
add_filter('woocommerce_shipping_package_name', 'gf_translate_shipping', 10, 3);
function gf_translate_shipping($name, $package)
{
//    return sprintf( _nx( 'Dostava', 'Dostava %d', ( $i + 1 ), 'shipping packages', 'green-friends' ), ( $i + 1 ) );
    return 'Dostava';
}

//Checks for custom price in product meta
function getCustomShippingPrice(WC_Product $product)
{
    if ($product instanceof WC_Product_Variation) {
        $product = wc_get_product($product->get_parent_id());
    }

    $customCost = $product->get_meta('customShippingPrice', true);
    if (strlen($customCost) > 0) {
        return $customCost;
    }
    return false;
}

add_action('woocommerce_before_cart', 'customShippingPriceNotice',50);

function customShippingPriceNotice()
{
    $cartContents = WC()->cart->get_cart_contents();

    /** @var WC_Product $product */
    foreach ($cartContents as $cartContent) {
        $product = $cartContent['data'];
        if (getCustomShippingPrice($product)) {
            $html = '<p><b>'.$product->get_name().'</b> ima dodatnu cenu dostave i ona iznozi '. getCustomShippingPrice($product) . get_woocommerce_currency_symbol().'</p>';
            wc_print_notice($html, 'notice');
        }
    }
}

/**
 * Display shipping category and price
 */
add_filter('woocommerce_package_rates', 'customShippingRates', 10, 2);
function customShippingRates($rates, $package)
{
    $cartWeight = WC()->cart->cart_contents_weight;
    $cartContents = WC()->cart->get_cart_contents();
    $customCost = 0;

    /** @var WC_Product $product */
    foreach ($cartContents as $cartContent) {
        $product = $cartContent['data'];

        if ($cartContent['quantity'] > 1) {
            if (getCustomShippingPrice($product)) {
                for ($i = 1; $i <= $cartContent['quantity']; $i++) {
                    $customCost += (int)getCustomShippingPrice($product);
                    $productWeight = (float)$product->get_weight();

                    /*
                    Remove weight of product with special price from cart weight so items with normal shipping
                    cost can have valid shipping price based on its weight
                    */
                    $cartWeight -= $productWeight;
                }
            }
            continue;
        }

        if (getCustomShippingPrice($product)) {
            $customCost += (int)getCustomShippingPrice($product);
            $productWeight = (float)$product->get_weight();

            /*
            Remove weight of product with special price from cart weight so items with normal shipping
            cost can have valid shipping price based on its weight
            */
            $cartWeight -= $productWeight;
        }
    }


    if ($customCost > 0) {
        /** @var WC_Shipping_Rate $rate */
        foreach ($rates as $rate) {
            //If cart weight after deducting special products is 0 or less set all weight based cost to 0
            if ($cartWeight <= 0) {
                $rate->set_cost('0');
            }

            $cost = $rate->get_cost();
            $newCost = (int)$cost + $customCost;
            //Add custom product shipping cost to all shipping rates
            $rate->set_cost($newCost);

            //Remove weight based title if product in cart has custom shipping price
            $rate->set_label('Dostava');
        }
    }

    //Weight based rates
    if ($cartWeight <= 0.5) {
        if (isset($rates['flat_rate:3']))
            unset(
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);
    } elseif ($cartWeight > 0.5 and $cartWeight <= 2) {
        if (isset($rates['flat_rate:4']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);
    } elseif ($cartWeight > 2 and $cartWeight <= 5) {
        if (isset($rates['flat_rate:5']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);
    } elseif ($cartWeight > 5 and $cartWeight <= 10) {
        if (isset($rates['flat_rate:6']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);
    } elseif ($cartWeight > 10 and $cartWeight <= 20) {
        if (isset($rates['flat_rate:7']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);
    } elseif ($cartWeight > 20 and $cartWeight <= 30) {
        if (isset($rates['flat_rate:8']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);
    } elseif ($cartWeight > 30 and $cartWeight <= 50) {
        if (isset($rates['flat_rate:9']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:10']);
    } elseif ($cartWeight > 50) {
        if (isset($rates['flat_rate:10'])) {
            $myExtraWeight = $cartWeight - 50;
            $flatRate10Cost = $rates['flat_rate:10']->get_cost();
            $myNewPrice = $flatRate10Cost + (10 * $myExtraWeight);
            $rates['flat_rate:10']->set_cost($myNewPrice);
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9']);
        }
    }
    return $rates;
}

add_action('woocommerce_before_cart', 'gf_cart_limit_notice');
function gf_cart_limit_notice()
{
    global $woocommerce;
    if ($woocommerce->cart->total > 20000) {
        $message = 'OBAVEŠTENJE: Plaćanje pouzećem nije omogućeno za narudžbine koje iznose preko 20.000 din.';
        wc_print_notice($message, 'notice');
    }
}

add_filter('woocommerce_available_payment_gateways', 'bbloomer_unset_gateway_by_category');
function bbloomer_unset_gateway_by_category($available_gateways)
{
    global $woocommerce;
    $unset = false;
    if ($woocommerce->cart->total > 20000) {
        $unset = true;
    }
    if ($unset == true) unset($available_gateways['cod']);
    return $available_gateways;
}

add_action('wp_footer', 'gf_cart_refresh_update_qty');
function gf_cart_refresh_update_qty()
{
    if (is_cart()) {
        ?>
        <script type="text/javascript">
            jQuery('div.woocommerce').on('click', 'input.qty', function () {
                jQuery("[name='update_cart']").trigger("click");
            });
            jQuery('div.woocommerce').on('change', 'input.qty', function () {
                jQuery("[name='update_cart']").trigger("click");
            });
        </script>
        <?php
    }
}

add_action('woocommerce_cart_collaterals', 'gf_cart_page_extra_buttons');
function gf_cart_page_extra_buttons()
{
    if (!is_user_logged_in()) {
        echo '<a class="gf-cart-extra-buttons d-block p-3 mb-3" href="/moj-nalog">REGISTRUJ SE</a>
              <a class="gf-cart-extra-buttons d-block p-3" href="/placanje">NASTAVI KUPOVINU BEZ REGISTRACIJE</a>';
    }
}