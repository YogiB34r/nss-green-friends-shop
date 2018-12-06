<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <title>NonStopShop.rs - online kupovina - uvek dobre cene - onlajn prodavnica - prodaja preko interneta - Srbija -
        Beograd</title>
    <?php wp_head(); ?>
    <!-- Facebook Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window,document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '264258047766442');
        fbq('track', 'PageView');
        window.addEventListener('load', function () {
            FBEvents();
        });

        function FBEvents() {
            <?php if (isset($_POST['add_to_cart'])): ?>
            fbq('track', 'AddToCart');
            <?php endif; ?>
            <?php if (get_query_var('pagename') === 'placanje' && !get_query_var('order-received')): ?>
            fbq('track', 'InitiateCheckout');
            <?php endif; ?>
            <?php if (get_query_var('order-received')): ?>
            var doc = document.getElementsByClassName('woocommerce-order-overview');
            if (doc.length) {
                var price = doc.item(0).getElementsByClassName('woocommerce-Price-amount').item(0).textContent;
                price = price.split('din')[0];
                price = price.replace(',', '.');
                fbq('track', 'Purchase', {value: price, currency: 'RSD'});
            }
            <?php endif; ?>
        }
    </script>
    <noscript>
        <img height="1" width="1" src="https://www.facebook.com/tr?id=264258047766442&ev=PageView&noscript=1"/>
    </noscript>
    <!-- End Facebook Pixel Code -->
    <script>
        if (window.location.pathname === '/' && window.location.host.search('nonstopshop.rs')) {
            window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
            ga('create', 'UA-108239528-1', { 'cookieDomain': 'nonstopshop.rs' } );
            // Plugins
            ga('send', 'pageview');
        }
    </script>
    <?php global $wp; ?>
    <?php if($wp->request === ''): ?>
    <script async src="https://www.google-analytics.com/analytics.js"></script>
    <!-- WooCommerce Google Analytics Integration -->
    <script type='text/javascript'>
        var gaProperty = 'UA-108239528-1';
        var disableStr = 'ga-disable-' + gaProperty;
        if ( document.cookie.indexOf( disableStr + '=true' ) > -1 ) {
            window[disableStr] = true;
        }
        function gaOptout() {
            document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
            window[disableStr] = true;
        }
    </script>
    <script type='text/javascript'>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga( 'create', 'UA-108239528-1', 'auto' );ga( 'set', 'anonymizeIp', true );
        ga( 'set', 'dimension1', 'no' );
        ga( 'require', 'ec' );</script>
    <!-- /WooCommerce Google Analytics Integration -->
    <?php endif; ?>
</head>

<body <?php body_class(); ?>>
<!-- (C)2000-2014 Gemius SA - gemiusAudience / nonstopshop.rs / Ceo sajt -->
<script type="text/javascript">
    <!--//--><![CDATA[//><!--
    var pp_gemius_identifier = 'nXdATXugR4Rheccjvn98r6bSfcBg4KM5o9bukzwvLDL.k7';
    // lines below shouldn't be edited
    function gemius_pending(i) { window[i] = window[i] || function() {var x = window[i+'_pdata'] = window[i+'_pdata'] || []; x[x.length]=arguments;};};
    gemius_pending('gemius_hit'); gemius_pending('gemius_event'); gemius_pending('pp_gemius_hit'); gemius_pending('pp_gemius_event');
    (function(d,t) {try {var gt=d.createElement(t),s=d.getElementsByTagName(t)[0],l='http'+((location.protocol=='https:')?'s':''); gt.setAttribute('async','async');
        gt.setAttribute('defer','defer'); gt.src=l+'://gars.hit.gemius.pl/xgemius.js'; s.parentNode.insertBefore(gt,s);} catch (e) {}})(document,'script');
    //--><!]]>
</script>
<div id="page" class="hfeed site">
    <header id="masthead" class="site-header" role="banner">
        <div class="container-fluid container--navigation">
            <div class="gf-top-bar">
                <div class="row gf-top-bar__container">
                    <div class="col-3"></div>
                    <div class="col-9 gf-top-bar__menu">
                        <?php dynamic_sidebar('gf-header-row-1'); ?>
                    </div>
                </div>
            </div>
            <div class="row gf-primary-navigation">
                <div class="col-3 gf-logo">
                    <div class="gf-logo-wrapper">
                        <?php dynamic_sidebar('gf-header-row-2-col-1') ?>
                    </div>
                </div>
                <div class="col-md-6 col-lg-7 gf-search">
                    <div class="gf-search-wrapper">
                        <?php dynamic_sidebar('gf-header-row-2-col-2') ?>
                    </div>
                </div>
                <div class="col-9 col-md-3 col-lg-2 gf-navigation">
                    <div class="gf-navigation-wrapper">
                        <?php dynamic_sidebar('gf-header-row-2-col-3') ?>
                    </div>
                </div>
            </div>
            <div class="row list-unstyled px-2">
                <div class="mobile-search">
                    <?php dynamic_sidebar('gf-search-form-mobile') ?>
                </div>
            </div>
        </div>
    </header>
    <div id="content" class="site-content" tabindex="-1">
        <div class="col-full">
            <div class="gf-main-content-container">

