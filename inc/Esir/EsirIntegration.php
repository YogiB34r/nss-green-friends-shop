<?php

namespace GF\DropBox;

class EsirIntegration
{
    public static function processEsirResponse($json)
    {
        $orders = json_decode($json);
        foreach ($orders as $order) {
//                $order = wc_get_order($order->orderID);
            $wcOrder = wc_get_order(636829);
            $to = get_user_by('ID', $wcOrder->get_customer_id())->user_email;
//                \GF\Esir\EsirIntegrationLogHandler::saveEsirResponse(
//                    (int) explode('-', $order->orderID)[1],
//                    json_encode($order),
//                    1
//                );
            $qrLib = new \chillerlan\QRCode\QRCode();
            $to = 'djavolak@mail.ru';
//                $msg = 'izvolte racun : ' . PHP_EOL . PHP_EOL;
            $msg = '<pre>' . $order->journal .'</pre>' . PHP_EOL . PHP_EOL;
            $msg .= '<img src="'. $qrLib->render($order->verificationUrl).'" alt="Pregled racuna" />';
            $subject = 'Vas racun';

            echo $msg;
            die();

            \wp_mail($to, $subject, $msg);
        }
    }

    public static function sendJsonToEsir($json, $logTitle) {
        $user = 'nssVwduqkqMHts7LQe2';
        $pass = 'nss56a32e50a63a97548881213989245c72';
        $url = 'https://cstest.abfiskal.rs:3005/csfiskal/apiOrdersReceiver';
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $json = substr($json, 3);
        $json = json_decode($json);
        foreach ($json->items as $item) {
            $item->label = \GF\DropBox\EsirIntegration::getPdvValues($item->label);
            $item->name = $item->Name;
            unset($item->Name);
        }
//    var_dump($json);
//    die();
        $json = json_encode($json);

        $request = new \GuzzleHttp\Psr7\Request('POST', $url, $headers, $json);
        $client = new \GuzzleHttp\Client(['auth' => [$user, $pass]]);
        try {
            $response = $client->send($request);
        } catch (\Exception $e) {
            $msg = $e->getMessage() . PHP_EOL . $e->getResponse()->getBody()->getContents() . PHP_EOL;
            $msg .= 'Tried to send : ' . $json;
//        \WP_Logging::add($logTitle . ' has FAILED', $msg);

            //debug
            var_dump('request error, tried to send: ');
            var_dump($json);
            echo $e->getMessage();
            echo $e->getResponse()->getBody()->getContents();
            die();
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            $msg = 'Status code: ' . $response->getStatusCode() . PHP_EOL;
            $msg .= $response->getBody()->getContents();
//        \WP_Logging::add($logTitle . ' has FAILED', $msg);
            // debug
            var_dump($response->getStatusCode());
            echo $response->getBody()->getContents();
            die();
            return false;
        }
        if ($response->getBody()->getContents() === '{"status":"OK"}') {
//        \WP_Logging::add($logTitle . ' has SUCCEDED', 'ok');
            return true;
        }
//    \WP_Logging::add($logTitle . ' has FAILED - general error. should not be after debugged.', 'damn');
        return false;
    }

    public static function getPdvValues($stopa) {
        $user = 'nssVwduqkqMHts7LQe2';
        $pass = 'nss56a32e50a63a97548881213989245c72';
        $url = 'https://cstest.abfiskal.rs:3005/csfiskal/apiGetTaxes';
        $request = new \GuzzleHttp\Psr7\Request('POST', $url);
        $client = new \GuzzleHttp\Client(['auth' => [$user, $pass]]);
        $response = $client->send($request);
        foreach (json_decode($response->getBody()->getContents())->data as $item) {
//        var_dump($item);
            if ($item->Stopa == $stopa) {
                return $item->Label;
            }
        }
        // @TODO debug REMOVE FOR PRODUCTION !!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        return $item->Label;
        throw new \Exception('could not get value');
    }
}