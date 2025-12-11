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
    // URLs de redes sociales
    $networks = gaia_get_social_networks();
    
    foreach ($networks as $network => $label) {
      register_setting('gaia_social', "gaia_social_{$network}", array(
        'sanitize_callback' => 'esc_url_raw',
      ));
    }

    // Opciones de estilo
    register_setting('gaia_social', 'gaia_social_shape', array(
      'sanitize_callback' => 'sanitize_text_field',
      'default' => 'none',
    ));

    register_setting('gaia_social', 'gaia_social_has_border', array(
      'sanitize_callback' => array($this, 'sanitize_checkbox'),
      'default' => '0',
    ));

    register_setting('gaia_social', 'gaia_social_has_background', array(
      'sanitize_callback' => array($this, 'sanitize_checkbox'),
      'default' => '0',
    ));

    register_setting('gaia_social', 'gaia_social_brand_colors', array(
      'sanitize_callback' => array($this, 'sanitize_checkbox'),
      'default' => '0',
    ));

    // Sección de URLs
    add_settings_section(
      'gaia_social_urls',
      __('Social Media URLs', 'gaia'),
      array($this, 'render_urls_description'),
      'gaia-social'
    );

    foreach ($networks as $network => $label) {
      add_settings_field(
        "gaia_social_{$network}",
        $label,
        array($this, 'render_url_field'),
        'gaia-social',
        'gaia_social_urls',
        array('network' => $network)
      );
    }

    // Sección de estilos
    add_settings_section(
      'gaia_social_style',
      __('Style Options', 'gaia'),
      array($this, 'render_style_description'),
      'gaia-social-style'
    );

    add_settings_field(
      'gaia_social_shape',
      __('Icon Shape', 'gaia'),
      array($this, 'render_shape_field'),
      'gaia-social-style',
      'gaia_social_style'
    );

    add_settings_field(
      'gaia_social_has_border',
      __('Border', 'gaia'),
      array($this, 'render_checkbox_field'),
      'gaia-social-style',
      'gaia_social_style',
      array(
        'field' => 'gaia_social_has_border',
        'label' => __('Show border around icons', 'gaia'),
      )
    );

    add_settings_field(
      'gaia_social_has_background',
      __('Background', 'gaia'),
      array($this, 'render_checkbox_field'),
      'gaia-social-style',
      'gaia_social_style',
      array(
        'field' => 'gaia_social_has_background',
        'label' => __('Show background fill', 'gaia'),
      )
    );

    add_settings_field(
      'gaia_social_brand_colors',
      __('Colors', 'gaia'),
      array($this, 'render_checkbox_field'),
      'gaia-social-style',
      'gaia_social_style',
      array(
        'field' => 'gaia_social_brand_colors',
        'label' => __('Use brand colors (otherwise uses header colors)', 'gaia'),
      )
    );
  }

  /**
   * Sanitize checkbox
   */
  public function sanitize_checkbox($value) {
    return ($value === '1' || $value === 'on' || $value === true) ? '1' : '0';
  }

  /**
   * Render URLs section description
   */
  public function render_urls_description() {
    echo '<p>' . __('Enter the URLs for your social media profiles. Leave empty to hide.', 'gaia') . '</p>';
  }

  /**
   * Render style section description
   */
  public function render_style_description() {
    echo '<p>' . __('Customize how social icons appear in your header.', 'gaia') . '</p>';
  }

  /**
   * Render URL field
   */
  public function render_url_field($args) {
    $network = $args['network'];
    $value = get_option("gaia_social_{$network}", '');
    
    printf(
      '<input type="url" name="gaia_social_%s" value="%s" class="gaia-admin__input" placeholder="https://">',
      esc_attr($network),
      esc_url($value)
    );
  }

  /**
   * Render shape field
   */
  public function render_shape_field() {
    $value = get_option('gaia_social_shape', 'none');
    $shapes = array(
      'none' => __('No shape (icon only)', 'gaia'),
      'circle' => __('Circle', 'gaia'),
      'square' => __('Square', 'gaia'),
      'rounded' => __('Rounded square', 'gaia'),
    );

    echo '<select name="gaia_social_shape" class="gaia-admin__select">';
    foreach ($shapes as $key => $label) {
      printf(
        '<option value="%s" %s>%s</option>',
        esc_attr($key),
        selected($value, $key, false),
        esc_html($label)
      );
    }
    echo '</select>';
  }

  /**
   * Render checkbox field
   */
  public function render_checkbox_field($args) {
    $field = $args['field'];
    $label = $args['label'] ?? __('Enabled', 'gaia');
    $value = get_option($field, '0');
    
    printf(
      '<label><input type="checkbox" name="%s" value="1" %s> %s</label>',
      esc_attr($field),
      checked($value, '1', false),
      esc_html($label)
    );
  }

  public function render() {
    ?>
    <div class="wrap gaia-admin">
      <h1><?php echo esc_html($this->get_title()); ?></h1>
      
      <form method="post" action="options.php">
        <?php settings_fields('gaia_social'); ?>
        
        <div class="gaia-admin__grid">
          <div class="gaia-admin__card">
            <h2><?php _e('Social Media URLs', 'gaia'); ?></h2>
            <?php $this->render_urls_description(); ?>
            <table class="form-table" role="presentation">
              <?php do_settings_fields('gaia-social', 'gaia_social_urls'); ?>
            </table>
          </div>

          <div class="gaia-admin__card">
            <h2><?php _e('Style Options', 'gaia'); ?></h2>
            <?php $this->render_style_description(); ?>
            <table class="form-table" role="presentation">
              <?php do_settings_fields('gaia-social-style', 'gaia_social_style'); ?>
            </table>
          </div>
        </div>

        <?php submit_button(); ?>
      </form>
    </div>
    <?php
  }
}