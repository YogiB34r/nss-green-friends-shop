<?php get_header();?>
<div class="row">
  <div class="col-3 gf-sidebar gf-left-sidebar">
    <div class="gf-left-sidebar-wrapper">
      <div class="gf-wrapper-before">
        <div class="gf-category-sidebar-toggle">Kategorije</div>
        <span class="fas fa-angle-up"></span>
      </div>
      <?php dynamic_sidebar('gf-left-sidebar'); ?>
    </div>
  </div>
  <div class="gf-content-wrapper col-md-9 col-sm-12">
    <div class="gf-row row list-unstyled">
        <a href="#" data-user-id="<?=get_current_user_id()?>" class="gf-ajax">Test Ajaxx</a>
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
