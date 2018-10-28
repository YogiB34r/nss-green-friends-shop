<?php
/* Template Name: back ajax */

ini_set('max_execution_time', 1200);
ini_set('display_errors', 1);
error_reporting(E_ALL);

global $wpdb;
$config = array(
    'host' => ES_HOST,
    'port' => 9200
);
$printMenu = true;

//$sw = new \Symfony\Component\Stopwatch\Stopwatch();
//$sw->start('gfmain');

//include(__DIR__ . "/inc/Search/Elastica/Search.php");
include(__DIR__ . "/inc/Search/Elastica/Indexer.php");
include(__DIR__ . "/inc/Search/Elastica/Config/ConfigInterface.php");
include(__DIR__ . "/inc/Search/Elastica/Config/Product.php");
include(__DIR__ . "/inc/Search/Elastica/Config/Term.php");
include(__DIR__ . "/inc/Search/Elastica/Setup.php");



if (isset($_GET['action'])) {
    $elasticaClient = new \Elastica\Client($config);

    switch ($_GET['action']) {
        case 'createIndex':
//            $products = new \GF\Search\Elastica\SetupProducts($elasticaClient);
            $productSetup = new \GF\Search\Elastica\Setup($elasticaClient, new \GF\Search\Elastica\Config\Product());
            $termSetup = new \GF\Search\Elastica\Setup($elasticaClient, new \GF\Search\Elastica\Config\Term());
            $recreate = true;
            $productSetup->createIndex($recreate);
            $termSetup->createIndex($recreate);

            break;

        case 'getList':
            $keywords = (isset($_GET['query'])) ? $_GET['query'] : 'test';
            $search = new \GF\Search\Elastica\Search($elasticaClient);
            $search->search($keywords);
            $search->printDebug();

            break;

        case 'syncIndex':
            \GF\Search\Elastica\Indexer::index($elasticaClient, new \GF\Search\Elastica\Config\Product());

            break;

        case 'printPreorder':
            $printMenu = false;
            $order = wc_get_order($_GET['id']);
            printPreorder($order);

            break;

        case 'printOrder':
            $printMenu = false;
            $order = wc_get_order($_GET['id']);
            printOrder($order);

            break;

        case 'exportJitexOrder':
            $printMenu = false;
            $order = wc_get_order($_GET['id']);
            exportJitexOrder($order);

            break;

        case 'adresnica':

            break;
    }
}
if ($printMenu) {
?>
<a href="/back-ajax/?action=createIndex">(re) create index</a>
<a href="/back-ajax/?action=syncIndex">sync index</a>
<a href="/back-ajax/?action=getList">test</a>
<?php
}


function createAdresnica() {

}

function exportJitexOrder(WC_Order $order) {
    $string = '';
    foreach ($order->get_items() as $item) {
        $p = wc_get_product($item->get_product()->get_id());
        if ($p->get_parent_id()) {
            $p = wc_get_product($p->get_parent_id());
        }

        $variation = '';
        if (get_class($p) === WC_Product_Variation::class)
        foreach (array_values($p->get_variation_attributes())[0] as $value) {
            if (strstr($item->get_name(), $value)) {
                $variation = $value;
            }
        }

        $name = $order->get_billing_first_name() .' '. $order->get_billing_last_name();
        $variantId = $p->get_sku() . $variation;
        $variantName = $item->get_name();
        $date = $order->get_date_created()->format('d.m.Y');
        $modifier = (float) '1' .'.'. (int) number_format($p->get_meta('pdv'));
        $priceNoPdv = number_format((int) $p->get_price() / $modifier, 2, ',', '.');
        $priceFormated =number_format($p->get_price(), 2, ',', '.');
        $string .= $name."\t".$order->get_billing_address_1()."\t".$order->get_billing_postcode()."\t".$order->get_billing_city()."\t"."Srbija"."\t".
        $order->get_billing_phone()."\t".$order->get_order_number()."\t".$date."\t".$order->get_payment_method_title()."\t".$variantId."\t".$variantName."\t".
            $item->get_quantity()."\t".$priceNoPdv."\t".$priceFormated."\t".$order->get_billing_company()."\r\n";
    }
    $shippingNoPdv = number_format($order->get_shipping_total() / 1.2, 2, ',', '.');

    $string .= $name."\t".$order->get_billing_address_1()."\t".$order->get_billing_postcode()."\t".$order->get_billing_city()."\t"."Srbija"."\t".
        $order->get_billing_phone()."\t".$order->get_order_number()."\t".$date."\t".$order->get_payment_method_title()."\t9999\tDostava\t1\t".
        $shippingNoPdv."\t".number_format($order->get_shipping_total(), 2, ',', '.')."\t".$order->get_billing_company();

    header('Content-Disposition: attachment; filename="' . $order->get_order_number() . '.txt' . '"');
    header("Content-Transfer-Encoding: binary");
    header('Expires: 0');
    header('Pragma: no-cache');
    print iconv('utf-8','windows-1250',str_replace(array('Ð', 'ð'), array('Đ', 'đ'), $string));
}

function printOrder(WC_Order $order) {
    require (__DIR__ . '/templates/orders/printRacun.phtml');
}
function printPreorder(WC_Order $order) {
    require (__DIR__ . '/templates/orders/printPredracun.phtml');
}