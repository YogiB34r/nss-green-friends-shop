<?php


namespace GF;


class Theme
{
    /**
     * @var Setup
     */
    private $setup;

    /**
     * Theme constructor.
     */
    public function __construct()
    {
        $enqueue = new Enqueue();
        $themeSupport = new ThemeSupport();
        $this->setup = new Setup($enqueue, $themeSupport);

        $this->init();
    }


    public function init()
    {
        $this->setup->init();
    }

    public static function afterThemeSetupAction ($function)
    {
        add_action( 'after_theme_setup', static function ($hook) use ( $function ) {
            $function($hook);
        } );
    }
}