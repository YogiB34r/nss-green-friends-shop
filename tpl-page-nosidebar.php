<?php
/*
 * Template name: Strana bez sidebar-a
 */
get_header();
if (have_posts()):
    while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <div class="row">
          <div class="gf-content-wrapper col-lg-9 offset-lg-3 col-md-12">
            <header class="gf-entry-header">
                <div class="gf-page-header__breadcrumb">
                  <?php woocommerce_breadcrumb(); ?>
                </div>
                <div class="gf-page-header__heading">
                  <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                </div>
              <?php get_the_ID(); ?>
            </header>
            <!-- .entry-header -->

            <div class="gf-entry-content">
              <?php
                the_content();
                wp_link_pages(array(
                    'before' => '<div class="page-links">' . __('Pages:', 'green-fiends'),
                    'after' => '</div>',
                ));
              ?>
            </div>
            <!-- .entry-content -->
          </div>
        </div>
      </article>
      <!-- #post-## -->
  <?php
    endwhile;
else:
    echo '<p>'._e('Sorry, no posts matched your criteria.', 'green-fiends').'</p>';
endif;
?>
<?php get_footer(); ?>
