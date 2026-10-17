<?php
/**
 * Custom SCF blocks: every blocks/<name>/block.json is registered as a block.
 * Pages can only use these blocks.
 */

if (!defined('ABSPATH')) {
  exit;
}

const GAIA_BLOCK_CATEGORY = 'gaia';

function gaia_get_block_directories() {
  return glob(get_template_directory() . '/blocks/*', GLOB_ONLYDIR) ?: [];
}

function gaia_register_blocks() {
  foreach (gaia_get_block_directories() as $block_directory) {
    if (is_readable($block_directory . '/block.json')) {
      register_block_type($block_directory);
    }
  }
}
add_action('init', 'gaia_register_blocks');

function gaia_register_block_category($block_categories) {
  array_unshift($block_categories, [
    'slug' => GAIA_BLOCK_CATEGORY,
    'title' => 'Gaia',
    'icon' => null,
  ]);

  return $block_categories;
}
add_filter('block_categories_all', 'gaia_register_block_category');

function gaia_get_registered_block_names() {
  $registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();

  return array_values(array_filter(
    array_keys($registered_blocks),
    fn($block_name) => str_starts_with($block_name, 'gaia/')
  ));
}

function gaia_filter_allowed_block_types($allowed_block_types, $editor_context) {
  if (($editor_context->post->post_type ?? '') === 'page') {
    return gaia_get_registered_block_names();
  }

  return $allowed_block_types;
}
add_filter('allowed_block_types_all', 'gaia_filter_allowed_block_types', 10, 2);

/**
 * Wrapper attributes shared by every block render template: block class,
 * extra classes from the editor and the HTML anchor.
 */
function gaia_get_block_wrapper_attributes($block, $base_class, $extra_classes = []) {
  $class_names = array_merge([$base_class], $extra_classes);

  if (!empty($block['className'])) {
    $class_names[] = $block['className'];
  }

  $attributes = sprintf('class="%s"', esc_attr(implode(' ', array_filter($class_names))));

  if (!empty($block['anchor'])) {
    $attributes .= sprintf(' id="%s"', esc_attr($block['anchor']));
  }

  return $attributes;
}

/**
 * Block field value: get_field() inside a block render template, where SCF
 * already knows the block, with a default for empty values.
 */
function gaia_get_block_field($field_name, $default_value = '') {
  $field_value = get_field($field_name);

  return $field_value === null || $field_value === '' || $field_value === false ? $default_value : $field_value;
}

/**
 * Section classes shared by the content blocks: background (none, surface,
 * primary, dark) with the scheme that fits it, and vertical spacing. Fields
 * "<prefix>_background" and "<prefix>_spacing" (Ajustes tab).
 */
function gaia_get_block_section_classes($field_prefix) {
  $background = gaia_get_block_field($field_prefix . '_background', 'none');
  $spacing = gaia_get_block_field($field_prefix . '_spacing', 'normal');
  $section_classes = ['section', 'section--background-' . $background, 'section--spacing-' . $spacing];

  if ($background === 'dark') {
    $section_classes[] = 'scheme-dark';
  } elseif ($background === 'primary') {
    $primary_color = gaia_get_first_style_color(['colors_primary'], GAIA_DEFAULT_PRIMARY_COLOR);
    $section_classes[] = gaia_is_dark_color($primary_color) ? 'scheme-dark' : 'scheme-light';
  }

  return $section_classes;
}

/**
 * Section heading fields of a block ("<prefix>_eyebrow", "_title",
 * "_intro"), for template-parts/blocks/section-heading.php.
 */
function gaia_get_block_heading($field_prefix, $alignment = 'left') {
  return [
    'eyebrow' => (string) gaia_get_block_field($field_prefix . '_eyebrow'),
    'title' => (string) gaia_get_block_field($field_prefix . '_title'),
    'intro' => (string) gaia_get_block_field($field_prefix . '_intro'),
    'alignment' => $alignment,
  ];
}

/**
 * Editor placeholder for blocks without content yet.
 */
function gaia_print_block_placeholder($message) {
  printf('<p class="block-placeholder">%s</p>', esc_html($message));
}
