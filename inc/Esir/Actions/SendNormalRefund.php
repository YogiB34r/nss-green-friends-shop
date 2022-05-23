<?php

namespace GF\Esir\Actions;

use Exception;
use GF\Esir\EsirIntegration;
use GF\Esir\EsirIntegrationLogHandler;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;

class SendNormalRefund
{
    private $orderId;
    private $json;

    public function __construct($json, $orderId)
    {
        $this->json = $json;
        $this->orderId = $orderId;
    }

    /**
     * @return void
     * @throws GuzzleException
     * @throws JsonException
     * @throws Exception
     */
    public function __invoke(): void
    {
        $orderData = EsirIntegrationLogHandler::getEsirResponse($this->orderId, 'NORMAL-SALE', EsirIntegrationLogHandler::STATUS_FISCALIZED);
        if ($this->json->transactionType !== 'Refund') {
            throw new Exception('Nije prondađen fajl za refundaciju, generište fajl na jitexu i pokušajte ponovo.');
        }
        if (count($orderData) === 0) {
            throw new Exception('Nije pronadđen fiskalni isečak u bazi na osnovu koga se može izvršiti refundacija.');
        }
        $orderDataResponse = json_decode($orderData[0]->response, false, 512, JSON_THROW_ON_ERROR);
        $referentDocumentNumber = $orderDataResponse->invoiceNumber;
        $this->json->referentDocumentNumber = $referentDocumentNumber;
        EsirIntegration::sendJsonToEsir($this->json);
    }
}