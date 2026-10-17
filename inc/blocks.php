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

/**
 * Shared carousel script, used by blocks as viewScript "gaia-carousel".
 */
function gaia_register_block_scripts() {
  wp_register_script('gaia-carousel', get_template_directory_uri() . '/assets/js/carousel.js', [], GAIA_VERSION, [
    'strategy' => 'defer',
    'in_footer' => true,
  ]);
}
add_action('init', 'gaia_register_block_scripts', 5);

/**
 * Data attributes of a carousel root (translated labels for the script).
 */
function gaia_get_carousel_attributes($autoplay_seconds = 0) {
  return sprintf(
    'data-carousel data-autoplay="%s" data-label-slide="%s" data-label-go-to="%s" aria-roledescription="carousel"',
    esc_attr((string) $autoplay_seconds),
    /* translators: 1: slide number, 2: number of slides */
    esc_attr__('Slide %1$d of %2$d', 'gaia'),
    /* translators: %d: slide number */
    esc_attr__('Go to slide %d', 'gaia')
  );
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

/**
 * Block editor (outside the content iframe): one field per row in the block
 * settings sidebar, and a fix for SCF tabs that render before the sidebar is
 * visible (assets/js/block-editor.js).
 */
function gaia_enqueue_block_editor_assets() {
  $theme_directory_uri = get_template_directory_uri();

  wp_enqueue_style('gaia-block-editor', $theme_directory_uri . '/assets/css/block-editor.css', [], GAIA_VERSION);
  wp_enqueue_script('gaia-block-editor', $theme_directory_uri . '/assets/js/block-editor.js', [], GAIA_VERSION, [
    'strategy' => 'defer',
    'in_footer' => true,
  ]);
}
add_action('enqueue_block_editor_assets', 'gaia_enqueue_block_editor_assets');

/**
 * Privacy-friendly embed URL of a YouTube or Vimeo link ('' otherwise):
 * youtube-nocookie.com, and Vimeo with "do not track".
 */
function gaia_get_video_embed_url($video_url) {
  if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $video_url, $youtube_match)) {
    return 'https://www.youtube-nocookie.com/embed/' . $youtube_match[1] . '?autoplay=1&rel=0';
  }

  if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $video_url, $vimeo_match)) {
    return 'https://player.vimeo.com/video/' . $vimeo_match[1] . '?autoplay=1&dnt=1';
  }

  return '';
}

/**
 * Lucide icon for a file extension (downloads block).
 */
function gaia_get_file_type_icon($file_extension) {
  $icon_names = [
    'pdf' => 'file-text', 'doc' => 'file-text', 'docx' => 'file-text', 'odt' => 'file-text', 'txt' => 'file-text', 'rtf' => 'file-text',
    'xls' => 'file-spreadsheet', 'xlsx' => 'file-spreadsheet', 'ods' => 'file-spreadsheet', 'csv' => 'file-spreadsheet',
    'zip' => 'file-archive', 'rar' => 'file-archive', '7z' => 'file-archive', 'gz' => 'file-archive',
    'jpg' => 'file-image', 'jpeg' => 'file-image', 'png' => 'file-image', 'webp' => 'file-image', 'gif' => 'file-image', 'svg' => 'file-image',
  ];

  return gaia_get_icon_svg('ui/' . ($icon_names[$file_extension] ?? 'file'));
}
