<?php
/* Template Name: Carousel widget (iframe) */
require_once(__DIR__ . '/inc/ExternalBannerWidget/ExternalBannerWidget.php');

global $wpdb;

$widget = new \GF\ExternalBannerWidget\ExternalBannerWidget($wpdb);
$widget->render_html(@$_GET['template'], @$_GET['source']);