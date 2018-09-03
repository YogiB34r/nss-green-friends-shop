<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
  <link rel="profile" href="http://gmpg.org/xfn/11">
  <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
  <title>NonStopShop.rs - online kupovina -  uvek dobre cene - onlajn prodavnica - prodaja preko interneta - Srbija - Beograd</title>
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <div id="page" class="hfeed site">
    <header id="masthead" class="site-header" role="banner">
      <div class="container-fluid container--navigation">
        <div class="gf-top-bar">
          <div class="row gf-top-bar__container">
            <div class="col-3"></div>
            <div class="col-9 gf-top-bar__menu">
              <?php dynamic_sidebar('gf-header-row-1') ;?>
            </div>
          </div>
        </div>
        <div class="row gf-primary-navigation">
          <div class="col-3 gf-logo">
            <div class="gf-logo-wrapper">
              <?php dynamic_sidebar('gf-header-row-2-col-1') ?>
            </div>
          </div>
          <div class="col-md-6 col-lg-7 gf-search">
            <div class="gf-search-wrapper">
              <?php get_search_form()?>
            </div>
          </div>
          <div class="col-9 col-md-3 col-lg-2 gf-navigation">
            <div class="gf-navigation-wrapper">
              <?php dynamic_sidebar('gf-header-row-2-col-3')?>
            </div>
          </div>
        </div>
      </div>
    </header>
    <form role="search" method="get" class="gf-search-form gf-search-form--mobile" action="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) )) ?>">
      <!-- <div class="search-toggle-wrapper"><div class="gf-search-toggle"><i class="fa fa-search"></i></div></div> -->
      <span class="screen-reader-text"><?php _x( 'Search for:', 'label' )?></span>
      <div class="search-input-wrapper">
        <input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search &hellip;', '' ) ?>" value="<?php echo get_search_query() ?>" name="s" />
        <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
      </div>
    </form>
    <div class="gf-radio-search-wrapper gf-radio-search-wrapper--mobile">
      <?php if (get_queried_object() && is_product_category()): ?>
          <label for="search-checkbox">
		    <input class="search-radio-box" type="radio" name="search-radiobutton" checked="checked" value="category" hidden>
			<span><?= get_queried_object()->name ?></span>
		  </label>
		  <span class="search-radio" type="radio" name="search-radiobutton" value="shop" hidden></span>
          <label for="search-checkbox">
            <input class="search-radio-box" type="radio" name="search-radiobutton" value="shop" hidden>
			<span>Pretraga celog sajta</span>
		  </label>
      <?php endif ;?>
    </div>
    <div id="content" class="site-content" tabindex="-1">
      <div class="col-full">
        <div class="gf-main-content-container">

