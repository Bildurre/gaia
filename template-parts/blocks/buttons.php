<?php
/**
 * Buttons of a block (repeater rows with "link" and "style").
 *
 * @param array $args ['buttons' => rows, 'class' => extra CSS class]
 */

if (!defined('ABSPATH')) {
  exit;
}

$buttons = array_filter(
  $args['buttons'] ?? [],
  fn($button) => !empty($button['link']['url']) && !empty($button['link']['title'])
);

if (!$buttons) {
  return;
}
?>
<div class="block-buttons <?php echo esc_attr($args['class'] ?? ''); ?>">
  <?php foreach ($buttons as $button) : ?>
    <?php $button_style = in_array($button['style'] ?? '', ['primary', 'secondary', 'light'], true) ? $button['style'] : 'primary'; ?>
    <a
      class="button button--<?php echo esc_attr($button_style); ?>"
      href="<?php echo esc_url($button['link']['url']); ?>"
      <?php if (!empty($button['link']['target'])) : ?>target="<?php echo esc_attr($button['link']['target']); ?>" rel="noopener"<?php endif; ?>
    ><?php echo esc_html($button['link']['title']); ?></a>
  <?php endforeach; ?>
</div>
