<?php
/**
 * Appearance: fonts, color mode and the CSS of the design options (see
 * inc/option-styles.php), printed on the site and in the block editor.
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
  $heading_font_slug = (string) gaia_get_option_field('typography_font_heading');
  $body_font_slug = (string) gaia_get_option_field('typography_font_body');

  return [
    'heading' => $available_fonts[$heading_font_slug] ?? $available_fonts[GAIA_DEFAULT_HEADING_FONT],
    'body' => $available_fonts[$body_font_slug] ?? $available_fonts[GAIA_DEFAULT_BODY_FONT],
  ];
}

function gaia_get_font_stack($font_settings) {
  return sprintf("'%s', %s", $font_settings['family'], $font_settings['fallback']);
}

/**
 * Color mode of the site: 'light', 'dark' or 'auto' (both, with a switcher;
 * follows the system preference until the visitor chooses).
 */
function gaia_get_color_mode() {
  $color_mode = (string) gaia_get_option_field('colors_mode');

  return in_array($color_mode, ['light', 'dark', 'auto'], true) ? $color_mode : 'light';
}

/**
 * @font-face rules for the selected fonts and the :root custom properties of
 * the design options ($excluded_options: see gaia_get_option_style_properties()).
 */
function gaia_get_appearance_css($context = 'site', $excluded_options = []) {
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

  $custom_properties = gaia_get_option_style_properties($excluded_options);

  // The block editor has no page scheme switch: show it in dark colors when
  // the site is dark only.
  if ($context === 'editor' && gaia_get_color_mode() === 'dark') {
    foreach (['background', 'surface', 'text', 'heading', 'accent', 'link'] as $color_role) {
      $custom_properties['--color-light-' . $color_role] = $custom_properties['--color-dark-' . $color_role] ?? 'var(--color-dark-' . $color_role . ')';
    }
  }

  $declarations = [];
  foreach ($custom_properties as $custom_property => $value) {
    $declarations[] = $custom_property . ':' . $value;
  }

  $css_rules[] = ':root{' . implode(';', $declarations) . '}';

  return implode("\n", $css_rules);
}

function gaia_enqueue_appearance_css() {
  wp_add_inline_style('gaia-main', gaia_get_appearance_css());
}
add_action('wp_enqueue_scripts', 'gaia_enqueue_appearance_css', 20);

function gaia_add_appearance_css_to_editor($editor_settings) {
  $editor_settings['styles'][] = ['css' => gaia_get_appearance_css('editor')];

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
 * Color mode attributes on <html>: data-color-mode always, data-color-scheme
 * for fixed modes (the "auto" mode sets it with JavaScript before painting).
 */
function gaia_add_color_mode_attributes($html_attributes) {
  $color_mode = gaia_get_color_mode();
  $html_attributes .= sprintf(' data-color-mode="%s"', esc_attr($color_mode));

  if ($color_mode !== 'auto') {
    $html_attributes .= sprintf(' data-color-scheme="%s"', esc_attr($color_mode));
  }

  return $html_attributes;
}
add_filter('language_attributes', 'gaia_add_color_mode_attributes');

/**
 * "Auto" mode: apply the saved choice, or the system preference, before the
 * page is painted (avoids a flash of the wrong scheme).
 */
function gaia_print_color_scheme_script() {
  if (gaia_get_color_mode() !== 'auto') {
    return;
  }

  echo "<script>(function(){var savedScheme=null;try{savedScheme=localStorage.getItem('gaia-color-scheme');}catch(error){}var preference=savedScheme==='dark'||savedScheme==='light'?savedScheme:'system';var scheme=preference==='system'?(window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'):preference;document.documentElement.setAttribute('data-color-preference',preference);document.documentElement.setAttribute('data-color-scheme',scheme);})();</script>\n";
}
add_action('wp_head', 'gaia_print_color_scheme_script', 0);

/**
 * Font choices for the typography select fields come from the bundled fonts.
 */
function gaia_load_font_field_choices($field) {
  $field['choices'] = array_map(fn($font_settings) => $font_settings['name'], gaia_get_available_fonts());

  return $field;
}
add_filter('acf/load_field/key=field_gaia_typography_font_heading', 'gaia_load_font_field_choices');
add_filter('acf/load_field/key=field_gaia_typography_font_body', 'gaia_load_font_field_choices');
