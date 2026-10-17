<?php
/**
 * Latest posts block: the most recent posts, all or from one category.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$heading = gaia_get_block_heading('latest_posts', gaia_get_block_field('latest_posts_alignment', 'left'));
$post_count = (int) gaia_get_block_field('latest_posts_count', '3');
$category_id = (int) get_field('latest_posts_category');
$shows_all_link = get_field('latest_posts_show_all_link') !== false;

$latest_posts = get_posts([
  'post_type' => 'post',
  'post_status' => 'publish',
  'numberposts' => $post_count,
  'cat' => $category_id ?: '',
  'ignore_sticky_posts' => true,
  'suppress_filters' => false, // Polylang: posts in the current language
]);

if (!$latest_posts && !$is_preview) {
  return;
}

$all_posts_url = $category_id ? get_category_link($category_id) : (get_option('page_for_posts') ? get_permalink((int) get_option('page_for_posts')) : '');

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'latest-posts', gaia_get_block_section_classes('latest_posts'));
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="container">
    <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>

    <?php if (!$latest_posts) : ?>
      <?php gaia_print_block_placeholder(__('Latest posts: there are no posts yet.', 'gaia')); ?>
    <?php else : ?>
      <ul class="latest-posts__list" role="list">
        <?php foreach ($latest_posts as $latest_post) : ?>
          <li class="latest-posts__item">
            <?php if (has_post_thumbnail($latest_post)) : ?>
              <div class="latest-posts__media">
                <?php echo get_the_post_thumbnail($latest_post, 'medium_large', ['class' => 'latest-posts__image', 'alt' => '']); ?>
              </div>
            <?php endif; ?>
            <div class="latest-posts__body">
              <time class="latest-posts__date" datetime="<?php echo esc_attr(get_the_date('c', $latest_post)); ?>"><?php echo esc_html(get_the_date('', $latest_post)); ?></time>
              <h3 class="latest-posts__title">
                <a class="latest-posts__link" href="<?php echo esc_url(get_permalink($latest_post)); ?>"><?php echo esc_html(get_the_title($latest_post)); ?></a>
              </h3>
              <p class="latest-posts__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt($latest_post), 22)); ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>

      <?php if ($shows_all_link && $all_posts_url) : ?>
        <?php get_template_part('template-parts/blocks/buttons', null, ['buttons' => [['link' => ['url' => $all_posts_url, 'title' => __('View all news', 'gaia')], 'style' => 'secondary']], 'class' => 'latest-posts__all']); ?>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
