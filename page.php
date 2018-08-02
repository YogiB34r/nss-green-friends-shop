<?php
get_header();
if (have_posts()):
    while (have_posts()) : the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="row">
                <div class="col-3 list-unstyled gf-sidebar">
                    <?php dynamic_sidebar('gf-left-sidebar') ?>
                </div>
                <div class="col-9">
                    <header class="entry-header">
                        <?php woocommerce_breadcrumb(); ?>
                        <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                        <?php get_the_ID(); ?>
                    </header><!-- .entry-header -->

                    <div class="entry-content">
                        <?php

                        the_content();

                        wp_link_pages(array(
                            'before' => '<div class="page-links">' . __('Pages:', 'non-stop-shop'),
                            'after' => '</div>',
                        ));
                        ?>
                    </div><!-- .entry-content -->
                </div>
            </div>
        </article><!-- #post-## -->

    <?php
    endwhile;
else:
    echo '<p>Sorry, no posts matched your criteria.</p>';
endif;
?>
<?php get_footer(); ?>

