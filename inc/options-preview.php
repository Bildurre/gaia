<?php
/**
 * Previews on the Ajustes de Gaia pages:
 * - Visual choices: radio fields with the "gaia-visual-choices" wrapper class
 *   get a small drawing of each option (layouts, backgrounds, fonts, logos…).
 * - Live preview: a panel with the real site in an iframe. The unsaved
 *   values of the form are posted to the front end, where SCF serves them
 *   instead of the saved options for that request only (nothing is saved).
 */

if (!defined('ABSPATH')) {
  exit;
}

const GAIA_OPTIONS_PREVIEW_NONCE_ACTION = 'gaia_options_preview';
const GAIA_OPTIONS_PREVIEW_FRAME_NAME = 'gaia-options-preview-frame';

/**
 * True on the Ajustes de Gaia option subpages.
 */
function gaia_is_options_admin_page() {
  $current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

  return is_admin() && array_key_exists($current_page, gaia_get_options_subpages());
}

/* Visual choices ---------------------------------------------------------- */

/**
 * Empty spans used to draw the thumbnails (styled in assets/scss/admin.scss).
 */
function gaia_get_thumbnail_parts($part_classes) {
  return implode('', array_map(
    fn($part_class) => sprintf('<span class="gaia-thumb__%s"></span>', esc_attr($part_class)),
    $part_classes
  ));
}

function gaia_get_thumbnail_page_lines() {
  return gaia_get_thumbnail_parts(['title', 'line', 'line gaia-thumb__line--short']);
}

function gaia_get_thumbnail_header_bar() {
  return '<span class="gaia-thumb__bar">' . gaia_get_thumbnail_parts(['logo', 'dots']) . '</span>';
}

function gaia_get_thumbnail_mini_page($scheme) {
  return sprintf(
    '<span class="gaia-thumb__page scheme-%s">%s<span class="gaia-thumb__body">%s%s</span></span>',
    esc_attr($scheme),
    gaia_get_thumbnail_header_bar(),
    gaia_get_thumbnail_page_lines(),
    gaia_get_thumbnail_parts(['button'])
  );
}

function gaia_get_header_layout_thumbnail() {
  return '<span class="gaia-thumb__header">'
    . gaia_get_thumbnail_parts(['logo'])
    . '<span class="gaia-thumb__nav"><span></span><span></span><span></span></span>'
    . '<span class="gaia-thumb__tools"><span></span><span></span></span>'
    . '<span class="gaia-thumb__toggle"><span></span><span></span><span></span></span>'
    . '</span>'
    . '<span class="gaia-thumb__body">' . gaia_get_thumbnail_page_lines() . '</span>';
}

function gaia_get_footer_layout_thumbnail($footer_layout) {
  $footer_contents = [
    'brand-contact' => '<span class="gaia-thumb__row">' . gaia_get_thumbnail_parts(['logo', 'dots']) . '</span>'
      . gaia_get_thumbnail_parts(['separator'])
      . '<span class="gaia-thumb__row gaia-thumb__row--start">' . gaia_get_thumbnail_parts(['line gaia-thumb__line--tiny', 'line gaia-thumb__line--tiny', 'line gaia-thumb__line--tiny']) . '</span>'
      . gaia_get_thumbnail_parts(['line gaia-thumb__line--credits']),
    'columns' => '<span class="gaia-thumb__columns">'
      . '<span class="gaia-thumb__stack">' . gaia_get_thumbnail_parts(['logo', 'line gaia-thumb__line--tiny']) . '</span>'
      . '<span class="gaia-thumb__stack">' . gaia_get_thumbnail_parts(['line gaia-thumb__line--tiny', 'line gaia-thumb__line--tiny', 'line gaia-thumb__line--tiny']) . '</span>'
      . '<span class="gaia-thumb__stack">' . gaia_get_thumbnail_parts(['line gaia-thumb__line--tiny', 'line gaia-thumb__line--tiny']) . '</span>'
      . '</span>'
      . gaia_get_thumbnail_parts(['separator', 'line gaia-thumb__line--credits']),
    'minimal' => '<span class="gaia-thumb__row">' . gaia_get_thumbnail_parts(['logo', 'line gaia-thumb__line--tiny', 'dots']) . '</span>',
  ];

  return '<span class="gaia-thumb__body">' . gaia_get_thumbnail_page_lines() . '</span>'
    . '<span class="gaia-thumb__footer scheme-dark">' . ($footer_contents[$footer_layout] ?? '') . '</span>';
}

/**
 * Logo thumbnail with the image the site will print for that logo type.
 */
function gaia_get_logo_thumbnail($logo_type, $background) {
  if ($logo_type === 'none') {
    return '<span class="gaia-thumb__none"></span>';
  }

  if ($logo_type === 'text') {
    return sprintf('<span class="gaia-thumb__site-name">%s</span>', esc_html(get_bloginfo('name')));
  }

  $logo_urls = gaia_get_logo_urls($logo_type);

  return sprintf('<img class="gaia-thumb__logo-image" src="%s" alt="">', esc_url($logo_urls[$background]));
}

/**
 * Thumbnail markup (inner parts and modifier classes) for a choice.
 */
function gaia_get_choice_thumbnail($field_name, $choice_value) {
  $thumbnail_classes = ['gaia-thumb', 'gaia-thumb--' . $field_name, 'gaia-thumb--' . $field_name . '-' . $choice_value];
  $thumbnail_contents = '';

  switch ($field_name) {
    case 'colors_mode':
      $thumbnail_contents = $choice_value === 'auto'
        ? gaia_get_thumbnail_mini_page('light') . gaia_get_thumbnail_mini_page('dark')
        : gaia_get_thumbnail_mini_page($choice_value);
      break;

    case 'typography_font_heading':
    case 'typography_font_body':
      $thumbnail_classes[] = 'gaia-thumb--type';
      $thumbnail_contents = sprintf('<span class="gaia-thumb__sample gaia-font-%s">Aa</span>', esc_attr($choice_value));
      break;

    case 'typography_heading_weight':
      $thumbnail_classes[] = 'gaia-thumb--type';
      $thumbnail_contents = '<span class="gaia-thumb__sample">Aa</span>';
      break;

    case 'style_radius':
      $thumbnail_contents = '<span class="gaia-thumb__card">' . gaia_get_thumbnail_page_lines() . gaia_get_thumbnail_parts(['button']) . '</span>';
      break;

    case 'style_buttons':
      $thumbnail_contents = gaia_get_thumbnail_parts(['button', 'button gaia-thumb__button--outline']);
      break;

    case 'style_container_width':
      $thumbnail_contents = gaia_get_thumbnail_parts(['bar'])
        . '<span class="gaia-thumb__container">' . gaia_get_thumbnail_page_lines() . gaia_get_thumbnail_parts(['line']) . '</span>';
      break;

    case 'header_layout':
      $thumbnail_contents = gaia_get_header_layout_thumbnail();
      break;

    case 'header_mobile_menu':
      $thumbnail_contents = '<span class="gaia-thumb__phone">'
        . '<span class="gaia-thumb__bar">' . gaia_get_thumbnail_parts(['menu-icon', 'logo']) . '</span>'
        . '<span class="gaia-thumb__body">' . gaia_get_thumbnail_page_lines() . '</span>'
        . gaia_get_thumbnail_parts(['backdrop'])
        . '<span class="gaia-thumb__drawer">' . gaia_get_thumbnail_parts(['line gaia-thumb__line--short', 'line gaia-thumb__line--short', 'line gaia-thumb__line--short']) . '</span>'
        . '</span>';
      break;

    case 'footer_layout':
      $thumbnail_contents = gaia_get_footer_layout_thumbnail($choice_value);
      break;

    case 'footer_background':
      $thumbnail_contents = '<span class="gaia-thumb__body">' . gaia_get_thumbnail_page_lines() . '</span>'
        . sprintf('<span class="gaia-thumb__footer%s">', $choice_value === 'dark' ? ' scheme-dark' : '') . '<span class="gaia-thumb__row">' . gaia_get_thumbnail_parts(['logo', 'dots']) . '</span>' . gaia_get_thumbnail_parts(['line gaia-thumb__line--credits']) . '</span>';
      break;

    case 'extras_announcement_style':
      $thumbnail_contents = '<span class="gaia-thumb__announcement">' . gaia_get_thumbnail_parts(['line gaia-thumb__line--tiny']) . '</span>'
        . gaia_get_thumbnail_header_bar()
        . '<span class="gaia-thumb__body">' . gaia_get_thumbnail_page_lines() . '</span>';
      break;

    case 'header_logo':
    case 'header_logo_mobile':
      $thumbnail_classes[] = 'gaia-thumb--logo scheme-light';
      $thumbnail_contents = gaia_get_logo_thumbnail($choice_value, 'light');
      break;

    case 'footer_logo':
      $footer_background = gaia_get_option_value('footer_background', 'dark');
      $logo_background = $footer_background === 'dark' ? 'dark' : 'light';
      $thumbnail_classes[] = 'gaia-thumb--logo gaia-thumb--logo-on-' . $footer_background . ' scheme-' . $logo_background;
      $thumbnail_contents = gaia_get_logo_thumbnail($choice_value, $logo_background);
      break;
  }

  return sprintf('<span class="%s" aria-hidden="true">%s</span>', esc_attr(implode(' ', $thumbnail_classes)), $thumbnail_contents);
}

/**
 * Choice labels with their thumbnail. Labels like "Name: description" show
 * the description in a smaller line. Only when the field is rendered, so the
 * field group JSON keeps plain labels.
 */
function gaia_add_choice_thumbnails($field) {
  if (empty($field['wrapper']['class']) || !str_contains($field['wrapper']['class'], 'gaia-visual-choices') || empty($field['choices'])) {
    return $field;
  }

  foreach ($field['choices'] as $choice_value => $choice_label) {
    $label_parts = array_map('trim', explode(':', wp_strip_all_tags($choice_label), 2));
    $choice_description = isset($label_parts[1])
      ? sprintf('<span class="gaia-choice__description">%s</span>', esc_html($label_parts[1]))
      : '';

    $field['choices'][$choice_value] = sprintf(
      '<span class="gaia-choice">%s<span class="gaia-choice__name">%s</span>%s</span>',
      gaia_get_choice_thumbnail($field['_name'], (string) $choice_value),
      esc_html($label_parts[0]),
      $choice_description
    );
  }

  return $field;
}
add_filter('acf/prepare_field/type=radio', 'gaia_add_choice_thumbnails');

/* Admin assets and panel -------------------------------------------------- */

function gaia_enqueue_options_admin_assets() {
  if (!gaia_is_options_admin_page()) {
    return;
  }

  $theme_directory_uri = get_template_directory_uri();

  wp_enqueue_style('gaia-admin', $theme_directory_uri . '/assets/css/admin.css', [], GAIA_VERSION);
  wp_add_inline_style('gaia-admin', gaia_get_appearance_css('admin') . "\n" . gaia_get_font_choices_css());
  wp_enqueue_script('gaia-options-preview', $theme_directory_uri . '/assets/js/options-preview.js', [], GAIA_VERSION, [
    'strategy' => 'defer',
    'in_footer' => true,
  ]);
}
add_action('admin_enqueue_scripts', 'gaia_enqueue_options_admin_assets');

/**
 * @font-face and classes for the font choice thumbnails (Tipografía page).
 */
function gaia_get_font_choices_css() {
  $current_page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

  if ($current_page !== 'gaia-typography') {
    return '';
  }

  $fonts_directory_uri = get_template_directory_uri() . '/assets/fonts/';
  $css_rules = [];

  foreach (gaia_get_available_fonts() as $font_slug => $font_settings) {
    $css_rules[] = sprintf(
      "@font-face{font-family:'%s';src:url('%s') format('woff2');font-weight:%s;font-style:normal;font-display:swap}",
      $font_settings['family'],
      esc_url($fonts_directory_uri . $font_settings['files']['normal']),
      $font_settings['weight']
    );
    $css_rules[] = sprintf('.gaia-font-%s{font-family:%s}', sanitize_html_class($font_slug), gaia_get_font_stack($font_settings));
  }

  return implode("\n", $css_rules);
}

/**
 * Pages offered in the preview: home, latest post and a missing page.
 */
function gaia_get_options_preview_pages() {
  $home_url = function_exists('pll_home_url') ? pll_home_url(gaia_get_default_language_slug()) : home_url('/');
  $preview_pages = [$home_url => __('Inicio', 'gaia')];

  $latest_post_ids = get_posts([
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => 1,
    'fields' => 'ids',
  ]);

  if ($latest_post_ids) {
    $preview_pages[get_permalink($latest_post_ids[0])] = __('Última entrada', 'gaia');
  }

  $preview_pages[add_query_arg('p', PHP_INT_MAX, home_url('/'))] = __('Página no encontrada (404)', 'gaia');

  return $preview_pages;
}

function gaia_print_options_preview_panel() {
  if (!gaia_is_options_admin_page()) {
    return;
  }
  ?>
  <aside
    class="gaia-options-preview"
    data-options-preview
    data-preview-nonce="<?php echo esc_attr(wp_create_nonce(GAIA_OPTIONS_PREVIEW_NONCE_ACTION)); ?>"
    data-preview-frame-name="<?php echo esc_attr(GAIA_OPTIONS_PREVIEW_FRAME_NAME); ?>"
    data-preview-loading-text="<?php esc_attr_e('Actualizando…', 'gaia'); ?>"
    aria-label="<?php esc_attr_e('Vista previa', 'gaia'); ?>"
    hidden
  >
    <div class="gaia-options-preview__toolbar">
      <strong class="gaia-options-preview__title"><?php esc_html_e('Vista previa', 'gaia'); ?></strong>

      <label class="screen-reader-text" for="gaia-options-preview-page"><?php esc_html_e('Página', 'gaia'); ?></label>
      <select id="gaia-options-preview-page" class="gaia-options-preview__page" data-preview-page>
        <?php foreach (gaia_get_options_preview_pages() as $preview_url => $preview_label) : ?>
          <option value="<?php echo esc_url($preview_url); ?>"><?php echo esc_html($preview_label); ?></option>
        <?php endforeach; ?>
        <option value="" hidden><?php esc_html_e('Otra página', 'gaia'); ?></option>
      </select>

      <span class="gaia-options-preview__devices" role="group" aria-label="<?php esc_attr_e('Dispositivo', 'gaia'); ?>">
        <button type="button" class="button" data-preview-device="desktop" aria-pressed="true">
          <span class="dashicons dashicons-desktop" aria-hidden="true"></span>
          <span class="screen-reader-text"><?php esc_html_e('Escritorio', 'gaia'); ?></span>
        </button>
        <button type="button" class="button" data-preview-device="mobile" aria-pressed="false">
          <span class="dashicons dashicons-smartphone" aria-hidden="true"></span>
          <span class="screen-reader-text"><?php esc_html_e('Móvil', 'gaia'); ?></span>
        </button>
      </span>

      <span class="gaia-options-preview__status" data-preview-status aria-live="polite"></span>

      <button type="button" class="gaia-options-preview__close" data-preview-close>
        <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
        <span class="screen-reader-text"><?php esc_html_e('Cerrar vista previa', 'gaia'); ?></span>
      </button>
    </div>

    <div class="gaia-options-preview__viewport" data-preview-viewport>
      <iframe
        class="gaia-options-preview__frame"
        name="<?php echo esc_attr(GAIA_OPTIONS_PREVIEW_FRAME_NAME); ?>"
        title="<?php esc_attr_e('Vista previa del sitio', 'gaia'); ?>"
        data-preview-frame
      ></iframe>
    </div>

    <p class="gaia-options-preview__note"><?php esc_html_e('La vista previa muestra los cambios sin guardar. Pulsa «Actualizar» para guardarlos.', 'gaia'); ?></p>
  </aside>

  <template data-preview-open-template>
    <button type="button" class="page-title-action gaia-options-preview-toggle" data-preview-open aria-expanded="false">
      <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
      <?php esc_html_e('Vista previa', 'gaia'); ?>
    </button>
  </template>
  <?php
}
add_action('admin_footer', 'gaia_print_options_preview_panel');

/* Front end: serve the posted (unsaved) values ---------------------------- */

/**
 * True when the request comes from the preview panel of an administrator.
 * Only valid once the current user is known (after init).
 */
function gaia_is_options_preview_request() {
  static $is_preview_request = null;

  if ($is_preview_request === null) {
    $preview_nonce = isset($_POST['gaia_options_preview_nonce']) ? sanitize_key(wp_unslash($_POST['gaia_options_preview_nonce'])) : '';
    $is_preview_request = !is_admin()
      && $preview_nonce
      && wp_verify_nonce($preview_nonce, GAIA_OPTIONS_PREVIEW_NONCE_ACTION)
      && current_user_can('edit_theme_options');
  }

  return $is_preview_request;
}

/**
 * Saved option meta as SCF stores it (wp_options rows "options_{name}" and
 * "_options_{name}"), keyed by meta name.
 */
function gaia_get_stored_option_meta() {
  global $wpdb;

  $option_rows = $wpdb->get_results(
    "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'options\_%' OR option_name LIKE '\_options\_%'"
  );
  $stored_meta = [];

  foreach ($option_rows as $option_row) {
    $meta_name = str_starts_with($option_row->option_name, '_')
      ? '_' . substr($option_row->option_name, strlen('_options_'))
      : substr($option_row->option_name, strlen('options_'));
    $stored_meta[$meta_name] = maybe_unserialize($option_row->option_value);
  }

  return $stored_meta;
}

/**
 * Turns the posted form values into option meta and makes SCF return them
 * for the "options" post ID during this request (saved values fill the
 * fields of the other option pages).
 */
function gaia_setup_options_preview() {
  if (!function_exists('acf_setup_meta') || !gaia_is_options_preview_request()) {
    return;
  }

  $posted_values = isset($_POST['acf']) && is_array($_POST['acf']) ? wp_unslash($_POST['acf']) : [];
  $capture_post_id = 'gaia_options_preview';

  // Simulated save under a throwaway ID: SCF captures the meta instead of
  // writing it, and any delete it attempts is blocked.
  $block_deletes = fn($check, $post_id) => $post_id === $capture_post_id ? true : $check;
  add_filter('acf/pre_delete_metadata', $block_deletes, 1, 2);
  $preview_meta = acf_get_instance('ACF_Local_Meta')->capture($posted_values, $capture_post_id);
  remove_filter('acf/pre_delete_metadata', $block_deletes, 1);
  acf_reset_meta($capture_post_id);

  acf_setup_meta(array_merge(gaia_get_stored_option_meta(), $preview_meta), 'options');

  show_admin_bar(false);
  nocache_headers();
}
add_action('wp_loaded', 'gaia_setup_options_preview');
