<?php
?>
<div class="nssFooterContainer">
    <div class="nssFooterSection">
        <div class="nssFooterLinks">
            <a href="/o-kompaniji/"><?=__('O kompaniji');?></a>
            <a href="/kontaktirajte-nas/"><?=__('Kontaktirajte nas')?></a>

            <?php if (is_user_logged_in()) :?>
                <a href="/moj-nalog/narudzbine/"><?=__('Praćenje narudžbenice'); ?></a>
            <?php else:?>
                <a href="/pracenje-narudzbenice/"><?=__('Praćenje narudžbenice'); ?></a>
            <?php endif;?>

            <a href="/podrska/"><?=__('Podrška'); ?></a>
            <a href="/uslovi-kupovine/"><?=__('Uslovi kupovine'); ?></a>
            <a href="/politika-privatnosti-2/"><?=__('Politika privatnosti'); ?></a>

        </div>
        <div class="nssFooterCardsWrapper">
            <div class="nssVisaCard"></div>
            <div class="nssMasterCard"></div>
            <div class="nssMaestroCard"></div>
            <div class="nssAmericanExpress"></div>
            <a rel="nofollow"
               href="https://www.visa.ca/en_CA/run-your-business/merchant-resources/verified-by-visa.html">
                <div class="nssVisaVerified"></div>
            </a>
            <a rel="nofollow" href="https://www.mastercard.us/en-us/merchants/safety-security/securecode.html">
                <div class="nssMasterSecure"></div>
            </a>
            <a rel="nofollow" href="http://www.bancaintesa.rs/pocetna.1.html">
                <div class="nssBancaIntesa"></div>
            </a>
        </div>
    </div>
</div>


