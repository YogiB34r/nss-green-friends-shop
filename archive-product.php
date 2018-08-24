<?php add_filter( 'get_the_archive_title', 'modify_archive_title' ); function modify_archive_title( $title ) { if( is_category() ) { $title = single_cat_title( '', false ); } return $title; }
?>
<?php get_header(); ?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php get_template_part('templates/template-parts/category-page/gf-category-with-filters');?>
    </main><!-- #main -->
</div><!-- #primary -->
<?php get_footer() ?>



