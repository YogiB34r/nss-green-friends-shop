<form role="search" method="get" class="gf-search-form"
      action="/pretraga/">
    <span class="screen-reader-text"><?php _x('Search for:', 'label') ?></span>
    <input type="search" autocomplete="off" class="search-field gf-search-box" name="query"
           placeholder="<?=esc_attr_x('Unesite frazu pretrage &hellip;', '')?>"
           value="<?= get_search_query() ?>"/>
    <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
    <div class="gf-widht-100">
        <div class="gf-autocomplete-results suggesstion-box"></div>
    </div>
</form>
<div class="gf-radio-search-wrapper">
  <?php if (get_queried_object() && is_product_category()): ?>
      <input class="search-radio-box" type="radio" id="search-radiobutton-cat" name="search-radiobutton" checked="checked" value="category" />
      <label for="search-radiobutton-cat"><?= get_queried_object()->name ?></label>
      <input class="search-radio-box" type="radio" id="search-radiobutton-main" name="search-radiobutton" value="shop" />
    	<label for="search-radiobutton-main">Pretraga celog sajta</label>
  <?php endif ;?>
</div>