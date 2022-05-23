<?php

namespace GF\Esir\Actions;

use GF\Esir\EsirIntegration;
use GF\Esir\EsirIntegrationLogHandler;

class SendAdvanceInvoice
{
    private $json;
    private $orderId;

    //@todo proveriti da li vec postoji izdati avans i spreciti izdavanje novog pre nego sto se stronira stari
    //@todo refrerenece number za fiskalizovanje necega za sta je pre izdat avans koristi refund number(znaci mora se refundirati avans pre fiskalizacije)
    //@todo avans refundiranje nece ici preko jitexa vec mi treba da sredimo to
    public function __construct($json, $orderId)
    {
        $this->json = $json;
        $this->orderId = $orderId;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public function __invoke()
    {
        if ($this->json->transactionType !== 'Sale' || $this->json->invoiceType !== 'Advance') {
            throw new \RuntimeException('Spremljeni fajl iz jitexa nije tip avansa, generišite fajl za avans pa pokušajte ponovo');
        }
        EsirIntegration::sendJsonToEsir($this->json);
    }
}