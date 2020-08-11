<?php

namespace GF\Util;

/**
 * Class PricelistUpdate
 *
 * [itemSku, inputprice, price, saleprice, status, sticker]
 * status [1 => online, 2 => offline, 3 => soldout]
 *
 * @package GF\Util
 */
class Pricelist
{
    public function init()
    {
        if (isset($_FILES['cenovnik'])) {
            echo $this->updatePricesAndStatuses($_FILES['cenovnik']);
        } else {
            echo $this->getUploadForm();
        }
    }

    public function getUploadForm()
    {
        return '<div>
            <h3>Import cenovnika</h3>
            <p>1. Klikni opciju browse i izaberite CSV fajl sa vaseg racunara</p>
            <p>2. Klikni pošalji kako bi izmenili cene proizvodima</p>

            <form action="admin.php?page=pricelist-import" method="POST" enctype="multipart/form-data">
                <input type="file" name="cenovnik" />
                <input type="submit" value="Pošalji" name="submit" />
            </form>
        </div>';
    }

    public function updatePricesAndStatuses($fileInfo)
    {
        $notFoundItems = [];
        $errorItems = [];
        foreach ($this->parseData($fileInfo) as $itemInfo) {
            if ($itemInfo[0]) {
                $product = get_product_by_sku($itemInfo[0]);
                if (!$product) {
                    $notFoundItems[] = $itemInfo;
                    continue;
                }
                $status = 'publish';
                $stock = 'instock';
                if ($itemInfo[4] == 2) {
                    $status = 'draft';
                }
                if ($itemInfo[4] == 3) {
                    $stock = 'outofstock';
                }

                if ($itemInfo[3] > 0 && $itemInfo[2] > $itemInfo[3]) {
                    $errorItems[] = $itemInfo;
                    continue;
                }
                if ($itemInfo[2] <  10 || ($itemInfo[3] > 0 && $itemInfo[3] < 10)) {
                    $errorItems[] = $itemInfo;
                    continue;
                }

                $product->update_meta_data('input_price', $itemInfo[1]);
                if ($itemInfo[3] > 0) {
                    $price = (int) $itemInfo[2];
                    $regularPrice = (int) $itemInfo[3];
                    $salePrice = (int) $itemInfo[2];
                } else {
                    $price = (int) $itemInfo[2];
                    $regularPrice = (int) $itemInfo[2];
                    $salePrice = '';
                }

                if (get_class($product) == \WC_Product_Variable::class) {
                    $product->get_available_variations();
                    foreach ($product->get_children() as $variationId) {
                        $variation = wc_get_product($variationId);
                        $variation->set_stock_status($stock);
                        $variation->set_status($status);
                        $variation->set_regular_price($regularPrice);
                        $variation->set_price($price);
                        $variation->set_sale_price($salePrice);
                        $variation->save();
                    }
                } else {
                    $product->set_price($price);
                    $product->set_regular_price($regularPrice);
                    $product->set_sale_price($salePrice);

                    $product->set_stock_status($stock);
                    $product->set_status($status);
                    $product->save();
                }
            }
        }
        $html = 'Proizvodi uspesno izmenjeni.';
        if (!empty($errorItems)) {
            $html .= $this->parseErrorItems($errorItems);
        }
        if (!empty($notFoundItems)) {
            $html .= $this->parseNotFoundItems($notFoundItems);
        }

        return $html;
    }

    private function parseErrorItems($items)
    {
        $html = '<p>CENA NIJE VALIDNA za sledece kataloske brojeve:</p>';
        $html .= '<ul>';
        foreach ($items as $item) {
            $html .= '<li>'.$item[0].'</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    private function parseNotFoundItems($items)
    {
        $html = '<p>Nisu pronadjeni proizvodi za sledece kataloske brojeve:</p>';
        $html .= '<ul>';
        foreach ($items as $item) {
            $html .= '<li>'.$item[0].'</li>';
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * @return array
     */
    protected function parseData($fileInfo)
    {
        $data = $this->readFile($fileInfo);
        $worksheet = $data->getSheet(1)
            ->rangeToArray(
                'A1:G' . $data->getActiveSheet()->getHighestRow(),
                null,
                true,
                true,
                false
            );

        return $worksheet;
    }

    protected function readFile($fileInfo)
    {
//        $targetFile = '/tmp/' . $fileInfo['tmp_name'];
//        move_uploaded_file($fileInfo['tmp_name'], $targetFile);
//        var_dump($fileInfo);
//        die();
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $reader->setReadDataOnly(true);
        $data = $reader->load($fileInfo['tmp_name']);

        return $data;
    }
}