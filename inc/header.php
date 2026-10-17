<?php
/**
 * Header settings (Ajustes de Gaia > Cabecera) and header features:
 * submenu toggles, transparent header over the hero, body classes.
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Header settings with defaults, resolved for the current page.
 */
function gaia_get_header_settings() {
  static $header_settings = null;

  if ($header_settings !== null) {
    return $header_settings;
  }

  $layout = gaia_get_option_value('header_layout', 'two-rows');
  $mobile_menu = gaia_get_option_value('header_mobile_menu', 'left');
  $is_sticky = (bool) gaia_get_option_value('header_sticky', true);

  $header_settings = [
    'layout' => in_array($layout, ['two-rows', 'one-row', 'centered', 'minimal'], true) ? $layout : 'two-rows',
    'logo' => gaia_get_option_value('header_logo', 'main'),
    'logo_mobile' => gaia_get_option_value('header_logo_mobile', 'main'),
    'is_sticky' => $is_sticky,
    'hides_on_scroll' => $is_sticky && (bool) gaia_get_option_value('header_hide_on_scroll', false),
    'is_transparent' => gaia_is_header_transparent(),
    'shows_languages' => (bool) gaia_get_option_value('header_show_languages', true),
    'shows_user_menu' => (bool) gaia_get_option_value('header_show_user', true),
    'shows_search' => (bool) gaia_get_option_value('header_show_search', false),
    'shows_scheme_toggle' => gaia_get_color_mode() === 'auto' && (bool) gaia_get_option_value('header_show_scheme_toggle', true),
    'mobile_menu' => in_array($mobile_menu, ['left', 'right', 'fullscreen'], true) ? $mobile_menu : 'left',
    'is_hidden' => false,
  ];

  $header_settings = apply_filters('gaia_header_settings', $header_settings);

  if ($header_settings['is_hidden']) {
    $header_settings['is_transparent'] = false;
  }

  return $header_settings;
}

/**
 * True when the main content of the current singular page starts with a Hero.
 */
function gaia_page_starts_with_hero() {
  if (!is_singular()) {
    return false;
  }

  $parsed_blocks = array_values(array_filter(
    parse_blocks((string) get_post_field('post_content', get_queried_object_id())),
    fn($parsed_block) => !empty($parsed_block['blockName'])
  ));

  return ($parsed_blocks[0]['blockName'] ?? '') === 'gaia/hero';
}

function gaia_is_header_transparent() {
  $is_enabled = (bool) gaia_get_option_value('header_transparent', false);

  return (bool) apply_filters('gaia_header_transparent', $is_enabled && gaia_page_starts_with_hero());
}

function gaia_get_header_classes() {
  $header_settings = gaia_get_header_settings();
  $header_classes = [
    'site-header',
    'site-header--' . $header_settings['layout'],
    'site-header--menu-' . $header_settings['mobile_menu'],
    $header_settings['is_sticky'] ? 'is-sticky' : '',
    $header_settings['is_transparent'] ? 'is-transparent scheme-dark' : '',
  ];

  return implode(' ', array_filter($header_classes));
}

function gaia_add_header_body_classes($body_classes) {
  if (gaia_get_header_settings()['is_transparent']) {
    $body_classes[] = 'has-transparent-header';
  }

  return $body_classes;
}
add_filter('body_class', 'gaia_add_header_body_classes');

/**
 * Adds a toggle button after primary menu items that have a submenu
 * (dropdown on wide screens, accordion in the drawer).
 */
function gaia_add_submenu_toggle($item_output, $menu_item, $depth, $menu_arguments) {
  $is_primary_menu = ($menu_arguments->theme_location ?? '') === 'primary';

  if (!$is_primary_menu || !in_array('menu-item-has-children', (array) $menu_item->classes, true)) {
    return $item_output;
  }

  return $item_output . sprintf(
    '<button class="submenu-toggle" type="button" aria-expanded="false" data-submenu-toggle>%s<span class="visually-hidden">%s</span></button>',
    gaia_get_icon_svg('ui/chevron-down'),
    esc_html(sprintf(
      /* translators: %s: menu item title */
      __('Show submenu: %s', 'gaia'),
      wp_strip_all_tags($menu_item->title)
    ))
  );
}
add_filter('walker_nav_menu_start_el', 'gaia_add_submenu_toggle', 10, 4);
