<?php

namespace GF;

class ThemeSupport
{

    public function init()
    {
        Theme::afterThemeSetupAction(function () {
            $this->customLogo(110, 470, true);
        });
        Theme::afterThemeSetupAction(function () {
            $this->customBackground('ffffff', '');
        });
        Theme::afterThemeSetupAction(function () {
            $this->customHeader('', false, 1950, 500, true, true);
        });
        Theme::afterThemeSetupAction(function () {
            $this->automaticFeedLinks();
        });
        Theme::afterThemeSetupAction(function () {
            $this->postThumbnails();
        });
        Theme::afterThemeSetupAction(function () {
            $this->html5();
        });
        Theme::afterThemeSetupAction(function () {
            $this->selectiveRefreshingWidgets();
        });
        $this->wcSupport();
    }

    /**
     * Setup wc support
     */
    private function wcSupport()
    {
        add_theme_support('title-tag');
        add_theme_support('woocommerce');
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');
        add_theme_support('yoast-seo-breadcrumbs');
    }

    /**
     * Add default posts and comments RSS feed links to head.
     */
    private function automaticFeedLinks()
    {
        add_theme_support('automatic-feed-links');
    }

    /**
     * Enable support for Post Thumbnails on posts and pages.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#Post_Thumbnails
     */
    private function postThumbnails()
    {
        add_theme_support('post-thumbnails');
    }

    /**
     * Enable support for site logo
     * @param int $height
     * @param int $width
     * @param bool $flexWidth
     */
    private function customLogo($height, $width, $flexWidth)
    {
        add_theme_support('custom-logo', array(
            'height' => $height,
            'width' => $width,
            'flex-width' => $flexWidth,
        ));
    }

    /**
     * Switch default core markup for search form, comment form, comments, galleries, captions and widgets
     * to output valid HTML5.
     */

    private function html5()
    {
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'widgets',
        ));
    }

    /** Setup the WordPress core custom background feature.
     * @param string $defaultColor
     * @param string $defaultImage
     */

    private function customBackground($defaultColor, $defaultImage)
    {

        add_theme_support('custom-background', array(
            'default-color' => $defaultColor,
            'default-image' => $defaultImage,
        ));
    }

    /** Declare support for selective refreshing of widgets. */

    private function selectiveRefreshingWidgets()
    {
        add_theme_support('customize-selective-refresh-widgets');
    }

    /**
     * @param string $defaultImage
     * @param bool $headerText
     * @param int $width
     * @param int $height
     * @param bool $flexWidth
     * @param bool $flexHeight
     */
    private function customHeader($defaultImage, $headerText, $width, $height, $flexWidth, $flexHeight)
    {
        add_theme_support('custom-header', array(
            'default-image' => $defaultImage,
            'header-text' => $headerText,
            'width' => $width,
            'height' => $height,
            'flex-width' => $flexWidth,
            'flex-height' => $flexHeight,
        ));
    }

}