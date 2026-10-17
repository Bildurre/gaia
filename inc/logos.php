<?php
/**
 * Logos (Ajustes de Gaia > Logos): favicon and a library of logos (main,
 * compact, icon), each one for light and dark backgrounds. Header and footer
 * choose which type to show; the light/dark version follows the scheme of
 * the area (CSS --show-on-light / --show-on-dark).
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Logo types available in the header/footer "Logo" selects.
 */
function gaia_get_logo_type_choices() {
  return [
    'main' => __('Logo principal', 'gaia'),
    'compact' => __('Logo compacto', 'gaia'),
    'icon' => __('Icono', 'gaia'),
    'text' => __('Nombre del sitio (texto)', 'gaia'),
    'none' => __('Ninguno', 'gaia'),
  ];
}

/**
 * Gaia logos used when the site has not uploaded its own.
 */
function gaia_get_default_logo_files() {
  return [
    'main' => ['light' => 'gaia-logo.png', 'dark' => 'gaia-logo-light.png'],
    'compact' => ['light' => 'gaia-logo.png', 'dark' => 'gaia-logo-light.png'],
    'icon' => ['light' => 'gaia-icon.png', 'dark' => 'gaia-icon.png'],
  ];
}

/**
 * Image URLs of a logo type as ['light' => url, 'dark' => url]. Missing
 * versions fall back to the other background, then to the main logo, then
 * to the Gaia logo.
 */
function gaia_get_logo_urls($logo_type) {
  $logo_types_to_try = array_unique([$logo_type, 'main']);

  foreach ($logo_types_to_try as $logo_type_to_try) {
    $light_logo_id = (int) gaia_get_option_field('logos_' . $logo_type_to_try . '_light');
    $dark_logo_id = (int) gaia_get_option_field('logos_' . $logo_type_to_try . '_dark');
    $light_logo_url = $light_logo_id ? wp_get_attachment_image_url($light_logo_id, 'medium_large') : '';
    $dark_logo_url = $dark_logo_id ? wp_get_attachment_image_url($dark_logo_id, 'medium_large') : '';

    if ($light_logo_url || $dark_logo_url) {
      return [
        'light' => $light_logo_url ?: $dark_logo_url,
        'dark' => $dark_logo_url ?: $light_logo_url,
      ];
    }
  }

  $default_logo_files = gaia_get_default_logo_files()[$logo_type] ?? gaia_get_default_logo_files()['main'];
  $images_directory_uri = get_template_directory_uri() . '/assets/images/';

  return [
    'light' => $images_directory_uri . $default_logo_files['light'],
    'dark' => $images_directory_uri . $default_logo_files['dark'],
  ];
}

/**
 * Logo markup for a type ('main', 'compact', 'icon', 'text' or 'none').
 * Prints both background versions when they differ; CSS shows the right one.
 */
function gaia_get_logo_markup($logo_type) {
  $site_name = get_bloginfo('name');

  if ($logo_type === 'none') {
    return '';
  }

  if ($logo_type === 'text' || !array_key_exists($logo_type, gaia_get_default_logo_files())) {
    return sprintf('<span class="site-logo site-logo--text">%s</span>', esc_html($site_name));
  }

  $logo_urls = gaia_get_logo_urls($logo_type);

  if ($logo_urls['light'] === $logo_urls['dark']) {
    return sprintf(
      '<span class="site-logo site-logo--%1$s"><img class="site-logo__image" src="%2$s" alt="%3$s"></span>',
      esc_attr($logo_type),
      esc_url($logo_urls['light']),
      esc_attr($site_name)
    );
  }

  return sprintf(
    '<span class="site-logo site-logo--%1$s"><img class="site-logo__image site-logo__image--on-light" src="%2$s" alt="%4$s"><img class="site-logo__image site-logo__image--on-dark" src="%3$s" alt="%4$s"></span>',
    esc_attr($logo_type),
    esc_url($logo_urls['light']),
    esc_url($logo_urls['dark']),
    esc_attr($site_name)
  );
}

/**
 * Favicon from the Logos options (or the Gaia icon). The WordPress site icon,
 * when set, takes precedence.
 */
function gaia_print_favicon() {
  if (has_site_icon()) {
    return;
  }

  $favicon_id = (int) gaia_get_option_field('logos_favicon');
  $favicon_url = $favicon_id ? wp_get_attachment_image_url($favicon_id, 'thumbnail') : get_template_directory_uri() . '/assets/images/gaia-icon.png';

  if ($favicon_url) {
    printf('<link rel="icon" href="%s">' . "\n", esc_url($favicon_url));
  }
}
add_action('wp_head', 'gaia_print_favicon');
add_action('admin_head', 'gaia_print_favicon');
add_action('login_head', 'gaia_print_favicon');

/**
 * Header and footer logo selects share the same choices.
 */
function gaia_load_logo_type_field_choices($field) {
  $field['choices'] = gaia_get_logo_type_choices();

  return $field;
}
add_filter('acf/load_field/key=field_gaia_header_logo', 'gaia_load_logo_type_field_choices');
add_filter('acf/load_field/key=field_gaia_header_logo_mobile', 'gaia_load_logo_type_field_choices');
add_filter('acf/load_field/key=field_gaia_footer_logo', 'gaia_load_logo_type_field_choices');
