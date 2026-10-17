<?php

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Option subpages of "Ajustes de Gaia" as [menu slug => title], in menu order.
 */
function gaia_get_options_subpages() {
  return [
    'gaia-colors' => __('Colores', 'gaia'),
    'gaia-typography' => __('Tipografía', 'gaia'),
    'gaia-style' => __('Estilo', 'gaia'),
    'gaia-logos' => __('Logos', 'gaia'),
    'gaia-header' => __('Cabecera', 'gaia'),
    'gaia-footer' => __('Pie de página', 'gaia'),
    'gaia-content' => __('Contenidos', 'gaia'),
    'gaia-contact' => __('Contacto', 'gaia'),
    'gaia-extras' => __('Extras', 'gaia'),
  ];
}

function gaia_register_options_pages() {
  if (!function_exists('acf_add_options_page')) {
    return;
  }

  acf_add_options_page([
    'page_title' => __('Ajustes de Gaia', 'gaia'),
    'menu_title' => __('Ajustes de Gaia', 'gaia'),
    'menu_slug' => 'gaia-settings',
    'capability' => 'edit_theme_options',
    'icon_url' => 'dashicons-admin-customizer',
    'position' => 59,
    'redirect' => true,
  ]);

  foreach (gaia_get_options_subpages() as $menu_slug => $page_title) {
    acf_add_options_sub_page([
      'page_title' => $page_title,
      'menu_title' => $page_title,
      'menu_slug' => $menu_slug,
      'parent_slug' => 'gaia-settings',
      'capability' => 'edit_theme_options',
    ]);
  }
}
add_action('acf/init', 'gaia_register_options_pages');

/**
 * Translatable option fields are SCF "group" fields (table layout) defined in
 * acf-json/ without sub fields. One sub field per Polylang language is added
 * here, so each language is a column and new languages appear automatically.
 * Values are read with gaia_get_translated_option_text() / _html().
 */
function gaia_get_translatable_option_fields() {
  return [
    'field_gaia_footer_tagline' => [
      'type' => 'text',
    ],
    'field_gaia_footer_credits' => [
      'type' => 'wysiwyg',
      'tabs' => 'visual',
      'toolbar' => 'basic',
      'media_upload' => 0,
      'delay' => 0,
    ],
    'field_gaia_contact_address' => [
      'type' => 'textarea',
      'rows' => 3,
      'new_lines' => '',
    ],
    'field_gaia_extras_announcement_text' => [
      'type' => 'text',
    ],
    'field_gaia_extras_announcement_button_text' => [
      'type' => 'text',
    ],
    'field_gaia_extras_maintenance_title' => [
      'type' => 'text',
    ],
    'field_gaia_extras_maintenance_text' => [
      'type' => 'textarea',
      'rows' => 3,
      'new_lines' => '',
    ],
    'field_gaia_extras_404_title' => [
      'type' => 'text',
    ],
    'field_gaia_extras_404_text' => [
      'type' => 'textarea',
      'rows' => 3,
      'new_lines' => '',
    ],
    'field_gaia_contact_phone_label' => [
      'type' => 'text',
    ],
    'field_gaia_contact_email_label' => [
      'type' => 'text',
    ],
  ];
}

function gaia_register_translatable_option_sub_fields() {
  if (!function_exists('acf_add_local_field')) {
    return;
  }

  $languages = gaia_get_languages();
  $column_width = (string) floor(100 / max(count($languages), 1));

  foreach (gaia_get_translatable_option_fields() as $parent_field_key => $sub_field_settings) {
    foreach ($languages as $language_slug => $language_name) {
      acf_add_local_field(array_merge([
        'key' => $parent_field_key . '_' . $language_slug,
        'label' => $language_name,
        'name' => $language_slug,
        'parent' => $parent_field_key,
        'instructions' => '',
        'required' => 0,
        'wrapper' => ['width' => $column_width, 'class' => '', 'id' => ''],
        'default_value' => '',
      ], $sub_field_settings));
    }
  }
}
add_action('acf/init', 'gaia_register_translatable_option_sub_fields');

// Short WYSIWYG editors for the credits columns (one or two lines of text).
function gaia_print_options_admin_styles() {
  echo '<style>[data-key^="field_gaia_footer_credits_"] iframe { height: 120px !important; min-height: 0 !important; }</style>';
}
add_action('acf/input/admin_head', 'gaia_print_options_admin_styles');
