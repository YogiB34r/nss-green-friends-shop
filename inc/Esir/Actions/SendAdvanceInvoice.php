<?php

namespace GF\Esir\Actions;

use GF\Esir\EsirIntegration;
use GF\Esir\EsirIntegrationLogHandler;

class SendAdvanceInvoice
{
    private $json;
    private $orderId;

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
        if (!EsirIntegration::sendJsonToEsir($this->json, $this->orderId)) {
            throw new \RuntimeException('Došlo je do greške prilikom slanja');
        }
    }
}