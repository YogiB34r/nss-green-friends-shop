<?php
add_filter('woocommerce_checkout_fields', 'gf_woocommerce_billing_field_checkbox');
function gf_woocommerce_billing_field_checkbox($fields)
{
    $fields['billing']['billing_company_checkbox'] = array(
        'label' => __('Pravno Lice?', 'woocommerce'),
        'required' => false,
        'clear' => true,
        'type' => 'checkbox',
        'class' => array('gf-company-checkbox')
    );

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'gf_woocommerce_billing_field_pib');
function gf_woocommerce_billing_field_pib($fields)
{
    $fields['billing']['billing_pib'] = array(
        'label' => __('PIB', 'woocommerce'),
        'placeholder' =>__('Unesite pib firme'),
        'required' => false,
        'clear' => true, //
        'type' => 'text',
        'class' => array('gf-billing-field-pib')
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
        "billing_address_2",
        "billing_postcode",
        "billing_country",
        "billing_email",
        "billing_phone"

    );
    foreach($order as $field)
    {
        $ordered_fields[$field] = $fields["billing"][$field];
    }

    $fields["billing"] = $ordered_fields;
    return $fields;

}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'gf_checkout_field_display_admin_order_meta', 10, 1 );

function gf_checkout_field_display_admin_order_meta($order){
    echo '<p><strong>'.__('Pib').':</strong> ' . get_post_meta( $order->get_id(), '_billing_pib', true ) . '</p>';
}
add_filter('woocommerce_email_order_meta_keys', 'gf_order_meta_keys');

function gf_order_meta_keys( $keys ) {
    $keys[] = '_billing_pib';
    return $keys;
}
add_action('woocommerce_checkout_process', 'gf_billing_field_pib_process');
function gf_billing_field_pib_process() {
    if ( strlen($_POST['billing_pib']) != 8 && strlen($_POST['billing_pib']) != 0 )
        wc_add_notice( __( 'PIB mora imati tacno osam cifara' ), 'error' );
}