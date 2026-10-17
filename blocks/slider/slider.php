<?php
/**
 * Slider block: full-width slides with an image, text and a button, on the
 * shared carousel (assets/js/carousel.js). Dark area, like the hero.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$slides = get_field('slider_slides') ?: [];
$height = gaia_get_block_field('slider_height', 'large');
$alignment = gaia_get_block_field('slider_alignment', 'left');
$overlay = gaia_get_block_field('slider_overlay', 'medium');
$autoplay_seconds = $is_preview ? 0 : max(0, (int) gaia_get_block_field('slider_autoplay', 6));
$first_title_tag = get_field('slider_first_is_h1') ? 'h1' : 'h2';

if (!$slides && $is_preview) {
  gaia_print_block_placeholder(__('Slider: add slides in the block settings.', 'gaia'));
  return;
}

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'slider', [
  'slider--height-' . $height,
  'slider--align-' . $alignment,
  'slider--overlay-' . $overlay,
  'carousel',
]);
?>
<section <?php echo $wrapper_attributes; ?> <?php echo gaia_get_carousel_attributes($autoplay_seconds); ?> aria-label="<?php esc_attr_e('Featured', 'gaia'); ?>">
  <ul class="carousel__track slider__track" role="list" tabindex="0" data-carousel-track>
    <?php foreach ($slides as $slide_index => $slide) : ?>
      <li class="slider__slide<?php echo empty($slide['image']) ? '' : ' has-image'; ?>">
        <?php if (!empty($slide['image'])) : ?>
          <?php
          echo wp_get_attachment_image((int) $slide['image'], 'full', false, [
            'class' => 'slider__image',
            'alt' => '',
            'sizes' => '100vw',
            'loading' => $slide_index === 0 ? 'eager' : 'lazy',
          ]);
          ?>
        <?php endif; ?>

        <div class="slider__content container">
          <div class="slider__text">
            <?php if (!empty($slide['eyebrow'])) : ?>
              <p class="slider__eyebrow"><?php echo esc_html($slide['eyebrow']); ?></p>
            <?php endif; ?>

            <?php if (!empty($slide['title'])) : ?>
              <?php $title_tag = $slide_index === 0 ? $first_title_tag : 'h2'; ?>
              <<?php echo $title_tag; ?> class="slider__title"><?php echo esc_html($slide['title']); ?></<?php echo $title_tag; ?>>
            <?php endif; ?>

            <?php if (!empty($slide['text'])) : ?>
              <p class="slider__description"><?php echo nl2br(esc_html($slide['text'])); ?></p>
            <?php endif; ?>

            <?php if (!empty($slide['link']['url']) && !empty($slide['link']['title'])) : ?>
              <?php get_template_part('template-parts/blocks/buttons', null, ['buttons' => [['link' => $slide['link'], 'style' => 'primary']]]); ?>
            <?php endif; ?>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>

  <div class="slider__controls container">
    <?php get_template_part('template-parts/carousel-controls', null, ['has_autoplay' => $autoplay_seconds > 0]); ?>
  </div>
</section>
