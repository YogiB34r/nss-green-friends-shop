<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/shop/' ) ) ?>">
    <span class="screen-reader-text"><?php _x( 'Search for:', 'label' )?></span>
    <input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search &hellip;', 'placeholder' ) ?>" value="<?php echo get_search_query() ?>" name="s" />
    <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
    <span class="toggle-search"><i class="fa fa-search"></i></span>
</form>
