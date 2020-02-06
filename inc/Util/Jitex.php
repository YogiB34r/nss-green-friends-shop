<?php

namespace GF\Util;


class Jitex
{
    public static function exportJitexOrder(\WC_Order $order)
    {
        $csvText = static::parseJitexDataFromOrder($order);

        header('Content-Disposition: attachment; filename="' . $order->get_order_number() . '.txt' . '"');
        header("Content-Transfer-Encoding: binary");
        header('Expires: 0');
        header('Pragma: no-cache');

//    print iconv('utf-8','windows-1250',str_replace(array('Ð', 'ð'), array('Đ', 'đ'), $csvText));
        $csvText = static::fixJitexCharacters($csvText);
        echo $csvText;
    }

    public static function fixJitexCharacters($str)
    {
        return str_replace(
            ['ć', 'Ć', 'č', 'Č', 'š', 'Š', 'đ', 'Đ', 'ž', 'Ž'],
            ['c', 'C', 'c', 'C', 's', 'S', 'd', 'D', 'z', 'Z'],
            $str
        );
    }

    public static function parseJitexDataFromOrder(\WC_Order $order) {
        $string = '';
        /* @var \WC_Order_Item_Product $item */
        /* @var \WC_Product_Variable $p */
        foreach ($order->get_items() as $item) {
            $p = wc_get_product($item->get_product()->get_id());
            $variation = '';
            if (get_class($p) === \WC_Product_Variation::class) {
                foreach ($p->get_variation_attributes() as $value) {
                    $variation = $value;
                }
            }

            if ($p->get_parent_id()) {
                $p = wc_get_product($p->get_parent_id());
            }
            $name = $order->get_billing_first_name() .' '. $order->get_billing_last_name();
            if ($order->get_meta('_billing_pib') != '') {
                $name = $order->get_billing_company();
            }
            $variantId = $p->get_sku() . $variation;
            $variantName = str_replace('-', '', $item->get_name());
            $date = $order->get_date_created()->format('d.m.Y');
            $itemPrice = (int) $item->get_total() / $item->get_quantity();
            $modifier = (float) '1' .'.'. (int) number_format($p->get_meta('pdv'));
            $priceNoPdv = number_format($itemPrice / $modifier, 2, ',', '.');
            $priceFormated = number_format($itemPrice, 2, ',', '.');
            $string .= $name."\t".$order->get_billing_address_1()."\t".$order->get_billing_postcode()."\t".$order->get_billing_city()."\t"."Srbija"."\t".
                $order->get_billing_phone()."\t".$order->get_order_number()."\t".$date."\t".$order->get_payment_method_title()."\t".$variantId."\t".$variantName."\t".
                $item->get_quantity()."\t".$priceNoPdv."\t".$priceFormated."\t".$order->get_billing_company()."\t".$order->get_meta('_billing_pib')."\r\n";
        }
        $order->update_meta_data('jitexExportCreated', 1);
        $order->save();
        $shippingNoPdv = number_format($order->get_shipping_total() / 1.2, 2, ',', '.');

        $string .= $name."\t".$order->get_billing_address_1()."\t".$order->get_billing_postcode()."\t".$order->get_billing_city()."\t"."Srbija"."\t".
            $order->get_billing_phone()."\t".$order->get_order_number()."\t".$date."\t".$order->get_payment_method_title()."\t9999\tDostava\t1\t".
            $shippingNoPdv."\t".number_format($order->get_shipping_total(), 2, ',', '.')."\t".$order->get_billing_company();

        return $string;
    }

    public static function getJitexExport()
    {
        $fileName = 'jitexItems.txt';
        $filePath = WP_CONTENT_DIR . '/uploads/feed/' . $fileName;

        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header('Content-type: text/plain');
        header("Content-Disposition: attachment; filename=".$fileName.'"');
        header('Content-Transfer-Encoding: binary');

        echo file_get_contents($filePath);
    }
}