<?php
$categories = get_search_category_aggregation();
if (!is_product_category()){

    echo '<div id="gf-expander-id" class="row gf-category-expander">';

    foreach ($categories as $category){
        echo '<div class="col-sm-6 col-xs-6 col-md-3 gf-expander-module-first-line">';
        echo '<a class="gf-expander-first-line-parent" href="/' . $category['url'] . '">' . $category['name'] .' ('.$category['count'].') </a>';
        echo '</div>';
    }

    echo '<div class="gf-category-expander__footer"></div>';
    echo '</div>';
}