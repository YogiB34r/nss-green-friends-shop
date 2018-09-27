<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

get_header(); ?>

    <div class="wrap">
        <div id="primary" class="content-area">
            <main id="main" class="site-main" role="main">

                <section class="error-404 not-found">
                    <header class="page-header">
                        <h1 class="page-title"><?php __('Oops! Ova stranica nije pronađena.'); ?></h1>
                    </header><!-- .page-header -->
                    <div class="page-content">
                        <p><?php _e('Izgleda da ništa nije pronađeno na ovoj lokaciji. Nastavite sa kupovinom ili probajte pretragu', 'twentyseventeen'); ?></p>

                        <a class="" href="/">Nastavite sa kupovinom</a>


                    </div><!-- .page-content -->
                </section><!-- .error-404 -->
            </main><!-- #main -->
        </div><!-- #primary -->
    </div><!-- .wrap -->

<?php
get_footer();
