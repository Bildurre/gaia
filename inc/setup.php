<?php

if (!defined('ABSPATH')) {
  exit;
}

function gaia_setup_theme() {
  load_theme_textdomain('gaia', get_template_directory() . '/languages');

  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);

  // Theme styles (fonts, tokens, typography) inside the block editor
  add_theme_support('editor-styles');
  add_editor_style(['assets/css/main.css', 'assets/css/editor.css']);

  register_nav_menus([
    'primary' => __('Primary menu', 'gaia'),
    'footer' => __('Menú del pie', 'gaia'),
    'legal' => __('Menú legal', 'gaia'),
  ]);
}
add_action('after_setup_theme', 'gaia_setup_theme');
