<?php
/**
 * Header Helper Functions
 */

if (!defined('ABSPATH')) exit;

/**
 * Get available header styles
 */
function gaia_get_header_styles() {
  return array(
    'default' => __('Default', 'gaia'),
    'centered' => __('Centered', 'gaia'),
    'minimal' => __('Minimal', 'gaia'),
  );
}

/**
 * Get current header style
 */
function gaia_get_header_style() {
  return get_option('gaia_header_style', 'default');
}

/**
 * Get header content width setting
 */
function gaia_get_header_content_width() {
  return get_option('gaia_header_content_width', 'content');
}

/**
 * Get header content width CSS value
 */
function gaia_get_header_content_width_value() {
  $width = gaia_get_header_content_width();
  
  switch ($width) {
    case 'full':
      return '100%';
    case 'content':
      return 'var(--wp--style--global--content-size)';
    case 'wide':
    default:
      return 'var(--wp--style--global--wide-size)';
  }
}

/**
 * Get header background color
 */
function gaia_get_header_bg_color() {
  return get_option('gaia_header_bg_color', 'primary');
}

/**
 * Get header text color
 */
function gaia_get_header_text_color() {
  return get_option('gaia_header_text_color', 'primary-foreground');
}

/**
 * Get header current item color
 */
function gaia_get_header_current_color() {
  return get_option('gaia_header_current_color', 'secondary');
}

/**
 * Check if header should show social links
 */
function gaia_header_show_social() {
  $value = get_option('gaia_header_show_social');
  // Default to true if not set
  return ($value === false || $value === '1');
}

/**
 * Check if header should show search
 */
function gaia_header_show_search() {
  return get_option('gaia_header_show_search', '0') === '1';
}

/**
 * Check if header is sticky
 */
function gaia_header_is_sticky() {
  return get_option('gaia_header_is_sticky', '0') === '1';
}

/**
 * Check if header should hide on scroll
 */
function gaia_header_hide_on_scroll() {
  return get_option('gaia_header_hide_on_scroll', '0') === '1';
}

/**
 * Get header CSS classes
 */
function gaia_get_header_classes() {
  $classes = array('header-default');
  
  if (gaia_header_is_sticky()) {
    $classes[] = 'header-default--sticky';
  }
  
  if (gaia_header_is_sticky() && gaia_header_hide_on_scroll()) {
    $classes[] = 'header-default--hide-on-scroll';
  }
  
  return implode(' ', $classes);
}

/**
 * Get header inline styles
 */
function gaia_get_header_styles_attr() {
  $bg_color = gaia_get_header_bg_color();
  $text_color = gaia_get_header_text_color();
  $current_color = gaia_get_header_current_color();
  $content_width = gaia_get_header_content_width_value();
  
  $styles = array(
    '--header-bg: var(--wp--preset--color--' . esc_attr($bg_color) . ')',
    '--header-text: var(--wp--preset--color--' . esc_attr($text_color) . ')',
    '--header-current: var(--wp--preset--color--' . esc_attr($current_color) . ')',
    '--header-content-width: ' . esc_attr($content_width),
  );
  
  return implode('; ', $styles);
}

/**
 * Get header data attributes for JS
 */
function gaia_get_header_data_attrs() {
  $attrs = array();
  
  if (gaia_header_is_sticky() && gaia_header_hide_on_scroll()) {
    $attrs[] = 'data-hide-on-scroll="true"';
  }
  
  return implode(' ', $attrs);
}

/**
 * Render header
 */
function gaia_render_header() {
  $header_style = gaia_get_header_style();
  get_template_part('parts/header', $header_style);
}
add_action('wp_body_open', 'gaia_render_header');