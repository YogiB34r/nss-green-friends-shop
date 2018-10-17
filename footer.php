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
                <div class="col-xs-12 col-md-9 gf-footer-section ">
                    <div class="row gf-footer-links-wrapper padding-left-45">
                        <a class="mr-3 mt-2" href="/o-kompaniji/">O kompaniji</a>
                        <a class="mr-3 mt-2" href="/kontaktirajte-nas/">Kontaktirajte nas</a>

                            <?php if (is_user_logged_in()) {
                                echo '<a class="mr-3 mt-2" href="/moj-nalog/narudzbine/">Praćenje narudžbenice</a>';
                            } else {
                                '<a class="mr-3 mt-2" href="/pracenje-narudzbenice/">Praćenje narudžbenice</a>';
                            }
                            ?>


                        <a class="mr-3 mt-2" href="/podrska/">Podrška</a>
                        <a class="mr-3 mt-2" href="/uslovi-kupovine/">Uslovi kupovine</a>
                        <a class="mr-3 mt-2" href="/politika-privatnosti-2/">Politika privatnosti</a>

                        </div>
                        <div class="row mt-4 gf-footer-card-images-wrapper padding-left-45">
                            <div class="">
                                <div class="gffooter-card-3"></div>
                                <div class="gffooter-card-4"></div>
                                <div class="gffooter-card-5"></div>
                                <div class="gffooter-card-6"></div>
                            </div>
                            <div class="">
                                <a href="https://www.visa.ca/en_CA/run-your-business/merchant-resources/verified-by-visa.html">
                                    <div class="gffooter-card-1"></div>
                                </a>
                                <a href="https://www.mastercard.us/en-us/merchants/safety-security/securecode.html">
                                    <div class="gffooter-card-2"></div>
                                </a>
                                <a href="http://www.bancaintesa.rs/pocetna.1.html">
                                    <div class="gffooter-card-7"></div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</footer>
<!-- #colophon -->
</div>
<!-- #page -->
<?php wp_footer(); ?>
<!-- Facebook Pixel Code -->
<script> !function (f, b, e, v, n, t, s) {
        if (f.fbq) return;
        n = f.fbq = function () {
            n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments)
        };
        if (!f._fbq) f._fbq = n;
        n.push = n;
        n.loaded = !0;
        n.version = '2.0';
        n.queue = [];
        t = b.createElement(e);
        t.async = !0;
        t.src = v;
        s = b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t, s)
    }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '125798604755023');</script>
<noscript><img height="1" width="1" style="display:none"
               src="https://www.facebook.com/tr?id=125798604755023&ev=PageView&noscript=1"/>
</noscript> <!-- DO NOT MODIFY --> <!-- End Facebook Pixel Code -->

<!--facebook share-->
<div id="fb-root"></div>
<script>(function (d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s);
        js.id = id;
        js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.1';
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>

<!--Pinterest share-->
<script type="text/javascript" async defer src="//assets.pinterest.com/js/pinit.js"></script>

<!--Google+ share-->
<script src="https://apis.google.com/js/platform.js" async defer></script>

<!--Twitter share-->
<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>


</body>
</html>



