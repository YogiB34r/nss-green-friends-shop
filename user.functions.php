<?php

remove_filter('authenticate', 'wp_authenticate_username_password');
add_filter('authenticate', 'gf_authenticate_username_password', 20, 3);
/**
 * Remove Wordpress filer and write our own with changed error text.
 */
function gf_authenticate_username_password($user, $username, $password) {
    if (is_a($user, 'WP_User'))
        return $user;

    if (empty($username) || empty($password)) {
        if (is_wp_error($user)) {
            return $user;
        }
        $error = new WP_Error();

        if (empty($username))
            return new WP_Error('invalid_username', sprintf(__('<strong>GREŠKA</strong>: Polje korisničko ime ne može biti prazno.'), wp_lostpassword_url()));

        if (empty($password))
            return new WP_Error('invalid_username', sprintf(__('<strong>GREŠKA</strong>: Polje lozinka ne može biti prazno.'), wp_lostpassword_url()));

        return $error;
    }
    $user = get_user_by('email', $username);

    if (!$user)
        return new WP_Error('invalid_username', sprintf(__('<strong>GREŠKA</strong>: Nepostojeće korisničko ime ili email. <a href="%s" title="Lozinka izgubljena">Izgubili ste lozinku</a>?'), wp_lostpassword_url()));

    if (get_user_meta($user->ID, 'migrated', true) == 1) {
        return gf_migrate_user_password($user, $password);
    } else {
        if (!wp_check_password($password, $user->user_pass, $user->ID))
            return new WP_Error('incorrect_password', sprintf(__('<strong>GREŠKA</strong>: Lozinka koju ste uneli za korisničko ime <strong>%1$s</strong> nije ispravna. <a href="%2$s" title="Lozinka izgubljena">Izgubili ste lozinku</a>?'),
                $user->user_login, wp_lostpassword_url()));

        $user = apply_filters('wp_authenticate_user', $user, $password);
    }

    if (is_wp_error($user))
        return $user;

    return $user;
}

/**
 * Migrate old user's password to new algorithm by checking with old version first, then updating password if ok.
 *
 * @param $user
 * @param $password
 * @return WP_Error|WP_User
 */
function gf_migrate_user_password($user, $password) {
    $salt = 'd@uy/o%b^';
    $passwordHash = $salt . md5($salt . $password);
    $hasher = new PasswordHash(8, true);
    if ($hasher->CheckPassword($passwordHash, $user->user_pass)) {
        wp_set_password($password, $user->ID);
        update_user_meta($user->ID, 'migrated', 2, 1);

        return $user;
    } else {
        return new WP_Error('incorrect_password',
            sprintf(
            /* translators: %s: user name */
                __('<strong>GREŠKA</strong>: Lozinka koju ste uneli za korisničko ime %s nije ispravna.'),
                '<strong>' . $user->data->user_login . '</strong>'
            ) .
            ' <a href="' . wp_lostpassword_url() . '">' .
            __('Izgubili ste lozinku?') .
            '</a>'
        );
    }
}

add_action('validate_password_reset', 'gf_validate_password_reset', 10, 2);
function gf_validate_password_reset($errors, $user)
{
    if (strlen($_POST['password_1']) < 5) {
        $errors->add('woocommerce_password_error', __('Lozinka mora imati minimum 6 karaktera.'));
    } // adding ability to set maximum allowed password chars -- uncomment the following two (2) lines to enable that
    elseif (strlen($_POST['password_1']) > 64)
        $errors->add('woocommerce_password_error', __('Lozinka ne može imati više od 64 karaktera.'));
    return $errors;
}

// Disable W3TC footer comment for everyone but Admins (single site & network mode)
if (!current_user_can('activate_plugins')) {
    add_filter('w3tc_can_print_comment', function ($w3tc_setting) {
        return false;
    }, 10, 1);
}

function action_woocommerce_register_form()
{
    ?>
    <div class="gf-wc-registration-info">
        <div class="woocommerce-info ">
            <p>Podaci o Vašem nalogu biće poslati na unetu email adresu</p>
        </div>
    </div>
    <?php
}

add_action('woocommerce_register_form', 'action_woocommerce_register_form', 20, 10);

//Custom addd to cart message
add_filter('wc_add_to_cart_message_html', '__return_null');
add_filter('wc_add_to_cart_message_html', 'gf_custom_add_to_cart_message', 10, 2);
function gf_custom_add_to_cart_message($message)
{
    if (isset($_POST['quantity']) && isset($_POST['add-to-cart'])) {
        $qty = $_POST['quantity'];
        $product_id = $_POST['add-to-cart'];
        $product_title = wc_get_product($product_id)->get_name();
        if ($qty <= 1) {
            $message = '&ldquo;' . $product_title . '&rdquo; je dodat u Vašu korpu.';
        } else {
            $message = $qty . ' &times; ' . '&ldquo;' . $product_title . '&rdquo; je dodat u Vašu korpu.';
        }
    }
    $cart_link = '<a href = "' . wc_get_page_permalink('cart') . '" class="button wc-forward" >Pogledaj korpu</a >';
    $message .= $cart_link;

    return $message;
}




//function remove_country_field_billing($fields)
//{
//    unset($fields['billing_country']);
//    unset($fields['billing_state']);
//    return $fields;
//
//}
//add_filter('woocommerce_billing_fields', 'remove_country_field_billing');
//function remove_country_field_shipping($fields)
//{
//    unset($fields['shipping_country']);
//    unset($fields['shipping_state']);
//    return $fields;
//}
//add_filter('woocommerce_shipping_fields', 'remove_country_field_shipping');

//function custom_override_checkout_fields( $fields ) {
//    unset($fields['billing']['billing_country']);
//    unset($fields['shipping_country']);
//
//    return $fields;
//}
//add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
