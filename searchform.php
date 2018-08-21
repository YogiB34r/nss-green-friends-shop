<form role="search" method="get" class="gf-search-form" action="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ))) ?>">
  <span class="screen-reader-text"><?php _x( 'Search for:', 'label' )?></span>
  <input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Unesite frazu pretrage &hellip;', '' ) ?>" value="<?php echo get_search_query() ?>" name="s" />
  <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
</form>
