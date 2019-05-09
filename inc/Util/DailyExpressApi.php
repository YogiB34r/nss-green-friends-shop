<?php

namespace GF\Util;


use GuzzleHttp\Psr7\Request;

class DailyExpressApi
{

    // test
    private $guid = '419AFE8D-15D9-4750-B4FC-9BE3851560CA';

    private $endpoint = 'http://callc.dailyexpress.rs/DePreCalls.asmx?WSDL';

    private $httpClient;

    private $soapXml;

    public function __construct()
    {
        $this->httpClient = new \GuzzleHttp\Client();
    }

    public function sendAdresnice()
    {
        $csv = file_get_contents(ABSPATH . '/wp-content/uploads/adresnice/adresnica-081118.csv');
        $this->compileAdresniceSoapXml($csv);
        $this->call();
    }

    private function call()
    {
        $headers = ['Content-Type' => 'text/xml'];
        $request = $this->httpClient->send(new Request('POST', $this->endpoint, $headers, $this->soapXml));

        var_dump($request->getBody()->getContents());
        die();
    }

    private function compileAdresniceSoapXml($csvString)
    {
        $this->soapXml = '<?xml version="1.0" encoding="utf-8"?>
<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
  <soap12:Body>
    <AddPrecalls xmlns="http://tempuri.org/">
      <authCode>' . $this->guid . '</authCode>
      <PreCallData>'. $csvString .'</PreCallData>
    </AddPrecalls>
  </soap12:Body>
</soap12:Envelope>';
    }

    public function createDailyExport($orders) {
//    ini_set('display_errors', 1);
        $adresnicaFields = [
            'ReferenceID','SBranchID','SName','SAddress','STownID','STown','SCName','SCPhone','PuBranchID','PuName',
            'PuAddress','PuTownID','PuTown','PuCName','PuCPhone','RBranchID','RName','RAddress','RTownID','RTown','RCName',
            'RCPhone','DlTypeID','PaymentBy','PaymentType','BuyOut','Value','Mass','ReturnDoc','SMS_Sender','Packages','Note', 'Content'
        ];

        $fileName = 'adresnica-' . date('dmy') . '.csv';
        $adresnicaPath = ABSPATH . '/wp-content/uploads/adresnice/' . $fileName;
        $tmpFile = fopen($adresnicaPath, 'wa');

        //insert csv header
        if (!fputcsv($tmpFile, $adresnicaFields, '|')) {
            throw new Exception('Doslo je do greske prilikom generisanja csv izvestaja.');
        }

        /* @var \WC_Order $order */
        foreach ($orders as $order) {
            $billingName = $order->get_billing_first_name() .' '. $order->get_billing_last_name();
            if ($order->get_shipping_address_1() !== ''):
                $shippingName = $order->get_shipping_first_name() .' '. $order->get_shipping_last_name();
                $shippingAddress = $order->get_shipping_address_1();
                $shippingZip = $order->get_shipping_postcode();
                $shippingCity = $order->get_shipping_city();
            else:
                $shippingName = $order->get_billing_first_name() .' '. $order->get_billing_last_name();
                $shippingAddress = $order->get_billing_address_1();
                $shippingZip = $order->get_billing_postcode();
                $shippingCity = $order->get_billing_city();
            endif;
            $otkupnina = 0;
            if ($order->get_payment_method_title() == 'Pouzećem') {
                $otkupnina = number_format($order->get_total(), 0, '', '');
                $otkupnina = $otkupnina * 100;
            }
            $weight = 0;
            $category = '';
            /* @var WC_Order_Item_Product $item */
            foreach ($order->get_items() as $item) {
                $weight += $item->get_product()->get_weight();
                if (isset($item->get_product()->get_category_ids()[0])) {
                    $cat = get_term_by('id', $item->get_product()->get_category_ids()[0], 'product_cat');
                    $category = $cat->name;
                }
            }

            $csvArray = array(
                'gpoid' => $order->get_order_number(),

                'ID nalogodavca' => 'UK17357',
                'Naziv nalogodavca' => 'Non Stop Shop d.o.o.',
                'Adresa nalogodavca' => 'Žorža Klemansoa 19',
                'ID naselja nalogodavca' => 11000,
                'Naziv naselja/mesta nalogodavca' => 'Beograd',
                'Kontakt osoba nalogodavca' => 'NonStopShop',
                'Kontakt telefon nalogodavca' => '011/3334773',

                'ID posaljioca' => 'UK17357',
                'Naziv posaljioca' => 'Non Stop Shop d.o.o.',
                'Adresa mesta preuzimanja' => 'Žorža Klemansoa 19',
                'ID naselja mesta preuzimanja' => 11000,
                'Naziv naselja/mesta preuzimanja' => 'Beograd',
                'Kontakt osoba mesta preuzimanja' => 'NonStopShop',
                'Kontakt telefon mesta preuzimanja' => '011/3334773',

                'ID primaoca' => '',
                'Naziv primaoca' => trim($shippingName),
                'Adresa primaoca' => trim($shippingAddress),
                'ID naselja primaoca' => $shippingZip,
                'Naziv naselja/mesta primaoca' => $shippingCity,
                'Kontakt osoba primaoca' => trim($billingName),
                'Kontakt telefon primaoca' => $order->get_billing_phone(),

                'Tip isporuke' => 2,
                'Ko plaća' => 1,
                'Način plaćanja' => 2,
                'Otkupnina' => $otkupnina,
                'Vrednost' => 0,
                'Masa' => $weight * 1000,
                'Povratna dokumentacija' => 0,
                'SMS pošiljaoca' => '',
                'Paketi' => 'SS'.preg_replace('/2018/', '', str_replace('-', '', $order->get_order_number()), 1), // i.e. 2018 -> '' , 2012 -> ''
                'Napomena' => '',
                'Content' => $category,
            );

            if (!fputcsv($tmpFile, $csvArray, '|')) {
                die('eerrorr');
            }
            $order->update_status('poslato');
        }
    }
}