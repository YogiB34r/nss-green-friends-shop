<?php
/*
 * Template Name: uplatnica
 */
$marketPlaceField = (int)get_post_meta($order->get_id(), 'marketplaceVendor', true);
$dateCreated = date('dmY', strtotime($order->get_date_created()));
if ($marketPlaceField !== '') {
    $marketPLace = new \GF\Marketplace\Marketplace();
    $vendor = $marketPLace->getByVendorId($marketPlaceField);
}
$svrhaUplate ='Kupovina na NonStopShop-u';
$companyName = $vendor['companyName'] ?? 'NON STOP SHOP d.o.o';
$companyAddress = $vendor['companyAddress'] ?? 'Beograd, Gvozdićeva 4';
$primalac = $companyName.'<br>'.$companyAddress;
$racunPrimaoca = $vendor['bankAccountNumber'] ?? '160-487203-63';
?>
<div class="payment-slip" style="border:1px solid;padding: 10px;overflow: auto;">
    <h2 style="text-align: right;">Nalog za uplatu</h2>
    <div class="gf-left-column" style="padding-right: 15px;float: left;width: 46%;">
        <div>
            <h3>Uplatilac</h3>
            <div style="border:1px solid;">
                <p><?= $order->get_billing_first_name(). ' '.$order->get_billing_last_name() ?></p>
                <p><?=$order->get_billing_address_1()?></p>
            </div>
        </div>
        <div>
            <h3>Svrha uplate</h3>
            <div style="border:1px solid;">
                <p><?=$svrhaUplate?></p>
        </div>
        </div>
        <div>
            <h3>Primalac</h3>
            <div style="border:1px solid;">
                <p>
                    <?=$primalac?>
                </p>
            </div>
        </div>
    </div>
    <div class="right-column" style="float: left;padding-left: 15px;margin-bottom: 10px;overflow:hidden;border-left: solid;width: 46%">
        <div style="float: left;">
            <h3 style="padding-bottom:6px ">Šifra Plaćanja</h3>
            <div style="border:1px solid;text-align: center;display: inline-block;">
                <p style="margin: 8px;min-height: 17px;min-width: 30px;">221</p>
            </div>
        </div>
        <div style="float: left;margin-left: 20px;">
            <h3 style="padding-bottom:6px ">Valuta</h3>
            <div style="border:1px solid;text-align: center;display: inline-block;">
                <p style="margin: 8px;min-height: 17px;min-width: 30px;">RSD</p>
            </div>
        </div>
        <div style="float: left;">
            <h3 style="padding-bottom:6px ">Iznos</h3>
            <div style="border:1px solid;text-align: center;">
                <p style="margin: 8px;min-height: 17px;min-width: 200px"><?=number_format_i18n((int)$order->get_total(),'2');?></p>
            </div>
        </div>
        <br style="clear: both;"/>
        <div>
            <h3>Račun primaoca</h3>
            <div style="border:1px solid;text-align: center;">
                <p style="margin: 8px;min-height: 17px;min-width: 300px"><?=$racunPrimaoca?></p>
            </div>
        </div>
        <div style="float: left;">
            <h3>Model i poziv na broj(odobrenje)</h3>
            <div style="border:1px solid;text-align: center;display: inline-block">
                <p style="min-height: 5px;min-width: 80px"></p>
            </div>
            <div style="border:1px solid;text-align: center;display: inline-block;margin-left: 0px">
                <p style="min-height: 5px;min-width: 220px"><?= $order->get_order_number()?></p>
            </div>
        </div>
    </div>

