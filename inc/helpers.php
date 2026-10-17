<?php

if (!defined('ABSPATH')) {
  exit;
}

function gaia_get_option_field($field_name) {
  if (!function_exists('get_field')) {
    return null;
  }

  return get_field($field_name, 'option');
}

/**
 * Site languages as [slug => name] (Polylang), Spanish only without Polylang.
 */
function gaia_get_languages() {
  if (!function_exists('pll_languages_list')) {
    return ['es' => 'Español'];
  }

  $language_slugs = pll_languages_list(['fields' => 'slug']);
  $language_names = pll_languages_list(['fields' => 'name']);

  return $language_slugs ? array_combine($language_slugs, $language_names) : ['es' => 'Español'];
}

function gaia_get_default_language_slug() {
  return function_exists('pll_default_language') ? (pll_default_language('slug') ?: 'es') : 'es';
}

function gaia_get_current_language_slug() {
  return function_exists('pll_current_language') ? (pll_current_language('slug') ?: gaia_get_default_language_slug()) : gaia_get_default_language_slug();
}

/**
 * Picks the current language from a [language slug => text] array. Falls back
 * to the default language and then to the first non-empty text.
 */
function gaia_pick_translation($texts_by_language) {
  if (is_string($texts_by_language)) {
    return $texts_by_language;
  }

  if (!is_array($texts_by_language)) {
    return '';
  }

  foreach ([gaia_get_current_language_slug(), gaia_get_default_language_slug()] as $language_slug) {
    $text = $texts_by_language[$language_slug] ?? '';

    if (is_string($text) && trim($text) !== '') {
      return $text;
    }
  }

  foreach ($texts_by_language as $text) {
    if (is_string($text) && trim($text) !== '') {
      return $text;
    }
  }

  return '';
}

function gaia_get_translated_option_text($field_name) {
  return gaia_pick_translation(gaia_get_option_field($field_name));
}

/**
 * For WYSIWYG translatable option fields (already formatted by SCF).
 */
function gaia_get_translated_option_html($field_name) {
  $translated_html = gaia_pick_translation(gaia_get_option_field($field_name));

  return $translated_html === '' ? '' : wp_kses_post($translated_html);
}

function gaia_get_option_rows($repeater_name) {
  $repeater_rows = gaia_get_option_field($repeater_name);

  return is_array($repeater_rows) ? $repeater_rows : [];
}

/**
 * Returns contact data ready to print:
 * phones: [['number', 'href', 'label']], emails: [['address', 'href', 'label']],
 * address: string, social_networks: [['network', 'label', 'url']].
 */
function gaia_get_contact_details() {
  $phones = [];
  foreach (gaia_get_option_rows('contact_phones') as $phone_row) {
    $phone_number = trim((string) ($phone_row['number'] ?? ''));

    if ($phone_number !== '') {
      $phones[] = [
        'number' => $phone_number,
        'href' => 'tel:' . preg_replace('/[^0-9+]/', '', $phone_number),
        'label' => gaia_pick_translation($phone_row['label'] ?? ''),
      ];
    }
  }

  $emails = [];
  foreach (gaia_get_option_rows('contact_emails') as $email_row) {
    $email_address = sanitize_email((string) ($email_row['address'] ?? ''));

    if ($email_address !== '') {
      $emails[] = [
        'address' => antispambot($email_address),
        'href' => 'mailto:' . antispambot($email_address),
        'label' => gaia_pick_translation($email_row['label'] ?? ''),
      ];
    }
  }

  $social_network_labels = gaia_get_social_network_labels();
  $social_networks = [];
  foreach (gaia_get_option_rows('contact_social_networks') as $social_network_row) {
    $network_slug = (string) ($social_network_row['network'] ?? '');
    $network_url = (string) ($social_network_row['url'] ?? '');

    if ($network_slug !== '' && $network_url !== '') {
      $social_networks[] = [
        'network' => $network_slug,
        'label' => $social_network_labels[$network_slug] ?? ucfirst($network_slug),
        'url' => $network_url,
      ];
    }
  }

  return [
    'phones' => $phones,
    'emails' => $emails,
    'address' => gaia_get_translated_option_text('contact_address'),
    'social_networks' => $social_networks,
  ];
}

/**
 * Network names come from the choices of the "Red" select field, so adding a
 * network only needs a new choice in SCF and its SVG in assets/icons/social/.
 */
function gaia_get_social_network_labels() {
  if (!function_exists('acf_get_field')) {
    return [];
  }

  $network_field = acf_get_field('field_gaia_contact_social_network');

  return is_array($network_field['choices'] ?? null) ? $network_field['choices'] : [];
}

function gaia_get_icon_svg($icon_path, $fallback_icon_path = '') {
  $icons_directory = get_template_directory() . '/assets/icons/';

  foreach ([$icon_path, $fallback_icon_path] as $candidate_icon_path) {
    $icon_file = $icons_directory . $candidate_icon_path . '.svg';

    if ($candidate_icon_path !== '' && is_readable($icon_file)) {
      return (string) file_get_contents($icon_file);
    }
  }

  return '';
}

function gaia_get_social_icon_svg($network_slug) {
  return gaia_get_icon_svg('social/' . sanitize_file_name($network_slug), 'ui/link');
}

function gaia_get_current_url() {
  global $wp;

  return home_url(add_query_arg([], $wp->request ?? ''));
}
