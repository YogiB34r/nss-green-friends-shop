<form role="search" method="get" class="gf-search-form"
      action="/pretraga/">
    <span class="screen-reader-text"><?php _x('Search for:', 'label') ?></span>
    <input type="search" class="search-field"
           placeholder="<?=esc_attr_x('Unesite frazu pretrage &hellip;', '')?>"
           value="<?= get_search_query() ?>" name="query" />
    <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
</form>
<div class="gf-radio-search-wrapper">
  <?php if (get_queried_object() && is_product_category()): ?>
      <input class="search-radio-box" type="radio" name="search-radiobutton" checked="checked" value="category">
      <label for="search-checkbox"><?= get_queried_object()->name ?></label>
      <input class="search-radio-box" type="radio" name="search-radiobutton" value="shop">
    	<label for="search-checkbox">Pretraga celog sajta</label>
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
