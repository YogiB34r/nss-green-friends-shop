<?php
$ordered_categories_ids = get_option('filter_fields_order');

$second_lvl_cats = [];
$third_lvl_cats = [];

foreach ($ordered_categories_ids as $category_id => $catData) {
    $ordered_cat_term = get_term($category_id, 'product_cat');
    if ($ordered_cat_term->parent !== 0) {
        if ($ordered_cat_term->parent == get_queried_object_id()) {
            $second_lvl_cats[] = $ordered_cat_term;
        } else {
            $third_lvl_cats[] = $ordered_cat_term;
        }
    }
}

if (count($second_lvl_cats) != 0) {
    echo '<div id="gf-expander-id" class="gf-category-expander">';

    $i = 0;
    $second_lvl_cat_ids = [];
    foreach ($second_lvl_cats as $second_lvl_cat) {
        $i++;

        $second_lvl_cat_ids[] = $second_lvl_cat->term_id;

        if ($i <= count($second_lvl_cat_ids)) {
            echo '<div class="gf-expander-module-first-line">';
            echo '<h2><a class="gf-expander-first-line-parent" href="' . user_trailingslashit(get_term_link($second_lvl_cat)) . '">' . $second_lvl_cat->name . '</a></h2>';
            echo '<ul class="gf-expander__subcategory-list">';
            foreach ($third_lvl_cats as $third_lvl_cat) {
                if ($third_lvl_cat->parent == $second_lvl_cat->term_id):
                    echo '<li>
                            <h2><a class="gf-category-expander__col__subcategory gf-module-first-href" href="' . user_trailingslashit(get_term_link($third_lvl_cat)) . '">' . $third_lvl_cat->name . '</a></h2>
                           </li>';
                endif;
            }

            echo '</ul>
              </div>';
            continue;
        }
        echo '<div class="gf-category-expander__col">';
        echo '<h2><a class="gf-category-expander__col__category" href="' . user_trailingslashit(get_term_link($second_lvl_cat)) . '">' . $second_lvl_cat->name . '</a></h2>';
        echo '<ul class="gf-expander__subcategory-list">';
        foreach ($third_lvl_cats as $third_lvl_cat) {
            if ($third_lvl_cat->parent == $second_lvl_cat->term_id):
            echo '<li>
                     <h2><a class="gf-category-expander__col__subcategory" href="' . user_trailingslashit(get_term_link($third_lvl_cat)) . '">' . $third_lvl_cat->name . '</a></h2>
                  </li>';
            endif;
        }
        echo '</ul>
              </div>';
    }

    if (!empty($third_lvl_cats)) {
        echo '<div id="nssCatExpander" class="gf-category-expander__footer"><span class="fas fa-angle-down"></span></div>';
    } else {
        echo '<div id="nssCatExpander" class="gf-category-expander__footer"></div>';
    }
    echo '</div>';

}