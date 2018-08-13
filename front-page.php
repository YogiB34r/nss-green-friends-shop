<?php get_header(); ?>

<div class="row">
  <div class="col-3 gf-sidebar gf-left-sidebar">
    <div class="gf-left-sidebar-wrapper">
      <div class="gf-wrapper-before"></div>
      <?php dynamic_sidebar('gf-left-sidebar'); ?>
    </div>
  </div>
  <div class="gf-content-wrapper col-md-9 col-sm-12">
    <div class="gf-row row list-unstyled">
      <?php dynamic_sidebar('gf-homepage-row-1'); ?>
    </div>
    <div class="gf-row row list-unstyled">
      <?php dynamic_sidebar('gf-homepage-row-2'); ?>
    </div>
    <div class="gf-row row list-unstyled">
      <?php dynamic_sidebar('gf-homepage-row-3'); ?>
    </div>
  </div>
</div>

<?php get_footer(); ?>
