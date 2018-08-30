<?php
add_shortcode('gf-footer-credits', 'gf_footer_credits_shortcode');
function gf_footer_credits_shortcode()
{
    echo get_bloginfo('name') . ' ' . date('Y');
}

add_shortcode('gf-my-account-link', 'gf_my_account_link_shortcode');
function gf_my_account_link_shortcode()
{
    $myaccount_page = get_option('woocommerce_myaccount_page_id');
    if ($myaccount_page) {
        $myaccount_page_url = get_permalink($myaccount_page);
    }

    echo '<div class="gf-my-account"><a href=" ' . $myaccount_page_url . '"><i class="fas fa-user"></i> ' . __('Moj nalog') . '</a></div>';
}

add_shortcode('gf-category-dropdown', 'gf_category_dropdown_shortcode');
function gf_category_dropdown_shortcode()
{
    $cat_args = array(
        'orderby' => 'term_group',
        'order' => 'asc',
        'hide_empty' => false,
        'hierarchical' => 1,
    );

    $product_categories = get_terms('product_cat', $cat_args);

    if (!empty($product_categories)) {
        echo '
        <div class="dropdown">
          <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
          data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-bars"></i>
            ' . _e('Categories', 'green-friends') . '
          </button>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">';
        foreach ($product_categories as $category) {
            echo '<a class="dropdown-item" href="' . get_term_link($category) . '">' . $category->name . '</a>';
        }
        echo '</div></div>';
    }
}

add_shortcode('gf-product-search', 'gf_product_search_shortcode');
function gf_product_search_shortcode()
{
    ?>
    <form class="form-inline row" name="gf-search-header" method="POST" action="<?php echo home_url(); ?>">
        <?php if (class_exists('WooCommerce')) : ?>

        <div class="col-7">
            <input type="text" name="s" class="form-control" maxlength="128" value="<?php echo get_search_query(); ?>"
                   placeholder="Search Products">
        </div>
        <div class="col-4">
            <?php
            if (isset($_REQUEST['product_cat']) && !empty($_REQUEST['product_cat'])) {
                $optsetlect = $_REQUEST['product_cat'];
            } else {
                $optsetlect = 0;
            }
            $args = array(
                'show_option_all' => esc_html__('All Categories', 'woocommerce'),
                'hierarchical' => 1,
                'class' => 'cat',
                'echo' => 1,
                'value_field' => 'slug',
                'selected' => $optsetlect
            );
            $args['taxonomy'] = 'product_cat';
            $args['name'] = 'product_cat';
            $args['class'] = 'dropdown hidden-xs';
            wp_dropdown_categories($args);
            ?>
            <input type="hidden" value="product" name="post_type">
            <?php endif; ?>
        </div>
        <div class="col-1">
            <button type="submit" title="<?php esc_attr_e('Search,', 'woocommerce'); ?>"
                    class="btn btn-primary my-sm-0"><i class="fas fa-search"></i>
            </button>
        </div>

    </form>
    <?php
}

add_shortcode('gf-cart', 'gf_cart_shortcode');
function gf_cart_shortcode()
{
    global $woocommerce ?>
    <a class="gf-header-cart" href="<?php echo wc_get_cart_url(); ?>"
       title="<?php _e('Cart View', 'green-friends'); ?>">
        <p class="gf-header-cart__title">
            <i class="fas fa-shopping-cart"></i> <span
                    class="shopping-cart__count"><?php echo sprintf(_n('%d', '%d', $woocommerce->cart->cart_contents_count, 'woothemes'),
                    $woocommerce->cart->cart_contents_count); ?></span>Korpa</p>
    </a>
    <?php
}

add_shortcode('gf-category-mobile', 'gf_category_mobile_toggle_shortcode');
function gf_category_mobile_toggle_shortcode()
{
    if (wp_is_mobile()) {
        echo '<div class="gf-category-mobile-toggle">Kategorije</div>';
        $product_cat_raw = get_terms(array('parent' => 0, 'taxonomy' => 'product_cat'));
        $product_cat = [];
        foreach ($product_cat_raw as $cat) {
            $product_cat[] = array(
                'name' => $cat->name,
                'term_id' => $cat->term_id
            );
        }
        $number_of_categories = 20;
        if (!empty(get_option('filter_fields_order'))) {
            $product_cat = get_option('filter_fields_order');
            $number_of_categories = esc_attr(get_option('number_of_categories_in_sidebar'));
        }
        $i = 0;
        echo '<div class="gf-category-accordion">';
        foreach ($product_cat as $parent_product_cat) {
            if ($parent_product_cat['name'] != 'Gf-slider' && $parent_product_cat['name'] != 'Uncategorized'):
                $i++;
                if ($i <= $number_of_categories) {
                    echo '<div class="gf-category-accordion__item gf-category-accordion__item--main">
                        <a tabindex="-1" href="' . get_term_link((int)$parent_product_cat['term_id']) . '">' . $parent_product_cat['name'] . '</a>
                        <i class="gf-category-accordion__expander fas fa-plus"></i>';
                    $child_args = array(
                        'taxonomy' => 'product_cat',
                        'hide_empty' => false,
                        'parent' => $parent_product_cat['term_id']
                    );
                    $child_product_cats = get_terms($child_args);

                    foreach ($child_product_cats as $child_product_cat) {
                        $child_child_args = array('taxonomy' => 'product_cat',
                            'hide_empty' => false,
                            'parent' => $child_product_cat->term_id
                        );
                        $child_child_product_cats = get_terms($child_child_args);
                        echo '<div class="gf-category-accordion__item gf-category-accordion__subitem mt-sm">
                              <a class="" href="' . get_term_link($child_product_cat->term_id) . '">' . $child_product_cat->name . '</a>
                              <i class="gf-category-accordion__expander fas fa-plus"></i>';
                        foreach ($child_child_product_cats as $child_child_product_cat) {
                            echo '<div class="gf-category-accordion__item gf-category-accordion__item--last">
                                <a class="" href="' . get_term_link($child_child_product_cat->term_id) . '">' . $child_child_product_cat->name . '</a>
                              </div>';
                        }
                        echo '</div>';
                    }
                    echo '</div>';
                };
            endif;
        }
        echo '</div>';
    }
}

add_shortcode('gf-mobile-nav-menu', 'gf_mobile_nav_menu_shortcode');
function gf_mobile_nav_menu_shortcode()
{
    if (wp_is_mobile()) {
        echo '<div class="gf-hamburger-menu"><i class="fas fa-bars"></i></div>';

        echo '<div class="gf-mobile-menu">';
        global $woocommerce ?>
        <li class="gf-mobile-menu__link">
            <a class="gf-header-cart" href="<?php echo wc_get_cart_url(); ?>"
               title="<?php _e('Cart View', 'green-friends'); ?>">
                Korpa(<?php echo sprintf(_n('%d', '%d', $woocommerce->cart->cart_contents_count, 'woothemes'), $woocommerce->cart->cart_contents_count); ?>
                )
            </a>
        </li>
        <li class="gf-mobile-menu__link">
            <?php
            $myaccount_page = get_option('woocommerce_myaccount_page_id');
            if ($myaccount_page) {
                $myaccount_page_url = get_permalink($myaccount_page);
            }
            echo '<a href=" ' . $myaccount_page_url . '">' . __('Moj nalog') . '</a>';
            ?>
        </li>
        <li class="gf-mobile-menu__link">
            <?php
            $menu_items = wp_get_nav_menu_items('Topbar');
            foreach ($menu_items as $menu_item) {
                if (is_user_logged_in() && $menu_item->post_title == 'Log Out') {
                    echo '<a href="' . $menu_item->url . '">' . $menu_item->post_title . '</a>';
                }
                if (!is_user_logged_in() && $menu_item->post_title != 'Log Out') {
                    echo '<a href="' . $menu_item->url . '">' . $menu_item->post_title . '</a>';
                }
            }
            ?>
        </li>
        <?php
        echo '</div>';
    }
}

// Category sidebar
add_shortcode('gf-category-megamenu', 'gf_category_megamenu_shortcode');
function gf_category_megamenu_shortcode()
{
    $key = 'gf-slider';
    $group = 'gf-sidebar-static';
    $html = wp_cache_get($key, $group);
    if ($html === false) {
        ob_start();
        printMegaMenu();
        $html = ob_get_clean();
        wp_cache_set($key, $html, $group, 0);
    }
    echo $html;
}

/**
 * Prints out mega menu with categories
 */
function printMegaMenu() {
    $gf_slider_id = '';
    if (get_term_by('slug', 'gf-slider', 'product_cat')) {
        $gf_slider_id = get_term_by('slug', 'gf-slider', 'product_cat')->term_id;
    }
    $uncategorized_id = '';
    if (get_term_by('slug', 'uncategorized', 'product_cat')) {
        $uncategorized_id = get_term_by('slug', 'gf-slider', 'product_cat')->term_id;
    }
    $product_cats = [];
    $parent_cats = [];
    $child_cats = [];
    $childs_of_child = [];
    $number_of_categories = 24;
    if (!empty(get_option('filter_fields_order'))) {
        $product_cats_array = get_option('filter_fields_order');
        foreach ($product_cats_array as $product_cat) {
            $product_cats[] = get_term($product_cat['term_id']);
        }
        $number_of_categories = esc_attr(get_option('number_of_categories_in_sidebar'));
    } else{
        foreach (gf_get_top_level_categories($gf_slider_id, $uncategorized_id) as $cat) {
            if (empty(get_term_children($cat->term_id, 'product_cat'))) {
                $product_cats[] = $cat;
            } else {
                $product_cats[] = $cat;
                foreach (get_term_children($cat->term_id, 'product_cat') as $second_level_cat) {
                    if (gf_check_level_of_category($second_level_cat) == 2) {
                        if (empty(get_term_children($second_level_cat, 'product_cat'))) {
                            $product_cats[] = get_term($second_level_cat, 'product_cat');
                        } else {
                            $product_cats[] = get_term($second_level_cat, 'product_cat');
                            foreach (get_term_children($second_level_cat, 'product_cat') as $third_level_cat) {
                                $product_cats[] = get_term($third_level_cat, 'product_cat');
                            }
                        }
                    }
                }
            }
        }
    }
    $i = 0;
    $c = 0;
    $pcc = 0;
    echo
    '<div id="gf-wrapper">
	     <div class="gf-sidebar">
		     <div class="gf-toggle"><i class="fa fa-bars"></i></div>
		       <div class="gf-navblock">';
    if ($i <= $number_of_categories) {
        foreach ($product_cats as $cat) {
            if ($cat->parent == 0) {
                $parent_children_count = count(get_term_children($cat->term_id,'product_cat'));
                $i++;
                require(realpath(__DIR__ . '/../templates/template-parts/category-megamenu/first-level.php'));
            }
            if (gf_check_level_of_category($cat->term_id) == 2) {
                $child_count = count(get_term_children($cat->term_id, 'product_cat'));
                require(realpath(__DIR__ . '/../templates/template-parts/category-megamenu/second-level.php'));
                $pcc++;
            }
            if (gf_check_level_of_category($cat->term_id) == 3) {
                require(realpath(__DIR__ . '/../templates/template-parts/category-megamenu/third-level.php'));
                $c++;
                $pcc++;
            }

            if (gf_check_level_of_category($cat->term_id) == 3 || gf_check_level_of_category($cat->term_id) == 2) {
                if ($c == $child_count) {
                    echo '</ol>
                                    </div>';
                    $c = 0;
                }
            }
            if ($pcc == $parent_children_count){
                echo '</div>
                    </div>
                </li>
            </ul>';
                $pcc=0;
            }
        }

    }
    echo '</div>
	</div>
</div>';
}
