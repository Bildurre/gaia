<?php
/**
 * Card of a post or custom post type item, with the card settings of its
 * post type (Ajustes de Gaia > Contenidos). Used by the archives, the latest
 * posts block and the options page sample.
 *
 * @param array $args [
 *   'post' => WP_Post,
 *   'featured_layout' => '' or side|overlay|top (featured card),
 *   'heading_tag' => h2|h3,
 *   'class' => extra CSS class,
 *   'is_sample' => bool (prints every part, the sample shows the checked ones),
 * ]
 */

if (!defined('ABSPATH')) {
  exit;
}

$card_post = $args['post'] ?? get_post();

if (!$card_post instanceof WP_Post) {
  return;
}

$is_sample = !empty($args['is_sample']);
$card_settings = gaia_get_card_settings($card_post->post_type);
$featured_layout = $args['featured_layout'] ?? '';
$heading_tag = ($args['heading_tag'] ?? 'h3') === 'h2' ? 'h2' : 'h3';
$shown_parts = $card_settings['fields'];
$shows_part = fn($part_name) => $is_sample || in_array($part_name, $shown_parts, true);
$sample_part_attributes = fn($part_name) => $is_sample
  ? sprintf(' data-sample-part-option="%s" data-sample-part="%s"', esc_attr(gaia_get_content_option_prefix($card_post->post_type) . 'card_fields'), esc_attr($part_name))
  : '';

$card_layout = $featured_layout ?: $card_settings['layout'];
$has_image = has_post_thumbnail($card_post) && $shows_part('image') && ($card_layout !== 'text' || $is_sample);
$main_taxonomy = gaia_get_main_taxonomy($card_post->post_type);
$card_terms = $main_taxonomy ? get_the_terms($card_post, $main_taxonomy) : [];
$card_terms = is_array($card_terms) ? array_slice($card_terms, 0, 2) : [];
$permalink = get_permalink($card_post);

$card_classes = [
  'post-card',
  'post-card--layout-' . $card_layout,
  'post-card--ratio-' . $card_settings['image_ratio'],
  'post-card--style-' . $card_settings['style'],
  $featured_layout ? 'post-card--featured' : '',
  $has_image ? '' : 'post-card--no-image',
  $args['class'] ?? '',
];

$sample_class_options = [];
if ($is_sample) {
  $option_prefix = gaia_get_content_option_prefix($card_post->post_type);
  $sample_class_options = [
    $option_prefix . 'card_layout' => 'post-card--layout-',
    $option_prefix . 'card_image_ratio' => 'post-card--ratio-',
    $option_prefix . 'card_style' => 'post-card--style-',
  ];
}
?>
<article
  class="<?php echo esc_attr(implode(' ', array_filter($card_classes))); ?>"
  <?php if ($sample_class_options) : ?>data-sample-classes="<?php echo esc_attr(wp_json_encode($sample_class_options)); ?>"<?php endif; ?>
>
  <?php if ($has_image) : ?>
    <div class="post-card__media"<?php echo $sample_part_attributes('image'); ?>>
      <?php echo get_the_post_thumbnail($card_post, $featured_layout ? 'large' : 'medium_large', ['class' => 'post-card__image', 'alt' => '']); ?>
    </div>
  <?php endif; ?>

  <div class="post-card__body">
    <?php if ($card_terms && $shows_part('terms')) : ?>
      <ul class="post-card__terms" role="list"<?php echo $sample_part_attributes('terms'); ?>>
        <?php foreach ($card_terms as $card_term) : ?>
          <li><a href="<?php echo esc_url(get_term_link($card_term)); ?>"><?php echo esc_html($card_term->name); ?></a></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <<?php echo $heading_tag; ?> class="post-card__title">
      <a class="post-card__link" href="<?php echo esc_url($permalink); ?>"><?php echo esc_html(get_the_title($card_post)); ?></a>
    </<?php echo $heading_tag; ?>>

    <?php if ($shows_part('date') || $shows_part('author') || $shows_part('reading_time')) : ?>
      <p class="post-card__meta">
        <?php if ($shows_part('date')) : ?>
          <time class="post-card__meta-item" datetime="<?php echo esc_attr(get_the_date('c', $card_post)); ?>"<?php echo $sample_part_attributes('date'); ?>><?php echo esc_html(get_the_date('', $card_post)); ?></time>
        <?php endif; ?>
        <?php $author_name = get_the_author_meta('display_name', (int) $card_post->post_author); ?>
        <?php if ($shows_part('author') && $author_name) : ?>
          <span class="post-card__meta-item"<?php echo $sample_part_attributes('author'); ?>><?php echo esc_html($author_name); ?></span>
        <?php endif; ?>
        <?php if ($shows_part('reading_time')) : ?>
          <span class="post-card__meta-item"<?php echo $sample_part_attributes('reading_time'); ?>>
            <?php
            /* translators: %d: minutes */
            echo esc_html(sprintf(__('%d min read', 'gaia'), gaia_get_reading_time($card_post)));
            ?>
          </span>
        <?php endif; ?>
      </p>
    <?php endif; ?>

    <?php if ($shows_part('excerpt')) : ?>
      <p class="post-card__excerpt"<?php echo $sample_part_attributes('excerpt'); ?>><?php echo esc_html(wp_trim_words(get_the_excerpt($card_post), $featured_layout ? $card_settings['excerpt_length'] * 2 : $card_settings['excerpt_length'])); ?></p>
    <?php endif; ?>

    <?php if ($shows_part('more')) : ?>
      <span class="post-card__more" aria-hidden="true"<?php echo $sample_part_attributes('more'); ?>>
        <?php esc_html_e('Read more', 'gaia'); ?>
        <?php echo gaia_get_icon_svg('ui/arrow-right'); ?>
      </span>
    <?php endif; ?>
  </div>
</article>
