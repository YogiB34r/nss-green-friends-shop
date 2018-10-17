<?php
function gf_wc_breadcrumbs_single_product()
{
    woocommerce_breadcrumb();
}

add_action('woocommerce_before_single_product', 'gf_wc_breadcrumbs_single_product', 10);

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

            <span class="sku_wrapper">Kataloški broj proizvoda:<?php esc_html__('SKU:', 'woocommerce'); ?> <span
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
function gf_display_tax_notice()
{
    echo '<p>' . __('Prikazana cena je sa uračunatim PDV-om.', 'green-fiends') . '</p>';
}

add_action('woocommerce_single_product_summary', 'gf_display_offer_notice', 12);
function gf_display_offer_notice()
{
    echo '<p>' . __('Ponuda važi dok traju zalihe', 'green-fiends') . '</p>';
}

add_filter('woocommerce_get_price_html', 'change_displayed_sale_price_html', 10, 2);
function change_displayed_sale_price_html($price, WC_Product $product)
{
    // Only on sale products on frontend
    // Get product prices
    if ($product->is_on_sale() && !is_admin()) {
        if ($product->is_type('variable')) {
            $regular_price = $product->get_variation_regular_price(); // Regular price
            $sale_price = $product->get_price(); // Active price (the "Sale price" when on-sale)
        } else {
            $regular_price = $product->get_regular_price(); // Regular price
            $sale_price = $product->get_price(); // Active price (the "Sale price" when on-sale)
        }
        // "Saving price" calculation and formatting
        $saving_price = wc_price($regular_price - $sale_price, ['decimals' => 0]);

        // "Saving Percentage" calculation and formatting
        $precision = 1; // Max number of decimals
        $saving_percentage = round(100 - ($sale_price / $regular_price * 100), 1) . '%';

        // Append to the formated html price
        $price .= sprintf(__('<p class="saved-sale">Ušteda: %s <em>(%s)</em></p>', 'woocommerce'), $saving_price, $saving_percentage);
    }
    return $price;
}


function woo_new_product_tab_content()
{
    $podrska = esc_url(get_permalink(get_page_by_title('podrška')));
    $link = __('ovde', 'nonstopshop');
    $nacni_placanja = __('Detaljnije o načinima plaćanja', 'nonstopshop');

    echo "
	<h3 class='reset footerTitle2'>" . __('Naručivanje telefonom', 'green-fiends') . "</h3>
	<p class='cartText3'>
		Za <strong>naručivanje telefonom</strong>" . __('neophodno je da imate email adresu i da proizvod nije rasprodat:', 'green-fiends') . "
	</p>
	<ul class='cartText3'>
		<li>" . __('Pozovete broj') . " <strong>011/33-34-773 ili 011/33-34-681</strong> (radnim danima od <strong>09-17h</strong>)</li>
		<li>" . __('Navedete operateru kataloške brojeve proizvoda koje naručujete kao i količinu svakog od njih') . " </li>
		<li>" . __('Navedete operateru lične podatke i adresu isporuke') . "</li>
		<li>" . __('Ukoliko kupujete kao pravno lice pripremite podatke firme:') . "<strong>naziv, adresu i PIB broj</strong></li>
	</ul>
	<div>
		<h3 class='reset footerTitle2' id='delivery'>" . __('Vreme isporuke proizvoda i troškovi dostave') . "</h3>
		<p class='defaultText'>
			" . __('Dostava se obavlja kurirskom službom i dodatno se naplaćuje.') . "<br> Cenovnik troškova dostave možete videti
			<a href='" . $podrska . "#troskovi_dostave' target='_blank' class='defaultLink3'>
				" . $link . "
			</a>.<br>" . __('Isporuka se vrši na teritoriji Srbije bez Kosova.') . "<br> " . __('Okvirno vreme uručenja proizvoda = Vreme pripreme pošiljke: 3 - 5 dana radnih + Vreme dostave: 1 radni dan.') . "
			<a title='Zašto?' class='moreInfoShipping' href='#'></a>
		</p>
	</div>
	<div>
		<h3 class='reset footerTitle2'>" . __('Ovaj proizvod se može platiti na jedan od sledećih načina:') . "</h3>
		<ul class='defaultText'>
			<li>" . __('Platnim karticama:') . " <strong>Visa, Master i Maestro</strong></li>
			<li>" . __('Uplatnicom na šalteru') . " (virmanom)</li>
			<li>E-Banking</li>
		</ul>
		<a href='" . $podrska . "#nacini_placanja' target='_blank' class='defaultLink3'>
			" . $nacni_placanja . "
		</a>
	</div>
	";
}

remove_action('woocommerce_after_single_variation', 'woocommerce_single_product_summary', 20);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
add_action('woocommerce_before_single_product', 'woocommerce_template_single_title', 11);

add_filter('woocommerce_product_tabs', 'woo_new_product_tab');
function woo_new_product_tab($tabs) {
    // Adds the new tab
    $tabs['narucivanje_tab'] = array(
        'title' => __('Naručivanje i plaćanje', 'woocommerce'),
        'priority' => 50,
        'callback' => 'woo_new_product_tab_content'
    );

    return $tabs;
}

/**
 * Customize product data tabs
 */
add_filter('woocommerce_product_tabs', 'woo_custom_description_tab', 98);
function woo_custom_description_tab($tabs){
    $tabs['description']['callback'] = 'woo_custom_description_tab_content';    // Custom description callback

    return $tabs;
}
function woo_custom_description_tab_content(){
    global $product;
//    global $post;
    echo '<p>' . htmlspecialchars_decode($product->get_description()) . '</p>';
    echo '<p>&nbsp</p>';
    echo nl2br('<p>' . get_post_meta($product->get_id(), 'features', true) . '</p>');
}


add_action('woocommerce_after_single_product_summary', 'gf_display_social_media_share_button', 11);
function gf_display_social_media_share_button()
{
    $post = get_queried_object();
    $title = $post->post_title;
    $link = $post->guid;
    $media = get_the_post_thumbnail_url($post->ID);
    $html = '<div class="gf-social-share-buttons mb-4">';
    $html .= '<div class="gf-social-share-button-single mr-2 gf-social-share-twitter"><a href="http://twitter.com/intent/tweet?status='.$title.'+'.$link.'" target="_blank"><i class="fab fa-twitter"></i></a></div>';
    $html .= '<div class="gf-social-share-button-single mr-2 gf-social-share-facebook"><a href="http://www.facebook.com/share.php?u='.$link.'&title='.$title.'" target="_blank"><i class="fab fa-facebook-f"></i></a></div>';
    $html .= '<div class="gf-social-share-button-single mr-2 gf-social-share-google"><a href="https://plus.google.com/share?url='.$link.'" target="_blank"><i class="fab fa-google-plus-g"></i></a></div>';
    $html .= '<div class="gf-social-share-button-single mr-2 gf-social-share-pinterest"><a href="http://pinterest.com/pin/create/bookmarklet/?media='.$media.'&url='.$link.'&is_video=false&description='.$title.'" target="_blank"><i class="fab fa-pinterest-p"></i></a></div>';
    $html .= '</div>';

    echo $html;

}
