<?php

namespace GF\Esir\Actions;

use GF\Esir\EsirIntegration;
use GF\Esir\EsirIntegrationLogHandler;

class SendNormalInvoice
{
    private $json;
    private $orderId;

    //@todo proveriti da li je potrebno stornirati neki advance sale pre nego sto se posalje na fiskalizaciju
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
        $orderData = EsirIntegrationLogHandler::getEsirResponse($this->orderId, 'NORMAL-SALE',
            EsirIntegrationLogHandler::STATUS_FISCALIZED);
        if (count($orderData) !== 0) {
            $refundData = EsirIntegrationLogHandler::getEsirResponse($this->orderId, 'NORMAL-REFUND',
                EsirIntegrationLogHandler::STATUS_REFUNDED);
            if ((count($refundData) !== 0) && $orderData[0]->id > $refundData[0]->id) {
                throw new \RuntimeException('Ovaj račun je već fiskalizovan a nema refundacije nakon poslednje fisklaizacije');
            }
        }
        if ($this->json->transactionType !== 'Sale' && $this->json->invoiceType !== 'Normal') {
            throw new \RuntimeException('Spremljeni fajl iz jitexa nije tip prodaje, generišite fajl za prodaju pa pokušajte ponovo');
        }
       if (!EsirIntegration::sendJsonToEsir($this->json, $this->orderId)) {
           throw new \RuntimeException('Došlo je do greške prilikom slanja');
       }
    }
}