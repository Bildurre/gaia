<?php
/**
 * Gaia Admin - Entry Point
 */

if (!defined('ABSPATH')) exit;

class Gaia_Admin {
  private static $instance = null;
  private $pages = array();

  public static function get_instance() {
    if (null === self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function __construct() {
    $this->load_pages();
    
    add_action('admin_menu', array($this, 'add_menu_pages'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
  }

  /**
   * Load page classes
   */
  private function load_pages() {
    $pages_dir = get_template_directory() . '/inc/admin/pages/';
    
    require_once $pages_dir . 'class-gaia-admin-page-dashboard.php';
    require_once $pages_dir . 'class-gaia-admin-page-headers.php';
    require_once $pages_dir . 'class-gaia-admin-page-social.php';

    $this->pages['dashboard'] = new Gaia_Admin_Page_Dashboard();
    $this->pages['headers'] = new Gaia_Admin_Page_Headers();
    $this->pages['social'] = new Gaia_Admin_Page_Social();
  }

  /**
   * Add menu pages
   */
  public function add_menu_pages() {
    // Main menu
    add_menu_page(
      __('Gaia Options', 'gaia'),
      __('Gaia', 'gaia'),
      'manage_options',
      'gaia-options',
      array($this->pages['dashboard'], 'render'),
      'dashicons-admin-customizer',
      60
    );

    // Subpages
    foreach ($this->pages as $key => $page) {
      if ($key === 'dashboard') continue;
      
      add_submenu_page(
        'gaia-options',
        $page->get_title(),
        $page->get_menu_title(),
        'manage_options',
        $page->get_slug(),
        array($page, 'render')
      );
    }
  }

  /**
   * Enqueue admin assets
   */
  public function enqueue_assets($hook) {
    if (strpos($hook, 'gaia') === false) return;

    wp_enqueue_style(
      'gaia-admin',
      get_template_directory_uri() . '/inc/admin/assets/admin.css',
      array(),
      filemtime(get_template_directory() . '/inc/admin/assets/admin.css')
    );
  }
}

// Initialize
Gaia_Admin::get_instance();