<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link visually-hidden-focusable" href="#main"><?php esc_html_e('Skip to content', 'gaia'); ?></a>

<?php
$header_settings = gaia_get_header_settings();
$has_mobile_logo = $header_settings['logo_mobile'] !== $header_settings['logo'];
?>

<?php if (!$header_settings['is_hidden']) : ?>
<header
  class="<?php echo esc_attr(gaia_get_header_classes()); ?>"
  data-site-header
  <?php if ($header_settings['hides_on_scroll']) : ?>data-hide-on-scroll<?php endif; ?>
  <?php if ($header_settings['is_transparent']) : ?>data-transparent<?php endif; ?>
>
  <div class="site-header__inner container">
    <button
      class="site-header__menu-toggle"
      type="button"
      aria-expanded="false"
      aria-controls="site-header-panel"
      data-site-header-toggle
    >
      <span class="site-header__menu-toggle-bars" aria-hidden="true">
        <span></span>
        <span></span>
        <span></span>
      </span>
      <span class="visually-hidden"><?php esc_html_e('Menu', 'gaia'); ?></span>
    </button>

    <?php if ($header_settings['logo'] !== 'none' || $header_settings['logo_mobile'] !== 'none') : ?>
      <a class="site-header__logo<?php echo $has_mobile_logo ? ' has-mobile-logo' : ''; ?>" href="<?php echo esc_url(home_url('/')); ?>">
        <span class="site-header__logo-main"><?php echo gaia_get_logo_markup($header_settings['logo']); ?></span>
        <?php if ($has_mobile_logo) : ?>
          <span class="site-header__logo-mobile"><?php echo gaia_get_logo_markup($header_settings['logo_mobile']); ?></span>
        <?php endif; ?>
      </a>
    <?php endif; ?>

    <div class="site-header__tools">
      <?php if ($header_settings['shows_search']) : ?>
        <button class="site-header__tool" type="button" aria-expanded="false" aria-controls="site-header-search" data-site-header-search-toggle>
          <?php echo gaia_get_icon_svg('ui/search'); ?>
          <span class="visually-hidden"><?php esc_html_e('Search', 'gaia'); ?></span>
        </button>
      <?php endif; ?>

      <?php if ($header_settings['shows_scheme_toggle']) : ?>
        <button class="site-header__tool site-header__scheme-toggle" type="button" aria-pressed="false" data-color-scheme-toggle>
          <span class="site-header__scheme-icon site-header__scheme-icon--dark"><?php echo gaia_get_icon_svg('ui/moon'); ?></span>
          <span class="site-header__scheme-icon site-header__scheme-icon--light"><?php echo gaia_get_icon_svg('ui/sun'); ?></span>
          <span class="visually-hidden"><?php esc_html_e('Dark mode', 'gaia'); ?></span>
        </button>
      <?php endif; ?>

      <?php if ($header_settings['shows_user_menu']) : ?>
        <?php get_template_part('template-parts/user-menu', null, ['class' => 'site-header__user']); ?>
      <?php endif; ?>
    </div>

    <?php // Drawer mode: closes the menu when clicked. ?>
    <div class="site-header__backdrop" data-site-header-backdrop></div>

    <?php // Drawer on narrow screens (and on the minimal layout); display: contents on wide screens, its children go into the header grid. ?>
    <div class="site-header__panel" id="site-header-panel" data-site-header-panel>
      <?php if ($header_settings['shows_languages']) : ?>
        <?php get_template_part('template-parts/language-switcher', null, ['class' => 'site-header__languages']); ?>
      <?php endif; ?>

      <?php if (has_nav_menu('primary')) : ?>
        <nav class="site-header__navigation" aria-label="<?php esc_attr_e('Main menu', 'gaia'); ?>">
          <?php
          wp_nav_menu([
            'theme_location' => 'primary',
            'container' => false,
            'menu_class' => 'site-header__menu',
            'depth' => 2,
          ]);
          ?>
        </nav>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($header_settings['shows_search']) : ?>
    <div class="site-header__search" id="site-header-search" hidden data-site-header-search>
      <form class="search-form container" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
        <label class="visually-hidden" for="site-header-search-input"><?php esc_html_e('Search', 'gaia'); ?></label>
        <input class="search-form__input" type="search" id="site-header-search-input" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Search…', 'gaia'); ?>">
        <button class="button" type="submit"><?php esc_html_e('Search', 'gaia'); ?></button>
      </form>
    </div>
  <?php endif; ?>
</header>
<?php endif; ?>

<main class="site-main" id="main">
