<?php
/**
 * Options that change the design, mapped to CSS custom properties and
 * classes. One map for everything: the site (inline :root CSS and element
 * classes) and the samples on the Ajustes de Gaia pages (options-admin.js
 * applies the same map to the unsaved values).
 *
 * Entry types:
 * - color:      'property' set when the option has a color. With
 *               'contrast_of' (list of option names) and no color, the
 *               property gets white or dark text for the first color found
 *               (or 'fallback'); without a color nor a source, nothing.
 * - choice:     'values' => [option value => [property => css value]].
 * - number:     'property' with 'unit'.
 * - background: area background mode; adds the 'prefix' class and a scheme
 *               class (.scheme-dark / .scheme-light) from the brightness of
 *               the background ('color_option' for the custom one).
 * Any entry can have 'class' => ['selector' => …, 'prefix' => …] (adds
 * prefix + value; 'choices' lists the values when other classes share the
 * prefix) or ['selector' => …, 'when_set' => class] (colors).
 */

if (!defined('ABSPATH')) {
  exit;
}

const GAIA_DEFAULT_PRIMARY_COLOR = '#1d6ff2';
const GAIA_DEFAULT_HIGHLIGHT_COLOR = '#ffcc00';
const GAIA_CONTRAST_DARK_TEXT = '#15181c';
const GAIA_CONTRAST_LIGHT_TEXT = '#ffffff';

/* Color helpers ----------------------------------------------------------- */

function gaia_get_relative_luminance($hex_color) {
  $hex_color = ltrim((string) sanitize_hex_color($hex_color), '#');

  if (strlen($hex_color) === 3) {
    $hex_color = preg_replace('/(.)/', '$1$1', $hex_color);
  }

  if (strlen($hex_color) !== 6) {
    return 1;
  }

  $channels = array_map(function ($channel_hex) {
    $channel = hexdec($channel_hex) / 255;

    return $channel <= 0.04045 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4;
  }, str_split($hex_color, 2));

  return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

function gaia_get_contrast_ratio($first_luminance, $second_luminance) {
  return (max($first_luminance, $second_luminance) + 0.05) / (min($first_luminance, $second_luminance) + 0.05);
}

/**
 * True when light text reads better than dark text on the color.
 */
function gaia_is_dark_color($hex_color) {
  $color_luminance = gaia_get_relative_luminance($hex_color);

  return gaia_get_contrast_ratio($color_luminance, gaia_get_relative_luminance(GAIA_CONTRAST_LIGHT_TEXT))
    >= gaia_get_contrast_ratio($color_luminance, gaia_get_relative_luminance(GAIA_CONTRAST_DARK_TEXT));
}

function gaia_get_contrast_text_color($hex_color) {
  return gaia_is_dark_color($hex_color) ? GAIA_CONTRAST_LIGHT_TEXT : GAIA_CONTRAST_DARK_TEXT;
}

/* Map --------------------------------------------------------------------- */

function gaia_get_option_style_map() {
  $font_values = [];
  foreach (gaia_get_available_fonts() as $font_slug => $font_settings) {
    $font_values[$font_slug] = gaia_get_font_stack($font_settings);
  }

  $color = fn($property, $extra_settings = []) => ['type' => 'color', 'property' => $property] + $extra_settings;
  $boolean = fn($default_value, $true_properties, $false_properties) => [
    'type' => 'choice',
    'default' => $default_value ? '1' : '0',
    'values' => ['1' => $true_properties, '0' => $false_properties],
  ];

  return [
    // Colores > Marca
    'colors_primary' => $color('--color-primary'),
    'colors_primary_light' => $color('--color-primary-light'),
    'colors_primary_dark' => $color('--color-primary-dark'),
    'colors_on_primary' => $color('--color-on-primary', ['contrast_of' => ['colors_primary'], 'fallback' => GAIA_DEFAULT_PRIMARY_COLOR]),
    'colors_secondary' => $color('--color-secondary'),
    'colors_on_secondary' => $color('--color-on-secondary', ['contrast_of' => ['colors_secondary']]),
    'colors_highlight' => $color('--color-highlight'),
    'colors_on_highlight' => $color('--color-on-highlight', ['contrast_of' => ['colors_highlight'], 'fallback' => GAIA_DEFAULT_HIGHLIGHT_COLOR]),

    // Colores > Estados
    'colors_success' => $color('--color-success-custom'),
    'colors_error' => $color('--color-error-custom'),

    // Colores > Tema claro / Tema oscuro
    'colors_light_background' => $color('--color-light-background'),
    'colors_light_surface' => $color('--color-light-surface'),
    'colors_light_text' => $color('--color-light-text'),
    'colors_light_heading' => $color('--color-light-heading'),
    'colors_light_accent' => $color('--color-light-accent'),
    'colors_light_link' => $color('--color-light-link'),
    'colors_dark_background' => $color('--color-dark-background'),
    'colors_dark_surface' => $color('--color-dark-surface'),
    'colors_dark_text' => $color('--color-dark-text'),
    'colors_dark_heading' => $color('--color-dark-heading'),
    'colors_dark_accent' => $color('--color-dark-accent'),
    'colors_dark_link' => $color('--color-dark-link'),

    // Colores > Botones
    'colors_button_primary_color' => $color('--button-primary-color'),
    'colors_button_primary_text' => $color('--button-primary-text', ['contrast_of' => ['colors_button_primary_color', 'colors_primary'], 'fallback' => GAIA_DEFAULT_PRIMARY_COLOR]),
    'colors_button_primary_hover_color' => $color('--button-primary-hover-color'),
    'colors_button_primary_hover_text' => $color('--button-primary-hover-text', ['contrast_of' => ['colors_button_primary_hover_color']]),
    'colors_button_secondary_color' => $color('--button-secondary-color'),
    'colors_button_secondary_text' => $color('--button-secondary-text', ['contrast_of' => ['colors_button_secondary_color', 'colors_secondary']]),
    'colors_button_secondary_hover_color' => $color('--button-secondary-hover-color'),
    'colors_button_secondary_hover_text' => $color('--button-secondary-hover-text', ['contrast_of' => ['colors_button_secondary_hover_color']]),

    // Tipografía
    'typography_font_heading' => ['type' => 'choice', 'default' => GAIA_DEFAULT_HEADING_FONT, 'values' => array_map(fn($font_stack) => ['--font-family-heading' => $font_stack], $font_values)],
    'typography_font_body' => ['type' => 'choice', 'default' => GAIA_DEFAULT_BODY_FONT, 'values' => array_map(fn($font_stack) => ['--font-family-body' => $font_stack], $font_values)],
    'typography_heading_weight' => ['type' => 'choice', 'default' => 'bold', 'values' => [
      'regular' => ['--heading-font-weight' => '500'],
      'bold' => ['--heading-font-weight' => '700'],
      'extra-bold' => ['--heading-font-weight' => '800'],
    ]],
    'typography_heading_uppercase' => $boolean(false,
      ['--heading-text-transform' => 'uppercase', '--heading-letter-spacing' => '0.03em'],
      ['--heading-text-transform' => 'none', '--heading-letter-spacing' => 'normal']
    ),
    'typography_scale' => ['type' => 'choice', 'default' => 'normal', 'values' => [
      'small' => ['--root-font-size' => '93.75%'],
      'normal' => ['--root-font-size' => '100%'],
      'large' => ['--root-font-size' => '106.25%'],
      'larger' => ['--root-font-size' => '112.5%'],
    ]],
    'typography_link_underline' => ['type' => 'choice', 'default' => 'always', 'values' => [
      'always' => ['--link-decoration' => 'underline', '--link-hover-decoration' => 'underline'],
      'hover' => ['--link-decoration' => 'none', '--link-hover-decoration' => 'underline'],
      'never' => ['--link-decoration' => 'none', '--link-hover-decoration' => 'none'],
    ]],

    // Estilo
    'style_radius' => ['type' => 'choice', 'default' => 'soft', 'values' => [
      'none' => ['--border-radius' => '0'],
      'soft' => ['--border-radius' => '0.375rem'],
      'round' => ['--border-radius' => '0.875rem'],
    ]],
    'style_container_width' => ['type' => 'choice', 'default' => 'normal', 'values' => [
      'narrow' => ['--container-width' => '62rem'],
      'normal' => ['--container-width' => '75rem'],
      'wide' => ['--container-width' => '90rem'],
    ]],
    'style_shadows' => ['type' => 'choice', 'default' => 'soft', 'values' => [
      'none' => ['--shadow-strength' => '0'],
      'soft' => ['--shadow-strength' => '1'],
      'strong' => ['--shadow-strength' => '2.5'],
    ]],
    'style_button_primary_type' => ['type' => 'choice', 'default' => 'solid', 'values' => [
      'solid' => ['--button-primary-outline' => '0'],
      'outline' => ['--button-primary-outline' => '1'],
    ]],
    'style_button_secondary_type' => ['type' => 'choice', 'default' => 'outline', 'values' => [
      'solid' => ['--button-secondary-outline' => '0'],
      'outline' => ['--button-secondary-outline' => '1'],
    ]],
    'style_buttons' => ['type' => 'choice', 'default' => 'rectangular', 'values' => [
      'rectangular' => ['--button-radius' => 'var(--border-radius)'],
      'pill' => ['--button-radius' => '999px'],
    ]],
    'style_button_size' => ['type' => 'choice', 'default' => 'medium', 'values' => [
      'small' => ['--button-padding-block' => '0.3125rem', '--button-padding-inline' => '0.75rem', '--button-font-size' => 'var(--font-size-small)'],
      'medium' => ['--button-padding-block' => 'var(--control-padding-block)', '--button-padding-inline' => '1rem', '--button-font-size' => 'var(--control-font-size)'],
      'large' => ['--button-padding-block' => '0.6875rem', '--button-padding-inline' => '1.5rem', '--button-font-size' => 'var(--font-size-base)'],
    ]],
    'style_button_weight' => ['type' => 'choice', 'default' => 'bold', 'values' => [
      'regular' => ['--button-font-weight' => '500'],
      'semibold' => ['--button-font-weight' => '600'],
      'bold' => ['--button-font-weight' => '700'],
    ]],
    'style_button_uppercase' => $boolean(false,
      ['--button-text-transform' => 'uppercase', '--button-letter-spacing' => '0.05em'],
      ['--button-text-transform' => 'none', '--button-letter-spacing' => 'normal']
    ),
    'style_button_border' => ['type' => 'choice', 'default' => 'thin', 'values' => [
      'thin' => ['--button-border-width' => '1px'],
      'thick' => ['--button-border-width' => '2px'],
    ]],
    'style_button_hover' => ['type' => 'choice', 'default' => 'color', 'values' => [
      'color' => ['--button-hover-transform' => 'none', '--button-hover-shadow' => 'none'],
      'lift' => ['--button-hover-transform' => 'translateY(-2px)', '--button-hover-shadow' => '0 4px 12px rgb(0 0 0 / 0.18)'],
      'grow' => ['--button-hover-transform' => 'scale(1.04)', '--button-hover-shadow' => 'none'],
    ]],

    // Cabecera ('header_layout' only for the samples: its class comes from
    // gaia_get_header_classes())
    'header_layout' => ['type' => 'choice', 'default' => 'two-rows', 'values' => [], 'class' => ['selector' => '.site-header', 'prefix' => 'site-header--', 'choices' => ['two-rows', 'one-row', 'centered', 'minimal']]],
    'header_logo_height' => ['type' => 'number', 'property' => '--header-logo-height', 'unit' => 'px', 'default' => 32],
    'header_background' => ['type' => 'background', 'default' => 'auto', 'color_option' => 'header_background_color', 'class' => ['selector' => '.site-header', 'prefix' => 'site-header--background-']],
    'header_background_color' => $color('--header-background-custom'),
    'header_text_color' => $color('--header-text-color', ['class' => ['selector' => '.site-header', 'when_set' => 'has-custom-text-color']]),
    'header_menu_accent_color' => $color('--header-menu-accent'),
    'header_menu_indicator' => ['type' => 'choice', 'default' => 'underline', 'values' => [], 'class' => ['selector' => '.site-header', 'prefix' => 'site-header--indicator-']],
    'header_border_bottom' => $boolean(true, ['--header-border-width' => '1px'], ['--header-border-width' => '0px']),
    'header_border_navigation' => $boolean(true, ['--header-navigation-border-width' => '1px'], ['--header-navigation-border-width' => '0px']),
    'header_separator_color' => $color('--header-separator-color'),
    'header_shadow' => ['type' => 'choice', 'default' => 'none', 'values' => [], 'class' => ['selector' => '.site-header', 'prefix' => 'site-header--shadow-']],

    // Pie de página
    'footer_logo_height' => ['type' => 'number', 'property' => '--footer-logo-height', 'unit' => 'px', 'default' => 54],
    'footer_background' => ['type' => 'background', 'default' => 'dark', 'color_option' => 'footer_background_color', 'class' => ['selector' => '.site-footer', 'prefix' => 'site-footer--background-']],
    'footer_background_color' => $color('--footer-background-custom'),
    'footer_text_color' => $color('--footer-text-color', ['class' => ['selector' => '.site-footer', 'when_set' => 'has-custom-text-color']]),
    'footer_accent_color' => $color('--footer-accent-color', ['class' => ['selector' => '.site-footer', 'when_set' => 'has-custom-accent-color']]),
    'footer_separators' => $boolean(true, ['--footer-separator-width' => '1px'], ['--footer-separator-width' => '0px']),
    'footer_separator_color' => $color('--footer-separator-custom'),

    // Contacto > Iconos
    'contact_icons_show' => ['type' => 'choice', 'default' => '1', 'values' => [], 'class' => ['selector' => '.contact-details', 'prefix' => 'contact-details--icons-']],
    'contact_icons_shape' => ['type' => 'choice', 'default' => 'none', 'values' => [], 'class' => ['selector' => '.contact-details', 'prefix' => 'contact-details--shape-']],
    'contact_icons_fill' => ['type' => 'choice', 'default' => 'outline', 'values' => [], 'class' => ['selector' => '.contact-details', 'prefix' => 'contact-details--fill-']],
    'contact_icons_color' => $color('--contact-icon-color'),
    'social_icons_shape' => ['type' => 'choice', 'default' => 'none', 'values' => [], 'class' => ['selector' => '.social-links', 'prefix' => 'social-links--shape-']],
    'social_icons_fill' => ['type' => 'choice', 'default' => 'outline', 'values' => [], 'class' => ['selector' => '.social-links', 'prefix' => 'social-links--fill-']],
    'social_icons_size' => ['type' => 'choice', 'default' => 'medium', 'values' => [], 'class' => ['selector' => '.social-links', 'prefix' => 'social-links--size-']],
    'social_icons_color' => $color('--social-icon-color'),

    // Extras > Barra de aviso
    'extras_announcement_style' => ['type' => 'choice', 'default' => 'primary', 'values' => [], 'class' => ['selector' => '.announcement-bar', 'prefix' => 'announcement-bar--']],
  ];
}

/* Resolving values -------------------------------------------------------- */

/**
 * Value of a mapped option: a sanitized color, a valid choice or a number.
 * $option_values overrides the saved options (used for page overrides).
 */
function gaia_get_style_option_value($option_name, $option_values = []) {
  $style_map = gaia_get_option_style_map();
  $map_entry = $style_map[$option_name] ?? null;
  $raw_value = array_key_exists($option_name, $option_values) ? $option_values[$option_name] : gaia_get_option_field($option_name);

  if (!$map_entry) {
    return $raw_value;
  }

  switch ($map_entry['type']) {
    case 'color':
      return (string) sanitize_hex_color((string) $raw_value);

    case 'number':
      return is_numeric($raw_value) ? (float) $raw_value : $map_entry['default'];

    default:
      if (is_bool($raw_value)) {
        $raw_value = $raw_value ? '1' : '0';
      }

      $raw_value = (string) $raw_value;
      $valid_values = !empty($map_entry['values']) ? array_map('strval', array_keys($map_entry['values'])) : null;

      if ($raw_value === '' || ($valid_values !== null && !in_array($raw_value, $valid_values, true))) {
        return (string) $map_entry['default'];
      }

      return $raw_value;
  }
}

/**
 * First color found among the options (for automatic text colors).
 */
function gaia_get_first_style_color($option_names, $fallback_color = '') {
  foreach ($option_names as $option_name) {
    $option_color = gaia_get_style_option_value($option_name);

    if ($option_color !== '') {
      return $option_color;
    }
  }

  return $fallback_color;
}

/**
 * Custom properties of every mapped option, as [property => value].
 * $excluded_options are left out (the admin samples set them from the form).
 */
function gaia_get_option_style_properties($excluded_options = []) {
  $custom_properties = [];

  foreach (gaia_get_option_style_map() as $option_name => $map_entry) {
    if (in_array($option_name, $excluded_options, true)) {
      continue;
    }

    $option_value = gaia_get_style_option_value($option_name);

    switch ($map_entry['type']) {
      case 'color':
        if ($option_value === '' && !empty($map_entry['contrast_of'])) {
          $source_color = gaia_get_first_style_color($map_entry['contrast_of'], $map_entry['fallback'] ?? '');
          $option_value = $source_color !== '' ? gaia_get_contrast_text_color($source_color) : '';
        }

        if ($option_value !== '') {
          $custom_properties[$map_entry['property']] = $option_value;
        }
        break;

      case 'number':
        $custom_properties[$map_entry['property']] = $option_value . $map_entry['unit'];
        break;

      case 'choice':
        $custom_properties += $map_entry['values'][$option_value] ?? [];
        break;
    }
  }

  return $custom_properties;
}

/**
 * Background mode classes for an area: prefix + mode and the scheme that
 * fits the background ('auto' and 'surface' follow the page scheme).
 */
function gaia_get_background_classes($option_name, $background_mode) {
  $map_entry = gaia_get_option_style_map()[$option_name];
  $background_classes = [$map_entry['class']['prefix'] . $background_mode];
  $background_color = '';

  if ($background_mode === 'primary') {
    $background_color = gaia_get_first_style_color(['colors_primary'], GAIA_DEFAULT_PRIMARY_COLOR);
  } elseif ($background_mode === 'custom') {
    $background_color = gaia_get_style_option_value($map_entry['color_option']);
  }

  if ($background_mode === 'dark' || ($background_color !== '' && gaia_is_dark_color($background_color))) {
    $background_classes[] = 'scheme-dark';
  } elseif ($background_color !== '' || $background_mode === 'light') {
    $background_classes[] = 'scheme-light';
  }

  return $background_classes;
}

/**
 * Classes that the given options add to their element.
 */
function gaia_get_option_classes($option_names) {
  $style_map = gaia_get_option_style_map();
  $option_classes = [];

  foreach ($option_names as $option_name) {
    $class_settings = $style_map[$option_name]['class'] ?? null;

    if (!$class_settings) {
      continue;
    }

    $option_value = gaia_get_style_option_value($option_name);

    if (isset($class_settings['when_set'])) {
      if ($option_value !== '') {
        $option_classes[] = $class_settings['when_set'];
      }
    } elseif ($style_map[$option_name]['type'] === 'background') {
      $option_classes = array_merge($option_classes, gaia_get_background_classes($option_name, $option_value));
    } else {
      $option_classes[] = $class_settings['prefix'] . $option_value;
    }
  }

  return $option_classes;
}
