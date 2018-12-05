<?php
/* Template Name: external banners widget */

global $wpdb;
$widget = new \GF\ExternalBannerWidget\ExternalBannerWidget($wpdb);

$widget->render_html();
