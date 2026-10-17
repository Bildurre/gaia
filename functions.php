<?php

if (!defined('ABSPATH')) {
  exit;
}

define('GAIA_VERSION', wp_get_theme()->get('Version'));
define('GAIA_BROWSERSYNC_PORT', 3000);

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/assets.php';
require_once get_template_directory() . '/inc/options-pages.php';
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/account.php';
require_once get_template_directory() . '/inc/avatars.php';
require_once get_template_directory() . '/inc/blocks.php';
require_once get_template_directory() . '/inc/appearance.php';
require_once get_template_directory() . '/inc/logos.php';
require_once get_template_directory() . '/inc/header.php';
require_once get_template_directory() . '/inc/footer.php';
require_once get_template_directory() . '/inc/page-settings.php';
require_once get_template_directory() . '/inc/extras.php';
