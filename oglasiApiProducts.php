<?php
/* Template Name: Oglasi Api, Kategorije */

ini_set('display_errors', '1');
$exporter = new \GF\Util\OglasiExporter();

try {
    if (isset($_GET{'id'})) {
        $cats = $exporter->getCategory($_GET{'id'});
    } else {
        $cats = $exporter->getRootCategories();
    }
} catch (\Exception $e) {
    echo $e->getMessage();
    exit();
}

header('Content-Type: application/json');

echo json_encode($cats);
