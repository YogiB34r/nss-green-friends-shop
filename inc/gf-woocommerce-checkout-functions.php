<?php
add_filter('woocommerce_checkout_fields', 'gf_woocommerce_billing_field_checkbox');
function gf_woocommerce_billing_field_checkbox($fields)
{
    $fields['billing']['billing_company_checkbox'] = array(
        'label' => __('Pravno Lice?', 'woocommerce'),
        'required' => false,
        'clear' => true,
        'type' => 'checkbox',
        'class' => array('gf-company-checkbox'),
    );

    return $fields;
}

add_filter('woocommerce_checkout_fields', 'gf_woocommerce_billing_field_pib');
function gf_woocommerce_billing_field_pib($fields)
{
    $fields['billing']['billing_pib'] = array(
        'label' => __('PIB', 'woocommerce'),
        'required' => false,
        'clear' => true, //
        'type' => 'text',
        'class' => array('gf-billing-field-pib'),
    );

    return $fields;
}

add_filter("woocommerce_checkout_fields", "gf_order_fields");
function gf_order_fields($fields)
{
    $order = array(
        "billing_company_checkbox",
        "billing_first_name",
        "billing_last_name",
        "billing_company",
        "billing_pib",
        "billing_address_1",
        "billing_address_2",
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
function gf_checkout_field_display_admin_order_meta($order)
{
    if (!empty(get_post_meta($order->get_id(), '_billing_pib', true))) {
        echo '<p class="gf-admin-orders-pib-field"><strong>' . __('Pib') . ':</strong> ' . get_post_meta($order->get_id(), '_billing_pib', true) . '</p>';
    }
}

//Action to add custom field to order emails disabled per user request do not delete maybe we will need it :)
//add_filter('woocommerce_email_order_meta_keys', 'gf_order_meta_keys');


add_filter('woocommerce_email_order_meta_fields', 'gf_order_meta_keys', 10, 3);
function gf_order_meta_keys($fields, $sent_to_admin, $order)
{
//    $keys[] = '_billing_pib';
//    return $keys;

    $value = get_post_meta($order->id, '_billing_pib', true);
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
function gf_checkbox_for_company()
{
    if (isset($_POST['billing_company_checkbox'])) {
        if (strlen($_POST['billing_pib']) != 8 && strlen($_POST['billing_pib']) != 0)
            wc_add_notice(__('PIB mora imati tacno osam cifara'), 'error');
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

add_action('woocommerce_order_details_after_order_table', 'nolo_custom_field_display_cust_order_meta', 10, 1);

function nolo_custom_field_display_cust_order_meta($order)
{
    $value = get_post_meta($order->get_id(), '_billing_pib', true);
    if (empty($value)) {
        return;
    }

    echo '<p><strong>' . _('Kompanija') . ':</strong> ' . $order->get_billing_company() . '</p>';
    echo '<p><strong>' . _('PIB') . ':</strong> ' . $value . '</p>';
}


add_filter('woocommerce_order_formatted_billing_address', 'woo_custom_order_formatted_billing_address', 10, 2);

function woo_custom_order_formatted_billing_address($address, $order)
{
//    $order = new WC_Order($order);

    $address = array(
        'first_name' => $order->get_billing_first_name(),
        'last_name' => $order->get_billing_last_name(),
        'address_1' => $order->get_billing_address_1(),
        'address_2' => $order->get_billing_address_2(),
        'city' => $order->get_billing_city(),
        'postcode' => $order->get_billing_postcode(),
    );

    return $address;
}


add_action('woocommerce_review_order_before_submit', 'gf_add_newsletter_checkbox_on_checkout');
function gf_add_newsletter_checkbox_on_checkout($checkout)
{
    woocommerce_form_field('gf_newsletter_checkout', array(
        'type' => 'checkbox',
        'class' => array('input-checkbox'),
        'label' => __('Želim da primam obaveštenja o specijalnim promocijama na email'),
        'required' => false,
    ), true);
}

add_action('woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta');

function my_custom_checkout_field_update_order_meta($order_id)
{
    if ($_POST['gf_newsletter_checkout']) update_post_meta($order_id, 'gf_newsletter_checkout', esc_attr($_POST['gf_newsletter_checkout']));
}

add_action('woocommerce_thankyou', 'my_test_f', 10, 1);
function my_test_f($orderid)
{
    $order = wc_get_order($orderid);
    $email = $order->get_billing_email();
    $newsletter_value = $order->get_meta('gf_newsletter_checkout', true);

    if ($newsletter_value == 1) {
        TNP::subscribe(['email' => $email, 'status' => 'C']);
    }
}

