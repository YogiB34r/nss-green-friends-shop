<?php
namespace GF\Esir;

use GF\Esir\Actions\SendAdvanceInvoice;
use GF\Esir\Actions\SendAdvanceRefund;
use GF\Esir\Actions\SendNormalInvoice;
use GF\Esir\Actions\SendNormalRefund;

class EsirIntegration
{
    const TEST_URL = 'https://cstest.abfiskal.rs:3005';
    const TEST_USER = 'nssVwduqkqMHts7LQe2';
    const TEST_PASS = 'nss56a32e50a63a97548881213989245c72';

    const PROD_URL = 'https://cube.cornerstone.rs:3005';
    const PROD_USER = 'nssaPAuqkqMTts9LQf3';
    const PROD_PASS = 'nsA2a32e512363a973456k121398924Cc7a';

    /**
     * @param $json
     * @return void
     * @throws \JsonException
     */
    public static function processEsirResponse($json): void
    {
        $orders = json_decode($json);
        if (is_array($orders)){
            foreach ($orders as $order) {
                self::processOrderAndSendEmail($order);
            }
        } else {
            self::processOrderAndSendEmail($orders);
        }

    }

    /**
     * @param $order
     * @return void
     * @throws \JsonException
     * @throws \Exception
     */
    private static function processOrderAndSendEmail($order): void
    {
        $wcOrderId = (int) explode('-', $order->orderID)[1];
        $action = $order->invoiceType . '-' . $order->transactionType;
        $wcOrder = wc_get_order($wcOrderId);
        switch ($order->invoiceType) {
            case 'NORMAL':
                if ($order->transactionType === 'SALE') {
                    $wcOrder->add_order_note('Fiskalni račun je kreiran, broj racuna je: ' . $order->invoiceNumber);
                    $wcOrder->update_meta_data('fiskalizationStatus', 'fiskalizovan');
                    \GF\Esir\EsirIntegrationLogHandler::saveResponse(
                        $wcOrderId,
                        json_encode($order, JSON_THROW_ON_ERROR),
                        $action,
                        EsirIntegrationLogHandler::STATUS_FISCALIZED
                    );
                } elseif ($order->transactionType === 'REFUND') {
                    $wcOrder->add_order_note('Uspesno storniran fiskalni racun, broj racuna je: ' . $order->invoiceNumber);
                    $wcOrder->update_meta_data('fiskalizationStatus', 'refundiran');
                    \GF\Esir\EsirIntegrationLogHandler::saveResponse(
                        $wcOrderId,
                        json_encode($order, JSON_THROW_ON_ERROR),
                        $action,
                        EsirIntegrationLogHandler::STATUS_REFUNDED
                    );

                }
                break;
            case 'ADVANCE':
                if ($order->transactionType === 'SALE') {
                    $wcOrder->add_order_note('Fiskalni avansni račun je kreiran, broj racuna je: ' . $order->invoiceNumber);
                    $wcOrder->update_meta_data('fiskalizationStatus', 'fiskalizovan-advance');
                    \GF\Esir\EsirIntegrationLogHandler::saveResponse(
                        $wcOrderId,
                        json_encode($order, JSON_THROW_ON_ERROR),
                        $action,
                        EsirIntegrationLogHandler::STATUS_FISCALIZED
                    );
                } elseif ($order->transactionType === 'REFUND') {
                    $wcOrder->add_order_note('Uspesno storniran avansni fiskalni racun, broj racuna je: ' . $order->invoiceNumber);
                    $wcOrder->update_meta_data('fiskalizationStatus', 'refundiran-advance');
                    \GF\Esir\EsirIntegrationLogHandler::saveResponse(
                        $wcOrderId,
                        json_encode($order, JSON_THROW_ON_ERROR),
                        $action,
                        EsirIntegrationLogHandler::STATUS_REFUNDED
                    );
                }
                break;
        }
        $wcOrder->save();
        $msg = '<pre><p>Broj narudžbenice #<b>'.$wcOrder->get_order_number().'</b></p>' . $order->journal .'</pre>' . PHP_EOL . PHP_EOL;
        $msg .= '<img src="'. static::saveQrImage($order).'" alt="Pregled racuna" />';
        $subject = 'Vas racun';
        $body = static::compileMail($order->verificationUrl, $msg, $wcOrder);
        $to = get_user_by('ID', $wcOrder->get_customer_id())->user_email;
        add_filter( 'wp_mail_content_type', function( $content_type ) { return 'text/html'; } );
        \wp_mail($to, $subject, $body);
        $to = 'narudzbenice@nonstopshop.rs';
        \wp_mail($to, $subject, $body);
    }

    /**
     * @param $order
     * @return string
     */
    public static function saveQrImage($order): string
    {
        $qrLib = new \chillerlan\QRCode\QRCode();
        $qrFileName = $order->orderID . '.jpg';
        $qrPath = WP_CONTENT_DIR . '/uploads/qrinvoices/' . $qrFileName;
        $qrLib->render($order->verificationUrl, $qrPath);

        return home_url() . '/wp-content/uploads/qrinvoices/' . $qrFileName;
    }

    /**
     * @param $json
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    public static function sendJsonToEsir($json): bool
    {
        if (ENVIRONMENT === 'production') {
            $user = static::PROD_USER;
            $pass = static::PROD_PASS;
            $url = static::PROD_URL . '/csfiskal/apiOrdersReceiver';
        } else {
            $user = static::TEST_USER;
            $pass = static::TEST_PASS;
            $url = static::TEST_URL . '/csfiskal/apiOrdersReceiver';
        }
        $headers = [
            'Content-Type' => 'application/json',
        ];
        foreach ($json->items as $item) {
            $item->label = static::getPdvValues($item->label);
        }
        $json = json_encode($json, JSON_THROW_ON_ERROR);
        $client = new \GuzzleHttp\Client(['auth' => [$user, $pass]]);
        try {
            $msg = "Sending json to esir: " . $json;
            static::errorLog($msg);
            $response = $client->send(new \GuzzleHttp\Psr7\Request('POST', $url, $headers, $json));
        } catch (\Exception $e) {
            $msg = $e->getMessage() . PHP_EOL . $e->getResponse()->getBody()->getContents() . PHP_EOL;
            $msg .= 'Tried to send : ' . $json;
            static::errorLog($msg);
            return false;
        }
        if ($response->getStatusCode() !== 200) {
            $msg = 'Status code: ' . $response->getStatusCode() . PHP_EOL;
            $msg .= $response->getBody()->getContents() . PHP_EOL;
            static::errorLog($msg);
            return false;
        }
        $response = $response->getBody()->getContents();
        if ($response === '{"status":"OK"}') {
            return true;
        }
        static::errorLog('Process has FAILED !!! - general error. should not be after debugged!!!');
        return false;
    }

    /**
     * @param $stopa
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getPdvValues($stopa) {
        if (ENVIRONMENT === 'production') {
            $user = static::PROD_USER;
            $pass = static::PROD_PASS;
            $url = static::PROD_URL . '/csfiskal/apiGetTaxes';
        } else {
            $user = static::TEST_USER;
            $pass = static::TEST_PASS;
            $url = static::TEST_URL . '/csfiskal/apiGetTaxes';
        }

        $request = new \GuzzleHttp\Psr7\Request('POST', $url);
        $client = new \GuzzleHttp\Client(['auth' => [$user, $pass]]);
        $response = $client->send($request);
        foreach (json_decode($response->getBody()->getContents())->data as $item) {
            if ($item->Stopa == $stopa) {
                return $item->Label;
            }
        }
        if (ENVIRONMENT !== 'production') {
            return $item->Label;
        }

        throw new \Exception('could not get value');
    }

    /**
     * @param $msg
     * @return void
     */
    public static function errorLog($msg): void
    {
        $path = WP_CONTENT_DIR . '/uploads/fiskalizacija.log';
        $msg = date('Y-m-d H:i:s') . ' | ' . $msg;
        file_put_contents($path, $msg, FILE_APPEND);
    }

    /**
     * @param $downloadLink
     * @param $fiskalniIsecak
     * @param $order
     * @return string
     */
    public static function compileMail($downloadLink, $fiskalniIsecak, $order): string
    {
        return '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Fiskalni Račun</title>
</head>
<body>
<table align="center" width="600px" style="width:600px;">
    <tbody style="width:600px;">
    <tr>
        <td><p>Kompanija <b>NON STOP SHOP DOO BEOGRAD</b> poslala Vam je fiskalizovan račun za narudžbenicu <b>#'.$order->get_order_number().', </b>preuzmite ga <b>bez naknade</b>
               klikom na sledeći link.</p></td>
    </tr>
    <tr>
        <td valign="middle" align="center">
            <a style="width: max-content; color: white; background-color: rebeccapurple; padding: 10px;
             border-radius: 5px; display: block; text-decoration: none"
               href="'.$downloadLink.'"><b>PREUZMITE Račun / DOWNLOAD Invoice<br/>LINK</b></a>
        </td>
    </tr>
    <tr>
        <td align="center">
           '.$fiskalniIsecak.'
        </td>
    </tr>
    <tr>
        <td>
            <p style="width:600px;word-break: break-all;">
                Ukoliko ne možete preuzeti fiskalni račun, kopirajte i zalepite URL koji se nalazi ispod u svoj Internet pretraživač ili
                pozovite tehničku podršku na telefon <b>011/7450-380</b>
            </p>
            <p style="width:600px;word-break: break-all;">'.$downloadLink.'</p>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
        ';
    }
}