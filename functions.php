<?php
//ini_set('upload_max_size', '128M');
//ini_set('post_max_size', '128M');
//ini_set('max_execution_time', '80');
ini_set('max_execution_time', '40');


require (__DIR__ . '/inc/autoload.php');
include(__DIR__ . '/inc/core.functions.php');


$theme = new \GF\Theme();
$theme->init();