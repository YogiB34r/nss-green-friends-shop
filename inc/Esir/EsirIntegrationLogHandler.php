<?php

namespace GF\Esir;

class EsirIntegrationLogHandler
{
    const STATUS_WAITING = 0;
    const STATUS_FISCALIZED = 1;
    const STATUS_FAILED = 2;
    const STATUS_REFUNDED = 3;
    const STATUS_VOIDED = 4;

    public static $statusCode = [
        0 => 'waitingFiskalization',
        1 => 'fiskalizationSuccess',
        2 => 'fiskalizationFailed',
        3 => 'refund',
        4 => 'void',
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