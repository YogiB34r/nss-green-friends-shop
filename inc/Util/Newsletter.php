<?php

namespace GF\Util;


class Newsletter
{
    public function init()
    {
        add_action('init', [$this, 'addNewsletterEndPoint']);
        add_filter('query_vars', [$this, 'newsletterQueryVars'], 0);
//        add_filter('woocommerce_account_menu_items', [$this, 'newsletterMenu']);
        add_action('woocommerce_account_newsletter-account_endpoint', [$this, 'newsletterMenuPage']);
    }

    /**
     * Register new endpoint to use for My Account page
     **/

    public function addNewsletterEndPoint()
    {
        add_rewrite_endpoint('newsletter-account', EP_ROOT | EP_PAGES);
    }

    public function newsletterQueryVars($vars)
    {
        $vars[] = 'newsletter-account';
        return $vars;
    }

    public function newsletterMenu($items)
    {
        $items['newsletter-account'] = __('Email notifikacije', 'gfShopTheme');
        return $items;
    }

    public function newsletterMenuPage()
    {
        wp_safe_redirect(\NewsletterProfile::instance()->get_profile_url(\NewsletterProfile::instance()->check_user()));
    }
}

$newsletter = new Newsletter();
$newsletter->init();
