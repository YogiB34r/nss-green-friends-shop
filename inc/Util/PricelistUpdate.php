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
class PricelistUpdate
{
    public function __construct()
    {

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
        foreach ($this->parseData($fileInfo) as $itemInfo) {
            if ($itemInfo[0]) {
                $product = get_product_by_sku($itemInfo[0]);
                if (!$product) {
                    $notFoundItems[] = $itemInfo;
                    continue;
                }

                $product->update_meta_data('input_price', $itemInfo[1]);
                if ($itemInfo[3] > 0) {
                    $product->set_price((int) $itemInfo[2]);
                    $product->set_regular_price((int) $itemInfo[3]);
                    $product->set_sale_price((int) $itemInfo[2]);
                } else {
                    $product->set_price((int) $itemInfo[2]);
                    $product->set_regular_price((int) $itemInfo[2]);

                }
                $status = 'publish';
                if ($itemInfo[4] == 2) {
                    $status = 'draft';
                }
                if ($itemInfo[4] == 3) {
                    $product->set_stock_status('outofstock');
                } else {
                    $product->set_stock_status('instock');
                }
                $product->set_status($status);
                $product->save();
            }
        }
        echo 'Proizvodi uspesno izmenjeni.';
        if (!empty($notFoundItems)) {
            return $this->parseNotFoundItems($notFoundItems);
        }
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
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        $reader->setReadDataOnly(true);
        $data = $reader->load($fileInfo['tmp_name']);

        return $data;
    }
}