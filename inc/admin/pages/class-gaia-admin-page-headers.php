<?php
/**
 * Gaia Admin - Headers Page
 */

if (!defined('ABSPATH')) exit;

class Gaia_Admin_Page_Headers {

  private $settings = array(
    'gaia_header_style',
    'gaia_header_content_width',
    'gaia_header_bg_color',
    'gaia_header_text_color',
    'gaia_header_current_color',
    'gaia_header_show_social',
    'gaia_header_show_search',
    'gaia_header_is_sticky',
    'gaia_header_hide_on_scroll',
  );

  public function __construct() {
    add_action('admin_init', array($this, 'register_settings'));
  }

  public function get_title() {
    return __('Header Settings', 'gaia');
  }

  public function get_menu_title() {
    return __('Headers', 'gaia');
  }

  public function get_slug() {
    return 'gaia-headers';
  }

  public function register_settings() {
    // Register all settings
    foreach ($this->settings as $setting) {
      $sanitize = 'sanitize_text_field';
      
      if (strpos($setting, '_show_') !== false || strpos($setting, '_is_') !== false || strpos($setting, '_hide_') !== false) {
        $sanitize = array($this, 'sanitize_checkbox');
      }
      
      register_setting('gaia_headers', $setting, array(
        'sanitize_callback' => $sanitize,
      ));
    }

    // Layout Section
    add_settings_section(
      'gaia_headers_layout',
      __('Layout', 'gaia'),
      '__return_false',
      'gaia-headers'
    );

    add_settings_field(
      'gaia_header_style',
      __('Header Style', 'gaia'),
      array($this, 'render_style_field'),
      'gaia-headers',
      'gaia_headers_layout'
    );

    add_settings_field(
      'gaia_header_content_width',
      __('Content Width', 'gaia'),
      array($this, 'render_content_width_field'),
      'gaia-headers',
      'gaia_headers_layout'
    );

    // Colors Section
    add_settings_section(
      'gaia_headers_colors',
      __('Colors', 'gaia'),
      array($this, 'render_colors_description'),
      'gaia-headers'
    );

    add_settings_field(
      'gaia_header_bg_color',
      __('Background', 'gaia'),
      array($this, 'render_color_field'),
      'gaia-headers',
      'gaia_headers_colors',
      array('field' => 'gaia_header_bg_color', 'default' => 'primary')
    );

    add_settings_field(
      'gaia_header_text_color',
      __('Text', 'gaia'),
      array($this, 'render_color_field'),
      'gaia-headers',
      'gaia_headers_colors',
      array('field' => 'gaia_header_text_color', 'default' => 'primary-foreground')
    );

    add_settings_field(
      'gaia_header_current_color',
      __('Current Item', 'gaia'),
      array($this, 'render_color_field'),
      'gaia-headers',
      'gaia_headers_colors',
      array('field' => 'gaia_header_current_color', 'default' => 'secondary')
    );

    // Elements Section
    add_settings_section(
      'gaia_headers_elements',
      __('Elements', 'gaia'),
      '__return_false',
      'gaia-headers'
    );

    add_settings_field(
      'gaia_header_show_social',
      __('Show Social Links', 'gaia'),
      array($this, 'render_checkbox_field'),
      'gaia-headers',
      'gaia_headers_elements',
      array('field' => 'gaia_header_show_social', 'default' => true)
    );

    add_settings_field(
      'gaia_header_show_search',
      __('Show Search', 'gaia'),
      array($this, 'render_checkbox_field'),
      'gaia-headers',
      'gaia_headers_elements',
      array('field' => 'gaia_header_show_search', 'default' => false)
    );

    // Behavior Section
    add_settings_section(
      'gaia_headers_behavior',
      __('Behavior', 'gaia'),
      '__return_false',
      'gaia-headers'
    );

    add_settings_field(
      'gaia_header_is_sticky',
      __('Sticky Header', 'gaia'),
      array($this, 'render_checkbox_field'),
      'gaia-headers',
      'gaia_headers_behavior',
      array(
        'field' => 'gaia_header_is_sticky',
        'default' => false,
        'description' => __('Header stays fixed at the top while scrolling.', 'gaia'),
      )
    );

    add_settings_field(
      'gaia_header_hide_on_scroll',
      __('Hide on Scroll Down', 'gaia'),
      array($this, 'render_checkbox_field'),
      'gaia-headers',
      'gaia_headers_behavior',
      array(
        'field' => 'gaia_header_hide_on_scroll',
        'default' => false,
        'description' => __('Header hides when scrolling down and reappears when scrolling up. Requires Sticky Header.', 'gaia'),
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
   * Get theme.json colors
   */
  private function get_theme_colors() {
    $colors = array();
    
    $theme_json_path = get_template_directory() . '/theme.json';
    if (file_exists($theme_json_path)) {
      $theme_json = json_decode(file_get_contents($theme_json_path), true);
      
      if (isset($theme_json['settings']['color']['palette'])) {
        foreach ($theme_json['settings']['color']['palette'] as $color) {
          $colors[$color['slug']] = array(
            'name' => $color['name'] ?? $color['slug'],
            'color' => $color['color'],
          );
        }
      }
    }
    
    return $colors;
  }

  /**
   * Get theme.json layout sizes
   */
  private function get_layout_sizes() {
    $sizes = array(
      'full' => array(
        'name' => __('Full Width', 'gaia'),
        'value' => '100%',
      ),
    );
    
    $theme_json_path = get_template_directory() . '/theme.json';
    if (file_exists($theme_json_path)) {
      $theme_json = json_decode(file_get_contents($theme_json_path), true);
      
      if (isset($theme_json['settings']['layout'])) {
        $layout = $theme_json['settings']['layout'];
        
        if (isset($layout['wideSize'])) {
          $sizes['wide'] = array(
            'name' => __('Wide', 'gaia') . ' (' . $layout['wideSize'] . ')',
            'value' => $layout['wideSize'],
          );
        }
        
        if (isset($layout['contentSize'])) {
          $sizes['content'] = array(
            'name' => __('Content', 'gaia') . ' (' . $layout['contentSize'] . ')',
            'value' => $layout['contentSize'],
          );
        }
      }
    }
    
    return $sizes;
  }

  /**
   * Render colors section description
   */
  public function render_colors_description() {
    echo '<p class="description">' . __('Colors are based on your theme.json palette and will adapt to style variations.', 'gaia') . '</p>';
  }

  /**
   * Render style field
   */
  public function render_style_field() {
    $value = get_option('gaia_header_style', 'default');
    $styles = gaia_get_header_styles();
    
    echo '<select name="gaia_header_style" class="gaia-admin__select">';
    foreach ($styles as $key => $label) {
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
   * Render content width field
   */
  public function render_content_width_field() {
    $value = get_option('gaia_header_content_width', 'wide');
    $sizes = $this->get_layout_sizes();
    
    echo '<select name="gaia_header_content_width" class="gaia-admin__select">';
    foreach ($sizes as $key => $size_data) {
      printf(
        '<option value="%s" %s>%s</option>',
        esc_attr($key),
        selected($value, $key, false),
        esc_html($size_data['name'])
      );
    }
    echo '</select>';
    echo '<p class="description">' . __('Maximum width of the header content.', 'gaia') . '</p>';
  }

  /**
   * Render color field as circles
   */
  public function render_color_field($args) {
    $field = $args['field'];
    $default = $args['default'] ?? '';
    $value = get_option($field, $default);
    $colors = $this->get_theme_colors();
    
    echo '<div class="gaia-admin__color-picker">';
    foreach ($colors as $slug => $color_data) {
      printf(
        '<label class="gaia-admin__color-option">
          <input type="radio" name="%s" value="%s" %s>
          <span class="gaia-admin__color-circle" style="background-color: %s;" title="%s"></span>
        </label>',
        esc_attr($field),
        esc_attr($slug),
        checked($value, $slug, false),
        esc_attr($color_data['color']),
        esc_attr($color_data['name'])
      );
    }
    echo '</div>';
  }

  /**
   * Render checkbox field
   */
  public function render_checkbox_field($args) {
    $field = $args['field'];
    $default = $args['default'] ?? false;
    $description = $args['description'] ?? '';
    
    $value = get_option($field);
    
    // Handle default value for new installs
    if ($value === false && $default === true) {
      $checked = true;
    } else {
      $checked = ($value === '1');
    }
    
    printf(
      '<label><input type="checkbox" name="%s" value="1" %s> %s</label>',
      esc_attr($field),
      checked($checked, true, false),
      esc_html__('Enabled', 'gaia')
    );
    
    if ($description) {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
  }

  public function render() {
    ?>
    <div class="wrap gaia-admin">
      <h1><?php echo esc_html($this->get_title()); ?></h1>
      
      <form method="post" action="options.php">
        <?php settings_fields('gaia_headers'); ?>
        
        <div class="gaia-admin__grid">
          <div class="gaia-admin__card">
            <h2><?php _e('Layout', 'gaia'); ?></h2>
            <table class="form-table" role="presentation">
              <?php do_settings_fields('gaia-headers', 'gaia_headers_layout'); ?>
            </table>
          </div>

          <div class="gaia-admin__card">
            <h2><?php _e('Colors', 'gaia'); ?></h2>
            <?php $this->render_colors_description(); ?>
            <table class="form-table" role="presentation">
              <?php do_settings_fields('gaia-headers', 'gaia_headers_colors'); ?>
            </table>
          </div>

          <div class="gaia-admin__card">
            <h2><?php _e('Elements', 'gaia'); ?></h2>
            <table class="form-table" role="presentation">
              <?php do_settings_fields('gaia-headers', 'gaia_headers_elements'); ?>
            </table>
          </div>

          <div class="gaia-admin__card">
            <h2><?php _e('Behavior', 'gaia'); ?></h2>
            <table class="form-table" role="presentation">
              <?php do_settings_fields('gaia-headers', 'gaia_headers_behavior'); ?>
            </table>
          </div>
        </div>

        <?php submit_button(); ?>
      </form>
    </div>
    <?php
  }
}