<form role="search" method="get" class="gf-search-form" action="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ))) ?>">
  <span class="screen-reader-text"><?php _x( 'Search for:', 'label' )?></span>
  <input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search &hellip;', '' ) ?>" value="<?php echo get_search_query() ?>" name="s" />
  <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
  <span class="toggle-search"><i class="fas fa-search"></i></span>
</form>

<form role="search" method="get" class="gf-search-form gf-search-form--mobile" action="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) )) ?>">
  <div class="search-toggle-wrapper"><div class="gf-search-toggle"><i class="fa fa-search"></i></div></div>
  <span class="screen-reader-text"><?php _x( 'Search for:', 'label' )?></span>
  <div class="search-input-wrapper">
    <input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search &hellip;', '' ) ?>" value="<?php echo get_search_query() ?>" name="s" />
    <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
  </div>
</form>
