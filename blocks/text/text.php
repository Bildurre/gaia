<?php
/**
 * Text block: heading and rich text, narrow or normal width, one or two
 * columns.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$heading = gaia_get_block_heading('text', gaia_get_block_field('text_alignment', 'left'));
$content = (string) get_field('text_content');
$width = gaia_get_block_field('text_width', 'narrow');
$columns = gaia_get_block_field('text_columns', '1');

if (!$content && !$heading['title'] && $is_preview) {
  gaia_print_block_placeholder(__('Text: write the text in the block settings.', 'gaia'));
  return;
}

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'text-block', array_merge(
  gaia_get_block_section_classes('text'),
  ['text-block--columns-' . $columns]
));
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="container<?php echo $width === 'narrow' ? ' container--narrow' : ''; ?>">
    <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>

    <?php if ($content) : ?>
      <div class="text-block__content block-text"><?php echo wp_kses_post($content); ?></div>
    <?php endif; ?>
  </div>
</section>
