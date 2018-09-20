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
    add_shortcode('gf-mobile-search', 'gf_mobile_search_form');
    function gf_mobile_search_form()
    {
        ?>
        <form role="search" method="get" class="gf-search-form gf-search-form--mobile"
              action="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))) ?>">
            <div class="search-toggle-wrapper">
                <div class="gf-search-toggle"><i class="fa fa-search"></i></div>
            </div>
            <span class="screen-reader-text"><?php _x('Search for:', 'label') ?></span>
            <div class="search-input-wrapper">
                <input type="search" class="search-field" placeholder="<?php echo esc_attr_x('Search &hellip;', '') ?>"
                       value="<?php echo get_search_query() ?>" name="s"/>
                <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
            </div>
        </form>
        <div class="gf-radio-search-wrapper gf-radio-search-wrapper--mobile">
        <?php if (get_queried_object() && is_product_category()): ?>
        <label for="search-checkbox">
            <input class="search-radio-box" type="radio" name="search-radiobutton" checked="checked" value="category"
                   hidden>
            <span><?= get_queried_object()->name ?></span>
        </label>
        <span class="search-radio" type="radio" name="search-radiobutton" value="shop" hidden></span>
        <label for="search-checkbox">
            <input class="search-radio-box" type="radio" name="search-radiobutton" value="shop" hidden>
            <span>Pretraga celog sajta</span>
        </label>
    <?php endif; ?>
        </div><?php
    }
}


