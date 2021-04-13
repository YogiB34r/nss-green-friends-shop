<?php


namespace GF\Orders;

class OrderAnalytics
{
    public function init()
    {
        $this->hooks();
    }

    private function hooks()
    {
        add_action('admin_menu', function () {
            $this->addMenuPage();
        });
        $ajaxHandler = new AjaxHandler();
        add_action('wp_ajax_gfOrdersTable', [$ajaxHandler, 'handleAction']);
        add_action('admin_enqueue_scripts', function (){
            $this->enqueueScripts();
            $this->enqueueStyles();
        });
    }

    private function addMenuPage()
    {
        add_submenu_page('nss-panel', 'Order Analytics', 'Orders', 'manage_options', 'order-analytics',
            [$this, 'menuPage']);
    }

    public function menuPage()
    {
        global $wpdb;

        //Used in select menu for filters
        $vendorDataTable = $wpdb->prefix . 'mpVendorData';
        $sql = "SELECT `vendorId` FROM {$vendorDataTable} WHERE `isActive` = 1";
        $activeVendors = $wpdb->get_results($sql);
        $dataStore = \WC_Data_Store::load('order');
        include('templates/orderTable.phtml');
    }
    /**
     * @param $order
     * @return string
     */
    public function formatOrderName($order)
    {
        $orderDate = $order->get_date_created()->format('dmY');
        $orderTitle = sprintf('# %s - %d %s %s', $orderDate, $order->get_id(), $order->get_billing_first_name(),
            $order->get_billing_last_name());
        $editLink = admin_url() . '/post.php?post=' . $order->get_id() . '&action=edit';
        return sprintf('<a target="_blank" href="%s">%s</a>', $editLink, $orderTitle);
    }

    public function enqueueStyles()
    {
        wp_enqueue_style('datatables','https://cdn.datatables.net/v/dt/dt-1.10.24/datatables.min.css');
        wp_enqueue_style('datepicker','//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        wp_enqueue_style('orderTableCss',get_stylesheet_directory_uri().'/inc/Orders/assets/style.css',[],'1.0.1');
    }

    public function enqueueScripts()
    {
        wp_enqueue_script('jqueryUi','https://code.jquery.com/ui/1.12.1/jquery-ui.js',
            ['jquery'], '1.0.0', true);
        wp_enqueue_script('datatables','https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js',
            ['jquery'],'1.0.0', true);
        wp_enqueue_script('orderTableJs', get_stylesheet_directory_uri().'/inc/Orders/assets/main.js',
            ['jqueryUi', 'datatables', 'jquery'],'1.0.1',true);
        wp_localize_script('orderTableJs','gfData', ['ajaxUrl' => admin_url('admin-ajax.php')]);
    }

    public function getOrderStatuses()
    {
        $defaultStatusesInUse = ['wc-on-hold' => _x( 'On hold', 'Order status', 'woocommerce' ),'wc-pending' => _x( 'Pending payment', 'Order status', 'woocommerce' )];
        $withCustomStatuses = apply_filters('wc_order_statuses', $defaultStatusesInUse);
        $statuses = [];
        foreach ($withCustomStatuses as $key => $status) {
            $statuses[] =
                [
                    'slug' => $key,
                    'value' => $status
                ];
        }
        return $statuses;
    }
}