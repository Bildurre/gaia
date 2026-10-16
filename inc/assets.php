<?php

if (!defined('ABSPATH')) {
  exit;
}

function gaia_enqueue_assets() {
  $theme_directory_uri = get_template_directory_uri();

  wp_enqueue_style('gaia-main', $theme_directory_uri . '/assets/css/main.css', [], GAIA_VERSION);
  wp_enqueue_script('gaia-main', $theme_directory_uri . '/assets/js/main.js', [], GAIA_VERSION, [
    'strategy' => 'defer',
    'in_footer' => true,
  ]);
  wp_enqueue_script('gaia-site-header', $theme_directory_uri . '/assets/js/site-header.js', [], GAIA_VERSION, [
    'strategy' => 'defer',
    'in_footer' => true,
  ]);

  if (is_page_template('page-templates/account.php')) {
    wp_enqueue_script('gaia-avatar-field', $theme_directory_uri . '/assets/js/avatar-field.js', [], GAIA_VERSION, [
      'strategy' => 'defer',
      'in_footer' => true,
    ]);
    wp_enqueue_script('gaia-no-autofill', $theme_directory_uri . '/assets/js/no-autofill.js', [], GAIA_VERSION, [
      'strategy' => 'defer',
      'in_footer' => true,
    ]);
  }

  if (is_user_logged_in()) {
    wp_enqueue_script('gaia-user-menu', $theme_directory_uri . '/assets/js/user-menu.js', [], GAIA_VERSION, [
      'strategy' => 'defer',
      'in_footer' => true,
    ]);
  }
}
add_action('wp_enqueue_scripts', 'gaia_enqueue_assets');

function gaia_enqueue_browsersync_client() {
  if (wp_get_environment_type() !== 'local') {
    return;
  }

  $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
  $browsersync_client_url = sprintf('//%s:%d/browser-sync/browser-sync-client.js', $site_host, GAIA_BROWSERSYNC_PORT);

  wp_enqueue_script('gaia-browsersync', $browsersync_client_url, [], null, [
    'strategy' => 'async',
    'in_footer' => true,
  ]);
}
add_action('wp_enqueue_scripts', 'gaia_enqueue_browsersync_client');
