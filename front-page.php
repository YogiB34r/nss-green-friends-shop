<?php get_header();
$file = file_get_contents(__DIR__ . '/users1.csv');
$lines = explode(PHP_EOL, $file);
unset($lines[0]);
global $wpdb;
foreach ($lines as $line){
    $data = explode(',', $line);

    $username = '';
    $email = '';
    $password = '';
    $first_name = '';
    $billing_first_name = '';
    $billing_company = '';
    $billing_pib = '';
    $billing_mb = '';
    $billing_address_1 = '';
    $billing_city = '';
    $billing_postcode = '';
    $billing_phone = '';

    $shipping_first_name = '';
    $shipping_company = '';
    $shipping_address_1 = '';
    $shipping_city = '';
    $shipping_postcode = '';
    $shipping_phone = '';

    $billing_country = 'RS';
    $shipping_country = 'RS';


    $age_range = '';
    $meta_sex = '';
    $migrated = '';

    if (!empty($data[0])) {
        $username = $data[0];
    }
    if (!empty($data[1])) {
        $email = $data[1];
    }
    if (!empty($data[2])) {
        $password = $data[2];
    }
    if (!empty($data[3])) {
        $first_name = $data[3];
    }
    if (!empty($data[4])) {
        $billing_first_name = $data[4];
    }
    if (!empty($data[5])) {
        $billing_company = $data[5];
    }
    if (!empty($data[6])) {
        $billing_pib = $data[6];
    }
    if (!empty($data[7])) {
        $billing_mb = $data[7];
    }
    if (!empty($data[8])) {
        $billing_address_1 = $data[8];
    }
    if (!empty($data[8])) {
        $billing_address_1 = $data[8];
    }
    if (!empty($data[9])) {
        $billing_city = $data[9];
    }
    if (!empty($data[10])) {
        $billing_postcode = $data[10];
    }
    if (!empty($data[11])) {
        $billing_phone = $data[11];
    }

    if (!empty($data[12])) {
        $shipping_first_name = $data[12];
    }
    if (!empty($data[13])) {
        $shipping_company = $data[13];
    }
    if (!empty($data[14])) {
        $shipping_address_1 = $data[14];
    }
    if (!empty($data[15])) {
        $shipping_city = $data[15];
    }
    if (!empty($data[16])) {
        $shipping_postcode = $data[16];
    }
    if (!empty($data[17])) {
        $shipping_phone = $data[17];
    }

    $billing_country = 'RS';
    $shipping_country = 'RS';

    if (!empty($data[20])) {
        $age_range = $data[20];
    }
    if (!empty($data[21])) {
        $meta_sex = $data[21];
    }
    if (!empty($data[22])) {
        $migrated = $data[22];
    }
//    $user_id = wc_create_new_customer($email, $username, $password);

    $user_data = array(
        'user_login' => $email,
        'user_pass' => $password,
        'user_email' => $email,
        'role' => 'customer'
    );

    $user_id = wp_insert_user($user_data);

    update_user_meta($user_id, "hash_password_old", $password);
    var_dump(get_user_meta($user_id, 'hash_password_old')[0]);


    update_user_meta($user_id, "first_name", $first_name);
//
//    //billing
//    update_user_meta($user_id, "billing_first_name", $billing_first_name);
//    update_user_meta($user_id, "billing_company", $billing_company);
//    update_user_meta($user_id, "billing_pib", $billing_pib);
//    update_user_meta($user_id, "billing_mb", $billing_mb);
//    update_user_meta($user_id, "billing_address_1", $billing_address_1);
//    update_user_meta($user_id, "billing_city", $billing_city);
//    update_user_meta($user_id, "billing_postcode", $billing_postcode);
//    update_user_meta($user_id, "billing_phone", $billing_phone);
//    update_user_meta($user_id, "billing_country", $billing_country);
//
//    //shipping
//    update_user_meta($user_id, "shipping_first_name", $shipping_first_name);
//    update_user_meta($user_id, "shipping_company", $shipping_company);
//    update_user_meta($user_id, "shipping_address_1", $shipping_address_1);
//    update_user_meta($user_id, "shipping_city", $shipping_city);
//    update_user_meta($user_id, "shipping_postcode", $shipping_postcode);
//    update_user_meta($user_id, "shipping_phone", $shipping_phone);
//    update_user_meta($user_id, "shipping_country", $shipping_country);
//
//    update_user_meta($user_id, "age_range", $age_range);
//    update_user_meta($user_id, "meta_sex", $meta_sex);
    update_user_meta($user_id, "migrated", 1);
}
?>
<div class="row">
    <div class="col-3 gf-sidebar gf-left-sidebar">
        <div class="gf-left-sidebar-wrapper">
            <div class="gf-wrapper-before">
                <div class="gf-category-sidebar-toggle">Kategorije</div>
                <span class="fas fa-angle-up"></span>
            </div>
            <?php dynamic_sidebar('gf-left-sidebar'); ?>
        </div>
    </div>
    <div class="gf-content-wrapper col-md-9 col-sm-12">
        <div class="gf-row row list-unstyled gf-image-slider-banners-desktop">
            <?php dynamic_sidebar('gf-homepage-row-1'); ?>
        </div>
        <div class="gf-row row list-unstyled gf-image-slider-banners-mobile">
            <?php dynamic_sidebar('gf-homepage-row-1-mobile'); ?>
        </div>
        <div class="gf-row row list-unstyled gf-product-sliders-desktop-version">
            <?php dynamic_sidebar('gf-homepage-row-2'); ?>
        </div>
        <div class="gf-row row list-unstyled gf-product-sliders-mobile-version">
            <?php dynamic_sidebar('gf-homepage-row-3'); ?>
        </div>
    </div>
</div>
<?php get_footer(); ?>
