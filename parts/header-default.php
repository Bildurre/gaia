<?php
/**
 * Header Default Template
 */
?>
<header class="header-default">
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
        <?php gaia_social_links(); ?>
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
      <div class="header-default__mobile-actions">
        <?php gaia_social_links(); ?>
      </div>

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