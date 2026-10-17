<?php
/**
 * Logos block: partner, producer or sponsor logos with optional links, in a
 * grid or a carousel.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$heading = gaia_get_block_heading('logos', gaia_get_block_field('logos_alignment', 'center'));
$logos = array_filter(get_field('logos_items') ?: [], fn($logo) => !empty($logo['image']));
$layout = gaia_get_block_field('logos_layout', 'grid');
$size = gaia_get_block_field('logos_size', 'medium');
$is_grayscale = get_field('logos_grayscale') !== false;
$autoplay_seconds = $is_preview ? 0 : max(0, (int) gaia_get_block_field('logos_autoplay', 0));

if (!$logos && $is_preview) {
  gaia_print_block_placeholder(__('Logos: add logos in the block settings.', 'gaia'));
  return;
}

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'logos', array_merge(
  gaia_get_block_section_classes('logos'),
  ['logos--' . $layout, 'logos--size-' . $size, $is_grayscale ? 'logos--grayscale' : '']
));

$print_logo = function ($logo) {
  $logo_image = wp_get_attachment_image((int) $logo['image'], 'medium', false, ['class' => 'logos__image', 'alt' => $logo['name'] ?? '']);

  if (!empty($logo['url'])) {
    printf('<a class="logos__link" href="%s" target="_blank" rel="noopener">%s</a>', esc_url($logo['url']), $logo_image);
  } else {
    echo '<span class="logos__link">' . $logo_image . '</span>';
  }
};
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="container">
    <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>

    <?php if ($layout === 'carousel') : ?>
      <div class="carousel logos__carousel" <?php echo gaia_get_carousel_attributes($autoplay_seconds); ?>>
        <ul class="carousel__track" role="list" tabindex="0" data-carousel-track>
          <?php foreach ($logos as $logo) : ?>
            <li class="logos__item"><?php $print_logo($logo); ?></li>
          <?php endforeach; ?>
        </ul>
        <?php get_template_part('template-parts/carousel-controls', null, ['has_autoplay' => $autoplay_seconds > 0]); ?>
      </div>
    <?php else : ?>
      <ul class="logos__list" role="list">
        <?php foreach ($logos as $logo) : ?>
          <li class="logos__item"><?php $print_logo($logo); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>
