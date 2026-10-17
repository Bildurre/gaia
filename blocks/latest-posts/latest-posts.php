<?php
/**
 * Latest posts block: the latest items of a post type, from all or several
 * terms, in a grid or a carousel. Cards use the settings of the post type
 * (Ajustes de Gaia > Contenidos).
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$heading = gaia_get_block_heading('latest_posts', gaia_get_block_field('latest_posts_alignment', 'left'));
$post_type = (string) gaia_get_block_field('latest_posts_post_type', 'post');
$post_type = array_key_exists($post_type, gaia_get_content_post_types()) ? $post_type : 'post';
$post_count = min(24, max(1, (int) gaia_get_block_field('latest_posts_count', 3)));
$order = gaia_get_block_field('latest_posts_order', 'date_desc');
$layout = gaia_get_block_field('latest_posts_layout', 'grid');
$columns = gaia_get_block_field('latest_posts_columns', '3');
$shows_all_link = get_field('latest_posts_show_all_link') !== false;
$all_link_text = (string) gaia_get_block_field('latest_posts_all_link_text', __('View all', 'gaia'));

// Selected terms ("taxonomy:term_id"), grouped by taxonomy; the old single
// category field still works
$terms_by_taxonomy = [];
foreach ((array) (get_field('latest_posts_terms') ?: []) as $term_choice) {
  [$taxonomy_name, $term_id] = array_pad(explode(':', (string) $term_choice, 2), 2, 0);

  if ($term_id && is_object_in_taxonomy($post_type, $taxonomy_name)) {
    $terms_by_taxonomy[$taxonomy_name][] = (int) $term_id;
  }
}

$legacy_category_id = (int) get_field('latest_posts_category');
if (!$terms_by_taxonomy && $legacy_category_id && $post_type === 'post') {
  $terms_by_taxonomy['category'] = [$legacy_category_id];
}

$tax_query = [];
foreach ($terms_by_taxonomy as $taxonomy_name => $term_ids) {
  $tax_query[] = ['taxonomy' => $taxonomy_name, 'field' => 'term_id', 'terms' => $term_ids];
}
if (count($tax_query) > 1) {
  $tax_query['relation'] = 'OR';
}

$order_arguments = [
  'date_desc' => ['orderby' => 'date', 'order' => 'DESC'],
  'date_asc' => ['orderby' => 'date', 'order' => 'ASC'],
  'title' => ['orderby' => 'title', 'order' => 'ASC'],
  'random' => ['orderby' => 'rand'],
][$order] ?? ['orderby' => 'date', 'order' => 'DESC'];

$current_post_id = get_the_ID();
$latest_posts = get_posts(array_merge([
  'post_type' => $post_type,
  'post_status' => 'publish',
  'numberposts' => $post_count,
  'tax_query' => $tax_query,
  'post__not_in' => get_field('latest_posts_exclude_current') !== false && $current_post_id ? [$current_post_id] : [],
  'ignore_sticky_posts' => true,
  'suppress_filters' => false, // Polylang: posts in the current language
], $order_arguments));

if (!$latest_posts && !$is_preview) {
  return;
}

// "View all": the term archive when there is exactly one term, else the
// archive of the post type (the posts page for posts)
$all_items_url = '';
$selected_term_ids = array_merge(...array_values($terms_by_taxonomy ?: [[]]));
if (count($selected_term_ids) === 1) {
  $term_link = get_term_link((int) $selected_term_ids[0]);
  $all_items_url = is_wp_error($term_link) ? '' : $term_link;
} elseif ($post_type === 'post') {
  $all_items_url = get_option('page_for_posts') ? get_permalink((int) get_option('page_for_posts')) : '';
} else {
  $all_items_url = (string) get_post_type_archive_link($post_type);
}

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'latest-posts', array_merge(
  gaia_get_block_section_classes('latest_posts'),
  ['latest-posts--' . $layout]
));
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="container">
    <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>

    <?php if (!$latest_posts) : ?>
      <?php gaia_print_block_placeholder(__('Latest posts: there is no content yet.', 'gaia')); ?>
    <?php elseif ($layout === 'carousel') : ?>
      <div class="carousel latest-posts__carousel" style="--carousel-columns: <?php echo esc_attr($columns); ?>" <?php echo gaia_get_carousel_attributes(); ?>>
        <ul class="carousel__track" role="list" tabindex="0" data-carousel-track>
          <?php foreach ($latest_posts as $latest_post) : ?>
            <li><?php get_template_part('template-parts/post-card', null, ['post' => $latest_post]); ?></li>
          <?php endforeach; ?>
        </ul>
        <?php get_template_part('template-parts/carousel-controls'); ?>
      </div>
    <?php else : ?>
      <ul class="post-grid post-grid--columns-<?php echo esc_attr($columns); ?>" role="list">
        <?php foreach ($latest_posts as $latest_post) : ?>
          <li class="latest-posts__item"><?php get_template_part('template-parts/post-card', null, ['post' => $latest_post]); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if ($latest_posts && $shows_all_link && $all_items_url) : ?>
      <?php get_template_part('template-parts/blocks/buttons', null, ['buttons' => [['link' => ['url' => $all_items_url, 'title' => $all_link_text], 'style' => 'secondary']], 'class' => 'latest-posts__all']); ?>
    <?php endif; ?>
  </div>
</section>
