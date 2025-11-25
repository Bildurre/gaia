<?php

function gaia_setup() {
  add_theme_support('title-tag');
}
add_action('after_setup_theme', 'gaia_setup');

function gaia_scripts() {
  // Enqueue main stylesheet from dist folder
  wp_enqueue_style(
    'gaia-style',
    get_template_directory_uri() . '/assets/dist/style.css',
    array(),
    filemtime(get_template_directory() . '/assets/dist/style.css')
  );
  
  // Enqueue main JavaScript from dist folder
  wp_enqueue_script(
    'gaia-main',
    get_template_directory_uri() . '/assets/dist/main.js',
    array(),
    filemtime(get_template_directory() . '/assets/dist/main.js'),
    true
  );
}
add_action('wp_enqueue_scripts', 'gaia_scripts');