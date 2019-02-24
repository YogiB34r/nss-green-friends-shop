<?php
add_filter('woocommerce_checkout_fields', 'gf_woocommerce_billing_field_checkbox');
function gf_woocommerce_billing_field_checkbox($fields) {
    $fields['billing']['billing_company_checkbox'] = array(
        'label' => __('Pravno Lice?', 'woocommerce'),
        'required' => false,
        'clear' => true,
        'type' => 'checkbox',
        'class' => array('gf-company-checkbox'),
        'priority' => 10
    );

    return $fields;
}

add_filter('woocommerce_checkout_fields', 'gf_woocommerce_billing_field_pib');
function gf_woocommerce_billing_field_pib($fields) {
    $fields['billing']['billing_pib'] = array(
        'label' => __('PIB', 'woocommerce'),
        'required' => false,
        'clear' => true, //
        'type' => 'text',
        'class' => array('gf-billing-field-pib'),
        'priority' => 20
    );
    $fields['billing']['billing_company'] = array(
        'label' => __('Ime firme', 'woocommerce'),
        'required' => false,
        'clear' => true, //
        'type' => 'text',
        'class' => array('gf-billing-field-company'),
        'priority' => 20
    );

    return $fields;
}

add_filter("woocommerce_checkout_fields", "gf_order_fields");
function gf_order_fields($fields) {
    $order = array(
        "billing_company_checkbox",
        "billing_first_name",
        "billing_last_name",
        "billing_company",
        "billing_pib",
        "billing_address_1",
//        "billing_address_2",
        "billing_city",
        "billing_postcode",
        "billing_country",
        "billing_email",
        "billing_phone"

    );
    foreach ($order as $field) {
        $ordered_fields[$field] = $fields["billing"][$field];
    }

    $fields["billing"] = $ordered_fields;
    return $fields;
}

add_action('woocommerce_admin_order_data_after_billing_address', 'gf_checkout_field_display_admin_order_meta', 10, 1);
function gf_checkout_field_display_admin_order_meta($order) {
    if (!empty(get_post_meta($order->get_id(), '_billing_pib', true))) {
        echo '<p class="gf-admin-orders-pib-field"><strong>' . __('Pib') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_pib', true) . '</p>';
    }
}

//Action to add custom field to order emails
add_filter('woocommerce_email_order_meta_fields', 'gf_order_meta_keys', 10, 3);
function gf_order_meta_keys($fields, $sent_to_admin, $order) {
    $value = get_post_meta($order->get_id(), '_billing_pib', true);
    if (empty($value)) {
        return;
    }
    $fields['meta_key'] = array(
        'label' => __('Pib'),
        'value' => $value,
    );

    return $fields;
}

add_action('woocommerce_checkout_process', 'gf_checkbox_for_company');
function gf_checkbox_for_company() {
    if (isset($_POST['billing_company_checkbox'])) {
        if (strlen($_POST['billing_pib']) != 9 && strlen($_POST['billing_pib']) != 0)
            wc_add_notice(__('PIB mora imati tačno devet cifara'), 'error');
        if (strlen($_POST['billing_pib']) === 0) {
            wc_add_notice(__('Pib je obavezno polje'), 'error');
        }
        if (strlen($_POST['billing_company']) === 0) {
            wc_add_notice(__('Ime firme je obavezno polje'), 'error');
        }
    }
    if (!isset($_POST['billing_company_checkbox']) && !empty($_POST['billing_pib']) && !empty($_POST['billing_company'])) {
        wc()->session->set('gf_billing_pib', $_POST['billing_pib']);
        wc()->session->set('gf_billing_company', $_POST['billing_company']);
        unset($_POST['billing_company']);
        unset($_POST['billing_pib']);
    }
}

add_action('woocommerce_order_details_after_order_table', 'custom_field_display_cust_order_meta', 10, 1);
function custom_field_display_cust_order_meta($order) {
    $value = get_post_meta($order->get_id(), '_billing_pib', true);
    if (empty($value)) {
        return;
    }

    echo '<p><strong>' . _('Kompanija') . ':</strong> ' . $order->get_billing_company() . '</p>';
    echo '<p><strong>' . _('PIB') . ':</strong> ' . $value . '</p>';
}

add_filter('woocommerce_order_formatted_billing_address', 'woo_custom_order_formatted_billing_address', 10, 2);
function woo_custom_order_formatted_billing_address($address, $order) {
    $address = array(
        'first_name' => $order->get_billing_first_name(),
        'last_name' => $order->get_billing_last_name(),
        'address_1' => $order->get_billing_address_1(),
        'address_2' => $order->get_billing_address_2(),
        'city' => $order->get_billing_city(),
        'postcode' => $order->get_billing_postcode(),
        'priority' => 50
    );

    return $address;
}

add_action('woocommerce_review_order_before_submit', 'gf_add_newsletter_checkbox_on_checkout');
function gf_add_newsletter_checkbox_on_checkout($checkout) {
    woocommerce_form_field('gf_newsletter_checkout', array(
        'type' => 'checkbox',
        'class' => array('input-checkbox'),
        'label' => __('Želim da primam obaveštenja o specijalnim promocijama na email'),
        'required' => false,
        'priority' => 50
    ), true);
}

add_action('woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta');



function my_custom_checkout_field_update_order_meta($order_id) {
    if (isset($_POST['gf_newsletter_checkout']) && $_POST['gf_newsletter_checkout']) update_post_meta($order_id, 'gf_newsletter_checkout', esc_attr($_POST['gf_newsletter_checkout']));
}

//add_action('woocommerce_thankyou', 'gf_newsletter_on_checkout_page', 10, 1);
function gf_newsletter_on_checkout_page($orderid) {
    $order = wc_get_order($orderid);
    $email = $order->get_billing_email();
    $newsletter_value = $order->get_meta('gf_newsletter_checkout', true);

    if ($newsletter_value == 1) {
        TNP::subscribe(['email' => $email, 'status' => 'C']);
    }

//    if ($order->get_status() === "pending") {
    if ($order->get_status() === "on-hold") {
        if ($order->get_payment_method() === 'bacs') {
            $order->set_status('cekaseuplata');
        } elseif ($order->get_payment_method() === 'cod') {
            $order->set_status('u-pripremi');
        } else {

        }
        $order->save();
    }
}

add_action('woocommerce_before_checkout_shipping_form', 'gf_checkout_shipping_notice');
function gf_checkout_shipping_notice()
{
    echo '<div class ="gf-checkout-shipping-notice p-3" >Ukoliko se adresa za dostavu razlikuje od navedene u detaljima naplate, popunite sledeća polja:</div>';
}

add_filter('woocommerce_shipping_fields', 'gf_unrequire_wc_state_field');
function gf_unrequire_wc_state_field($fields) {
    $fields['shipping_state']['required'] = false;
    return $fields;
}

add_action('save_post_shop_order', 'gf_order_created', 666, 3);
function gf_order_created($post_id, $post, $update)
{
    $order = new WC_Order($post_id);
    if ($update && isset($_POST['gf_phone_order'])) {
        $phone_order_value = $_POST['gf_phone_order'];
        if (!$update) {
            if ($phone_order_value == 1) {
                update_post_meta($post_id, 'gf_order_created_method', 'Telefonom');
            } else {
                update_post_meta($post_id, 'gf_order_created_method', 'WWW');
            }
        } else {
            if ($phone_order_value == 1) {
                update_post_meta($post_id, 'gf_order_created_method', 'Telefonom');
            } else {
                update_post_meta($post_id, 'gf_order_created_method', 'WWW');
            }
        }
    }
}

add_action('woocommerce_review_order_before_submit', 'gf_add_www_field_on_checkout');
function gf_add_www_field_on_checkout($checkout) {
    woocommerce_form_field('gf_www_orders', array(
        'type' => 'hidden',
    ), true);
}

add_action('woocommerce_checkout_update_order_meta', 'gf_custom_checkout_field_update_order_meta_created_method');
function gf_custom_checkout_field_update_order_meta_created_method($order_id) {
    if (isset($_POST['gf_www_orders']) && $_POST['gf_www_orders']) update_post_meta($order_id, 'gf_order_created_method', 'WWW');
}

add_filter( 'woocommerce_checkout_fields', 'gf_change_city_field_to_dropdown' );
/**
 * Change the checkout city field to a dropdown field.
 */
function gf_change_city_field_to_dropdown( $fields ) {
    global $wpdb;
//    $sql = "SELECT * FROM wp_nss_city GROUP BY name ORDER BY gname ASC";
    $sql = "SELECT distinct(name) FROM wp_city ORDER BY name ASC";
    $result = $wpdb->get_results($sql);
    $cities = ['' => ''];
    foreach ($result as $city){
        $cities[$city->name] = $city->name;
    }
    
    $city_args = wp_parse_args( array(
        'type' => 'select',
        'options' => $cities,
        'input_class' => array(
            'wc-enhanced-select',
        )
    ), $fields['shipping']['shipping_city'] );
    $fields['shipping']['shipping_city'] = $city_args;
    $fields['billing']['billing_city'] = $city_args; // Also change for billing field

    wc_enqueue_js( "
	jQuery( ':input.wc-enhanced-select' ).filter( ':not(.enhanced)' ).each( function() {
		var select2_args = { minimumResultsForSearch: 5 };
		jQuery( this ).select2( select2_args ).addClass( 'enhanced' );
	});" );

    return $fields;
}


add_action( 'woocommerce_order_status_changed', 'gf_processing_notification', 10, 1 );
function gf_processing_notification($order_id, $checkout = null) {
    $order = wc_get_order( $order_id );
    if (in_array($order->get_status(), ['u-pripremi', 'cekaseuplata']) && $order->get_meta('backOrderUndo') == '') {
        // load the mailer class
        $mailer = WC()->mailer();
        $recipient = $order->get_billing_email();
        $from = 'prodaja@nonstopshop.rs';
        $headers = "Content-Type: text/html\r\n";
        $headers .= "From: NonStopShop <'{$from}'>\r\n";
        $headers .= "Reply-to: NonStopShop <'{$from}'>\r\n";
        $subject = 'Vaša nonstopshop.rs narudžbina je primljena';
        $template = 'emails/customer-processing-order.php';
        $content = wc_get_template_html($template, [
            'order' => $order,
            'email_heading' => $subject,
            'sent_to_admin' => true,
            'plain_text' => false,
            'email' => $mailer
        ]);

        $mailer->send($recipient, $subject, $content, $headers);
    }
}

add_filter('woocommerce_valid_order_statuses_for_payment', 'appendValidPaymentStatuses');
function appendValidPaymentStatuses($statuses) {
    array_push($statuses, 'u-obradi');
    array_push($statuses, 'pending');

    return $statuses;
}

add_filter('woocommerce_checkout_fields', 'gf_remove_apartment_checkout_fields');
function gf_remove_apartment_checkout_fields($fields) {
    unset($fields['billing']['billing_address_2']);
    unset($fields['shipping']['shipping_address_2']);

    return $fields;
}

add_filter('woocommerce_default_address_fields', 'gf_remove_unwanted_address_form_fields');
function gf_remove_unwanted_address_form_fields($fields) {
    unset($fields ['company']);
    unset($fields ['address_2']);

    return $fields;
}