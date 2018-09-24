<form role="search" method="get" class="gf-search-form"
      action="/pretraga/">
    <span class="screen-reader-text"><?php _x('Search for:', 'label') ?></span>
    <input type="search" autocomplete="off" class="search-field" name="query"
           placeholder="<?=esc_attr_x('Unesite frazu pretrage &hellip;', '')?>"
           value="<?= get_search_query() ?>" id="gf-search-box" />
    <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
    <div id="suggesstion-box" class="gf-autocomplete-results"></div>
</form>
<div class="gf-radio-search-wrapper">
  <?php if (get_queried_object() && is_product_category()): ?>
      <input class="search-radio-box" type="radio" id="search-radiobutton-cat" name="search-radiobutton-cat" checked="checked" value="category">
      <label for="search-radiobutton-cat"><?= get_queried_object()->name ?></label>
      <input class="search-radio-box" type="radio" id="search-radiobutton-shop" name="search-radiobutton-shop" value="shop">
    	<label for="search-radiobutton-shop">Pretraga celog sajta</label>
  <?php endif ;?>
</div>
<script type="text/javascript">
    jQuery(document).ready(function($){
        var radioValue='';
        $(".gf-search-form").submit(function () {
            var radioValue = $('input[name=search-radiobutton]:checked').val();
            if (radioValue === 'category'){
                $(".gf-search-form").attr("action", "");
            }
        });
    });
</script>
