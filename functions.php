<?php
/**
 * Gaia Theme Functions
 */

// Setup
require_once get_template_directory() . '/inc/setup.php';

// Helpers
require_once get_template_directory() . '/inc/helpers/helper-icons.php';
require_once get_template_directory() . '/inc/helpers/helper-header.php';
require_once get_template_directory() . '/inc/helpers/helper-menu.php';
require_once get_template_directory() . '/inc/helpers/helper-social.php';

// Admin
if (is_admin()) {
  require_once get_template_directory() . '/inc/admin/class-gaia-admin.php';
}