</div>
<!-- gf main content container -->
</div>
<!-- .col-full -->
</div>
<!-- #content -->
<footer id="colophon" class="site-footer" role="contentinfo">
    <div class="container-fluid">
        <div class="gf-main-content-container">
            <div class="row list-unstyled gf-footer justify-content-end">
                <div class="col-xs-12 col-md-3 gf-footer-section gf-footer-newsletter">
                    <?php dynamic_sidebar('gf-footer-row-1-column-1'); ?>
                </div>
                <div class="col-xs-12 col-md-3 gf-footer-section align-self-end">
                    <?php dynamic_sidebar('gf-footer-row-1-column-2'); ?>
                </div>
                <div class="col-xs-12 col-md-3 gf-footer-section align-self-end">
                    <?php dynamic_sidebar('gf-footer-row-1-column-3'); ?>
                </div>
                <div class="col-xs-12 col-md-3 gf-footer-section align-self-end">
                    <?php dynamic_sidebar('gf-footer-row-1-column-4'); ?>
                </div>
            </div>
            <div class="row list-unstyled">
                <div class="col-9">
                    <?php dynamic_sidebar('gf-footer-row-2-column-1'); ?>
                </div>
                <div class="col-3">
                    <?php dynamic_sidebar('gf-footer-row-2-column-2'); ?>
                </div>
            </div>
            <div class="row list-unstyled gf-footer gf-footer-images-wrapper">
                <div class="my-footer-images">
                    <?php $theme_dir = get_template_directory_uri(); ?>
                    <ul class="row">
                        <div class="col d-flex gf-justify justify-content-center mt-3 px-4">
                            <li>
                                <img src="<?= $theme_dir ?>/assets/images/footer_card_3.png" alt="Visa">
                            </li>
                            <li>
                                <img src="<?= $theme_dir ?>/assets/images/footer_card_4.png" alt="mastercard">
                            </li>
                            <li>
                                <img src="<?= $theme_dir ?>/assets/images/footer_card_5.png" alt="maestrocard">
                            </li>
                            <li>
                                <img src="<?= $theme_dir ?>/assets/images/footer_card_6.png" alt="American Express">
                            </li>
                        </div>
                        <div class="col d-flex justify-content-center mt-3 px-4">
                            <li>
                                <a href="https://www.visa.ca/en_CA/run-your-business/merchant-resources/verified-by-visa.html">
                                    <img src="<?= $theme_dir ?>/assets/images/footer_card_1.png" alt="Verified by Visa">
                                </a>
                            </li>
                            <li>
                                <a href="https://www.mastercard.us/en-us/merchants/safety-security/securecode.html">
                                    <img src="<?= $theme_dir ?>/assets/images/footer_card_2.png"
                                         alt="MasterCard SecureCode">
                                </a>
                            </li>
                            <li>
                                <a href="http://www.bancaintesa.rs/pocetna.1.html">
                                    <img src="<?= $theme_dir ?>/assets/images/footer_card_7.jpg" alt="Banca Intesa">
                                </a>
                            </li>
                        </div>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- #colophon -->
</div>
<!-- #page -->
<?php wp_footer(); ?>
<!-- Facebook Pixel Code --> <script> !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod? n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n; n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0; t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js'); fbq('init', '125798604755023'); // Insert your pixel ID here. fbq('track', 'PageView'); </script> <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=125798604755023&ev=PageView&noscript=1" /></noscript> <!-- DO NOT MODIFY --> <!-- End Facebook Pixel Code -->
</body>
</html>
