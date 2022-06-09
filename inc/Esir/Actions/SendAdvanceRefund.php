<?php

namespace GF\Esir\Actions;

use Exception;
use GF\Esir\EsirIntegration;
use GF\Esir\EsirIntegrationLogHandler;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;

class SendAdvanceRefund
{
    private $json;
    private $orderId;

    //@todo u slucaju da pokusava da se stornira avansni racun koji je vec storniran i za koji je izdat fiskalni racun treba obavaestiti korisnika
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
        $orderData = EsirIntegrationLogHandler::getEsirResponse($this->orderId, 'ADVANCE-SALE', EsirIntegrationLogHandler::STATUS_FISCALIZED);
        if (count($orderData) === 0) {
            throw new \RuntimeException('Nije pronadđen avansni fiskalni isečak u bazi na osnovu koga se može izvršiti refundacija.');
        }
        $orderDataResponse = json_decode($orderData[0]->response, false, 512, JSON_THROW_ON_ERROR);
        $referentDocumentNumber = $orderDataResponse->invoiceNumber;
        $this->json->referentDocumentNumber = $referentDocumentNumber;
        $this->json->invoiceType = 'Advance';
        $this->json->transactionType = 'Refund';
        if (!EsirIntegration::sendJsonToEsir($this->json, $this->orderId)) {
            throw new \RuntimeException('Došlo je do greške prilikom slanja');
        }
    }
}