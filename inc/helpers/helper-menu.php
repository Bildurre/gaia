<?php
/**
 * Menu Helper Functions
 */

if (!defined('ABSPATH')) exit;

/**
 * Fallback menu - shows pages
 */
function gaia_fallback_menu() {
  echo '<ul class="header-default__menu">';
  wp_list_pages(array(
    'title_li' => '',
    'depth' => 1,
  ));
  echo '</ul>';
}

/**
 * Fallback menu mobile - shows pages
 */
function gaia_fallback_menu_mobile() {
  echo '<ul class="header-default__mobile-menu">';
  wp_list_pages(array(
    'title_li' => '',
    'depth' => 2,
  ));
  echo '</ul>';
}