<?php
$custom_logo_id = get_theme_mod('custom_logo');
$image = wp_get_attachment_image_src($custom_logo_id, 'full');
$cartUrl = wc_get_cart_url();
$myaccount_page = get_option('woocommerce_myaccount_page_id');
if ($myaccount_page) {
    $myaccount_page_url = get_permalink($myaccount_page);
}
?>
<header id="masthead" class="nssHeader" role="banner">
    <div class="nssTopBar">
        <div class="nssTopBarMenu">
            <?php dynamic_sidebar('gf-header-row-1'); ?>
            <?php wp_nav_menu(array('menu' => 'Topbar')); ?>
        </div>
    </div>
    <div class="nssPrimaryNav">
        <div class="nssLogo">
            <a href=<?= get_home_url(); ?>><img src="<?= $image[0]; ?>" alt="logo"></a>
        </div>
        <?php if (!wp_is_mobile()) : ?>
        <div class="nssSearch">
            <?php require_once(__DIR__ . '/../../searchform.php'); ?>
        </div>
        <div class="nssNavIcons">
            <a class="nssHeaderCart" href=<?= $cartUrl; ?> title="CartView">
                <p class="nssHeaderCartTitle">
                    <i class="fas fa-shopping-cart"></i><span class="nssCartCount"></span><?= __(' Korpa'); ?>
                </p>
            </a>
            <a href=<?= $myaccount_page_url; ?>><i class="fas fa-user"></i><?= __(' Moj nalog'); ?></a>
        </div>
    </div>
    <?php else: ?>
        <?php require_once(__DIR__ . '/headerMobile.php'); ?>
    <?php endif; ?>
</header>
