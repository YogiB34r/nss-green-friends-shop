<form role="search" method="get" class="gf-search-form"
     action="<?=esc_url(get_permalink(wc_get_page_id('shop'))); ?>">
   <span class="screen-reader-text"><?php _x('Search for:', 'label') 
?></span>
   <input type="search" class="search-field"
          placeholder="<?=esc_attr_x('Unesite frazu pretrage &hellip;', 
'')?>"
          value="<?= get_search_query() ?>" name="s"/>
   <button type="submit" class="search-submit"><i class="fa 
fa-search"></i></button> </form>
   <div class="gf-radio-search-wrapper">
     <?php if (get_queried_object() && is_product_category()): ?>
         <label for="search-checkbox">
            <input class="search-radio-box" type="radio" 
name="search-radiobutton" checked="checked" value="category" hidden>
            <span><?= get_queried_object()->name ?></span>
          </label>
          <span class="search-radio" type="radio" 
name="search-radiobutton" value="shop" hidden></span>
         <label for="search-checkbox">
           <input class="search-radio-box" type="radio" 
name="search-radiobutton" value="shop" hidden>
            <span>Pretraga celog sajta</span>
          </label>
     <?php endif ;?>
   </div>
