<?php

//good
add_shortcode('gf-footer-credits', 'gf_footer_credits_shortcode');
function gf_footer_credits_shortcode()
{
    echo get_bloginfo('name') . ' ' . date('Y');
}
//bad
/*
add_shortcode('gf_my_account_link', 'gf_my_account_link_shortcode');
function gf_my_account_link_shortcode()
{
    $myaccount_page = get_option('woocommerce_myaccount_page_id');
    if ($myaccount_page) {
        $myaccount_page_url = get_permalink($myaccount_page);
    }

    echo '<div class="gf-my-account"><a href=" ' . $myaccount_page_url . '"><i class="fas fa-user"></i> ' . __('Moj nalog') . '</a></div>';
}

//bad
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

//bad
//mislim da se ovo ne koristi, moram da proverim
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

//bad
add_shortcode('gf-cart', 'gf_cart_shortcode');
function gf_cart_shortcode()
{
    global $woocommerce;
    ?>
    <a class="gf-header-cart" href="<?php echo wc_get_cart_url(); ?>"
       title="<?php _e('Cart View', 'green-friends'); ?>">
        <p class="gf-header-cart__title">
            <i class="fas fa-shopping-cart"></i> <span
                    class="shopping-cart__count"><?php echo sprintf(_n('%d', '%d', $woocommerce->cart->cart_contents_count, 'woothemes'),
                    $woocommerce->cart->cart_contents_count); ?></span>Korpa</p>
    </a>
    <?php
}
*/
/*
add_shortcode('gf-mobile-nav-menu', 'gf_mobile_nav_menu_shortcode');
function gf_mobile_nav_menu_shortcode()
{
//    if (wp_is_mobile()) {
    global $woocommerce;
    $cartUrl = wc_get_cart_url();
    $cartCount = sprintf(_n('%d', '%d', $woocommerce->cart->cart_contents_count, 'woothemes'), $woocommerce->cart->cart_contents_count);
    echo '<div class="gf-cart-icon-mobile"><a href="'.$cartUrl.'"><i class="fas fa-shopping-cart"></i><span class="shopping-cart__count">'.$cartCount.'</span></a></div>';
    echo '<div class="gf-search-icon" id="my-search-icon-toggle"><i class="fas fa-search " id="my-fancy-search"></i></div>';
    echo '<div class="gf-user-account-menu"><i class="fas fa-user" id="my-fancy-user"></i></div>';

    echo '<div class="gf-mobile-menu">'; ?>
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
*/
//good, mobile header search
add_shortcode('gf-mobile-search', 'gf_mobile_search_form');
function gf_mobile_search_form()
{
?>
<form id="gfSearchFormMobile" role="search" method="get" class="gf-search-form gf-search-form--mobile"
      action="/pretraga/">
    <span class="screen-reader-text"><?php _x('Search for:', 'label') ?></span>
    <input aria-label="Unesite frazu pretrage" type="search" id="searchInput" autocomplete="off" class="search-field gf-search-box" name="query"
           placeholder="<?=esc_attr_x('Unesite frazu pretrage &hellip;', '')?>"
           value="<?= get_search_query() ?>"/>
<!--    <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>-->
    <div class="nssWidth100">
        <div class="gf-autocomplete-results suggesstion-box suggesstion-box-mobile"></div>
    </div>
</form>
<div class="gf-radio-search-wrapper-mobile">
    <?php if (get_queried_object() && is_product_category()): ?>
        <div class="gf-search-radio-button-wrapper">
            <input class="searchRadioBox searchRadioCat" type="radio" id="search-radiobutton-cat" name="search-radiobutton" value="category" checked />
            <label for="search-radiobutton-cat" class="radioBtn1"><?= get_queried_object()->name ?></label>
        </div>
        <div class="gf-search-radio-button-wrapper">
            <input class="searchRadioBox searchRadioMain" type="radio" id="search-radiobutton-main" name="search-radiobutton" value="shop"/>
            <label for="search-radiobutton-main" class="radioBtn2">Pretraga celog sajta</label>
        </div>
    <?php endif ;?>
</div>
<?php
}

//good
add_shortcode('gf-best-selling-products', 'gf_display_best_selling_products');
function gf_display_best_selling_products(){
    include_once(WC()->plugin_path().'/includes/admin/reports/class-wc-admin-report.php');
    $wc_report = new WC_Admin_Report();

    $data = $wc_report->get_order_report_data( array(
        'data' => array(
            '_qty' => array(
                'type' => 'order_item_meta',
                'order_item_type' => 'line_item',
                'function' => 'SUM',
                'name' => 'quantity'
            ),
            '_line_subtotal' => array(
                'type' => 'order_item_meta',
                'order_item_type' => 'line_item',
                'function' => 'SUM',
                'name' => 'gross'
            ),
            '_product_id' => array(
                'type' => 'order_item_meta',
                'order_item_type' => 'line_item',
                'function' => '',
                'name' => 'product_id'
            ),
            'order_item_name' => array(
                'type'     => 'order_item',
                'function' => '',
                'name'     => 'order_item_name',
            ),
        ),
        'group_by'     => 'product_id',
        'order_by'     => 'quantity DESC',
        'query_type' => 'get_results',
        'limit' => 20,
        'order_status' => array( 'completed', 'processing', 'finalizovano','u-pripremi-placeno','naruceno',
            'spremno-za-slanje', 'spremno-za-pakovanje', 'poslato', 'isporuceno'),
    ) );

    $ids = [];
    foreach ($data as $datum){
        $ids[] = $datum->product_id;
    }

    $args = array(
        'post_type' => 'product',
        'orderby' => 'post__in',
        'post__in' => $ids,
        'posts_per_page' => 3,
        'meta_query' => [[
            'key' => '_stock_status',
            'value' => 'outofstock',
            'compare' => 'NOT IN'
        ]],
        'suppress_filters' => true,
        'no_found_rows' => true
    );

$query = new WP_Query($args);
echo '<h2>Najprodavaniji proizvodi</h2>';
echo '
<div class="woocommerce columns-1">';
    echo '
    <ul class="products columns-1">';
        if($query->have_posts($args)) :
        while($query->have_posts()) : $query->the_post();

        wc_get_template_part('content', 'product');

        endwhile;
        wp_reset_postdata();
        endif;
        echo '
    </ul>
    ';
    echo '
</div>';
}


