<?php
/**
 * Gaia Admin - Dashboard Page
 */

if (!defined('ABSPATH')) exit;

class Gaia_Admin_Page_Dashboard {

  public function get_title() {
    return __('Gaia Options', 'gaia');
  }

  public function get_menu_title() {
    return __('Dashboard', 'gaia');
  }

  public function get_slug() {
    return 'gaia-options';
  }

  public function render() {
    ?>
    <div class="wrap gaia-admin">
      <h1><?php echo esc_html($this->get_title()); ?></h1>
      
      <div class="gaia-admin__card">
        <h2><?php _e('Welcome to Gaia Theme', 'gaia'); ?></h2>
        <p><?php _e('Use the subpages to configure your theme:', 'gaia'); ?></p>
        
        <div class="gaia-admin__links">
          <a href="<?php echo admin_url('admin.php?page=gaia-headers'); ?>" class="gaia-admin__link">
            <span class="dashicons dashicons-align-none"></span>
            <strong><?php _e('Headers', 'gaia'); ?></strong>
            <span><?php _e('Configure header style', 'gaia'); ?></span>
          </a>
          
          <a href="<?php echo admin_url('admin.php?page=gaia-social'); ?>" class="gaia-admin__link">
            <span class="dashicons dashicons-share"></span>
            <strong><?php _e('Social Links', 'gaia'); ?></strong>
            <span><?php _e('Add your social media links', 'gaia'); ?></span>
          </a>
        </div>
      </div>
    </div>
    <?php
  }
}