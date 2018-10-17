<?php
$categories = get_terms([
    'taxonomy' => get_queried_object()->taxonomy,
    'parent' => get_queried_object_id(),
    'hide_empty' => true
]);
if (count($categories) != 0) {
    echo '<div id="gf-expander-id" class="row gf-category-expander">';

    $i = 0;
    $second_lvl_cat_ids = [];
    foreach ($categories as $category) {
        $i++;
        $child_args = array(
            'taxonomy' => 'product_cat',
            'parent' => $category->term_id
        );
        $second_lvl_cat_ids[] = $category->term_id;
        $child_cats = get_terms($child_args);

        if ($i <= count($second_lvl_cat_ids)) {
            echo '<div class="col-sm-6 col-xs-6 col-md-3 gf-expander-module-first-line">';
            echo '<a class="gf-expander-first-line-parent" href="' . get_term_link($category) . '">' . $category->name .'</a>';
            echo '<ul class="gf-expander__subcategory-list">';
            foreach ($child_cats as $child_cat) {
                echo '<li>
                     <a class="gf-category-expander__col__subcategory gf-module-first-href" href="' . get_term_link($child_cat) . '">' . $child_cat->name . '</a>
                  </li>';
            }
            echo '</ul>
              </div>';
            continue;
        }
        echo '<div class="col-sm-6 col-xs-6 col-md-3 gf-category-expander__col">';
        echo '<a class="gf-category-expander__col__category" href="' . get_term_link($category) . '">' . $category->name.'</a>';
        echo '<ul class="gf-expander__subcategory-list">';
        foreach ($child_cats as $child_cat) {
            echo '<li>
                     <a class="gf-category-expander__col__subcategory" href="' . get_term_link($child_cat) . '">' . $child_cat->name . '</a>
                  </li>';
        }
        echo '</ul>
              </div>';
    }
//    $args = array(
//        'taxonomy' => 'product_cat',
//        'childless' => 1,
//    );
//    $childless_cats = get_terms($args);
//    $childless_cats_ids = [];
//    foreach ($childless_cats as $cat) {
//        $childless_cats_ids[] = $cat->term_id;
//    }
//    $result = false;
//    foreach ($second_lvl_cat_ids as $second_lvl_cat_id) {
//        if (!in_array($second_lvl_cat_id, $childless_cats_ids)) {
//            $result = true;
//            break;
//        }
//    }
    if (!empty($child_cats)) {
        echo '<div class="gf-category-expander__footer"><span class="fas fa-angle-down"></span></div>';
    } else {
        echo '<div class="gf-category-expander__footer"></div>';
    }
    echo '</div>';

}