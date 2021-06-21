<?php
$customLogoId = get_theme_mod('custom_logo');
$image = wp_get_attachment_image_src($customLogoId, 'full');
$cartUrl = wc_get_cart_url();
$myaccountPage = get_option('woocommerce_myaccount_page_id');
if ($myaccountPage) {
    $myaccountPageUrl = get_permalink($myaccountPage);
}
?>
<header id="masthead" class="nssHeader" role="banner">
    <?php if(!wp_is_mobile()):?>
    <div class="nssTopBar">
        <div class="nssTopBarMenu">
            <?php dynamic_sidebar('gf-text-top-bar'); ?>
            <?php wp_nav_menu(array('menu' => 'Topbar')); ?>
        </div>
    </div>
    <?php endif; ?>
    <div class="nssPrimaryNav">
        <div class="nssLogo">
            <a href=<?= get_home_url(); ?>><img src="<?= $image[0]; ?>" width="<?=$image[1];?>" height="<?=$image[2];?>" alt="logo"></a>
        </div>
        <?php if (!wp_is_mobile()) : ?>
        <div class="nssSearch">
            <?php require_once(__DIR__ . '/../../searchform.php'); ?>
        </div>
        <div class="nssNavIcons">
            <a class="nssHeaderCart" href=<?= $cartUrl; ?> title="CartView">
                <p class="nssHeaderCartTitle"><i class="fas fa-shopping-cart"></i><span id="cartCount" class="nssCartCount"> </span><?= __(' Korpa'); ?></p>
            </a>
            <a href=<?= $myaccountPageUrl; ?>><i class="fas fa-user"></i><?= __(' Moj nalog'); ?></a>
        </div>
    </div>
    <?php else: ?>
        <?php require_once(__DIR__ . '/headerMobile.php'); ?>
    <?php endif; ?>
</header>
