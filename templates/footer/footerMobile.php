<?php
?>
<div class="nssFooterContainer">
    <div class="nssFooterSection">
        <?php dynamic_sidebar('gf-footer-row-1-column-1'); ?>
    </div>
    <div class="nssFooterSection">
        <div class="nssFooterLinks">
            <li class="nssMobileFooterLinks">
                <a href="/o-kompaniji/"><?=__('O kompaniji');?></a>
                <a href="/kontaktirajte-nas/"><?=__('Kontaktirajte nas')?></a>
            </li>
            <li class="nssMobileFooterLinks">
                <?php if (is_user_logged_in()) :?>
                    <a href="/moj-nalog/narudzbine/"><?=__('Praćenje narudžbenice'); ?></a>
                <?php else:?>
                    <a href="/pracenje-narudzbenice/"><?=__('Praćenje narudžbenice'); ?></a>
                <?php endif;?>

                <a href="/podrska/"><?=__('Podrška'); ?></a>
            </li>
            <li class="nssMobileFooterLinks">
                <a href="/uslovi-kupovine/"><?=__('Uslovi kupovine'); ?></a>
                <a href="/politika-privatnosti-2/"><?=__('Politika privatnosti'); ?></a>
            </li>
        </div>
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
