<?php
/*
 * Template name: Strana bez sidebar-a
 */
get_header();
if (have_posts()):
    while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="gf-content-wrapper">
                <header>
                    <div class="gf-page-header__breadcrumb">
                        <?php woocommerce_breadcrumb(); ?>
                    </div>
                    <?php the_title('<h1>', '</h1>'); ?>
                <!--nisam siguran da ovo radi nesto, ali neka ga za sad-->
                    <?php get_the_ID(); ?>
                </header>
                <!-- .entry-header -->
                <div class="gf-entry-content">
                    <?php
                    the_content();
                    // Ne znam da li ovo radi ista
                    wp_link_pages(array(
                        'before' => '<div class="page-links">' . __('Pages:', 'green-fiends'),
                        'after' => '</div>',
                    ));
                    ?>
                </div>
                <!-- .entry-content -->
            </div>
        </article>
        <!-- #post-## -->
    <?php
    endwhile;
else:
    echo '<p>' . _e('Sorry, no posts matched your criteria.', 'green-fiends') . '</p>';
endif;
?>
<?php get_footer(); ?>
