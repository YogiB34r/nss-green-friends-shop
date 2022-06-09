<?php

namespace GF\Esir\Actions;

use GF\Esir\EsirIntegration;
use GF\Esir\EsirIntegrationLogHandler;

class FiskalAdvanceInvoice
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
        if ($this->json->transactionType !== 'Sale' || $this->json->invoiceType !== 'Normal') {
            throw new \RuntimeException('Spremljeni fajl iz jitexa nije tip prodaje, generišite fajl za prodaju pa pokušajte ponovo');
        }
        $orderData = EsirIntegrationLogHandler::getEsirResponse($this->orderId, 'ADVANCE-REFUND',
            EsirIntegrationLogHandler::STATUS_REFUNDED);
        if (count($orderData) !== 0) {
            $orderDataResponse = json_decode($orderData[0]->response, false, 512, JSON_THROW_ON_ERROR);
            $referentDocumentNumber = $orderDataResponse->invoiceNumber;
            $this->json->referentDocumentNumber = $referentDocumentNumber;
            if (!EsirIntegration::sendJsonToEsir($this->json, $this->orderId)) {
                throw new \RuntimeException('Došlo je do greške prilikom slanja');
            }
            return;
        }
        throw new \RuntimeException('Da bi se fiskalizovao avans potrebno je prvo odraditi refundaciju i 
        sacekati makar 15 minuta pre ponovnog pokusaja fiskalizacije');
    }
}