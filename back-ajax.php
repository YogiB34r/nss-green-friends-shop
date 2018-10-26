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

//$cache = new GF_Cache();

//$cache->redis->delete('gf-megamenu-mobile');
//$cache->redis->delete('gf-megamenu');
//
//gf_category_megamenu_shortcode();
//gf_category_mobile_toggle_shortcode();
//
//exit();


//$sw = new \Symfony\Component\Stopwatch\Stopwatch();
//$sw->start('gfmain');

//if (isset($_GET['testVendor'])) {
//    gf_change_supplier_id_by_vendor_id();
//}

//include(__DIR__ . "/inc/Search/Elastica/Search.php");
include(__DIR__ . "/inc/Search/Elastica/Indexer.php");
include(__DIR__ . "/inc/Search/Elastica/Config/ConfigInterface.php");
include(__DIR__ . "/inc/Search/Elastica/Config/Product.php");
include(__DIR__ . "/inc/Search/Elastica/Config/Term.php");
include(__DIR__ . "/inc/Search/Elastica/Setup.php");

$printMenu = true;

if (isset($_GET['action'])) {
    $config = array(
        'host' => ES_HOST,
        'port' => 9200
    );
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

        case 'printOrder':
            $printMenu = false;
            $order = wc_get_order($_GET['id']);
            printOrder($order);

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


function printOrder(WC_Order $order) {


    $pdv = 1.2;
    var_dump($order->get_shipping_total());
    var_dump($order->get_shipping_total() / $pdv);
?>
    <style>
        .gf-print-bill-wrapper{
            width: 80%;
            padding: 5px;
            margin: auto;
            border: 1px solid;
        }
        .gf-print-bill-number{
            width: 100%;
        }
        .gf-print-bill-header{
            width: 100%;
            text-align: center;
            margin-top: 50px;
            font-weight: bold;
        }
        .gf-print-bill-info-wrapper{
            width: 75%;
            margin: auto;
            margin-top: 40px;
            display: flex;
        }
        .gf-print-bill__customer-info, .gf-print-bill__shipping-info{
            width: 50%;
        }
        .gf-print-bill__shipping-info{
            text-align: right;
        }
        .gf-print-bill-info-wrapper p{
            margin: 0;
        }
        .gf-print-bill__date-number-method{
            margin-top: 30px;
        }
        .gf-print-bill-table{
            width: 75%;
            margin: auto;
            margin-top: 50px;
        }
        .gf-print-bill-table th{
            border-bottom: 1px solid;
        }
        .gf-print-bill-table tfoot{
            border-top: 1px solid;
            font-weight: bolder;
        }
        .gf-print-bill-table__total{
            margin-right: auto;
        }
    </style>
    <div class="gf-print-bill-wrapper">
        <div class="gf-print-bill-number">
            <p>Račun br. <?=$order->get_order_number()?></p>
        </div>
        <div class="gf-print-bill-header">
            <h3>NALOG ZA PRIPREMU POSILJKE / NARUČIVANJE PROIZVODA</h3>
        </div>
        <div class="gf-print-bill-info-wrapper">
            <div class="gf-print-bill__customer-info">
                <h4>PODACI O NARUČIOCU / PODACI ZA RAČUN</h4>
                <p>Ime i prezime: <?=$order->get_billing_first_name() .' '. $order->get_billing_last_name()?></p>
                <p>Adresa: <?=$order->get_billing_address_1()?>,</p>
                <p><?=$order->get_billing_postcode() .', '. $order->get_billing_city()?></p>
                <p>Telefon: <?=$order->get_billing_phone()?></p>
                <div class="gf-print-bill__date-number-method">
                    <p>Broj narudžbenice: <?=$order->get_order_number()?></p>
                    <p>Datum naručivanja: <?=$order->get_date_created()->format('d/m/Y')?></p>
                    <p>Način plaćanja: <?=$order->get_payment_method_title()?></p>
                </div>
            </div>
            <div class="gf-print-bill__shipping-info">
                <h4>ADRESA KUPCA ZA ISPORUKU</h4>
                <p><?=$order->get_shipping_first_name() .' '. $order->get_shipping_last_name()?></p>
                <p><?=$order->get_shipping_address_1()?></p>
                <p><?=$order->get_shipping_postcode() .', '. $order->get_shipping_city() . ', Srbija'?></p>
            </div>
        </div>
        <table class="gf-print-bill-table">
            <tr>
                <th>Proizvod</th>
                <th>kol</th>
                <th>osnovica</th>
                <th>PDV</th>
                <th>iznos PDV</th>
                <th>Cena din</th>
            </tr>
        <?php
            foreach ($order->get_items() as $item) {
                $product = wc_get_product($item->get_product()->get_id());
                if ($product->get_parent_id()) {
                    $product = wc_get_product($product->get_parent_id());
                }

            /* @var \WC_Product $product */
            //        var_dump($product->get_meta_data());
//            var_dump($product->get_meta('pdv'));
//            var_dump($product->get_price());
//            var_dump($item->get_quantity());

            $modifier = (float) '1' .'.'. (int) number_format($product->get_meta('pdv'));
            $priceNoPdv = (int) $product->get_price() / $modifier;
            $pdvInDin = $product->get_price() - $priceNoPdv;

            $shippingNoPdv = number_format($order->get_shipping_total() / 1.2);
            $shippingPdvInDin = $order->get_shipping_total() - $shippingNoPdv;
        ?>
            <tr>
                <td><?=$product->get_name()?></td>
                <td><?=$item->get_quantity()?></td>
                <td><?=number_format($priceNoPdv, 2)?></td>
                <td><?=$product->get_meta('pdv')?> %</td>
                <td><?=number_format($pdvInDin)?></td>
                <td><?=$product->get_price()?> din</td>
            </tr>
            <?php } ?>
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Zbir</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><?=$order->get_total() - $order->get_shipping_total() ?> din</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Dostava</td>
                <td></td>
                <td><?=$shippingNoPdv?></td>
                <td>20%</td>
                <td><?=$shippingPdvInDin?> din</td>
                <td><?=$order->get_shipping_total()?> din</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tfoot>
            <tr>
                <td class="">TOTAL</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><?=$order->get_total()?> din</td>
            </tr>
            </tfoot>
        </table>
    </div>
<?php
}