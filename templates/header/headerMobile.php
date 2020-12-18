<div class="nssNavIcons">
    <div class="nssCartMobile">
        <a href=<?= $cartUrl; ?>><i class="fas fa-shopping-cart"></i><span
                    class="nssCartCount"></span>
        </a>
    </div>
    <div class="nssSearchMobile" id="nssSearchToggle">
        <i class="fas fa-search " id="nssFancySearch"></i>
    </div>
    <div class="nssUserMobile">
        <i class="fas fa-user" id="nssFancyUser"></i>
    </div>
    <div class="nssMobileUserMenu" style="display: none;">
        <li class="nssMobileUserLink">
            <a class="nssHeaderMobileCart" href=<?= $cartUrl; ?> title="CartView">
            </a>
        </li>
        <?php if (is_user_logged_in()) :?>
            <li class="nssMobileUserLink">
                <a href=<?= $myaccount_page_url; ?> > <?= __(' Moj nalog'); ?> </a>
            </li>
        <?php else:?>
            <li class="nssMobileUserLink">
                <a href="/moj-nalog/edit-account/" > <?= __(' Registracija'); ?> </a>
            </li>
            <li class="nssMobileUserLink">
                <a href="/moj-nalog/edit-account/" > <?= __(' Prijava'); ?> </a>
            </li>
        <?php endif;?>

        <li class="nssMobileUserLink">
        </li>
    </div>
    <?php printMobileMegaMenu(); ?>

</div>
</div>
<div class="nssSearchSlider">
    <?php gf_mobile_search_form(); ?>
<!--    --><?php //dynamic_sidebar('gf-search-form-mobile') ?>
</div>
