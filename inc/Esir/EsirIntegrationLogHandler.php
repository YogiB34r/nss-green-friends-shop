<?php

namespace GF\Esir;

class EsirIntegrationLogHandler
{
    public static $statusCode = [
        0 => 'waitingFiskalization',
        1 => 'fiskalizationSuccess',
        2 => 'fistalizationFailed',
    ];
    public static function saveDropoxResponse(int $orderId, string $response)
    {
        global $wpdb;
        $wpdb->insert('esir_log', array( 'orderId' => $orderId, 'dropboxResponse' => $response, 'status' => 0));
    }

    public static function saveEsirResponse(int $orderId, string $response, int $status = 2)
    {
        global $wpdb;
        $wpdb->insert( 'esir_log', array( 'orderId' => $orderId, 'esirResponse' => $response, 'status' => $status ?? 2));
    }



}