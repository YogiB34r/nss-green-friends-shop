<?php
//ini_set('max_execution_time', '40');
use Gf\Search\Functions;
use GF\Theme;
use Gf\Util\Url;
use GF\Woocommerce\Shipping;
use GF\Woocommerce\WooFunctions;
use GfPluginsCore\ProductStickers;

require (__DIR__ . '/inc/autoload.php');
global $wpdb;

$useElastic = true; // create admin setting
if (defined('USE_ELASTIC')) {
    $useElastic = USE_ELASTIC;
}
$searchFunctions = new Functions($wpdb, $useElastic);
$wooFunctions = new WooFunctions();
$wooShipping = new Shipping();
$wooShipping->init();
$theme = new Theme();
$theme->init();

$urlUtils = new Url();
$urlUtils->init();

$adminMenu = new \Gf\Util\AdminMenu();
$adminMenu->init();

//$stickers = new ProductStickers();



add_filter('request', 'customRewriteFix');
/**
 * Prevent main wp query from returning 404 page on a category page when it thinks there are no more results.
 *
 * @param $query_string
 * @return mixed
 */
function customRewriteFix($query_string) {
    if (isset($query_string['page']) && $query_string['page'] !== '' && isset($query_string['name'])) {
        unset($query_string['name']);
    }
    return $query_string;
}



function get_search_category_aggregation() {
    return $GLOBALS['gf-search']['facets']['category'];
}

add_filter('upload_dir', 'upload_dir_filter');
/**
 * Saves uploads into folders organized by day.
 *
 * @param $uploads
 * @return mixed
 */
function upload_dir_filter($uploads)
{
    $day = date('d/i');
    $uploads['path'] .= '/' . $day;
    $uploads['url'] .= '/' . $day;

    return $uploads;
}

//********* infinite scroll START *********

/*
 * load more script ajax hooks
 */
add_action('wp_ajax_nopriv_ajax_script_load_more', 'ajax_script_load_more');
add_action('wp_ajax_ajax_script_load_more', 'ajax_script_load_more');
/*
 * initial posts dispaly
 */
function ajax_infinite_scroll($args)
{
    //initial posts load
    echo '<div id="ajax-primary" class="content-area">';
    echo '<div id="ajax-content" class="content-area">';

    ajax_script_load_more($args);

    $mobile = 'desktop';
    if (wp_is_mobile()) {
        $mobile = 'mobile';
    }

    echo '</div>';
    echo '<a href="#" id="loadMore" class="'.$mobile.'" data-page="1" data-url="' . admin_url("admin-ajax.php") . '" ></a>';
    echo '</div>';
}

/*
 * load more script call back
 */
function ajax_script_load_more($args)
{
    global $searchFunctions;
    $searchFunctions->customShopLoop($args);

    exit();
}

//********* infinite scroll END *********



add_filter('script_loader_tag', 'add_async_attribute', 10, 2);
function add_async_attribute($tag, $handle) {
    $scripts_to_defer = array('merged-script');
    foreach($scripts_to_defer as $defer_script) {
        if ($defer_script === $handle) {
            return str_replace(' src', ' async="async" src', $tag);
        }
    }
    return $tag;
}








//@TODO Custom admin product table
//require(__DIR__ . '/templates/admin/search-settings.php');
//require(__DIR__ . '/templates/admin/list-product-search-settings.php');


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

function get_product_by_sku( $sku ) {
    global $wpdb;

    $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );
    if ($product_id){
        return get_product($product_id);
//        return new WC_Product( $product_id );
    }

    return null;
}


//Migrate comments from old site
function gf_migrate_comments()
{
    $rows = array_map('str_getcsv', file(__DIR__ . '/reviews.csv'));
    $header = array_shift($rows);
    $csv = array();
    foreach ($rows as $row) {
        $csv[] = array_combine($header, $row);
    }
    $successfulComments = [];
    $emptySkus = [];
    $emptyUsers = [];
    foreach ($csv as $comment) {
        $postId = wc_get_product_id_by_sku($comment['sku']);
        if (!$postId) {
            $emptySkus[] = $comment['sku'];
            continue;
        }
        $user = get_user_by('email', $comment['Email']);
        if (!$user) {
            $emptyUsers[] = $comment['Email'];
            continue;
        }
        $commentAuthor = $user->get('display_name');
        $commentAuthorEmail = $user->get('user_email');
        $commentAuthorUrl = $user->get('user_url');
        $commentContent = $comment['comment'];
        $userId = $user->get('ID');
        $commentDate = $comment['date'];


        $data = array(
            'comment_post_ID' => $postId,
            'comment_author' => $commentAuthor,
            'comment_author_email' => $commentAuthorEmail,
            'comment_author_url' => $commentAuthorUrl,
            'comment_content' => $commentContent,
            'comment_date' => $commentDate,
            'comment_date_gmt' => $commentDate,
            'comment_approved' => 1,
            'user_id' => $userId,
        );
        $comment_id = wp_insert_comment($data);
        $successfulComments[] = $comment_id;
        update_comment_meta($comment_id, 'migrated', '1');
    } //foreach comments

    $skuLogFile = fopen(LOG_PATH . '/skuLog.csv', 'w');
    fwrite($skuLogFile, implode(',', $emptySkus));
    fclose($skuLogFile);

    $userLogFile = fopen(LOG_PATH . '/usersLog.csv', 'w');
    fwrite($userLogFile, implode(',', $emptyUsers));
    fclose($userLogFile);
    echo '<p>Uspešno importovano ' . count($successfulComments) . ' komentara</p>';
}

function generateUploadsPath() {
    return WP_CONTENT_DIR . '/uploads/'. date('Y') .'/'. date('m') .'/'. date('d') . '/';
}