<?php

namespace GF\Util;


class Adresnica
{
    public static function createAdresnica($orderId) {
        $order = wc_get_order($orderId);
        $path = static::createAdresnicaPdf($order);

        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-type: text/plain');
        header("Content-Disposition: attachment; filename=".basename($path));
        header('Content-Transfer-Encoding: binary');

        echo file_get_contents($path);
    }

    public static function createAdresnicaPdf(\WC_Order $order) {
        $name = 'Adresnica-'.$order->get_order_number().'.pdf';

        if (in_array($order->get_status(), ['spz-pakovanje'])) {
            $order->update_status('spz-slanje');
        }
        $order->update_meta_data('adresnicaCreated', 1);
        $order->save();

        $html = '';
        require (__DIR__ . '/../../templates/orders/adresnica.phtml');

        $uploadsDir = WP_CONTENT_DIR . '/uploads/'. date('Y');
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir);
        }
        $uploadsDir .= '/'. date('m');
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir);
        }
        $uploadsDir .= '/'. date('d');
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir);
        }
        $uploadsDir .= '/';
        $filePath = $uploadsDir . $name;

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();
        file_put_contents($filePath, $dompdf->output());

        return $filePath;
    }
}