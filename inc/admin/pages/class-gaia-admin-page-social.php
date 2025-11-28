<?php
/**
 * Gaia Admin - Social Links Page
 */

if (!defined('ABSPATH')) exit;

class Gaia_Admin_Page_Social {

  public function __construct() {
    add_action('admin_init', array($this, 'register_settings'));
  }

  public function get_title() {
    return __('Social Links', 'gaia');
  }

  public function get_menu_title() {
    return __('Social Links', 'gaia');
  }

  public function get_slug() {
    return 'gaia-social';
  }

  public function register_settings() {
    $networks = gaia_get_social_networks();
    
    foreach ($networks as $network => $label) {
      register_setting('gaia_social', "gaia_social_{$network}", array(
        'sanitize_callback' => 'esc_url_raw',
      ));
    }

    add_settings_section(
      'gaia_social_section',
      '',
      array($this, 'render_section_description'),
      'gaia-social'
    );

    foreach ($networks as $network => $label) {
      add_settings_field(
        "gaia_social_{$network}",
        $label,
        array($this, 'render_field'),
        'gaia-social',
        'gaia_social_section',
        array('network' => $network)
      );
    }
  }

  public function render_section_description() {
    echo '<p>' . __('Enter the URLs for your social media profiles. Leave empty to hide.', 'gaia') . '</p>';
  }

  public function render_field($args) {
    $network = $args['network'];
    $value = get_option("gaia_social_{$network}", '');
    
    printf(
      '<input type="url" name="gaia_social_%s" value="%s" class="gaia-admin__input" placeholder="https://">',
      esc_attr($network),
      esc_url($value)
    );
  }

  public function render() {
    ?>
    <div class="wrap gaia-admin">
      <h1><?php echo esc_html($this->get_title()); ?></h1>
      
      <div class="gaia-admin__card">
        <form method="post" action="options.php">
          <?php
          settings_fields('gaia_social');
          do_settings_sections('gaia-social');
          submit_button();
          ?>
        </form>
      </div>
    </div>
    <?php
  }
}