<?php

add_action('woocommerce_before_single_product', 'woocommerce_breadcrumb', 10);

remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);

remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);

add_action('woocommerce_single_product_summary', 'gf_get_single_product_meta', 9);
function gf_get_single_product_meta()
{
    /**
     * Single Product Meta
     *
     * This template can be overridden by copying it to yourtheme/woocommerce/single-product/meta.php.
     *
     * HOWEVER, on occasion WooCommerce will need to update template files and you
     * (the theme developer) will need to copy the new files to your theme to
     * maintain compatibility. We try to do this as little as possible, but it does
     * happen. When this occurs the version of the template file will be bumped and
     * the readme will list any important changes.
     *
     * @see        https://docs.woocommerce.com/document/template-structure/
     * @author        WooThemes
     * @package    WooCommerce/Templates
     * @version     3.0.0
     */

    if (!defined('ABSPATH')) {
        exit;
    }

    global $product;
    ?>
    <div class="product_meta">

        <?php do_action('woocommerce_product_meta_start'); ?>

        <?php if (wc_product_sku_enabled() && ($product->get_sku() || $product->is_type('variable'))) : ?>

            <span class="sku_wrapper"><?php esc_html_e('SKU:', 'woocommerce'); ?> <span
                        class="sku"><?php echo ($sku = $product->get_sku()) ? $sku : esc_html__('N/A', 'woocommerce'); ?></span></span>

        <?php endif; ?>

        <?php do_action('woocommerce_product_meta_end'); ?>

    </div>
    <?php
}

add_filter('woocommerce_get_stock_html', 'my_wc_hide_in_stock_message', 10, 2);
function my_wc_hide_in_stock_message($html, $product)
{
    if ($product->is_in_stock()) {
        return '';
    }

    return $html;
}

add_action('woocommerce_single_product_summary', 'gf_display_tax_notice', 11);
function gf_display_tax_notice(){
    echo '<p>Prikazana cena je sa uračunatim PDV-om.</p>';
}
add_action('woocommerce_single_product_summary', 'gf_display_offer_notice', 12);
function gf_display_offer_notice(){
    echo '<p>Ponuda važi dok traju zalihe.</p>';
}

add_filter( 'woocommerce_get_price_html', 'change_displayed_sale_price_html', 10, 2 );
function change_displayed_sale_price_html( $price, $product ) {
    // Only on sale products on frontend and excluding min/max price on variable products
    if( $product->is_on_sale() && ! is_admin() && ! $product->is_type('variable')){
        // Get product prices
        $regular_price = $product->get_regular_price(); // Regular price
        $sale_price = $product->get_price(); // Active price (the "Sale price" when on-sale)

        // "Saving price" calculation and formatting
        $saving_price = wc_price( $regular_price - $sale_price, ['decimals' => 0 ] );

        // "Saving Percentage" calculation and formatting
        $precision = 1; // Max number of decimals
        $saving_percentage = round( 100 - ( $sale_price / $regular_price * 100 ), 1 ) . '%';

        // Append to the formated html price
        $price .= sprintf( __('<p class="saved-sale">Ušteda: %s <em>(%s)</em></p>', 'woocommerce' ), $saving_price, $saving_percentage );
    }
    return $price;
}

add_filter( 'woocommerce_product_tabs', 'woo_new_product_tab' );
function woo_new_product_tab( $tabs ) {

    // Adds the new tab

    $tabs['narucivanje_tab'] = array(
        'title' 	=> __( 'Naručivanje i plaćanje', 'woocommerce' ),
        'priority' 	=> 50,
        'callback' 	=> 'woo_new_product_tab_content'
    );

    return $tabs;

}
function woo_new_product_tab_content() {
    $podrska = esc_url( get_permalink( get_page_by_title( 'podrška' ) ) );
    $link = __( 'ovde', 'nonstopshop' );
    $nacni_placanja = __( 'Detaljnije o načinima plaćanja', 'nonstopshop' );

    echo "
	<h3 class='reset footerTitle2'>Naručivanje telefonom</h3>
	<p class='cartText3'>
		Za <strong>naručivanje telefonom</strong> neophodno je da imate email adresu i da proizvod nije rasprodat:
	</p>
	<ul class='cartText3'>
		<li>Pozovete broj <strong>011/33-34-773 ili 011/33-34-681</strong> (radnim danima od <strong>09-17h</strong>)</li>
		<li>Navedete operateru kataloške brojeve proizvoda koje naručujete kao i količinu svakog od njih</li>
		<li>Kataloški broj ovog proizvoda je: <strong>3093024</strong></li>
		<li>Navedete operateru lične podatke i adresu isporuke</li>
		<li>Ukoliko kupujete kao pravno lice pripremite podatke firme: <strong>naziv, adresu i PIB broj</strong></li>
	</ul>
	<div>
		<h3 class='reset footerTitle2' id='delivery'>Vreme isporuke proizvoda i troškovi dostave</h3>
		<p class='defaultText'>
			Dostava se obavlja kurirskom službom i dodatno se naplaćuje.<br> Cenovnik troškova dostave možete videti
			<a href='". $podrska ."#dostava' target='_blank' class='defaultLink3'>
				". $link ."
			</a>.<br> Isporuka se vrši na teritoriji Srbije bez Kosova. <br> Okvirno vreme uručenja proizvoda = Vreme pripreme pošiljke: 3 - 5 dana radnih + Vreme dostave: 1 radni dan.
			<a title='Zašto?' class='moreInfoShipping' href='#'></a>
		</p>
	</div>
	<div>
		<h3 class='reset footerTitle2'>Ovaj proizvod se može platiti na jedan od sledećih načina:</h3>
		<ul class='defaultText'>
			<li>Platnim karticama: <strong>Visa, Master i Maestro</strong></li>
			<li>Uplatnicom na šalteru (virmanom)</li>
			<li>E-Banking</li>
		</ul>
		<a href='". $podrska ."' target='_blank' class='defaultLink3'>
			". $nacni_placanja ."
		</a>
	</div>
	";
}


remove_action( 'woocommerce_after_single_variation','woocommerce_single_product_summary', 20 );
