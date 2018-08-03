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

    echo '<div class="gf-my-account"><a href=" ' . $myaccount_page_url . '"><i class="fas fa-user"></i> My account</a></div>';
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
            Categories
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
      <input type="text" name="s" class="form-control" maxlength="128" value="<?php echo get_search_query(); ?>" placeholder="Search Products">
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
      <button type="submit" title="<?php esc_attr_e('Search,', 'woocommerce'); ?>" class="btn btn-primary my-sm-0"><i class="fas fa-search"></i>
            </button>
    </div>

  </form>
  <?php
}

add_shortcode('gf-cart', 'gf_cart_shortcode');
function gf_cart_shortcode()
{
    global $woocommerce ?>
    <a class="gf-header-cart" href="<?php echo wc_get_cart_url(); ?>" title="<?php _e('Cart View', 'green-friends'); ?>">
      <p class="gf-header-cart__title">
        <i class="fas fa-shopping-cart"></i> <span class="shopping-cart__count"><?php echo sprintf(_n('%d', '%d', $woocommerce->cart->cart_contents_count, 'woothemes'),
            $woocommerce->cart->cart_contents_count);?></span>Vaša korpa</p>
    </a>
    <?php
}

// Category sidebar
function gf_category_megamenu_shortcode() {
    $args = array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'parent'   => 0
    );
    $product_cat = get_terms( $args );
    echo
    '<div id="gf-wrapper">
	     <div class="gf-sidebar">
		     <div class="gf-toggle"><i class="fa fa-bars"></i></div>
		       <div class="gf-navblock">';
    foreach ($product_cat as $parent_product_cat) {
        if($parent_product_cat->name != 'Slider'):
            echo '
            <ul class="gf-navigation">
              <li class="category-item">
                <a tabindex="-1" href="'.get_term_link($parent_product_cat->term_id).'">'.$parent_product_cat->name.'</a>
                <div class="mega-menu row z-depth-1 primary-color-dark" aria-labelledby="navbarDropdownMenuLink2">
                  <div class="row mega-menu__row">';
            $child_args = array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'parent'   => $parent_product_cat->term_id
            );
            $child_product_cats = get_terms( $child_args );

            foreach ($child_product_cats as $child_product_cat) {
                $child_child_args = array('taxonomy' => 'product_cat',
                    'hide_empty' => false,
                    'parent'   => $child_product_cat->term_id
                );
                $child_child_product_cats = get_terms( $child_child_args );
                echo '<div class="col-md-3 col-xl-3 sub-menu">
                        <ol class="list-unstyled ml-4 mr-md-0 mr-4">
                          <li class="sub-category-title text-uppercase mt-sm">
                            <a class="menu-item" href="'.get_term_link($child_product_cat->term_id).'">'.$child_product_cat->name.'</a>
                          </li>';
                foreach ($child_child_product_cats as $child_child_product_cat) {
                    echo '<li class="sub-sub-category-title text-uppercase">
                            <a class="sub-menu-item" href="'.get_term_link($child_child_product_cat->term_id).'">'.$child_child_product_cat->name.'</a>
                          </li>';
                }
                echo '</ol>
                    </div>';
            }
            echo '</div></div></li></ul>';
        endif;
    }
    echo '</div>
	</div>
</div>';
}
add_shortcode('gf-category-megamenu', 'gf_category_megamenu_shortcode');
