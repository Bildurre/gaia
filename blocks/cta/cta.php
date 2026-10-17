<?php
/**
 * Call to action block: title, text and buttons, as a full-width band or a
 * box inside the content.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$title = (string) get_field('cta_title');
$text = (string) get_field('cta_text');
$buttons = get_field('cta_buttons') ?: [];
$layout = gaia_get_block_field('cta_layout', 'center');
$style = gaia_get_block_field('cta_style', 'box');

if (!$title && $is_preview) {
  gaia_print_block_placeholder(__('Call to action: fill in the title in the block settings.', 'gaia'));
  return;
}

// Band: the background covers the whole section. Box: the section keeps
// the page background and the box gets the color.
$section_classes = gaia_get_block_section_classes('cta');
$box_classes = [];

if ($style === 'box') {
  $box_classes = array_values(array_filter($section_classes, fn($class_name) => str_starts_with($class_name, 'scheme-') || str_starts_with($class_name, 'section--background-')));
  $section_classes = array_merge(array_diff($section_classes, $box_classes), ['section--background-none']);
}

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'cta', array_merge(
  $section_classes,
  ['cta--' . $layout, 'cta--' . $style]
));
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="container">
    <div class="cta__box <?php echo esc_attr(implode(' ', $box_classes)); ?>">
      <div class="cta__text">
        <h2 class="cta__title"><?php echo esc_html($title); ?></h2>
        <?php if ($text) : ?>
          <p class="cta__description"><?php echo nl2br(esc_html($text)); ?></p>
        <?php endif; ?>
      </div>

      <?php get_template_part('template-parts/blocks/buttons', null, ['buttons' => $buttons, 'class' => 'cta__buttons']); ?>
    </div>
  </div>
</section>
