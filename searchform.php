<form class="nssSearchForm" role="search" method="get" id="gfSearchForm"
      action="/pretraga/">
    <span class="screen-reader-text"><?php _x('Search for:', 'label') ?></span>
    <input aria-label="input field for search" type="search" autocomplete="off" class="searchField" id="gfSearchBox" name="query"
           placeholder="<?=esc_attr_x('Unesite frazu pretrage &hellip;', '')?>"
           value="<?= get_search_query() ?>"/>
    <button aria-label="searchSubmit" type="submit" class="searchSubmit"><i class="fa fa-search"></i></button>
    <div class="nssWidth100">
        <div id="nssSuggestionBox" class="nssAutocompleteResults"></div>
    </div>
</form>
<div class="nssRadioSearchWrapper">
  <?php if (get_queried_object() && is_product_category()): ?>
  <form>
      <div class="nssRadioButtonWrapper">
          <input class="searchRadioBox searchRadioCat" type="radio" id="search-radiobutton-cat" name="search-radiobutton" value="category" checked />
          <label for="search-radiobutton-cat" class="radioBtn1"><?= get_queried_object()->name ?></label>
      </div>
      <div class="nssRadioButtonWrapper">
          <input class="searchRadioBox searchRadioMain" type="radio" id="search-radiobutton-main" name="search-radiobutton" value="shop"/>
          <label for="search-radiobutton-main" class="radioBtn2">Pretraga celog sajta</label>
      </div>
  </form>
  <?php endif ;?>
</div>