<?php
/**
 * Menu Helper Functions
 */

if (!defined('ABSPATH')) exit;

/**
 * Fallback menu - shows pages with children
 */
function gaia_fallback_menu() {
  echo '<ul class="header-default__menu">';
  wp_list_pages(array(
    'title_li' => '',
    'depth' => 2,
  ));
  echo '</ul>';
}

/**
 * Fallback menu mobile - shows pages with children
 */
function gaia_fallback_menu_mobile() {
  echo '<ul class="header-default__mobile-menu">';
  wp_list_pages(array(
    'title_li' => '',
    'depth' => 2,
  ));
  echo '</ul>';
}