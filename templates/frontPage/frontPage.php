<div class="nssSingleWrapper">
    <?php
    if (!wp_is_mobile())
        gfGetTemplate('megaMenu');
    ?>
    <div class="nssContentWrapper">
        <?php
        if (!wp_is_mobile()) :?>
            <?php dynamic_sidebar('gf-homepage-row-1'); ?>
            <?php dynamic_sidebar('gf-homepage-row-2'); ?>
        <?php else: ?>
            <?php dynamic_sidebar('gf-homepage-row-1-mobile'); ?>
            <?php dynamic_sidebar('gf-homepage-row-3'); ?>
        <?php endif; ?>
    </div>
</div>
