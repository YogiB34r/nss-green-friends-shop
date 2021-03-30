<?php


namespace GF\Woocommerce;


use Composer\Package\Loader\ValidatingArrayLoader;
use GF\Marketplace\Marketplace;

use function GuzzleHttp\Psr7\parse_query;

class Shipping
{

    /**
     * Shipping constructor.
     */
    public function __construct()
    {
    }

    public function init()
    {
        //Customer orders
        add_filter('woocommerce_package_rates', [$this, 'customShippingRatesCustomer'], 12, 2);
        add_action('woocommerce_before_cart', [$this, 'customShippingPriceNotice'], 50);

        //Manual orders
        add_action('woocommerce_admin_order_totals_after_tax', [$this, 'manualOrdersShippingCalculation'], 10, 1);
    }

    /**
     * @param $rates
     * @return mixed
     */
    public function customShippingRatesCustomer($rates)
    {
        $cartWeight = WC()->cart->cart_contents_weight;
        $cartContents = WC()->cart->get_cart_contents();

        $overrides = $this->settingCustomShippingPriceOverride($cartWeight, $cartContents);
        $customCost = $overrides['customShippingCost'];
        $cartWeight = $overrides['cartWeight'];

        if ($customCost > 0) {
            $this->changeRatesBasedOnPriceOverride($rates, $cartWeight, $customCost);
        }
        return $this->getWeightBasedShippingRate($rates, $cartWeight);
    }


    public function manualOrdersShippingCalculation($orderId)
    {
        $order = wc_get_order($orderId);
        $cartWeight = 0;
        $cartContent = [];
        $i = 0;

        //Serbia iz zone id 1
        $zone = \WC_Shipping_Zones::get_zone('1');
        $rates = [];

        /** @var \WC_Shipping_Flat_Rate $shippingMethod */
        foreach ($zone->get_shipping_methods() as $shippingMethod) {
            $rates[$shippingMethod->get_rate_id()] = $shippingMethod;
        }
        $suppliers = [];
        /** @var \WC_Order_Item_Product $item */
        foreach ($order->get_items() as $item) {
            $cartWeight += $item->get_product()->get_weight() * $item->get_quantity();
            $cartContent[$i]['data'] = $item->get_product();
            $cartContent[$i]['quantity'] = $item->get_quantity();
            $product = wc_get_product($item->get_product_id());
            $supplierId = (int)$product->get_meta('supplier');
            if (!in_array($supplierId, $suppliers, true)) {
                $suppliers[] = $supplierId;
            }
            $i++;
        }
        $overrides = $this->settingCustomShippingPriceOverride($cartWeight, $cartContent);
        $customCost = $overrides['customShippingCost'];
        $cartWeight = $overrides['cartWeight'];

        $rate = $this->getWeightBasedShippingRate($rates, $cartWeight);
        $rate = array_values($rate)[0];


        if (count($suppliers) === 1) {
            $marketplace = new Marketplace();
            $vendor = $marketplace->getByVendorId($suppliers[0]);
            if (isset($vendor['isActive']) && $vendor['isActive'] === '1') {
                $minPrice = (int)$vendor['minFreeShippingCost'];
                if ($minPrice) {
                    $orderPrice = $order->get_subtotal();
                    if ($orderPrice >= $minPrice) {
                        $case = 1;
                    } else {
                        $case = 0;
                        $shippingPriceTable = unserialize($vendor['shippingPrices'],
                                ['allowed_classes' => false]) ?? [];
                        $overWeightPrice = (int)($shippingPriceTable['overWeightPrice'] ?? 0);
                        unset($shippingPriceTable['overWeightPrice']);
                        if (count($shippingPriceTable) > 0) {
                            foreach ($shippingPriceTable as $key => $value) {
                                if ((float)$value['weight'] >= $cartWeight) {
                                    $maxWeight = (float)$value['weight'];
                                    $minWeight = (float)$shippingPriceTable[$key - 1]['weight'];
                                    $label = $minWeight . ' - ' . $maxWeight . ' kg';
                                    $shippingPrice = (float)$value['price'];
                                    break;
                                }
                                $lastItemIndex = count($shippingPriceTable)-1;
                                if ($overWeightPrice !== 0 && $key === $shippingPriceTable[$lastItemIndex] && ((float)$value['weight'] < $cartWeight)) {
                                    $shippingPrice = (float)$value['price'] + ($cartWeight - (float)$value['weight']) * $overWeightPrice;
                                    $label = sprintf('Preko %d kg (%d) + %ddin po kg',
                                    (float)$value['weight'], (int)$value['price'], $overWeightPrice);
                                } else {
                                    $maxWeight = (float)$value['weight'];
                                    $label = 'Preko ' . $maxWeight . ' kg';
                                    $shippingPrice = (float)$value['price'];
                                }
                            }

                            /** @var \WC_Shipping_Flat_Rate $rate */
                            foreach ($rates as $index => $rate) {
                                if ($rate->get_rate_id() === 'flat_rate:12') {
                                    $marketplaceRate = $rate;
                                    $marketplaceRateLabel = $label;
                                    $marketplaceRateCost = $shippingPrice;
                                    $marketplaceRateId = $rate->get_rate_id();
                                    break;
                                }
                            }
                            $rate = $marketplaceRate ?? $rate;
                        }
                    }
                }
            }
            if (in_array('free_shipping', parse_query(urldecode($_POST['items'])))) {
                $case = 1;
            }
        } elseif (isset($_POST['action']) && ($_POST['action'] === 'woocommerce_save_order_items' ||
                $_POST['action'] === 'woocommerce_calc_line_taxes')) {
            $case = 0;
            if (in_array('free_shipping', parse_query(urldecode($_POST['items'])))) {
                $case = 1;
            }
        }
        /** @var \WC_Shipping_Flat_Rate $rate */
        $cost = $rate->get_option('cost');
        $title = $rate->title;
        if (isset($marketplaceRateLabel, $marketplaceRateId, $marketplaceRateCost)
            && $marketplaceRateLabel !== ''
            && $marketplaceRateId !== ''
            && $marketplaceRateCost !== '') {
            $title = $marketplaceRateLabel;
            $cost = (int)$marketplaceRateCost;
        }

        if ($cartWeight == 0) {
            $cost = 0;
        }

        if ($customCost > 0) {
            $cost = $cost + $customCost;
            $title = 'Dostava';
        } else {
            $title = 'Dostava: ' . $title;
        }
        if (isset($_POST['items'])) {
            switch ($case) {
                case 0:
                    $order->remove_order_items('shipping');
                    $shipping = new \WC_Order_Item_Shipping();
                    $shipping->set_props(['method_title' => $title, 'method_id' => $rate->id, 'total' => $cost]);
                    $shipping->apply_changes();
                    $order->add_item($shipping);
                    $order->calculate_shipping();
                    $order->calculate_totals();
                    $order->save();
                    break;

                case 1 :
                    $order->remove_order_items('shipping');
                    $shipping = new \WC_Order_Item_Shipping();
                    $freeShipping = new \WC_Shipping_Free_Shipping();
                    $shipping->set_props([
                        'method_title' => $freeShipping->title,
                        'method_id' => $freeShipping->id,
                        'total' => 0
                    ]);
                    $shipping->set_name('Besplatna Dostava');
                    $order->add_item($shipping);
                    $order->calculate_totals();
                    $order->save();
                    break;

                default :
                    $order->remove_order_items('shipping');
                    $shipping = new \WC_Order_Item_Shipping();
                    $shipping->set_props(['method_title' => $title, 'method_id' => $rate->id, 'total' => $cost]);
                    $shipping->calculate_taxes();
                    $order->add_item($shipping);
                    $order->calculate_totals();
                    $order->save();
                    break;
            }
        }
    }

    /**
     * Adds notice if cart has item with custom shipping Price
     */
    public function customShippingPriceNotice()
    {
        $cartContents = WC()->cart->get_cart_contents();

        /** @var \WC_Product $product */
        foreach ($cartContents as $cartContent) {
            $product = $cartContent['data'];
            if ($this->getCustomShippingPrice($product)) {
                $html = '<p><b>' . $product->get_name() . '</b> ima dodatnu cenu dostave i ona iznosi ' . $this->getCustomShippingPrice($product) . get_woocommerce_currency_symbol() . '</p>';
                wc_print_notice($html, 'notice');
            }
        }
    }

    /**
     * Unset rates based on cart weight
     * @param $rates
     * @param $cartWeight
     * @return \WC_Shipping_Rate[]
     */
    private function getWeightBasedShippingRate($rates, $cartWeight)
    {
        if ($cartWeight <= 0.5) {
            if (isset($rates['flat_rate:3'])) {
                unset(
                    $rates['flat_rate:4'],
                    $rates['flat_rate:5'],
                    $rates['flat_rate:6'],
                    $rates['flat_rate:7'],
                    $rates['flat_rate:8'],
                    $rates['flat_rate:9'],
                    $rates['flat_rate:10']);
            }
        } elseif ($cartWeight > 0.5 and $cartWeight <= 2) {
            if (isset($rates['flat_rate:4'])) {
                unset(
                    $rates['flat_rate:3'],
                    $rates['flat_rate:5'],
                    $rates['flat_rate:6'],
                    $rates['flat_rate:7'],
                    $rates['flat_rate:8'],
                    $rates['flat_rate:9'],
                    $rates['flat_rate:10']);
            }
        } elseif ($cartWeight > 2 and $cartWeight <= 5) {
            if (isset($rates['flat_rate:5'])) {
                unset(
                    $rates['flat_rate:3'],
                    $rates['flat_rate:4'],
                    $rates['flat_rate:6'],
                    $rates['flat_rate:7'],
                    $rates['flat_rate:8'],
                    $rates['flat_rate:9'],
                    $rates['flat_rate:10']);
            }
        } elseif ($cartWeight > 5 and $cartWeight <= 10) {
            if (isset($rates['flat_rate:6'])) {
                unset(
                    $rates['flat_rate:3'],
                    $rates['flat_rate:4'],
                    $rates['flat_rate:5'],
                    $rates['flat_rate:7'],
                    $rates['flat_rate:8'],
                    $rates['flat_rate:9'],
                    $rates['flat_rate:10']);
            }
        } elseif ($cartWeight > 10 and $cartWeight <= 20) {
            if (isset($rates['flat_rate:7'])) {
                unset(
                    $rates['flat_rate:3'],
                    $rates['flat_rate:4'],
                    $rates['flat_rate:5'],
                    $rates['flat_rate:6'],
                    $rates['flat_rate:8'],
                    $rates['flat_rate:9'],
                    $rates['flat_rate:10']);
            }
        } elseif ($cartWeight > 20 and $cartWeight <= 30) {
            if (isset($rates['flat_rate:8'])) {
                unset(
                    $rates['flat_rate:3'],
                    $rates['flat_rate:4'],
                    $rates['flat_rate:5'],
                    $rates['flat_rate:6'],
                    $rates['flat_rate:7'],
                    $rates['flat_rate:9'],
                    $rates['flat_rate:10']);
            }
        } elseif ($cartWeight > 30 and $cartWeight <= 50) {
            if (isset($rates['flat_rate:9'])) {
                unset(
                    $rates['flat_rate:3'],
                    $rates['flat_rate:4'],
                    $rates['flat_rate:5'],
                    $rates['flat_rate:6'],
                    $rates['flat_rate:7'],
                    $rates['flat_rate:8'],
                    $rates['flat_rate:10']);
            }
        } elseif ($cartWeight > 50) {
            if (isset($rates['flat_rate:10'])) {
                $myExtraWeight = $cartWeight - 50;

                if (isset($rates['flat_rate:10']->instance_settings['cost'])) {
                    $flatRate10Cost = $rates['flat_rate:10']->instance_settings['cost'];
                } else {
                    $flatRate10Cost = $rates['flat_rate:10']->get_cost();
                }

                $myNewPrice = $flatRate10Cost + (10 * $myExtraWeight);
                if (isset($rates['flat_rate:10']->instance_settings['cost'])) {
                    $rates['flat_rate:10']->instance_settings['cost'] = $myNewPrice;
                } else {
                    $rates['flat_rate:10']->set_cost($myNewPrice);
                }
                unset(
                    $rates['flat_rate:3'],
                    $rates['flat_rate:4'],
                    $rates['flat_rate:5'],
                    $rates['flat_rate:6'],
                    $rates['flat_rate:7'],
                    $rates['flat_rate:8'],
                    $rates['flat_rate:9']);
            }
        }

        return $rates;
    }

    /**
     * Reduce cart weight used for calculating weight based shipping if product has custom shipping price,
     * returns updated cart weight and total cost of custom shipping
     * @param $cartWeight
     * @param $cartContents
     * @return array
     */
    private function settingCustomShippingPriceOverride($cartWeight, $cartContents)
    {
        $customCost = 0;
        /** @var \WC_Product $product */
        foreach ($cartContents as $cartContent) {
            $product = $cartContent['data'];
            //If there is more than one product with custom shipping price add price for each of them to custom cost total
            if ($cartContent['quantity'] > 1) {
                if ($this->getCustomShippingPrice($product)) {
                    for ($i = 1; $i <= $cartContent['quantity']; $i++) {
                        $customCost += (int)$this->getCustomShippingPrice($product);
                        $productWeight = (float)$product->get_weight();

                        /*
                        Remove weight of product with special price from cart weight so items with normal shipping
                        cost can have valid shipping price based on its weight
                        */
                        $cartWeight -= $productWeight;
                    }
                }
                continue;
            }
            if ($this->getCustomShippingPrice($product)) {
                $customCost += (int)$this->getCustomShippingPrice($product);
                $productWeight = (float)$product->get_weight();
                $cartWeight -= $productWeight;
            }
        }
        return [
            'cartWeight' => $cartWeight,
            'customShippingCost' => $customCost
        ];
    }

    /**
     * Adds custom shipping cost to all weight based rates and changes labels of weight based shipping rates
     * @param $rates
     * @param $cartWeight
     * @param $customCost
     */
    private function changeRatesBasedOnPriceOverride($rates, $cartWeight, $customCost)
    {
        /** @var \WC_Shipping_Rate $rate */
        foreach ($rates as $rate) {
            if ($rate->get_method_id() === 'free_shipping') {
                continue;
            }
            //If cart weight after deducting special products is 0 or less set all weight based cost to 0
            if ($cartWeight <= 0) {
                $rate->set_cost('0');
            }

            $cost = $rate->get_cost();
            $newCost = (int)$cost + $customCost;
            //Add custom product shipping cost to all shipping rates
            $rate->set_cost($newCost);

            //Remove weight based title if product in cart has custom shipping price
            $rate->set_label('Dostava');
        }
    }

    /**
     * Checks for custom price in product meta
     * @param \WC_Product $product
     * @return bool|string
     */
    private function getCustomShippingPrice(\WC_Product $product)
    {
        if ($product instanceof \WC_Product_Variation) {
            $product = wc_get_product($product->get_parent_id());
        }

        $customCost = $product->get_meta('customShippingPrice', true);
        if (strlen($customCost) > 0) {
            return $customCost;
        }
        return false;
    }


}