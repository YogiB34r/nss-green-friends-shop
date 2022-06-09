<?php

namespace GF\Esir;

class EsirIntegrationLogHandler
{
    const STATUS_WAITING = 0;
    const STATUS_FISCALIZED = 1;
    const STATUS_REFUNDED = 2;
    const STATUS_ERROR = 3;
    const STATUS_SENT = 4;

    /**
     *
     * @param int $orderId
     * @param string $response
     * @param string $action
     * @param int $status
     * @return void
     * @throws \JsonException
     */
    public static function saveResponse(int $orderId, string $response, string $action, int $status): void
    {
        global $wpdb;
        if ($action === 'send') {
            $wpdb->insert('esir_log', [
                'orderId' => $orderId,
                'response' => $response,
                'status' => $status,
                'action' => $action,
            ]);
            return;
        }
        if ($action === 'getFile') {
            $decodedResponse = json_decode($response, false, 512, JSON_THROW_ON_ERROR);
            $wpdb->insert('esir_log', [
                'orderId' => $orderId,
                'response' => $response,
                'status' => $status,
                'action' => $action,
                'jitexId' => $decodedResponse->orderID
            ]);
            return;
        }
        if ($action === 'createAdvanceFile') {
            $wpdb->insert('esir_log', [
                'orderId' => $orderId,
                'response' => $response,
                'status' => $status,
                'action' => $action
            ]);
            return;
        }
        $responseJson = json_decode($response, false, 512, JSON_THROW_ON_ERROR);
        if (!isset($responseJson->messages)) {
            $status = self::STATUS_ERROR;
        }
        if (isset($responseJson->messages) && $responseJson->messages !== 'Success') {
            $status = self::STATUS_ERROR;
        }
        $wpdb->insert('esir_log', [
            'orderId' => $orderId,
            'response' => $response,
            'status' => $status,
            'action' => $action,
            'jitexId' => $responseJson->orderID
        ]);
    }

    public static function getEsirResponse(int $orderId, string $action = null, int $status = null)
    {
        global $wpdb;
        $sql = "SELECT * FROM esir_log WHERE orderId = {$orderId}";
        if ($action) {
            $sql .= " AND action = '{$action}'";
        }
        if ($status) {
            $sql .= " AND status = {$status}";
        }
        $sql .= " ORDER BY id DESC";
        return  $wpdb->get_results($sql);
    }

    /**
     * @throws \Exception
     */
    public static function getOrderIdByJitexId(int $jitexId, string $action = null, int $status = null)
    {
        global $wpdb;
        $sql = "SELECT * FROM esir_log WHERE jitexId = {$jitexId}";
        if ($action) {
            $sql .= " AND action = '{$action}'";
        }
        if ($status) {
            $sql .= " AND status = {$status}";
        }
        $sql .= " ORDER BY id DESC";
        $data = $wpdb->get_row($sql);
        if ($data) {
            return $data->orderId;
        }
        throw new \RuntimeException('No order found');
    }
}