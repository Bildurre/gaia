<?php
/**
 * Ajustes de Gaia pages: element thumbnails on the visual choices and live
 * samples of the elements each page changes.
 * - Visual choices: radio fields with the "gaia-visual-choices" wrapper class
 *   get a drawing of each option, centered on the element it changes (a
 *   corner, a button, the icons of a collection…).
 * - Samples: message fields with the "gaia-sample-field" wrapper class show
 *   real site components (header, footer, buttons…) inside a shadow root
 *   with main.css. assets/js/options-admin.js applies the unsaved values of
 *   the form through the same map as the site (inc/option-styles.php).
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * True on the Ajustes de Gaia option subpages.
 */
function gaia_is_options_admin_page() {
  return is_admin() && array_key_exists(gaia_get_current_options_page(), gaia_get_options_subpages());
}

function gaia_get_current_options_page() {
  return isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
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
 * Inline SVG icon (sanitized by the "acf" kses context, see below).
 */
function gaia_get_thumbnail_icon($icon_path) {
  return '<span class="gaia-thumb__icon">' . gaia_get_icon_svg($icon_path) . '</span>';
}

function gaia_get_thumbnail_button($button_text = '', $extra_class = '') {
  return sprintf(
    '<span class="gaia-thumb__sample-button %s">%s</span>',
    esc_attr($extra_class),
    esc_html($button_text !== '' ? $button_text : __('Botón', 'gaia'))
  );
}

/**
 * Small card (image + text lines) for the Contenidos thumbnails.
 */
function gaia_get_thumbnail_post_card($extra_class = '') {
  return sprintf(
    '<span class="gaia-thumb__post-card %s"><span class="gaia-thumb__post-image"></span><span class="gaia-thumb__post-text">%s</span></span>',
    esc_attr($extra_class),
    gaia_get_thumbnail_parts(['title', 'line', 'line gaia-thumb__line--short'])
  );
}

/**
 * Thumbnail markup (inner parts and modifier classes) for a choice.
 */
function gaia_get_choice_thumbnail($field_name, $choice_value) {
  // Contenidos fields are per post type ("content_<type>_card_layout")
  if (preg_match('/^content_.+_(card_layout|card_image_ratio|card_style|archive_layout|archive_pagination|archive_featured_layout)$/', $field_name, $field_name_parts)) {
    $field_name = 'content_' . $field_name_parts[1];
  }

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
      $thumbnail_classes[] = 'gaia-thumb--center';
      $thumbnail_contents = sprintf('<span class="gaia-thumb__sample gaia-font-%s">Aa</span>', esc_attr($choice_value));
      break;

    case 'typography_heading_weight':
    case 'typography_scale':
      $thumbnail_classes[] = 'gaia-thumb--center';
      $thumbnail_contents = '<span class="gaia-thumb__sample">Aa</span>';
      break;

    case 'typography_link_underline':
      $thumbnail_classes[] = 'gaia-thumb--center';
      $thumbnail_contents = sprintf('<span class="gaia-thumb__text">%s <span class="gaia-thumb__link">%s</span></span>', esc_html__('Texto con', 'gaia'), esc_html__('enlace', 'gaia'));
      break;

    case 'style_radius':
      $thumbnail_contents = '<span class="gaia-thumb__corner"></span>';
      break;

    case 'style_container_width':
      $thumbnail_contents = gaia_get_thumbnail_parts(['bar'])
        . '<span class="gaia-thumb__container">' . gaia_get_thumbnail_page_lines() . gaia_get_thumbnail_parts(['line']) . '</span>';
      break;

    case 'style_shadows':
      $thumbnail_classes[] = 'gaia-thumb--center';
      $thumbnail_contents = '<span class="gaia-thumb__card">' . gaia_get_thumbnail_parts(['title', 'line', 'line gaia-thumb__line--short']) . '</span>';
      break;

    case 'style_button_primary_type':
    case 'style_button_secondary_type':
    case 'style_buttons':
    case 'style_button_size':
    case 'style_button_weight':
    case 'style_button_border':
    case 'style_button_hover':
      $thumbnail_classes[] = 'gaia-thumb--center';
      $thumbnail_contents = gaia_get_thumbnail_button();
      break;

    case 'header_layout':
      $thumbnail_contents = gaia_get_header_layout_thumbnail();
      break;

    case 'header_background':
      $thumbnail_contents = '<span class="gaia-thumb__header">'
        . gaia_get_thumbnail_parts(['logo'])
        . '<span class="gaia-thumb__nav"><span></span><span></span><span></span></span>'
        . '</span>'
        . '<span class="gaia-thumb__body">' . gaia_get_thumbnail_page_lines() . '</span>';
      break;

    case 'header_menu_indicator':
      $thumbnail_classes[] = 'gaia-thumb--center';
      $thumbnail_contents = sprintf(
        '<span class="gaia-thumb__menu"><span>%s</span><span class="is-current">%s</span></span>',
        esc_html__('Inicio', 'gaia'),
        esc_html__('Agenda', 'gaia')
      );
      break;

    case 'header_shadow':
      $thumbnail_contents = gaia_get_thumbnail_header_bar() . '<span class="gaia-thumb__body">' . gaia_get_thumbnail_page_lines() . gaia_get_thumbnail_parts(['line']) . '</span>';
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

    case 'contact_icons_collection':
      $thumbnail_classes[] = 'gaia-thumb--center';
      foreach (['address', 'phone', 'email'] as $contact_type) {
        $thumbnail_contents .= '<span class="gaia-thumb__icon">' . gaia_get_contact_icon_svg($contact_type, $choice_value) . '</span>';
      }
      break;

    case 'contact_icons_shape':
    case 'contact_icons_fill':
      $thumbnail_classes[] = 'gaia-thumb--center';
      $thumbnail_contents = '<span class="gaia-thumb__icon">' . gaia_get_contact_icon_svg('phone') . '</span>';
      break;

    case 'social_icons_shape':
    case 'social_icons_fill':
    case 'social_icons_size':
      $thumbnail_classes[] = 'gaia-thumb--center';
      foreach (['instagram', 'telegram'] as $network_slug) {
        $thumbnail_contents .= '<span class="gaia-thumb__icon">' . gaia_get_social_icon_svg($network_slug) . '</span>';
      }
      break;

    case 'content_card_layout':
    case 'content_card_style':
      $thumbnail_classes[] = 'gaia-thumb--center';
      $thumbnail_contents = gaia_get_thumbnail_post_card();
      break;

    case 'content_card_image_ratio':
      $thumbnail_classes[] = 'gaia-thumb--center';
      $thumbnail_contents = '<span class="gaia-thumb__ratio"></span>';
      break;

    case 'content_archive_layout':
      $thumbnail_contents = '<span class="gaia-thumb__post-grid">' . str_repeat(gaia_get_thumbnail_post_card(), $choice_value === 'list' ? 3 : 6) . '</span>';
      break;

    case 'content_archive_pagination':
      $pagination_markup = [
        'numbers' => '<span class="gaia-thumb__pages"><span class="is-current">1</span><span>2</span><span>3</span><span>›</span></span>',
        'load_more' => gaia_get_thumbnail_button(__('Cargar más', 'gaia'), 'gaia-thumb__sample-button--outline'),
        'infinite' => '<span class="gaia-thumb__loading"><span></span><span></span><span></span></span>',
      ];
      $thumbnail_contents = '<span class="gaia-thumb__post-grid">' . str_repeat(gaia_get_thumbnail_post_card(), 3) . '</span>' . ($pagination_markup[$choice_value] ?? '');
      break;

    case 'content_archive_featured_layout':
      $thumbnail_contents = '<span class="gaia-thumb__post-grid">' . gaia_get_thumbnail_post_card('is-featured') . str_repeat(gaia_get_thumbnail_post_card(), 3) . '</span>';
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

/**
 * SCF escapes choice labels with the "acf" kses context (post tags): allow
 * the inline SVG of the icon thumbnails on these pages.
 */
function gaia_allow_svg_in_choice_labels($allowed_tags, $context) {
  if ($context !== 'acf' || !gaia_is_options_admin_page()) {
    return $allowed_tags;
  }

  $svg_attributes = array_fill_keys(['xmlns', 'viewbox', 'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'aria-hidden', 'focusable', 'role', 'class'], true);
  $shape_attributes = array_fill_keys(['d', 'x', 'y', 'x1', 'x2', 'y1', 'y2', 'cx', 'cy', 'r', 'rx', 'ry', 'width', 'height', 'points', 'fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'fill-rule', 'clip-rule', 'transform'], true);

  $allowed_tags['svg'] = $svg_attributes;
  foreach (['path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon', 'g'] as $shape_tag) {
    $allowed_tags[$shape_tag] = $shape_attributes;
  }

  return $allowed_tags;
}
add_filter('wp_kses_allowed_html', 'gaia_allow_svg_in_choice_labels', 10, 2);

/* Samples ----------------------------------------------------------------- */

/**
 * Sample markup of a sample field (key without the "field_gaia_" prefix).
 */
function gaia_get_sample_markup($sample_name) {
  ob_start();

  switch ($sample_name) {
    case 'colors_sample_brand':
      $brand_swatches = [
        ['--color-primary', '--color-on-primary', __('Principal', 'gaia')],
        ['--color-primary-light', '--color-light-text', __('Principal claro', 'gaia')],
        ['--color-primary-dark', '--color-white', __('Principal oscuro', 'gaia')],
        ['--color-secondary, var(--color-light-text)', '--color-on-secondary, var(--color-white)', __('Secundario', 'gaia')],
        ['--color-highlight', '--color-on-highlight', __('Realce', 'gaia')],
      ];
      echo '<div class="sample-swatches">';
      foreach ($brand_swatches as [$background_property, $text_property, $swatch_label]) {
        printf(
          '<div class="sample-swatch"><div class="sample-swatch__color" style="background-color: var(%1$s); color: var(%2$s);">Aa</div><span class="sample-swatch__label">%3$s</span></div>',
          esc_attr($background_property),
          esc_attr($text_property),
          esc_html($swatch_label)
        );
      }
      echo '</div>';
      break;

    case 'colors_sample_states':
      printf('<div class="sample-padding"><div class="form-notice form-notice--success"><p>%s</p></div>', esc_html__('Los cambios se han guardado.', 'gaia'));
      printf('<div class="form-notice form-notice--error"><p>%s</p></div></div>', esc_html__('Revisa los campos marcados.', 'gaia'));
      break;

    case 'colors_sample_light':
    case 'colors_sample_dark':
      $scheme = $sample_name === 'colors_sample_light' ? 'light' : 'dark';
      ?>
      <div class="scheme-<?php echo esc_attr($scheme); ?> sample-padding sample-scheme">
        <h3><?php esc_html_e('Título de ejemplo', 'gaia'); ?></h3>
        <p><?php esc_html_e('Texto de lectura con un', 'gaia'); ?> <a href="#"><?php esc_html_e('enlace', 'gaia'); ?></a>. <span class="sample-muted"><?php esc_html_e('Texto secundario.', 'gaia'); ?></span></p>
        <div class="sample-surface">
          <span class="has-accent-color sample-eyebrow"><?php esc_html_e('Acento', 'gaia'); ?></span>
          <input type="text" value="<?php esc_attr_e('Campo de formulario', 'gaia'); ?>" aria-label="<?php esc_attr_e('Campo de formulario', 'gaia'); ?>">
          <span class="button"><?php esc_html_e('Botón', 'gaia'); ?></span>
        </div>
      </div>
      <?php
      break;

    case 'colors_sample_buttons':
    case 'style_sample_buttons':
      foreach (['light', 'dark'] as $scheme) {
        ?>
        <div class="scheme-<?php echo esc_attr($scheme); ?> sample-padding sample-buttons">
          <?php foreach (['' => __('Principal', 'gaia'), 'button--secondary' => __('Secundario', 'gaia')] as $button_class => $button_label) : ?>
            <span class="button <?php echo esc_attr($button_class); ?>"><?php echo esc_html($button_label); ?></span>
            <span class="button is-hover <?php echo esc_attr($button_class); ?>"><?php echo esc_html($button_label); ?></span>
          <?php endforeach; ?>
        </div>
        <?php
      }
      printf('<p class="sample-caption">%s</p>', esc_html__('Cada botón aparece normal y al pasar el ratón.', 'gaia'));
      break;

    case 'typography_sample_text':
      ?>
      <div class="sample-padding sample-typography">
        <h2><?php esc_html_e('Energía compartida para el barrio', 'gaia'); ?></h2>
        <h4><?php esc_html_e('Un subtítulo más pequeño', 'gaia'); ?></h4>
        <p><?php esc_html_e('Así se leen los textos largos de la web, con un', 'gaia'); ?> <a href="#"><?php esc_html_e('enlace dentro del texto', 'gaia'); ?></a> <?php esc_html_e('y algunas palabras más para ver el interlineado.', 'gaia'); ?></p>
      </div>
      <?php
      break;

    case 'header_sample':
      echo '<div class="sample-header">';
      get_template_part('template-parts/header/site-header', null, ['is_sample' => true]);
      echo '</div>';
      break;

    case 'footer_sample':
      $current_layout = gaia_get_footer_settings()['layout'];
      echo '<div data-sample-variants="footer_layout">';
      foreach (['brand-contact', 'columns', 'minimal'] as $footer_layout) {
        printf('<div data-variant-value="%s"%s>', esc_attr($footer_layout), $footer_layout === $current_layout ? '' : ' hidden');
        get_template_part('template-parts/footer/site-footer', null, ['layout' => $footer_layout, 'is_sample' => true]);
        echo '</div>';
      }
      echo '</div>';
      break;

    case 'contact_sample_icons':
      // On the footer background, without a footer layout
      $footer_area_classes = array_merge(
        ['site-footer'],
        gaia_get_background_classes('footer_background', gaia_get_footer_settings()['background']),
        gaia_get_option_classes(['footer_text_color', 'footer_accent_color'])
      );
      printf('<div class="%s"><div class="container sample-contact">', esc_attr(implode(' ', $footer_area_classes)));
      get_template_part('template-parts/contact-details', null, ['class' => 'site-footer__contact', 'is_sample' => true]);
      get_template_part('template-parts/social-links');
      echo '</div></div>';
      break;

    case (preg_match('/^content_(.+)_sample$/', $sample_name) === 1):
      $sample_post_type = '';
      foreach (array_keys(gaia_get_content_post_types()) as $content_post_type) {
        if (gaia_get_content_option_prefix($content_post_type) . 'sample' === $sample_name) {
          $sample_post_type = $content_post_type;
        }
      }

      // The latest item with an image, else the latest one
      $sample_query = ['post_type' => $sample_post_type, 'numberposts' => 1, 'post_status' => 'publish'];
      $sample_posts = $sample_post_type ? get_posts($sample_query + ['meta_key' => '_thumbnail_id']) : [];
      $sample_posts = $sample_posts ?: ($sample_post_type ? get_posts($sample_query) : []);

      if (!$sample_posts) {
        printf('<p class="sample-padding sample-caption">%s</p>', esc_html__('Todavía no hay contenidos publicados de este tipo.', 'gaia'));
        break;
      }

      echo '<div class="sample-padding"><div class="post-grid post-grid--columns-2 sample-card">';
      get_template_part('template-parts/post-card', null, ['post' => $sample_posts[0], 'is_sample' => true]);
      echo '</div></div>';
      break;

    case 'extras_sample_announcement':
      get_template_part('template-parts/announcement-bar', null, ['is_sample' => true]);
      break;
  }

  return (string) ob_get_clean();
}

/**
 * Prints a sample inside a shadow root with the site stylesheet, so admin
 * and site styles do not mix. Custom properties (the design options) come
 * from the page and are inherited into the shadow root.
 */
function gaia_render_sample_field($field) {
  $sample_name = str_replace('field_gaia_', '', $field['key']);
  $sample_markup = gaia_get_sample_markup($sample_name);

  if ($sample_markup === '') {
    return;
  }
  ?>
  <div class="gaia-sample" data-gaia-sample="<?php echo esc_attr($sample_name); ?>">
    <template shadowrootmode="open">
      <link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . '/assets/css/main.css?ver=' . GAIA_VERSION); ?>">
      <link rel="stylesheet" href="<?php echo esc_url(get_template_directory_uri() . '/assets/css/admin-sample.css?ver=' . GAIA_VERSION); ?>">
      <div class="sample-canvas"><?php echo $sample_markup; ?></div>
    </template>
  </div>
  <?php
}

function gaia_register_sample_field_renderers() {
  foreach (['colors_sample_brand', 'colors_sample_states', 'colors_sample_light', 'colors_sample_dark', 'colors_sample_buttons', 'typography_sample_text', 'style_sample_buttons', 'header_sample', 'footer_sample', 'contact_sample_icons', 'extras_sample_announcement'] as $sample_name) {
    add_action('acf/render_field/key=field_gaia_' . $sample_name, 'gaia_render_sample_field');
  }
}
add_action('acf/init', 'gaia_register_sample_field_renderers');

/* Admin assets ------------------------------------------------------------ */

/**
 * Saved values of the mapped options, for the options that are not on the
 * current page (the samples need them too).
 */
function gaia_get_saved_style_values() {
  $saved_values = [];

  foreach (array_keys(gaia_get_option_style_map()) as $option_name) {
    $saved_values[$option_name] = gaia_get_style_option_value($option_name);
  }

  return $saved_values;
}

function gaia_enqueue_options_admin_assets() {
  if (!gaia_is_options_admin_page()) {
    return;
  }

  $theme_directory_uri = get_template_directory_uri();

  // The design options are applied by options-admin.js from the form and
  // the saved values; only the fonts come from PHP here.
  wp_enqueue_style('gaia-admin', $theme_directory_uri . '/assets/css/admin.css', [], GAIA_VERSION);
  wp_add_inline_style('gaia-admin', gaia_get_appearance_css('admin', array_keys(gaia_get_option_style_map())) . "\n" . gaia_get_font_choices_css());

  wp_enqueue_script('gaia-options-admin', $theme_directory_uri . '/assets/js/options-admin.js', [], GAIA_VERSION, [
    'strategy' => 'defer',
    'in_footer' => true,
  ]);
  wp_add_inline_script('gaia-options-admin', 'window.gaiaOptionStyles = ' . wp_json_encode([
    'map' => gaia_get_option_style_map(),
    'savedValues' => gaia_get_saved_style_values(),
    'defaultPrimaryColor' => GAIA_DEFAULT_PRIMARY_COLOR,
    'contrastTextColors' => ['light' => GAIA_CONTRAST_LIGHT_TEXT, 'dark' => GAIA_CONTRAST_DARK_TEXT],
  ]) . ';', 'before');
}
add_action('admin_enqueue_scripts', 'gaia_enqueue_options_admin_assets');

/**
 * @font-face for every bundled font and the classes of the font choice
 * thumbnails, so the samples can switch fonts without reloading.
 */
function gaia_get_font_choices_css() {
  $fonts_directory_uri = get_template_directory_uri() . '/assets/fonts/';
  $css_rules = [];

  foreach (gaia_get_available_fonts() as $font_slug => $font_settings) {
    foreach ($font_settings['files'] as $font_style => $font_file) {
      $css_rules[] = sprintf(
        "@font-face{font-family:'%s';src:url('%s') format('woff2');font-weight:%s;font-style:%s;font-display:swap}",
        $font_settings['family'],
        esc_url($fonts_directory_uri . $font_file),
        $font_settings['weight'],
        $font_style
      );
    }

    $css_rules[] = sprintf('.gaia-font-%s{font-family:%s}', sanitize_html_class($font_slug), gaia_get_font_stack($font_settings));
  }

  return implode("\n", $css_rules);
}
