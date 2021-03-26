<?php


namespace GF\Orders;

use ZipArchive;

class AjaxHandler
{
    /**
     * @var OrderAnalytics
     */
    private $orderPage;

    /**
     * AjaxHandler constructor.
     */
    public function __construct()
    {
        $this->orderPage = new OrderAnalytics();
    }

    public function handleAction()
    {
        switch ($_GET['ajaxAction']) {
            case 'bulkActions':
                switch ($_POST['bulkAction']) {
                    case '1':
                        $this->moveToTrash($_POST['orderIds']);
                        break;
                    case '2':
                        $this->adresnice($_POST['orderIds']);
                        break;
                    case '3':
                        $this->jitexExport($_POST['orderIds']);
                        break;
                    default:
                        $this->changeOrderStatus($_POST['orderIds'], $_POST['bulkAction']);
                }

                break;
            default:
                $this->getOrders();
        }
    }

    private function moveToTrash($orderIds)
    {
        foreach ($orderIds as $orderId) {
            wp_trash_post($orderId);
        }
        wp_send_json_success();
    }

    private function adresnice($orderIds)
    {
        $zipArchive = new ZipArchive();
        $zipPath = generateUploadsPath() . date('Ymdhis') . '-adresnice-' . md5(serialize($orderIds)) . '.zip';
        $open = $zipArchive->open($zipPath, ZipArchive::CREATE);
        if ($open !== true) {
            var_dump($open);
            die('cannot open');
        }

        foreach ($orderIds as $orderId) {
            $order = wc_get_order($orderId);
            $path = \Gf\Util\Adresnica::createAdresnicaPdf($order);
            if (file_exists($path) && is_readable($path)) {
                $add = $zipArchive->addFile($path, basename($path));
            } else {
                throw new \Exception('there was a problem reading file: ' . $path);
            }
            if ($add !== true) {
                throw new \Exception('could not add file to archive');
            }
        }
        if ($zipArchive->close() !== true) {
            throw new \Exception('could not close archive.');
        }
        $path = str_replace('public_html', '', str_replace(strstr($zipPath, 'public_html', true), '', $zipPath));
        wp_send_json_success(['zipUrl' => get_home_url() . $path]);
    }

    private function jitexExport($orderIds)
    {
        $zipArchive = new ZipArchive();
        $zipPath = generateUploadsPath() . date('Ymdhis') . '-export-' . md5(serialize($orderIds)) . '.zip';
        $zipArchive->open($zipPath, ZipArchive::CREATE);
        foreach ($orderIds as $orderId) {
            $order = wc_get_order($orderId);
            $csvText = \Gf\Util\Jitex::parseJitexDataFromOrder($order);
            $zipArchive->addFromString($order->get_order_number() . '.txt', $csvText);
        }
        if ($zipArchive->close() !== true) {
            var_dump('could not close archive.');
            die();
        }
        $path = str_replace('public_html', '', str_replace(strstr($zipPath, 'public_html', true), '', $zipPath));
        wp_send_json_success(['zipUrl' => get_home_url() . $path]);
    }

    private function changeOrderStatus($orderIds, $status)
    {
        foreach ($orderIds as $orderId) {
            $order = wc_get_order($orderId);
            $order->set_status($status);
            $order->save();
        }
        wp_send_json_success();
    }

    private function getOrders()
    {
        $dt = new \DateTime();
        $orderType = '';
        $dateTo = $_GET['to'] ?? null;
        $dateFrom = $_GET['from'] ?? null;
        $marketplaceOrder = '';
        $orderStatus = '';
        $paymentMethod = '';

        if (isset($_GET['orderType'])) {
            $orderType = $_GET['orderType'] !== '-1' ? $_GET['orderType'] : '';
        }
        if (isset($_GET['paymentMethod'])) {
            $paymentMethod = $_GET['paymentMethod'] !== '-1' ? $_GET['paymentMethod'] : '';
        }
        if (isset($_GET['orderStatus'])) {
            $orderStatus = $_GET['orderStatus'] !== '-1' ? $_GET['orderStatus'] : '';
        }
        if ($dateFrom === null || $dateFrom === '') {
            $dateFrom = (string)$dt->setTimestamp(0)->getTimestamp();
        }
        if ($dateTo === null || $dateTo === '') {
            $dateTo = (string)time();
        }
        if (isset($_GET['marketplaceOrder'])) {
            $marketplaceOrder = $_GET['marketplaceOrder'];
        }
        if (isset($_GET['vendorIdSelect'])) {
            $vendorId = $_GET['vendorIdSelect'];
        }
        $formattedArray = [];
        $searchValue = $_GET['search']['value'] ?? '';

        //Without search
        if ($searchValue === '') {
            if ($marketplaceOrder !== '-1') {
                global $wpdb;
                $sql = "SELECT `post_id` FROM {$wpdb->postmeta} WHERE `meta_key` = 'marketplaceVendor'";
                if (isset($vendorId) && $vendorId !== '-1') {
                    $sql .= ' AND `meta_value` = ' . $_GET['vendorIdSelect'];
                }
                $posts = $wpdb->get_results($sql, ARRAY_N);
                foreach ($posts as $post) {
                    $formattedArray[] = $post[0];
                }
            }
            $args = [
                    'paginate' => true,
                    'offset' => $_GET['start'],
                    'limit' => $_GET['length'],
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'created_via' => $orderType,
                    'payment_method' => $paymentMethod,
                    'status' => $orderStatus,
                    'date_created' => $dateFrom . '...' . $dateTo
                ];

            if ($marketplaceOrder === '1') {
                $args['post__in'] = $formattedArray;
            }
            if ($marketplaceOrder === '2'){
                $args['post__not_in'] = $formattedArray;
            }

            $query = new \WC_Order_Query($args);
            $result = $query->get_orders();
        }

        $orders = [];
        $pageOrdersSubtotal = 0;
        $pageShippingTotal = 0;
        $pageOrdersTotal = 0;
        /** @var \WC_Order $order */
        foreach ($result->orders as $order) {
            $pageOrdersSubtotal += $order->get_subtotal();
            $pageShippingTotal += (int)$order->get_shipping_total();
            $pageOrdersTotal += (int)$order->get_total();
            $orders[] = [
                'orderTitle' => $this->orderPage->formatOrderName($order),
                'paymentMethod' => $order->get_payment_method_title(),
                'createdVia' =>$this->changeCreatedViaTitle($order->get_created_via()),
                'date' => $order->get_date_created()->format('d/m/y'),
                'shippingMethod' => $order->get_shipping_method(),
                'total' => $order->get_total().get_woocommerce_currency_symbol(),
                'status' => $this->getStatusMarkup($order->get_status(), wc_get_order_notes(['order_id' => $order->get_id(),
                    'customer_note' => 'false','added_by' => 'system'])),
                'shippingTotal' => $order->get_shipping_total().get_woocommerce_currency_symbol(),
                'itemsTotal' => $order->get_subtotal().get_woocommerce_currency_symbol(),
                'orderId' => sprintf('<input class="individualCheckbox" type="checkbox" data-id="%s">',
                    $order->get_id()[0]),
                'actions' => $this->getActionsForOrder($order)
            ];
        }
        $data = [
            'draw' => (int)$_GET['draw'],
            'recordsTotal' => $result->total,
            'recordsFiltered' => $result->total,
            'data' => $orders,
            'pageOrdersTotal' => $pageOrdersTotal.get_woocommerce_currency_symbol(),
            'pageShippingTotal' => $pageShippingTotal.get_woocommerce_currency_symbol(),
            'pageOrderSubtotals' => $pageOrdersSubtotal.get_woocommerce_currency_symbol()
        ];
        wp_send_json($data);

        //With Search
        global $wpdb;
        $countSql = "SELECT COUNT(ID) FROM wp_posts WHERE (ID LIKE '%{$searchValue}%' OR ID IN (SELECT post_id FROM wp_postmeta WHERE meta_key = '_customer_user' AND meta_value IN (SELECT ID FROM wp_users WHERE display_name LIKE '%{$searchValue}%'))) AND post_status NOT LIKE 'trash' AND post_status NOT LIKE 'auto-draft' LIMIT {$_GET['length']} OFFSET {$_GET['start']}";
        $totalOrdersCount = $wpdb->get_results($countSql, ARRAY_N)[0][0];
        $sql = "SELECT * FROM wp_posts WHERE (ID LIKE '%{$searchValue}%' OR ID IN (SELECT post_id FROM wp_postmeta WHERE meta_key = '_customer_user' AND meta_value IN (SELECT ID FROM wp_users WHERE display_name LIKE '%{$searchValue}%'))) AND post_status NOT LIKE 'trash' AND post_status NOT LIKE 'auto-draft' LIMIT {$_GET['length']} OFFSET {$_GET['start']}";
        $posts = $wpdb->get_results($sql);
        $formattedArray = [];

        foreach ($posts as $post) {
            $formattedArray[] = $post->ID;
        }
        $orders = [];
        $pageOrdersTotal = 0;
        $pageShippingTotal = 0;
        $pageOrdersSubtotal = 0;
        foreach ($formattedArray as $postId) {
            $order = wc_get_order($postId);
            if ($order) {
                $pageOrdersSubtotal += $order->get_subtotal();
                $pageShippingTotal += (int)$order->get_shipping_total();
                $pageOrdersTotal += (int)$order->get_total();
                $orders[] = [
                    'orderTitle' => $this->orderPage->formatOrderName($order),
                    'paymentMethod' => $order->get_payment_method_title(),
                    'createdVia' =>$this->changeCreatedViaTitle($order->get_created_via()),
                    'date' => $order->get_date_created()->format('d/m/y'),
                    'shippingMethod' => $order->get_shipping_method(),
                    'total' => $order->get_total().get_woocommerce_currency_symbol(),
                    'status' => $this->getStatusMarkup($order->get_status(), wc_get_order_notes(['order_id' => $order->get_id(),
                        'customer_note' => 'false','added_by' => 'system'])),
                    'shippingTotal' => $order->get_shipping_total().get_woocommerce_currency_symbol(),
                    'itemsTotal' => $order->get_subtotal().get_woocommerce_currency_symbol(),
                    'orderId' => sprintf('<input class="individualCheckbox" type="checkbox" data-id="%s">',
                        $order->get_id()),
                    'actions' => $this->getActionsForOrder($order)
                ];
            }
        }
        $data = [
            'draw' => (int)$_GET['draw'],
            'recordsTotal' => $totalOrdersCount,
            'recordsFiltered' => $totalOrdersCount,
            'data' => $orders,
            'pageOrdersTotal' => $pageOrdersTotal.get_woocommerce_currency_symbol(),
            'pageShippingTotal' => $pageShippingTotal.get_woocommerce_currency_symbol(),
            'pageOrderSubtotals' => $pageOrdersSubtotal.get_woocommerce_currency_symbol()
        ];
        wp_send_json($data);
    }

    private function getActionsForOrder(\WC_Order $order)
    {
        return $this->predracunAction($order) . $this->exportAction($order) . $this->adresnicaAction($order) .
            $this->noteAction($order) . $this->syncToMisAction($order);
    }

    private function adresnicaAction($order)
    {
        if ($order->get_meta('adresnicaCreated')) {
            $style = 'color:white;background-color:gray;font-style:italic;';
        }
        return sprintf('<a style="%s" class="button" href="/back-ajax/?action=adresnica&id=%s" target="_blank">%s</a>',
            $style ?? '', $order->get_id(), 'Adresnica');
    }

    private function exportAction($order)
    {
        if ($order->get_meta('jitexExportCreated')) {
            $style = 'color:white;background-color:gray;font-style:italic;';
        }
        return sprintf('<a style="%s" class="button" href="/back-ajax/?action=exportJitexOrder&id=%s" target="_blank">%s</a>',
            $style ?? '', $order->get_id(), 'Export');
    }

    private function predracunAction($order)
    {
        return sprintf('<a class="button" href="/back-ajax/?action=exportJitexOrder&id=%s" target="_blank">%s</a>',
             $order->get_id(), 'Predracun');
    }

    private function syncToMisAction($order)
    {
        if ($order->get_meta('synced')) {
            $style = 'color:white;background-color:gray;font-style:italic';
        }
        $user = wp_get_current_user();
        if ($user->ID === 1) {
            return sprintf('<a style="%s" class="button" href="/back-ajax/?action=mis&type=order&id=%s" target="_blank">%s</a>',
                $style ?? '' ,$order->get_id(), 'Mis');
        }
        return '';
    }

    private function noteAction($order)
    {
        $orderNote = $order->get_customer_note();
        if ($orderNote !== '') {
            return sprintf('<a class="button" style="background-color:yellow;" href="#" title="%s" 
            target="_blank">Napomena</a>', $orderNote);
        }
        return '';
    }

    private function getStatusMarkup($status, $notes)
    {
        $color = 'black';
        $title = 'Nepoznat Status';
        $backgroundColor = '#e5e5e5';
        $note = '';
        $dashicon = '';
        if (count($notes) > 0) {
            $note = $notes[0]->content;
            $dashicon = '<span class="dashicons dashicons-format-status"></span></span>';
        }
        switch ($status){
            case 'pending':
                $title = 'Čeka se naplata';
                break;
            case 'processing':
                $backgroundColor = '#5b841b';
                $title = 'Procesuira se';
                break;
            case 'on-hold':
                $backgroundColor = '#94660c';
                $title= 'Na čekanju';
                break;
            case 'completed':
                $backgroundColor = '';
                $title = 'Završeno';
                break;
            case 'cancelled':
                $backgroundColor = '';
                $title = 'Otkazano';
                break;
            case 'failed':
                $backgroundColor = '';
                $title = 'Neuspelo';
                break;
            case 'stornirano':
                $backgroundColor = '#D50000';
                $title = 'Stornirano';
                break;
            case 'reklamacija':
                $backgroundColor = 'D50000';
                $title = 'Reklamacija';
                break;
            case 'vracena-posiljka':
                $backgroundColor = 'D50000 ';
                $title = 'Vraćena pošiljka';
                break;
            case 'stornirano-pn':
                $backgroundColor = 'D50000 ';
                $title = 'Stornirano (povraćaj novca)';
                break;
            case 'reklamacija-pnns':
                $backgroundColor = 'D50000 ';
                $title = 'Reklamacija (proizvod nema na stanju)';
                break;
            case 'finalizovano':
                $backgroundColor = '#5b841b';
                $title = 'Finalizovano';
                break;
            case 'isporuceno':
                $backgroundColor = '#6600cc';
                $title = 'Isporučeno';
                break;
            case 'spz-slanje':
                $backgroundColor = '#cc00ff';
                $title = 'Spremno za slanje';
                break;
            case 'spz-pakovanje':
                $backgroundColor = '#80B0FF';
                $title = 'Spremno za pakovanje';
                break;
            case 'naruceno':
                $backgroundColor = '#80B0FF';
                $title = 'Naručeno';
                break;
            case 'poslato':
                $backgroundColor = '#FFA3A3';
                $title = 'Poslato';
                break;
            case 'u-obradi':
                $title = 'U obradi';
                break;
            case 'u-pripremiplaceno':
                $backgroundColor = 'yellow';
                $title = 'U pripremi (plaćeno)';
                break;
            case 'u-pripremi':
                $backgroundColor = 'yellow';
                $title = 'U pripremi';
                break;
            case 'cekaseuplata':
                $backgroundColor = '#5b841b';
                $title = 'Čeka se uplata';
                break;
        }
        if ($backgroundColor === 'yellow') {
            $color = 'black';
        }
        return sprintf('<span title="%s" class="tableStatus" style=" font-weight:bold;background-color: %s;color: %s">%s%s</span>',
            $note, $backgroundColor, $color, $title, $dashicon);
    }

    private function changeCreatedViaTitle($createdVia)
    {
        switch ($createdVia){
            case 'checkout':
                $title = 'WWW';
                break;
            case 'admin':
                $title = 'Telefonom';
                break;
            default :
                $title = 'Nepoznat nacin kreiranja';
        }
        return $title;
    }
}