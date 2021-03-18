<?php

//require_once(__DIR__ . "/Util/DailyExpressApi.php");
require_once(__DIR__ . "/Search/Elastica/Indexer.php");
require_once(__DIR__ . "/Search/Indexer/Indexer.php");
require_once(__DIR__ . "/Search/Elastica/Config/ConfigInterface.php");
require_once(__DIR__ . "/Search/Elastica/Config/Product.php");
require_once(__DIR__ . "/Search/Elastica/Config/Term.php");
require_once(__DIR__ . "/Search/Elastica/Setup.php");
require_once(__DIR__ . "/Search/Elastica/Search.php");
require_once(__DIR__ . "/Search/Elastica/TermSearch.php");
require_once(__DIR__ . "/Search/Factory/ElasticClientFactory.php");
require_once(__DIR__ . "/Search/Factory/ProductSetupFactory.php");
require_once(__DIR__ . "/Search/Factory/TermSetupFactory.php");
require_once(__DIR__ . "/Search/Search.php");
require_once(__DIR__ . "/Search/AdapterInterface.php");
require_once(__DIR__ . "/Search/Adapter/MySql.php");
require_once(__DIR__ . "/Search/Adapter/Elastic.php");
require_once(__DIR__ . "/Search/Functions.php");

require_once(__DIR__ . '/CheckoutHelper/CheckoutHelper.php');
require_once(__DIR__ . '/Cli/GF_CLI.php');
require_once(__DIR__ . '/ExternalBannerWidget/ExternalBannerWidget.php');

require_once(__DIR__ . '/override.functions.php');


require_once(get_stylesheet_directory() . "/cron.functions.php");


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

foreach (glob(__DIR__ . "/Woocommerce/*.php") as $file) {
    require_once $file;
}
foreach (glob(__DIR__ . "/Marketplace/*.php") as $file) {
    require_once $file;
}
foreach (glob(__DIR__ . "/Orders/*.php") as $file) {
    require_once $file;
}