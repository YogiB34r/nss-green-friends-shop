<?php


namespace GF;


/**
 * Class Setup
 * @package GF
 */
class Setup
{
    /**
     * @var Enqueue
     */
    private $enqueue;
    /**
     * @var ThemeSupport
     */
    private $themeSupport;

    /**
     * Setup constructor.
     * @param Enqueue $enqueue
     * @param ThemeSupport $themeSupport
     */
    public function __construct(Enqueue $enqueue, ThemeSupport $themeSupport)
    {
        $this->enqueue = $enqueue;
        $this->themeSupport = $themeSupport;
    }

    public function init()
    {
        $this->enqueueStyles();
        $this->themeSupport->init();

        Theme::afterThemeSetupAction(function () {
            $this->loadTextDomain();
        });
        Theme::afterThemeSetupAction(function () {
            $this->registerNavMenu();
        });
    }

    private function enqueueStyles()
    {
        $this->enqueue->init();
    }

    /**
     * Load Localisation files.
     *
     * Note: the first-loaded translation file overrides any following ones if the same translation is present.
     */
    private function loadTextDomain()
    {
        load_theme_textdomain('green-friends', get_template_directory() . '/languages');
    }

    /**Register navigation menus in wp*/
    private function registerNavMenu()
    {
        register_nav_menus(array(
            'primary' => __('Header Menus'),
            'secondary' => __('Footer Menus'),
            'handheld' => __('Other Menus'),
        ));
    }

}