<?php
/* Template Name: Oglasi Api, Proizvodi */

ini_set('display_errors', '1');
header('Content-Type: application/json');
$exporter = new \GF\Util\OglasiExporter();
$perPage = 100;

try {
    if (!isset($_GET{'categoryId'})) {
        echo json_encode(['error' => 'No categoryId parameter passed.']);
        exit();
    }
    $token = 1;
    if (isset($_GET{'token'})) {
        $token = $_GET{'token'};
    }
    $items = $exporter->getProductList($_GET{'categoryId'}, $token, $perPage);
} catch (\Exception $e) {
    echo $e->getMessage();
    exit();
}


echo json_encode($items);
