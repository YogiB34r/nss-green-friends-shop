<?php
/**
 * Template Name: item export
 */
//ini_set('max_execution_time', 1200);
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

echo file_get_contents(ABSPATH . '/wp-content/uploads/itemExport.xml');
