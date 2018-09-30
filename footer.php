</div>
<!-- gf main content container -->
</div>
<!-- .col-full -->
</div>
<!-- #content -->
<footer id="colophon" class="site-footer" role="contentinfo">
  <div class="container-fluid">
    <div class="gf-main-content-container">
      <div class="row list-unstyled gf-footer justify-content-end">
        <div class="col-xs-12 col-md-3 gf-footer-section gf-footer-newsletter">
          <?php dynamic_sidebar('gf-footer-row-1-column-1'); ?>
        </div>
        <div class="col-xs-12 col-md-3 gf-footer-section align-self-end">
          <?php dynamic_sidebar('gf-footer-row-1-column-2'); ?>
        </div>
        <div class="col-xs-12 col-md-3 gf-footer-section align-self-end">
          <?php dynamic_sidebar('gf-footer-row-1-column-3'); ?>
        </div>
        <div class="col-xs-12 col-md-3 gf-footer-section align-self-end">
          <?php dynamic_sidebar('gf-footer-row-1-column-4'); ?>
        </div>
      </div>
      <div class="row list-unstyled">
        <div class="col-9">
          <?php dynamic_sidebar('gf-footer-row-2-column-1'); ?>
        </div>
        <div class="col-3">
          <?php dynamic_sidebar('gf-footer-row-2-column-2'); ?>
        </div>
      </div>
      <div class="row list-unstyled gf-footer gf-footer-images-wrapper">
        <div class="gf-footer-images">
            <?php $theme_dir = get_template_directory_uri();?>
            <ul class="row">
                <li class="col-md col-sm">
                    <img src="<?=$theme_dir?>/assets/images/footer_card_1.png" alt="Verified by Visa">
                </li>
                <li class="col-md col-sm">
                    <img src="<?=$theme_dir?>/assets/images/footer_card_2.png" alt="MasterCard SecureCode">
                </li>
                <li class="col-md col-sm">
                    <img src="<?=$theme_dir?>/assets/images/footer_card_3.png" alt="Visa">
                </li>
                <li class="col-md col-sm">
                    <img src="<?=$theme_dir?>/assets/images/footer_card_4.png" alt="mastercard">
                </li>
                <li class="col-md col-sm">
                    <img src="<?=$theme_dir?>/assets/images/footer_card_5.png" alt="maestrocard">
                </li>
                <li class="col-md col-sm">
                    <img src="<?=$theme_dir?>/assets/images/footer_card_6.png" alt="American Express">
                </li>
                <li class="col-md col-sm">
                    <img src="<?=$theme_dir?>/assets/images/footer_card_7.png" alt="Banca Intesa">
                </li>
            </ul>
        </div>
      </div>
    </div>
  </div>
</footer>
<!-- #colophon -->
</div>
<!-- #page -->
<?php wp_footer(); ?>
</body>
</html>
