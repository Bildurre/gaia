<?php
/**
 * Per-page settings (side box "Ajustes de la página" on pages and posts):
 * override the header and footer options of Ajustes de Gaia.
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Value of a page setting for the current singular page, or '' (use the
 * global option).
 */
function gaia_get_page_setting($field_name) {
  if (!is_singular() || !function_exists('get_field')) {
    return '';
  }

  $field_value = get_field($field_name, get_queried_object_id());

  return $field_value === null ? '' : $field_value;
}

function gaia_apply_page_header_settings($header_settings) {
  $page_header_layout = gaia_get_page_setting('page_header_layout');

  if (in_array($page_header_layout, ['two-rows', 'one-row', 'centered', 'minimal'], true)) {
    $header_settings['layout'] = $page_header_layout;
  }

  $header_settings['is_hidden'] = (bool) gaia_get_page_setting('page_hide_header');

  return $header_settings;
}
add_filter('gaia_header_settings', 'gaia_apply_page_header_settings');

function gaia_apply_page_header_transparency($is_transparent) {
  $page_header_transparent = gaia_get_page_setting('page_header_transparent');

  if ($page_header_transparent === 'yes') {
    return gaia_page_starts_with_hero();
  }

  if ($page_header_transparent === 'no') {
    return false;
  }

  return $is_transparent;
}
add_filter('gaia_header_transparent', 'gaia_apply_page_header_transparency');

function gaia_apply_page_footer_settings($footer_settings) {
  $page_footer_layout = gaia_get_page_setting('page_footer_layout');
  $page_footer_background = gaia_get_page_setting('page_footer_background');

  if (in_array($page_footer_layout, ['brand-contact', 'columns', 'minimal'], true)) {
    $footer_settings['layout'] = $page_footer_layout;
  }

  if (in_array($page_footer_background, ['dark', 'light', 'primary'], true)) {
    $footer_settings['background'] = $page_footer_background;
  }

  $footer_settings['is_hidden'] = (bool) gaia_get_page_setting('page_hide_footer');

  return $footer_settings;
}
add_filter('gaia_footer_settings', 'gaia_apply_page_footer_settings');
