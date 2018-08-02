<?php get_header(); ?>

<div class="row">
  <div class="col-3 gf-sidebar gf-left-sidebar">
    <div class="gf-left-sidebar-wrapper">
      <?php dynamic_sidebar('gf-left-sidebar'); ?>
    </div>
  </div>
  <div class="col-9">
    <div class="row list-unstyled">
      <?php dynamic_sidebar('gf-homepage-row-1'); ?>
    </div>
    <div class="row list-unstyled">
      <?php dynamic_sidebar('gf-homepage-row-2'); ?>
    </div>
    <div class="row list-unstyled">
      <?php dynamic_sidebar('gf-homepage-row-3'); ?>
    </div>
  </div>
</div>

<?php get_footer(); ?>
