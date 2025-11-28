<?php
/**
 * Theme Setup
 */

if (!defined('ABSPATH')) exit;

/**
 * Setup theme
 */
function gaia_setup() {
  add_theme_support('title-tag');
  add_theme_support('custom-logo');
  
  register_nav_menus(array(
    'primary' => __('Primary Menu', 'gaia'),
  ));
}
add_action('after_setup_theme', 'gaia_setup');

/**
 * Enqueue scripts and styles
 */
function gaia_scripts() {
  wp_enqueue_style(
    'gaia-style',
    get_template_directory_uri() . '/assets/dist/style.css',
    array(),
    filemtime(get_template_directory() . '/assets/dist/style.css')
  );
  
  wp_enqueue_script(
    'gaia-main',
    get_template_directory_uri() . '/assets/dist/main.js',
    array(),
    filemtime(get_template_directory() . '/assets/dist/main.js'),
    true
  );
}
add_action('wp_enqueue_scripts', 'gaia_scripts');