<div class="nssNavIcons">
    <div class="nssCartMobile">
        <a href=<?= $cartUrl; ?>>
            <i class="fas fa-shopping-cart"></i>
            <span id="cartCount" class="nssCartCount"></span>
        </a>
    </div>
    <div class="nssSearchMobile" id="nssSearchToggle">
        <i class="fas fa-search " id="nssFancySearch"></i>
    </div>
    <div class="nssUserMobile">
        <i class="fas fa-user" id="nssFancyUser"></i>
    </div>
    <?php wp_nav_menu(array(
        'menu' => 'mobileUser',
        'menu_class' => 'nssMobileUserLink',
        'container_class' => 'nssMobileUserMenu',
        'container_id' => 'nssMobileUserMenu',
    )); ?>
<!--    <div class="nssMobileUserMenu" id="nssMobileUserMenu">-->
<!--        <div class="nssMobileUserLink">-->
<!--            <a id="accCartCount" class="nssHeaderMobileCart" href=--><?//= $cartUrl; ?><!-- -->
<!--            title="CartView">-->
<!--            </a>-->
<!--        </div>-->
<!---->
<!--        --><?php //if (is_user_logged_in()) : ?>
<!--            <div class="nssMobileUserLink">-->
<!--                <a href=--><?//= $myaccountPageUrl; ?><!-- --><?//= __(' Moj nalog'); ?>
<!--                </a>-->
<!--            </div>-->
<!--        --><?php //else: ?>
<!--            <div class="nssMobileUserLink">-->
<!--                <a href="/moj-nalog/edit-account/"> --><?//= __(' Registracija'); ?><!-- -->
<!--                </a>-->
<!--            </div>-->
<!--            <div class="nssMobileUserLink">-->
<!--                <a href="/moj-nalog/edit-account/"> --><?//= __(' Prijava'); ?><!-- -->
<!--                </a>-->
<!--            </div>-->
<!--        --><?php //endif; ?>
<!--    </div>-->
    <div class="nssMegaMenuMobileToggle"><i class="fas fa-bars" id="gf-bars-icon-toggle"></i></div>
    <div id="mobileMegaMenu" class="gf-category-accordion"></div>
</div>
</div>
<div class="nssSearchSlider" id="searchSlider">
    <?php gf_mobile_search_form(); ?>
</div>
