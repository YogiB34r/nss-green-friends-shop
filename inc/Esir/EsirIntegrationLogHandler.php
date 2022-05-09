<?php

namespace GF\Esir;

class EsirIntegrationLogHandler
{
    public static $statusCode = [
        0 => 'waitingFiskalization',
        1 => 'fiskalizationSuccess',
        2 => 'fistalizationFailed',
    ];
    public function saveDropoxResponse(int $orderId, string $response)
    {
        global $wpdb;
        wpdb::insert('esirResponseLog', array( 'orderId' => $orderId, 'dropboxResponse' => $response, 'status' => 0));
    }

    public function saveEsirResponse(int $orderId, string $response)
    {
        global $wpdb;
        //@todo check response type and save status for order to be fiskalizovano ili failovano. maybe send email to admin
        if ($response === 'OK') {
            $status = 1;
        }
        wpdb::insert( 'esirResponseLog', array( 'orderId' => $orderId, 'esirResponse' => $response, 'status' => $status ?? 2));
    }



}