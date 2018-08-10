

<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
  <link rel="profile" href="http://gmpg.org/xfn/11">
  <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <div id="page" class="hfeed site">
    <header id="masthead" class="site-header" role="banner">
      <div class="container-fluid container--navigation">
        <div class="row gf-top-bar">
          <div class="gf-top-bar__container">
            <?php dynamic_sidebar('gf-header-row-1') ?>
          </div>
        </div>
        <div class="row gf-primary-navigation">
          <div class="col-3 gf-logo">
            <div class="gf-logo-wrapper">
              <?php dynamic_sidebar('gf-header-row-2-col-1') ?>
            </div>
          </div>
          <div class="col-5 gf-search">
            <div class="gf-search-wrapper">
              <?php get_search_form()?>
            </div>
          </div>
          <div class="col-4 gf-navigation">
            <div class="gf-navigation-wrapper">
              <?php dynamic_sidebar('gf-header-row-2-col-3')?>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div id="content" class="site-content" tabindex="-1">
      <div class="col-full">
        <div class="gf-main-content-container">
