<?php
/**
 * Content types (Ajustes de Gaia > Contenidos): card and archive settings
 * for each public post type (posts and the types registered by plugins).
 * The field group is built in PHP because the post types are only known at
 * run time: one tab per post type, fields "content_<type>_<setting>".
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Post types with cards and archives, as [slug => label].
 */
function gaia_get_content_post_types() {
  $post_types = [];

  foreach (get_post_types(['public' => true], 'objects') as $post_type) {
    if (in_array($post_type->name, ['page', 'attachment'], true)) {
      continue;
    }

    $post_types[$post_type->name] = $post_type->labels->name;
  }

  return $post_types;
}

/**
 * Option name prefix of a post type ("content_post_", "content_event_"…).
 */
function gaia_get_content_option_prefix($post_type) {
  return 'content_' . str_replace('-', '_', sanitize_key($post_type)) . '_';
}

function gaia_get_card_field_choices() {
  return [
    'image' => __('Imagen', 'gaia'),
    'date' => __('Fecha', 'gaia'),
    'terms' => __('Categorías', 'gaia'),
    'excerpt' => __('Extracto', 'gaia'),
    'author' => __('Autor', 'gaia'),
    'reading_time' => __('Tiempo de lectura', 'gaia'),
    'more' => __('Enlace «Leer más»', 'gaia'),
  ];
}

/**
 * Card settings of a post type, with defaults.
 */
function gaia_get_card_settings($post_type) {
  static $cached_settings = [];

  if (isset($cached_settings[$post_type])) {
    return $cached_settings[$post_type];
  }

  $option_prefix = gaia_get_content_option_prefix($post_type);
  $card_fields = gaia_get_option_field($option_prefix . 'card_fields');
  $layout = gaia_get_option_value($option_prefix . 'card_layout', 'top');
  $image_ratio = gaia_get_option_value($option_prefix . 'card_image_ratio', 'landscape');
  $card_style = gaia_get_option_value($option_prefix . 'card_style', 'surface');

  $cached_settings[$post_type] = apply_filters('gaia_card_settings', [
    'layout' => in_array($layout, ['top', 'side', 'overlay', 'text'], true) ? $layout : 'top',
    'image_ratio' => in_array($image_ratio, ['landscape', 'square', 'portrait', 'wide'], true) ? $image_ratio : 'landscape',
    'style' => in_array($card_style, ['surface', 'bordered', 'plain'], true) ? $card_style : 'surface',
    'fields' => is_array($card_fields) ? $card_fields : ['image', 'date', 'terms', 'excerpt', 'more'],
    'excerpt_length' => max(5, (int) gaia_get_option_value($option_prefix . 'card_excerpt_length', 22)),
  ], $post_type);

  return $cached_settings[$post_type];
}

/**
 * Archive settings of a post type, with defaults.
 */
function gaia_get_archive_settings($post_type) {
  $option_prefix = gaia_get_content_option_prefix($post_type);
  $layout = gaia_get_option_value($option_prefix . 'archive_layout', 'grid');
  $columns = (string) gaia_get_option_value($option_prefix . 'archive_columns', '3');
  $pagination = gaia_get_option_value($option_prefix . 'archive_pagination', 'numbers');
  $featured_layout = gaia_get_option_value($option_prefix . 'archive_featured_layout', 'side');

  return apply_filters('gaia_archive_settings', [
    'layout' => in_array($layout, ['grid', 'list'], true) ? $layout : 'grid',
    'columns' => in_array($columns, ['2', '3', '4'], true) ? $columns : '3',
    'posts_per_page' => max(1, (int) gaia_get_option_value($option_prefix . 'archive_per_page', 9)),
    'pagination' => in_array($pagination, ['numbers', 'load_more', 'infinite'], true) ? $pagination : 'numbers',
    'has_featured' => (bool) gaia_get_option_value($option_prefix . 'archive_featured', false),
    'featured_layout' => in_array($featured_layout, ['side', 'overlay', 'top'], true) ? $featured_layout : 'side',
    'shows_filters' => (bool) gaia_get_option_value($option_prefix . 'archive_filters', true),
  ], $post_type);
}

/**
 * Post type whose archive settings apply to the current listing.
 */
function gaia_get_archive_post_type() {
  if (is_post_type_archive()) {
    $queried_post_type = get_query_var('post_type');

    return is_array($queried_post_type) ? (string) reset($queried_post_type) : (string) $queried_post_type;
  }

  if (is_category() || is_tag() || is_tax()) {
    $queried_taxonomy = get_taxonomy(get_queried_object()->taxonomy ?? '');

    return $queried_taxonomy->object_type[0] ?? 'post';
  }

  return 'post';
}

/**
 * Main taxonomy of a post type, used for card terms and archive filters:
 * "category" for posts, else the first public hierarchical taxonomy (or
 * the first public one).
 */
function gaia_get_main_taxonomy($post_type) {
  if ($post_type === 'post') {
    return 'category';
  }

  $taxonomies = get_object_taxonomies($post_type, 'objects');
  $public_taxonomies = array_filter($taxonomies, fn($taxonomy) => $taxonomy->public && $taxonomy->show_ui && $taxonomy->name !== 'language' && $taxonomy->name !== 'post_translations');
  $hierarchical_taxonomies = array_filter($public_taxonomies, fn($taxonomy) => $taxonomy->hierarchical);

  return array_key_first($hierarchical_taxonomies ?: $public_taxonomies) ?? '';
}

/**
 * Posts per page of the main archive queries.
 */
function gaia_set_archive_posts_per_page($query) {
  if (is_admin() || !$query->is_main_query() || !($query->is_home() || $query->is_archive() || $query->is_search())) {
    return;
  }

  // The conditional tags read the global query: wait until it is this one
  $archive_post_type = 'post';

  if ($query->is_post_type_archive()) {
    $queried_post_type = $query->get('post_type');
    $archive_post_type = is_array($queried_post_type) ? (string) reset($queried_post_type) : (string) $queried_post_type;
  } elseif ($query->is_tax() || $query->is_category() || $query->is_tag()) {
    $tax_query = $query->tax_query->queries[0]['taxonomy'] ?? '';
    $archive_post_type = get_taxonomy($tax_query)->object_type[0] ?? 'post';
  }

  $query->set('posts_per_page', gaia_get_archive_settings($archive_post_type)['posts_per_page']);
}
add_action('pre_get_posts', 'gaia_set_archive_posts_per_page');

/**
 * Reading time in minutes (200 words per minute).
 */
function gaia_get_reading_time($post) {
  $word_count = str_word_count(wp_strip_all_tags(strip_shortcodes((string) get_post_field('post_content', $post))));

  return max(1, (int) ceil($word_count / 200));
}

/* Options page fields ----------------------------------------------------- */

function gaia_register_content_option_fields() {
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  $visual = fn($key, $name, $label, $choices, $default_value, $instructions = '', $width = '') => [
    'key' => $key, 'label' => $label, 'name' => $name, 'type' => 'radio', 'instructions' => $instructions,
    'wrapper' => ['width' => $width, 'class' => 'gaia-visual-choices', 'id' => ''], 'choices' => $choices,
    'default_value' => $default_value, 'return_format' => 'value', 'allow_null' => 0, 'other_choice' => 0, 'layout' => 'horizontal',
  ];
  $fields = [];

  foreach (gaia_get_content_post_types() as $post_type => $post_type_label) {
    $option_prefix = gaia_get_content_option_prefix($post_type);
    $key_prefix = 'field_gaia_' . $option_prefix;

    $fields[] = ['key' => $key_prefix . 'tab', 'label' => $post_type_label, 'type' => 'tab', 'placement' => 'top'];
    add_action('acf/render_field/key=' . $key_prefix . 'sample', 'gaia_render_sample_field');
    $fields[] = [
      'key' => $key_prefix . 'sample', 'label' => __('Muestra', 'gaia'), 'type' => 'message', 'message' => '',
      'instructions' => __('La tarjeta del último contenido publicado, con los ajustes de esta pestaña.', 'gaia'),
      'wrapper' => ['width' => '', 'class' => 'gaia-sample-field', 'id' => ''],
    ];
    $fields[] = ['key' => $key_prefix . 'card_heading', 'label' => __('Tarjeta', 'gaia'), 'type' => 'message', 'message' => __('Cómo se ve cada elemento en los archivos y en el bloque «Últimas entradas».', 'gaia')];
    $fields[] = $visual($key_prefix . 'card_layout', $option_prefix . 'card_layout', __('Formato', 'gaia'), [
      'top' => __('Imagen arriba', 'gaia'),
      'side' => __('Imagen a la izquierda', 'gaia'),
      'overlay' => __('Texto sobre la imagen', 'gaia'),
      'text' => __('Sin imagen', 'gaia'),
    ], 'top');
    $fields[] = $visual($key_prefix . 'card_image_ratio', $option_prefix . 'card_image_ratio', __('Proporción de la imagen', 'gaia'), [
      'wide' => __('Panorámica', 'gaia'),
      'landscape' => __('Horizontal', 'gaia'),
      'square' => __('Cuadrada', 'gaia'),
      'portrait' => __('Vertical', 'gaia'),
    ], 'landscape', '', 50);
    $fields[] = $visual($key_prefix . 'card_style', $option_prefix . 'card_style', __('Estilo', 'gaia'), [
      'surface' => __('Con fondo', 'gaia'),
      'bordered' => __('Con borde', 'gaia'),
      'plain' => __('Sin fondo', 'gaia'),
    ], 'surface', '', 50);
    $fields[] = [
      'key' => $key_prefix . 'card_fields', 'label' => __('Qué se muestra', 'gaia'), 'name' => $option_prefix . 'card_fields', 'type' => 'checkbox',
      'choices' => gaia_get_card_field_choices(), 'default_value' => ['image', 'date', 'terms', 'excerpt', 'more'],
      'layout' => 'horizontal', 'return_format' => 'value', 'wrapper' => ['width' => '70', 'class' => '', 'id' => ''],
    ];
    $fields[] = [
      'key' => $key_prefix . 'card_excerpt_length', 'label' => __('Palabras del extracto', 'gaia'), 'name' => $option_prefix . 'card_excerpt_length',
      'type' => 'number', 'default_value' => 22, 'min' => 5, 'max' => 80, 'wrapper' => ['width' => '30', 'class' => '', 'id' => ''],
    ];
    $fields[] = ['key' => $key_prefix . 'archive_heading', 'label' => __('Archivo', 'gaia'), 'type' => 'message', 'message' => __('La página con el listado (archivo, categorías y etiquetas).', 'gaia')];
    $fields[] = $visual($key_prefix . 'archive_layout', $option_prefix . 'archive_layout', __('Distribución', 'gaia'), [
      'grid' => __('Rejilla', 'gaia'),
      'list' => __('Lista', 'gaia'),
    ], 'grid', '', 50);
    $fields[] = [
      'key' => $key_prefix . 'archive_columns', 'label' => __('Columnas de la rejilla', 'gaia'), 'name' => $option_prefix . 'archive_columns',
      'type' => 'button_group', 'choices' => ['2' => '2', '3' => '3', '4' => '4'], 'default_value' => '3', 'layout' => 'horizontal',
      'wrapper' => ['width' => '25', 'class' => '', 'id' => ''],
    ];
    $fields[] = [
      'key' => $key_prefix . 'archive_per_page', 'label' => __('Elementos por página', 'gaia'), 'name' => $option_prefix . 'archive_per_page',
      'type' => 'number', 'default_value' => 9, 'min' => 1, 'max' => 60, 'wrapper' => ['width' => '25', 'class' => '', 'id' => ''],
    ];
    $fields[] = $visual($key_prefix . 'archive_pagination', $option_prefix . 'archive_pagination', __('Paginación', 'gaia'), [
      'numbers' => __('Números de página', 'gaia'),
      'load_more' => __('Botón «Cargar más»', 'gaia'),
      'infinite' => __('Scroll infinito', 'gaia'),
    ], 'numbers');
    $fields[] = [
      'key' => $key_prefix . 'archive_featured', 'label' => __('Primer elemento destacado', 'gaia'), 'name' => $option_prefix . 'archive_featured',
      'type' => 'true_false', 'ui' => 1, 'default_value' => 0, 'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
      'instructions' => __('Con números, en cada página; con «Cargar más» o scroll infinito, solo al principio.', 'gaia'),
    ];
    $fields[] = [
      'key' => $key_prefix . 'archive_filters', 'label' => __('Filtro por categorías', 'gaia'), 'name' => $option_prefix . 'archive_filters',
      'type' => 'true_false', 'ui' => 1, 'default_value' => 1, 'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
      'instructions' => __('Botones con las categorías encima del listado.', 'gaia'),
    ];
    $fields[] = $visual($key_prefix . 'archive_featured_layout', $option_prefix . 'archive_featured_layout', __('Formato del destacado', 'gaia'), [
      'side' => __('Imagen grande al lado', 'gaia'),
      'overlay' => __('Texto sobre la imagen', 'gaia'),
      'top' => __('Imagen ancha arriba', 'gaia'),
    ], 'side') + ['conditional_logic' => [[['field' => $key_prefix . 'archive_featured', 'operator' => '==', 'value' => '1']]]];
  }

  acf_add_local_field_group([
    'key' => 'group_gaia_content',
    'title' => __('Contenidos', 'gaia'),
    'fields' => $fields,
    'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'gaia-content']]],
    'style' => 'seamless',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'active' => true,
  ]);
}
// After the post types of plugins are registered
add_action('init', 'gaia_register_content_option_fields', 20);

/* Latest posts block choices ---------------------------------------------- */

function gaia_load_content_post_type_choices($field) {
  $field['choices'] = gaia_get_content_post_types();

  return $field;
}
add_filter('acf/load_field/key=field_gaia_latest_posts_post_type', 'gaia_load_content_post_type_choices');

/**
 * Term choices of every content post type, grouped by "Type: Taxonomy",
 * as "taxonomy:term_id".
 */
function gaia_load_content_term_choices($field) {
  $term_choices = [];

  foreach (gaia_get_content_post_types() as $post_type => $post_type_label) {
    foreach (get_object_taxonomies($post_type, 'objects') as $taxonomy) {
      if (!$taxonomy->public || !$taxonomy->show_ui || in_array($taxonomy->name, ['post_format', 'language', 'post_translations'], true)) {
        continue;
      }

      $taxonomy_terms = get_terms(['taxonomy' => $taxonomy->name, 'hide_empty' => false]);

      if (!is_array($taxonomy_terms) || !$taxonomy_terms) {
        continue;
      }

      $group_label = $post_type_label . ': ' . $taxonomy->labels->name;
      foreach ($taxonomy_terms as $taxonomy_term) {
        $term_choices[$group_label][$taxonomy->name . ':' . $taxonomy_term->term_id] = $taxonomy_term->name;
      }
    }
  }

  $field['choices'] = $term_choices;

  return $field;
}
add_filter('acf/load_field/key=field_gaia_latest_posts_terms', 'gaia_load_content_term_choices');
