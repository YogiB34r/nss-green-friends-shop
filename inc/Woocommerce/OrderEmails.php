<?php
namespace GF\Woocommerce;

class OrderEmails
{
    public function init()
    {
        add_action('woocommerce_order_status_changed', [$this, 'processingNotification'], 10, 3);
        add_action('woocommerce_order_status_reklamacija-pnns', [$this, 'sendReklamacijaNotice']);
        add_action('woocommerce_order_status_u-pripremiplaceno', [$this, 'sendPaymentNotice']);
        add_action('woocommerce_order_status_poslato', [$this, 'sendPoslatoNotice']);
    }

    public function processingNotification($order_id, $old, $new)
    {
        $order = wc_get_order($order_id);

        $targetStatuses = [
//            'naruceno' => 'Naručeno',
//            'u-pripremi-placeno' => 'U pripremi (plaćeno)',
//            'spz-pakovanje' => 'Spremno za pakovanje',
            'poslato' => 'Poslato',
//            'isporuceno' => 'Isporučeno',
//            'finalizovano' => 'Finalizovano',
//            'reklamacija' => 'Reklamacija',
            'reklamacija-pnns' => 'Reklamacija - proizvod nema na stanju',
            'stornirano-pn' => 'Stornirano - proizvod nema na stanju',
//            'stornirano' => 'Stornirano',
        ];

        if (in_array($new, array_keys($targetStatuses))) {
            $subject = 'Status Vaše narudžbine je promenjen';
            $msg = 'Status vaše narudžbine je promenjen u' . '<b> ' . $targetStatuses[$order->get_status()] . '</b>';
            $this->sendOrderStatusUpdateMail($order, $subject, $msg);

        }

        if (in_array($order->get_status(), ['u-pripremi', 'cekaseuplata']) && $order->get_meta('backOrderUndo') == '') {
            $mailer = WC()->mailer();
            $recipient = $order->get_billing_email();
            $from = 'prodaja@nonstopshop.rs';
            $headers = "Content-Type: text/html\r\n";
            $headers .= "From: NonStopShop <'{$from}'>\r\n";
            $headers .= "Reply-to: NonStopShop <'{$from}'>\r\n";
            $subject = 'Vaša nonstopshop.rs narudžbina je primljena';
            $template = 'emails/customer-processing-order.php';
            $content = wc_get_template_html($template, [
                'order' => $order,
                'email_heading' => $subject,
                'sent_to_admin' => true,
                'plain_text' => false,
                'email' => $mailer
            ]);

            $mailer->send($recipient, $subject, $content, $headers);
        }
    }

    public function sendReklamacijaNotice($orderId)
    {
        //(Neki od) Proizvod(a) koji ste naručili nemamo na stanju (u zavisnosti od toga da li je naručen 1 ili više proizvoda)

        $order = wc_get_order($orderId);
        $subject = 'Proizvod koji ste naručili nemamo na stanju';
        if (count($order->get_items()) > 1) {
            $subject = 'Neki od proizvoda koji ste naručili nemamo na stanju';
        }
        $msg = 'Poštovani, nažalost, neki od proizvoda iz Vaše narudžbenice trenutno nemamo na stanju.<br />
                Kolege će se potruditi da Vam pronađu odgovarajuću zamenu (ukoliko postoji), a zatim će Vas kontaktirati. <br />
                Hvala na razumevanju.';
        $this->sendOrderStatusUpdateMail($order, $subject, $msg);
    }

    public function sendPaymentNotice($orderId)
    {
        $order = wc_get_order($orderId);
        $subject = 'Vaša uplata je upravo evidentirana';
        $msg = 'Poštovani,<br /> 
                Vaša uplata je upravo evidentirana.<br />
                Očekivani rok isporuke je 3-5 radnih dana. Dodatno ćemo Vas obavestiti kad pošaljemo narudžbinu. ';
        $this->sendOrderStatusUpdateMail($order, $subject, $msg);
    }

    public function sendPoslatoNotice($orderId)
    {
        $order = wc_get_order($orderId);
        $subject = 'Vaša narudžbina je poslata';
        $msg = 'Poštovani, <br />
                Vaša pošiljka je upravo poslata. Isporuku možete očekivati sutra.<br/> 
                Isporuku vrši kurirska služba D Express, čiji će Vas kurir kontaktirati kako bi Vam uručio pošiljku.';
        $this->sendOrderStatusUpdateMail($order, $subject, $msg);
    }

    private function sendOrderStatusUpdateMail(\WC_Order $order, $subject, $msg)
    {
        $mailer = WC()->mailer();
        $recipient = $order->get_billing_email();
        $from = 'prodaja@nonstopshop.rs';
        $headers = "Content-Type: text/html\r\n";
        $headers .= "From: NonStopShop <'{$from}'>\r\n";
        $headers .= "Reply-to: NonStopShop <'{$from}'>\r\n";
        $template = 'emails/customer-order-status.php';
        $content = wc_get_template_html($template, [
            'order' => $order,
            'email_heading' => $subject,
            'sent_to_admin' => true,
            'plain_text' => false,
            'email' => $mailer,
            'msg' => $msg
        ]);

        $mailer->send($recipient, $subject, $content, $headers);
    }
}

$emails = new OrderEmails();
$emails->init();