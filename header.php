<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <?php if ( function_exists( 'gtm4wp_the_gtm_tag' ) ) { gtm4wp_the_gtm_tag(); } ?>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <?php if(is_single()):
    $product = wc_get_product(get_queried_object_id());
        if (has_post_thumbnail($product->get_id())):
            $imageUrl = get_the_post_thumbnail_url($product->get_id(), 'shop_catalog');
            $title = $product->get_title(); ?>
            <meta property="og:title" content="<?=$title?>" />
            <meta property="og:type" content="product" />
            <meta property="og:url" content="<?=get_the_permalink($product->get_id())?>"/>
            <meta property="og:image" content="<?=$imageUrl?>" />
            <meta name="twitter:title" content="<?=$title?>" />
            <meta name="twitter:image" content="<?=$imageUrl?>" />
            <meta name="twitter:card" content="summary_large_image" />
        <?php endif;?>
    <?php endif;?>
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
</head>
<body <?php body_class(); ?>>
<!-- End Google Tag Manager (noscript) -->
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

