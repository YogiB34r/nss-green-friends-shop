<?php
?>
<div class="nssFooterContainer">
    <div class="nssFooterSection">
        <?php dynamic_sidebar('gf-footer-row-1-column-1'); ?>
    </div>
    <div class="nssFooterSection">
        <?php wp_nav_menu(array(
            'menu' => 'Footer',
            'menu_class' => 'footerMenu',
            'container_class' => 'nssFooterLinks'
        )); ?>
        <div class="nssFooterCardsWrapper">
            <li>
            <div class="nssVisaCard"></div>
            <div class="nssMasterCard"></div>
            <div class="nssMaestroCard"></div>
            <div class="nssAmericanExpress"></div>
            </li>
            <li>
            <a rel="nofollow"
               href="https://www.visa.ca/en_CA/run-your-business/merchant-resources/verified-by-visa.html">
                <div class="nssVisaVerified"></div>
            </a>
            <a rel="nofollow" href="https://www.mastercard.us/en-us/merchants/safety-security/securecode.html">
                <div class="nssMasterSecure"></div>
            </a>
            </li>
            <a rel="nofollow" href="http://www.bancaintesa.rs/pocetna.1.html">
                <div class="nssBancaIntesa"></div>
            </a>
        </div>
    </div>
</div>
