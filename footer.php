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

<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-108239528-1', 'auto'); ga('send', 'pageview');

</script>
</body>
</html>



