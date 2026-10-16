<?php
/**
 * Appearance options (Ajustes del sitio > Apariencia): colors, fonts and
 * favicon. Printed as CSS custom properties and @font-face rules on the site
 * and in the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

const GAIA_DEFAULT_HEADING_FONT = 'montserrat';
const GAIA_DEFAULT_BODY_FONT = 'inter';

/**
 * Fonts bundled in assets/fonts/ (self-hosted, no external requests).
 */
function gaia_get_available_fonts() {
  return [
    'inter' => [
      'name' => 'Inter',
      'family' => 'Inter',
      'fallback' => 'system-ui, sans-serif',
      'weight' => '100 900',
      'files' => ['normal' => 'inter-variable.woff2', 'italic' => 'inter-variable-italic.woff2'],
    ],
    'montserrat' => [
      'name' => 'Montserrat',
      'family' => 'Montserrat',
      'fallback' => 'system-ui, sans-serif',
      'weight' => '100 900',
      'files' => ['normal' => 'montserrat-variable.woff2', 'italic' => 'montserrat-variable-italic.woff2'],
    ],
    'open-sans' => [
      'name' => 'Open Sans',
      'family' => 'Open Sans',
      'fallback' => 'system-ui, sans-serif',
      'weight' => '300 800',
      'files' => ['normal' => 'opensans-variable.woff2', 'italic' => 'opensans-variable-italic.woff2'],
    ],
    'lora' => [
      'name' => 'Lora (serif)',
      'family' => 'Lora',
      'fallback' => 'Georgia, serif',
      'weight' => '400 700',
      'files' => ['normal' => 'lora-variable.woff2', 'italic' => 'lora-variable-italic.woff2'],
    ],
    'merriweather' => [
      'name' => 'Merriweather (serif)',
      'family' => 'Merriweather',
      'fallback' => 'Georgia, serif',
      'weight' => '300 900',
      'files' => ['normal' => 'merriweather-variable.woff2', 'italic' => 'merriweather-variable-italic.woff2'],
    ],
    'playfair-display' => [
      'name' => 'Playfair Display (serif, solo títulos)',
      'family' => 'Playfair Display',
      'fallback' => 'Georgia, serif',
      'weight' => '400',
      'files' => ['normal' => 'playfairdisplay.woff2', 'italic' => 'playfairdisplay-italic.woff2'],
    ],
  ];
}

/**
 * Selected fonts as ['heading' => font settings, 'body' => font settings].
 */
function gaia_get_selected_fonts() {
  $available_fonts = gaia_get_available_fonts();
  $heading_font_slug = (string) gaia_get_option_field('appearance_font_heading');
  $body_font_slug = (string) gaia_get_option_field('appearance_font_body');

  return [
    'heading' => $available_fonts[$heading_font_slug] ?? $available_fonts[GAIA_DEFAULT_HEADING_FONT],
    'body' => $available_fonts[$body_font_slug] ?? $available_fonts[GAIA_DEFAULT_BODY_FONT],
  ];
}

function gaia_get_font_stack($font_settings) {
  return sprintf("'%s', %s", $font_settings['family'], $font_settings['fallback']);
}

/**
 * Color options mapped to the palette custom properties they override.
 */
function gaia_get_color_option_properties() {
  return [
    'appearance_color_primary' => '--color-primary',
    'appearance_color_primary_light' => '--color-primary-light',
    'appearance_color_primary_dark' => '--color-primary-dark',
    'appearance_color_text' => '--color-ink',
    'appearance_color_highlight' => '--color-highlight',
  ];
}

function gaia_get_appearance_css() {
  $fonts_directory_uri = get_template_directory_uri() . '/assets/fonts/';
  $selected_fonts = gaia_get_selected_fonts();
  $css_rules = [];
  $printed_font_families = [];

  foreach ($selected_fonts as $font_settings) {
    if (in_array($font_settings['family'], $printed_font_families, true)) {
      continue;
    }

    $printed_font_families[] = $font_settings['family'];

    foreach ($font_settings['files'] as $font_style => $font_file) {
      $css_rules[] = sprintf(
        "@font-face{font-family:'%s';src:url('%s') format('woff2');font-weight:%s;font-style:%s;font-display:swap}",
        $font_settings['family'],
        esc_url($fonts_directory_uri . $font_file),
        $font_settings['weight'],
        $font_style
      );
    }
  }

  $custom_properties = [
    '--font-family-heading:' . gaia_get_font_stack($selected_fonts['heading']),
    '--font-family-body:' . gaia_get_font_stack($selected_fonts['body']),
  ];

  foreach (gaia_get_color_option_properties() as $option_name => $custom_property) {
    $color = sanitize_hex_color((string) gaia_get_option_field($option_name));

    if ($color) {
      $custom_properties[] = $custom_property . ':' . $color;
    }
  }

  $css_rules[] = ':root{' . implode(';', $custom_properties) . '}';

  return implode("\n", $css_rules);
}

function gaia_enqueue_appearance_css() {
  wp_add_inline_style('gaia-main', gaia_get_appearance_css());
}
add_action('wp_enqueue_scripts', 'gaia_enqueue_appearance_css', 20);

function gaia_add_appearance_css_to_editor($editor_settings) {
  $editor_settings['styles'][] = ['css' => gaia_get_appearance_css()];

  return $editor_settings;
}
add_filter('block_editor_settings_all', 'gaia_add_appearance_css_to_editor');

function gaia_preload_selected_fonts() {
  $preloaded_font_files = array_unique(array_map(
    fn($font_settings) => $font_settings['files']['normal'],
    gaia_get_selected_fonts()
  ));

  foreach ($preloaded_font_files as $font_file) {
    printf(
      '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
      esc_url(get_template_directory_uri() . '/assets/fonts/' . $font_file)
    );
  }
}
add_action('wp_head', 'gaia_preload_selected_fonts', 1);

/**
 * Favicon from the options (or the Gaia icon). The WordPress site icon,
 * when set, takes precedence.
 */
function gaia_print_favicon() {
  if (has_site_icon()) {
    return;
  }

  $favicon_id = (int) gaia_get_option_field('appearance_favicon');
  $favicon_url = $favicon_id ? wp_get_attachment_image_url($favicon_id, 'thumbnail') : get_template_directory_uri() . '/assets/images/gaia-icon.png';

  if ($favicon_url) {
    printf('<link rel="icon" href="%s">' . "\n", esc_url($favicon_url));
  }
}
add_action('wp_head', 'gaia_print_favicon');
add_action('admin_head', 'gaia_print_favicon');
add_action('login_head', 'gaia_print_favicon');

/**
 * Font choices for the appearance select fields come from the bundled fonts.
 */
function gaia_load_font_field_choices($field) {
  $field['choices'] = array_map(fn($font_settings) => $font_settings['name'], gaia_get_available_fonts());

  return $field;
}
add_filter('acf/load_field/key=field_gaia_appearance_font_heading', 'gaia_load_font_field_choices');
add_filter('acf/load_field/key=field_gaia_appearance_font_body', 'gaia_load_font_field_choices');
