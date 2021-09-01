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
//    $uploadsDir = generateUploadsPath();
//    if (file_exists($uploadsDir . $name)) {
//        return $uploadsDir . $name;
//    }

        if (in_array($order->get_status(), ['spz-pakovanje'])) {
            $order->update_status('spz-slanje');
        }
        $order->update_meta_data('adresnicaCreated', 1);
        $order->save();

        $html = '';
        require (__DIR__ . '/../../templates/orders/adresnica.phtml');

        //test dir structure
//        $uploadsDir = __DIR__ . '/../../../../uploads/'. date('Y');
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

//        echo $html;
//        die();

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();
        file_put_contents($filePath, $dompdf->output());

//    $pdf = new \Spipu\Html2Pdf\Html2Pdf();
//    $pdf->writeHTML($html);
//    $pdf->output($uploadsDir . $name, 'F');

        return $filePath;
    }
}