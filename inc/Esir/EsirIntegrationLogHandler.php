<?php

namespace GF\Esir;

class EsirIntegrationLogHandler
{
    public static $statusCode = [
        0 => 'waitingFiskalization',
        1 => 'fiskalizationSuccess',
        2 => 'fiskalizationFailed',
    ];

    /**
     *
     * @param int $orderId
     * @param string $response
     * @return void
     */
    public static function saveDropoxResponse(int $orderId, string $response)
    {
        global $wpdb;
        $wpdb->insert('esir_log', ['orderId' => $orderId, 'dropboxResponse' => $response, 'status' => 0]);
    }

    public static function saveEsirResponse(int $orderId, string $response, int $status = 2)
    {
        global $wpdb;
        $wpdb->update('esir_log', ['esirResponse' => $response, 'status' => $status], ['orderId' => $orderId]);
    }

    public static function getEsirResponse(int $orderId)
    {
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM esir_log WHERE orderId = {$orderId}");
    }
}