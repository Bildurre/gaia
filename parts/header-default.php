<?php
/**
 * Header Default Template
 */

$header_classes = gaia_get_header_classes();
$header_styles = gaia_get_header_styles_attr();
$header_data = gaia_get_header_data_attrs();
$show_social = gaia_header_show_social();
$show_search = gaia_header_show_search();
?>
<header class="<?php echo esc_attr($header_classes); ?>" style="<?php echo esc_attr($header_styles); ?>" <?php echo $header_data; ?>>
  <div class="header-default__container">
    
    <!-- Top Row -->
    <div class="header-default__top">
      <!-- Hamburger -->
      <button class="header-default__hamburger" aria-label="<?php esc_attr_e('Toggle menu', 'gaia'); ?>" aria-expanded="false">
        <span class="header-default__hamburger-bar"></span>
        <span class="header-default__hamburger-bar"></span>
        <span class="header-default__hamburger-bar"></span>
      </button>

      <!-- Logo -->
      <div class="header-default__logo">
        <?php if (has_custom_logo()) : ?>
          <?php the_custom_logo(); ?>
        <?php else : ?>
          <a href="<?php echo esc_url(home_url('/')); ?>" class="header-default__site-name">
            <?php bloginfo('name'); ?>
          </a>
        <?php endif; ?>
      </div>

      <!-- Header Actions -->
      <div class="header-default__actions">
        <?php if ($show_search) : ?>
          <div class="header-default__search">
            <?php get_search_form(); ?>
          </div>
        <?php endif; ?>
        
        <?php if ($show_social) : ?>
          <?php gaia_social_links(); ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Bottom Row (Navigation) -->
    <nav class="header-default__nav">
      <?php
      wp_nav_menu(array(
        'theme_location' => 'primary',
        'container' => false,
        'menu_class' => 'header-default__menu',
        'fallback_cb' => 'gaia_fallback_menu',
      ));
      ?>
    </nav>

  </div>

  <!-- Mobile Panel -->
  <div class="header-default__mobile-panel">
    <div class="header-default__mobile-content">
      <?php if ($show_search) : ?>
        <div class="header-default__mobile-search">
          <?php get_search_form(); ?>
        </div>
      <?php endif; ?>

      <?php if ($show_social) : ?>
        <div class="header-default__mobile-actions">
          <?php gaia_social_links(); ?>
        </div>
      <?php endif; ?>

      <nav class="header-default__mobile-nav">
        <?php
        wp_nav_menu(array(
          'theme_location' => 'primary',
          'container' => false,
          'menu_class' => 'header-default__mobile-menu',
          'fallback_cb' => 'gaia_fallback_menu_mobile',
        ));
        ?>
      </nav>
    </div>
  </div>

  <!-- Backdrop -->
  <div class="header-default__backdrop"></div>
</header>