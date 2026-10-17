<?php
/**
 * Site header (Ajustes de Gaia > Cabecera). Also printed as a sample on the
 * options pages ($args['is_sample']): no behavior attributes, and every logo
 * type, so the sample can switch logos without reloading.
 *
 * @param array $args ['is_sample' => bool]
 */

if (!defined('ABSPATH')) {
  exit;
}

$is_sample = !empty($args['is_sample']);
$header_settings = gaia_get_header_settings();
$has_mobile_logo = $header_settings['logo_mobile'] !== $header_settings['logo'];
?>
<header
  class="<?php echo esc_attr(gaia_get_header_classes($is_sample)); ?>"
  <?php if (!$is_sample) : ?>
    data-site-header
    <?php if ($header_settings['hides_on_scroll']) : ?>data-hide-on-scroll<?php endif; ?>
    <?php if ($header_settings['is_transparent']) : ?>data-transparent data-solid-scheme="<?php echo esc_attr(gaia_get_header_scheme_class()); ?>"<?php endif; ?>
  <?php endif; ?>
>
  <?php if (!$is_sample) : ?>
    <?php get_template_part('template-parts/announcement-bar'); ?>
  <?php endif; ?>

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

    <?php if ($is_sample) : ?>
      <a class="site-header__logo" href="<?php echo esc_url(home_url('/')); ?>" data-sample-variants="header_logo">
        <?php foreach (array_keys(gaia_get_logo_type_choices()) as $logo_type) : ?>
          <span class="site-header__logo-main" data-variant-value="<?php echo esc_attr($logo_type); ?>"<?php echo $logo_type === $header_settings['logo'] ? '' : ' hidden'; ?>>
            <?php echo gaia_get_logo_markup($logo_type); ?>
          </span>
        <?php endforeach; ?>
      </a>
    <?php elseif ($header_settings['logo'] !== 'none' || $header_settings['logo_mobile'] !== 'none') : ?>
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
        <?php get_template_part('template-parts/scheme-switcher'); ?>
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

  <?php if ($header_settings['shows_search'] && !$is_sample) : ?>
    <div class="site-header__search" id="site-header-search" hidden data-site-header-search>
      <form class="search-form container" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
        <label class="visually-hidden" for="site-header-search-input"><?php esc_html_e('Search', 'gaia'); ?></label>
        <input class="search-form__input" type="search" id="site-header-search-input" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Search…', 'gaia'); ?>">
        <button class="button" type="submit"><?php esc_html_e('Search', 'gaia'); ?></button>
      </form>
    </div>
  <?php endif; ?>
</header>
