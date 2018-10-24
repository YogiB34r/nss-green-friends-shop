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

function wc_empty_cart_redirect_url()
{
    return get_home_url();
}

add_filter('woocommerce_return_to_shop_redirect', 'wc_empty_cart_redirect_url');

//They only way to translate shipping
function gf_translate_shipping($name, $package)
{
//    return sprintf( _nx( 'Dostava', 'Dostava %d', ( $i + 1 ), 'shipping packages', 'green-friends' ), ( $i + 1 ) );
    return 'Dostava';
}

add_filter('woocommerce_shipping_package_name', 'gf_translate_shipping', 10, 3);

/**
  * Display shipping category and price
  */
add_filter('woocommerce_package_rates', 'bbloomer_woocommerce_tiered_shipping', 10, 2);
function bbloomer_woocommerce_tiered_shipping($rates, $package)
{
    if (WC()->cart->cart_contents_weight <= 0.5) {
        if (isset($rates['flat_rate:3']))
            unset(
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);
    } elseif (WC()->cart->cart_contents_weight > 0.5 and WC()->cart->cart_contents_weight <= 2) {
        if (isset($rates['flat_rate:4']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);
    } elseif (WC()->cart->cart_contents_weight > 2 and WC()->cart->cart_contents_weight <= 5) {
        if (isset($rates['flat_rate:5']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);
    } elseif (WC()->cart->cart_contents_weight > 5 and WC()->cart->cart_contents_weight <= 10) {
        if (isset($rates['flat_rate:6']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);
    } elseif (WC()->cart->cart_contents_weight > 10 and WC()->cart->cart_contents_weight <= 20) {
        if (isset($rates['flat_rate:7']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:8'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);
    } elseif (WC()->cart->cart_contents_weight > 20 and WC()->cart->cart_contents_weight <= 30) {
        if (isset($rates['flat_rate:8']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:9'],
                $rates['flat_rate:10']);
    } elseif (WC()->cart->cart_contents_weight > 30 and WC()->cart->cart_contents_weight <= 50) {
        if (isset($rates['flat_rate:9']))
            unset(
                $rates['flat_rate:3'],
                $rates['flat_rate:4'],
                $rates['flat_rate:5'],
                $rates['flat_rate:6'],
                $rates['flat_rate:7'],
                $rates['flat_rate:8'],
                $rates['flat_rate:10']);
    } elseif (WC()->cart->cart_contents_weight > 50) {
        if (isset($rates['flat_rate:10'])) {
            $cartWeight = WC()->cart->cart_contents_weight;
            $myExtraWeight = $cartWeight - 50;
            $myNewPrice = 500 + (10 * $myExtraWeight);
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