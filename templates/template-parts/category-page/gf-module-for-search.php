<?php
$categories = get_search_category_aggregation();

if (count($categories) != 0) {
    $html = '<div id="gf-expander-id" class="row gf-category-expander">';

    foreach ($categories as $category) {
        $url = $category['url'] . '?query=' . $_GET['query'];
        $html .= '<div class="col-sm-6 col-xs-6 col-md-3 gf-expander-module-first-line">';
        $html .= '<a class="gf-expander-first-line-parent" href="' . $url . '">' . $category['name'] .' ('.$category['count'].') </a>';
        $html .= '</div>';
    }

    $html .= '<div class="gf-category-expander__footer"></div>';
    $html .= '</div>';

    echo $html;
}