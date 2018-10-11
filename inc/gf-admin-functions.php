<?php
add_action('woocommerce_admin_order_totals_after_tax', 'custom_admin_order_totals_after_tax', 10, 1);
function custom_admin_order_totals_after_tax($orderid)
{
    $order = wc_get_order($orderid);
    $totalWeight = 0;
    foreach ($order->get_items() as $item_id => $item_data) {
        $totalWeight += $item_data->get_product()->get_weight() * $item_data->get_quantity();
    }

    $price = 0;
    if ($totalWeight > 0 and $totalWeight <= 0.5) {
        $price = 175;
    } elseif ($totalWeight > 0.5 and $totalWeight <= 2) {
        $price = 200;
    } elseif ($totalWeight > 2 and $totalWeight <= 5) {
        $price = 230;
    } elseif ($totalWeight > 5 and $totalWeight <= 10) {
        $price = 270;
    } elseif ($totalWeight > 10 and $totalWeight <= 20) {
        $price = 360;
    } elseif ($totalWeight > 20 and $totalWeight <= 30) {
        $price = 470;
    } elseif ($totalWeight > 30 and $totalWeight <= 50) {
        $price = 500;
    } elseif ($totalWeight > 50) {
        $newWeight = $totalWeight - 50;
        $price = 500 + ($newWeight * 10);
    }
    if (isset(array_keys($order->get_shipping_methods())[0])) {
        $order->remove_item(array_keys($order->get_shipping_methods())[0]);
        $order->set_shipping_total(0);
    }

    if ($price > 0 && $order->get_shipping_total() == 0) {
        $shipping = new WC_Order_Item_Shipping();
        $shipping->set_total($price);
        $order->add_item($shipping);
        $order->set_shipping_total($price);
        $order->save();
    }
}

?>