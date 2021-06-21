<?php
$orderedCategoriesIds = get_option('filter_fields_order');

$secondLevelCategories = [];
$thirdLevelCategories = [];

foreach ($orderedCategoriesIds as $categoryId => $catData) {
    $orderedCategoryTerm = get_term($categoryId, 'product_cat');
    if ($orderedCategoryTerm->parent !== 0) {
        if ($orderedCategoryTerm->parent == get_queried_object_id()) {
            $secondLevelCategories[] = $orderedCategoryTerm;
        } else {
            $thirdLevelCategories[] = $orderedCategoryTerm;
        }
    }
}


if (count($secondLevelCategories) != 0) {
    echo '<div id="gf-expander-id" class="gf-category-expander">';

    $counter = 0;
    $secondLevelCategoryIds = [];
    foreach ($secondLevelCategories as $secondLevelCat) {
        $counter++;

        $secondLevelCategoryIds[] = $secondLevelCat->term_id;

        if ($counter <= count($secondLevelCategoryIds)) {
            echo '<div class="gf-expander-module-first-line">';
            echo '<h2><a class="gf-expander-first-line-parent" href="' . user_trailingslashit(get_term_link($secondLevelCat)) . '">' . $secondLevelCat->name . '</a></h2>';
            echo '<ul class="gf-expander__subcategory-list">';
            foreach ($thirdLevelCategories as $thirdLevelCat) {
                if ($thirdLevelCat->parent == $secondLevelCat->term_id):
                    echo '<li>
                            <h2><a class="gf-category-expander__col__subcategory gf-module-first-href" href="' . user_trailingslashit(get_term_link($thirdLevelCat)) . '">' . $thirdLevelCat->name . '</a></h2>
                           </li>';
                endif;
            }

            echo '</ul>
              </div>';
            continue;
        }
        echo '<div class="gf-category-expander__col">';
        echo '<h2><a class="gf-category-expander__col__category" href="' . user_trailingslashit(get_term_link($secondLevelCat)) . '">' . $secondLevelCat->name . '</a></h2>';
        echo '<ul class="gf-expander__subcategory-list">';
        foreach ($thirdLevelCategories as $thirdLevelCat) {
            if ($thirdLevelCat->parent == $secondLevelCat->term_id):
            echo '<li>
                     <h2><a class="gf-category-expander__col__subcategory" href="' . user_trailingslashit(get_term_link($thirdLevelCat)) . '">' . $thirdLevelCat->name . '</a></h2>
                  </li>';
            endif;
        }
        echo '</ul>
              </div>';
    }

    if (!empty($thirdLevelCategories)) {
        echo '<div id="nssCatExpander" class="gf-category-expander__footer"><span class="fas fa-angle-down"></span></div>';
    } else {
        echo '<div id="nssCatExpander" class="gf-category-expander__footer"></div>';
    }
    echo '</div>';

}