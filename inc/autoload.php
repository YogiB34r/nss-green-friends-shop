<?php

require_once(get_stylesheet_directory() . "/user.functions.php");
require_once(get_stylesheet_directory() . "/search.functions.php");
require_once(get_stylesheet_directory() . "/util.functions.php");
require_once(get_stylesheet_directory() . "/cron.functions.php");


require_once(__DIR__ . '/CheckoutHelper/CheckoutHelper.php');
require_once(__DIR__ . '/Cli/GF_CLI.php');
require_once(__DIR__ . '/ExternalBannerWidget/ExternalBannerWidget.php');

foreach (glob(__DIR__ . "/Search/*.php") as $file) {
    require_once $file;
}
foreach (glob(__DIR__ . "/Util/*.php") as $file) {
    require_once $file;
}
foreach (glob(__DIR__ . "/Theme/*.php") as $file) {
    require_once $file;
}
require_once(__DIR__ . '/archive-page-functions.php');
require_once(__DIR__ . '/class-wc-breadcrumb.php');
require_once(__DIR__ . '/gf-admin-functions.php');
require_once(__DIR__ . '/gf-cart-page-functions.php');
require_once(__DIR__ . '/gf-registred-sidebars.php');
require_once(__DIR__ . '/gf-shortcodes.php');
require_once(__DIR__ . '/gf-single-product-functions.php');
require_once(__DIR__ . '/gf-woocommerce-checkout-functions.php');
require_once(__DIR__ . '/theme-setup.php');
require_once(__DIR__ . '/Woocommerce/WooFunctions.php');