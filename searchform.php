
<form role="search" method="get" class="gf-search-form"
      action="">
    <span class="screen-reader-text"><?php _x('Search for:', 'label') ?></span>
    <input type="search" class="search-field"
           placeholder="<?php echo esc_attr_x('Unesite frazu pretrage &hellip;', '') ?>"
           value="<?php echo get_search_query() ?>" name="s"/>
    <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
</form>
<?php if (get_queried_object() && is_product_category()): ?>
    <input class="search-radio-box" type="radio" name="search-radiobutton" checked="checked" value="">
    <label for="search-checkbox"><?= get_queried_object()->name ?></label>
    <input class="search-radio-box" type="radio" name="search-radiobutton" value="shop"><label
            for="search-checkbox">Pretraga celog sajta</label>
<?php endif ;?>
<script type="text/javascript">
    jQuery(document).ready(function($){
        var radioValue='';
        $(".gf-search-form").submit(function () {
            var radioValue = $('input[name=search-radiobutton]:checked').val();
            console.log(radioValue);
            if (radioValue === 'shop'){
                $(".gf-search-form").attr("action", "<?=esc_url(get_permalink(wc_get_page_id('shop'))); ?>");
                console.log('cao');
            }
        });
    });
</script>
