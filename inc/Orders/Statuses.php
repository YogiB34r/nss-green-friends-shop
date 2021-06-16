<?php


namespace GF\Orders;


class Statuses
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    private function registerStatus($statusName, $statusSlug)
    {
        register_post_status($statusSlug, [
            'label' => _x($statusName, 'Order status', 'woocommerce'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop($statusName . ' <span class="count">(%s)</span>',
                $statusName . '<span class="count">(%s)</span>', 'woocommerce')
        ]);
    }

    public function registerStatuses()
    {
        foreach ($this->config['statuses'] as $slug => $label) {
            //Registers custom post status in wp
            $this->registerStatus($label, $slug);

            //Sets our registered status inside of wc status list
            add_filter('wc_order_statuses', static function ($orderStatuses) use ($slug, $label) {
                $orderStatuses[$slug] = _x($label, 'Order status', 'woocommerce');
                return $orderStatuses;
            });
        }
    }

    public function setDefaultOrderStatus($paymentType, $status)
    {
        add_action('woocommerce_thankyou', static function ($orderId) use ($paymentType, $status) {
            if (!$orderId) {
                return;
            }
            $order = wc_get_order($orderId);
            if ($order) {
                $orderStatus = $order->get_status();
                if ($orderStatus === "processing" || $orderStatus === "on-hold") {
                    if ($order->get_payment_method() === $paymentType) {
                        $order->set_status($status);
                        $order->save();
                    }
                }
            }
        }, 10, 1);
    }
}