<?php get_header();

?>

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
      <div class="dropdown-menua mega-menu row z-depth-1 primary-color-dark" aria-labelledby="navbarDropdownMenuLink2">
        <div class="row mega-menu__row">
          <div class="col-md-3 col-xl-3 sub-menu mt-5 mb-5">
            <ol class="list-unstyled ml-4 mr-md-0 mr-4">
              <li class="sub-title text-uppercase mt-sm"><a class="menu-item" href="">Technology</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Design</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Lifestyle</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Laptops</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Phones</a></li>
            </ol>
          </div>
          <div class="col-md-3 col-xl-3 sub-menu mt-5 mb-5">
            <ol class="list-unstyled ml-4 mr-md-0 mr-4">
              <li class="sub-title text-uppercase mt-sm"><a class="menu-item" href="">Technology</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Design</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Lifestyle</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Laptops</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Phones</a></li>
            </ol>
          </div>
          <div class="col-md-3 col-xl-3 sub-menu mt-5 mb-5">
            <ol class="list-unstyled ml-4 mr-md-0 mr-4">
              <li class="sub-title text-uppercase mt-sm"><a class="menu-item" href="">Technology</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Design</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Lifestyle</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Laptops</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Phones</a></li>
            </ol>
          </div>
          <div class="col-md-3 col-xl-3 sub-menu mt-5 mb-5">
            <ol class="list-unstyled ml-4 mr-md-0 mr-4">
              <li class="sub-title text-uppercase mt-sm"><a class="menu-item" href="">Technology</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Design</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Lifestyle</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Laptops</a></li>
              <li class="sub-title text-uppercase"><a class="sub-menu-item" href="">Phones</a></li>
            </ol>
          </div>
        </div>
      </div>
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
