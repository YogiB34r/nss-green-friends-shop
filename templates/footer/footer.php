<?php
$frontPageID = (int)get_option('page_on_front');
$currentPageID = get_queried_object_id();
$mobile = wp_is_mobile();
?>
<div class="nssFooterContainer">
    <?php if($currentPageID !== $frontPageID) :?>
    <div class="nssFooterSectionWidget">
        <?php dynamic_sidebar('gf-footer-row-1-column-1'); ?>
    </div>
    <?php endif; ?>

    <div class="nssFooterSectionNav <?= !$mobile && $currentPageID == $frontPageID ? 'marginLeft':''; ?>">
        <?php wp_nav_menu(array(
            'menu' => 'Footer',
            'menu_class' => 'footerMenu',
            'container_class' => 'nssFooterLinks'
        )); ?>
        <div class="nssFooterCardsWrapper">
            <div class="nssVisaCard"></div>
            <div class="nssMasterCard"></div>
            <div class="nssMaestroCard"></div>
            <div class="nssAmericanExpress"></div>
            <a aria-label="Visa verified" rel="nofollow"
               href="https://www.visa.ca/en_CA/run-your-business/merchant-resources/verified-by-visa.html">
                <div class="nssVisaVerified"></div>
            </a>
            <a aria-label="Master Card" rel="nofollow" href="https://www.mastercard.us/en-us/merchants/safety-security/securecode.html">
                <div class="nssMasterSecure"></div>
            </a>
            <a aria-label="Banca Intesa" rel="nofollow" href="http://www.bancaintesa.rs/pocetna.1.html">
                <div class="nssBancaIntesa"></div>
            </a>
        </div>
    </div>
</div>


