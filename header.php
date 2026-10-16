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
$header_logo_id = (int) gaia_get_option_field('header_logo');
?>

<header class="site-header" data-site-header>
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

    <a class="site-header__logo" href="<?php echo esc_url(home_url('/')); ?>">
      <?php if ($header_logo_id) : ?>
        <?php echo wp_get_attachment_image($header_logo_id, 'medium', false, ['alt' => get_bloginfo('name'), 'loading' => 'eager']); ?>
      <?php else : ?>
        <?php bloginfo('name'); ?>
      <?php endif; ?>
    </a>

    <?php get_template_part('template-parts/user-menu', null, ['class' => 'site-header__user']); ?>

    <?php // Narrow screens: closes the menu when clicked. ?>
    <div class="site-header__backdrop" data-site-header-backdrop></div>

    <?php // Narrow screens: drawer from the left. Wide screens: display: contents, its children are placed in the header grid. ?>
    <div class="site-header__panel" id="site-header-panel" data-site-header-panel>
      <?php get_template_part('template-parts/language-switcher', null, ['class' => 'site-header__languages']); ?>

      <?php if (has_nav_menu('primary')) : ?>
        <nav class="site-header__navigation" aria-label="<?php esc_attr_e('Main menu', 'gaia'); ?>">
          <?php
          wp_nav_menu([
            'theme_location' => 'primary',
            'container' => false,
            'menu_class' => 'site-header__menu',
            'depth' => 1,
          ]);
          ?>
        </nav>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="site-main" id="main">
