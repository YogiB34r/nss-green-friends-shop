<?php

use GF\Esir\EsirIntegration;
use GF\Esir\EsirIntegrationLogHandler;
use GuzzleHttp\Exception\GuzzleException;
use League\Flysystem\FilesystemException;

if (isset($_POST) && count($_POST) > 0){
    try {
        $orderId = $_POST['orderNumber'] ?? null;
        if ($orderId) {
            $orderId = explode('-', $orderId)[1];
            $order = wc_get_order($orderId);
            if ($order) {
                $jitexOrderNumber = $_POST['jitexOrderNumber'] ?? null;
                //this is hack for older orders
                $referentDocumentNumber = 'XXXXXXXX-XXXXXXXX-'.$jitexOrderNumber;

                //this is hidden input for refunds that are not created by Jitex it is shown by pressing alt + s
                if (isset($_POST['refOrderId'])  && $_POST['refOrderId'] !== '') {
                    $referentDocumentNumber = $_POST['refOrderId'];
                }
                $order = wc_get_order($orderId);
                $orderNumber = $order->get_order_number();
                $dropbox = new \GF\DropBox\DropboxApi();
                $dropbox->setupFileSystem();
                $json = $dropbox->getOrderFileContents($orderNumber);
                EsirIntegrationLogHandler::saveResponse($orderId, $json, 'getFile',
                    EsirIntegrationLogHandler::STATUS_WAITING);
                $json = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
                $json->referentDocumentNumber = $referentDocumentNumber;
                EsirIntegration::sendJsonToEsir($json, $orderId);
                echo '<div class="notice notice-success is-dismissible">
                            <p>Poslato refundiranje na fiskalizaciju</p>
                          </div>';
                $_POST = [];
            } else {
                    echo '<div class="notice notice-error is-dismissible">
                            <p>Nije pronađena narudžbenica sa unetim brojem</p>
                          </div>';
            }
        }
    } catch (GuzzleException|JsonException|FilesystemException $e) {
        var_dump($e->getMessage());
        die();
    }
}
?>
<form style="display: flex; flex-direction: column; width: 10%; gap: 10px" method="POST">
    <label style="display: flex;flex-direction: column;">
        <span>Broj Narudžbenice iz admina</span>
        <input required type="text" name="orderNumber" value="<?=$_POST['orderNumber'] ?? ''?>">
    </label>
    <label style="display: flex;flex-direction: column;">
        <span>Broj Računa iz jitexa za originalnu nardužbenicu</span>
        <input required type="text" name="jitexOrderNumber" value="<?=$_POST['jitexOrderNumber'] ?? ''?>">
    </label>
    <label class="hiddenInput" style="display: none;flex-direction: column;">
        <span>Referentni broj nardužbine</span>
        <input type="text" name="refOrderId" value="<?=$_POST['refOrderId'] ?? ''?>">
    </label>
    <button type="submit">Pošalji</button>
</form>

<script>
    document.addEventListener('keyup', function (e){
        if (e.altKey && e.key === 's') {
            document.querySelector('.hiddenInput').style.display = 'flex';
        }
    });
</script>

