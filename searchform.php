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
  <form>
      <div class="gf-search-radio-button-wrapper">
          <input class="search-radio-box search-radiobutton-cat" type="radio" id="search-radiobutton-cat" name="search-radiobutton" value="category"/>
          <label for="search-radiobutton-cat" class="s-radio-btn-1"><?= get_queried_object()->name ?></label>
      </div>
      <div class="gf-search-radio-button-wrapper">
          <input class="search-radio-box search-radiobutton-main" type="radio" id="search-radiobutton-main" name="search-radiobutton" value="shop"/>
          <label for="search-radiobutton-main" class="s-radio-btn-2">Pretraga celog sajta</label>
      </div>
  </form>
  <?php endif ;?>
</div>