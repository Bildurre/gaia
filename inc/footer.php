<?php
/**
 * Footer settings (Ajustes de Gaia > Pie de página).
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Footer settings with defaults, resolved for the current page.
 */
function gaia_get_footer_settings() {
  static $footer_settings = null;

  if ($footer_settings !== null) {
    return $footer_settings;
  }

  $layout = gaia_get_option_value('footer_layout', 'brand-contact');
  $background = gaia_get_option_value('footer_background', 'dark');

  $footer_settings = [
    'layout' => in_array($layout, ['brand-contact', 'columns', 'minimal'], true) ? $layout : 'brand-contact',
    'background' => in_array($background, ['dark', 'light', 'primary', 'custom'], true) ? $background : 'dark',
    'logo' => gaia_get_option_value('footer_logo', 'main'),
    'shows_contact' => (bool) gaia_get_option_value('footer_show_contact', true),
    'shows_social' => (bool) gaia_get_option_value('footer_show_social', true),
    'is_hidden' => false,
  ];

  $footer_settings = apply_filters('gaia_footer_settings', $footer_settings);

  return $footer_settings;
}

/**
 * @param string $layout Layout to use instead of the setting (samples).
 */
function gaia_get_footer_classes($layout = '') {
  $footer_settings = gaia_get_footer_settings();

  return implode(' ', array_merge(
    ['site-footer', 'site-footer--' . ($layout ?: $footer_settings['layout'])],
    gaia_get_background_classes('footer_background', $footer_settings['background']),
    gaia_get_option_classes(['footer_text_color', 'footer_accent_color'])
  ));
}

/**
 * Prints a footer menu location (footer or legal) if it has a menu.
 */
function gaia_footer_menu($theme_location, $aria_label, $menu_class) {
  if (!has_nav_menu($theme_location)) {
    return;
  }

  printf('<nav class="%s" aria-label="%s">', esc_attr($menu_class), esc_attr($aria_label));
  wp_nav_menu([
    'theme_location' => $theme_location,
    'container' => false,
    'menu_class' => $menu_class . '-list',
    'depth' => 1,
  ]);
  echo '</nav>';
}
