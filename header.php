<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5.0, user-scalable=yes">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
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
                if (price != '') {
                    fbq('track', 'Purchase', {value: price, currency: 'RSD'});
                }
            }
            <?php endif; ?>
        }
    </script>
    <noscript>
        <img height="1" width="1" src="https://www.facebook.com/tr?id=264258047766442&ev=PageView&noscript=1"/>
    </noscript>
    <!-- End Facebook Pixel Code -->

    <!-- Global site tag (gtag.js) - Google Ads: 775970702 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-775970702"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'AW-775970702');
    </script>

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-MGS7JCR');</script>
    <!-- End Google Tag Manager -->
</head>
<body <?php body_class(); ?>>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MGS7JCR"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<div id="page" class="hfeed site">
    <?php gfGetTemplate('header'); ?>
    <div id="content" class="siteContent" tabindex="-1">