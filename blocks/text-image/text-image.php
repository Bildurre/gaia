<?php
/**
 * Text and image block: heading, rich text and buttons next to an image.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$heading = gaia_get_block_heading('text_image');
$content = (string) get_field('text_image_content');
$buttons = get_field('text_image_buttons') ?: [];
$image_id = (int) get_field('text_image_image');
$image_side = gaia_get_block_field('text_image_image_side', 'right');
$image_ratio = gaia_get_block_field('text_image_image_ratio', 'original');
$vertical_alignment = gaia_get_block_field('text_image_vertical_alignment', 'center');

if (!$content && !$heading['title'] && !$image_id && $is_preview) {
  gaia_print_block_placeholder(__('Text and image: add the text and the image in the block settings.', 'gaia'));
  return;
}

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'text-image', array_merge(
  gaia_get_block_section_classes('text_image'),
  [
    'text-image--image-' . $image_side,
    'text-image--ratio-' . $image_ratio,
    'text-image--align-' . $vertical_alignment,
    $image_id ? '' : 'text-image--no-image',
  ]
));
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="text-image__inner container">
    <div class="text-image__text">
      <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>

      <?php if ($content) : ?>
        <div class="block-text"><?php echo wp_kses_post($content); ?></div>
      <?php endif; ?>

      <?php get_template_part('template-parts/blocks/buttons', null, ['buttons' => $buttons, 'class' => 'text-image__buttons']); ?>
    </div>

    <?php if ($image_id) : ?>
      <figure class="text-image__media">
        <?php
        echo wp_get_attachment_image($image_id, 'large', false, [
          'class' => 'text-image__image',
          'sizes' => '(min-width: 48rem) 50vw, 100vw',
        ]);
        ?>
      </figure>
    <?php endif; ?>
  </div>
</section>
