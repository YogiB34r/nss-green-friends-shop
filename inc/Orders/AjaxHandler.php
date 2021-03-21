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
            $marketplaceOrder = $_GET['marketplaceOrder'] !== '-1' ? $_GET['marketplaceOrder'] : '2';
        }
        if (isset($_GET['vendorIdSelect'])) {
            $vendorId = $_GET['vendorIdSelect'];
        }
        $formattedArray = [];

        $searchValue = $_GET['search']['value'] ?? '';

        //Without search
        if ($searchValue === '') {
            if ($marketplaceOrder === '1') {
                global $wpdb;
                $sql = "SELECT `post_id` FROM {$wpdb->postmeta} WHERE `meta_key` = 'marketplaceVendor'";
                if ($vendorId !== '-1') {
                    $sql .= ' AND `meta_value` = ' . $_GET['vendorIdSelect'];
                }
                $posts = $wpdb->get_results($sql, ARRAY_N);
                foreach ($posts as $post) {
                    $formattedArray[] = $post[0];
                }
            }
            //Default query
            $query = new \WC_Order_Query([
                'paginate' => true,
                'offset' => $_GET['start'],
                'limit' => $_GET['length'],
                'orderby' => 'date',
                'order' => 'DESC',
                'created_via' => $orderType,
                'payment_method' => $paymentMethod,
                'status' => $orderStatus,
                'date_created' => $dateFrom . '...' . $dateTo,
                'post__in' => $formattedArray,
            ]);
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
                'paymentMethod' => $order->get_payment_method(),
                'createdVia' => $order->get_created_via(),
                'date' => $order->get_date_created()->format('d/m/y'),
                'shippingMethod' => $order->get_shipping_method(),
                'total' => $order->get_total(),
                'status' => $order->get_status(),
                'shippingTotal' => $order->get_shipping_total(),
                'itemsTotal' => $order->get_subtotal(),
                'orderId' => sprintf('<input class="individualCheckbox" type="checkbox" data-id="%s">',
                    $order->get_id()),
                'actions' => $this->getActionsForOrder($order)
            ];
        }
        $data = [
            'draw' => (int)$_GET['draw'],
            'recordsTotal' => $result->total,
            'recordsFiltered' => $result->total,
            'data' => $orders,
            'pageOrdersTotal' => $pageOrdersTotal,
            'pageShippingTotal' => $pageShippingTotal,
            'pageOrderSubtotals' => $pageOrdersSubtotal
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
                    'paymentMethod' => $order->get_payment_method(),
                    'createdVia' => $order->get_created_via(),
                    'date' => $order->get_date_created()->format('d/m/y'),
                    'shippingMethod' => $order->get_shipping_method(),
                    'total' => $order->get_total(),
                    'status' => $order->get_status(),
                    'shippingTotal' => $order->get_shipping_total(),
                    'itemsTotal' => $order->get_subtotal(),
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
            'pageOrdersTotal' => $pageOrdersTotal,
            'pageShippingTotal' => $pageShippingTotal,
            'pageOrderSubtotals' => $pageOrdersSubtotal
        ];
        wp_send_json($data);
    }

    private function getActionsForOrder(\WC_Order $order)
    {
        $orderId = $order->get_id();
        return
            $this->getActionHtml('Predracun', 'printPreorder', $orderId) .
            $this->getActionHtml('Export', 'exportJitexOrder', $orderId) .
            $this->getActionHtml('Adresnica', 'adresnica', $orderId);
    }

    private function getActionHtml($title, $action, $orderId)
    {
        $actionUrl = sprintf('/back-ajax/?action=%s&id=%s', $action, $orderId);
        return sprintf('<a class="button" href="%s" title="%s" target="_blank">%s</a>', $actionUrl, $title, $title);
    }
}