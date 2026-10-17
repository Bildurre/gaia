<?php
/**
 * Steps block: numbered steps in columns or as a list.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$heading = gaia_get_block_heading('steps', gaia_get_block_field('steps_alignment', 'left'));
$steps = get_field('steps_items') ?: [];
$buttons = get_field('steps_buttons') ?: [];
$layout = gaia_get_block_field('steps_layout', 'columns');

if (!$steps && $is_preview) {
  gaia_print_block_placeholder(__('Steps: add steps in the block settings.', 'gaia'));
  return;
}

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'steps', array_merge(
  gaia_get_block_section_classes('steps'),
  ['steps--' . $layout]
));
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="container">
    <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>

    <ol class="steps__list">
      <?php foreach ($steps as $step_index => $step) : ?>
        <li class="steps__item">
          <span class="steps__number" aria-hidden="true"><?php echo esc_html((string) ($step_index + 1)); ?></span>
          <div class="steps__body">
            <h3 class="steps__title"><?php echo esc_html($step['title']); ?></h3>
            <?php if (!empty($step['text'])) : ?>
              <p class="steps__text"><?php echo nl2br(esc_html($step['text'])); ?></p>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>

    <?php get_template_part('template-parts/blocks/buttons', null, ['buttons' => $buttons, 'class' => 'steps__buttons']); ?>
  </div>
</section>
