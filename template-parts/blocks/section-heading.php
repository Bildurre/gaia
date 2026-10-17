<?php
/**
 * Eyebrow, title and intro of a content block (see gaia_get_block_heading()).
 *
 * @param array $args ['eyebrow', 'title', 'intro', 'alignment' => left|center, 'title_tag' => h1|h2]
 */

if (!defined('ABSPATH')) {
  exit;
}

$eyebrow = $args['eyebrow'] ?? '';
$title = $args['title'] ?? '';
$intro = $args['intro'] ?? '';

if (!$eyebrow && !$title && !$intro) {
  return;
}

$title_tag = in_array($args['title_tag'] ?? 'h2', ['h1', 'h2'], true) ? ($args['title_tag'] ?? 'h2') : 'h2';
$alignment = ($args['alignment'] ?? 'left') === 'center' ? 'center' : 'left';
?>
<header class="section-heading section-heading--<?php echo esc_attr($alignment); ?>">
  <?php if ($eyebrow) : ?>
    <p class="section-heading__eyebrow"><?php echo esc_html($eyebrow); ?></p>
  <?php endif; ?>

  <?php if ($title) : ?>
    <<?php echo $title_tag; ?> class="section-heading__title"><?php echo esc_html($title); ?></<?php echo $title_tag; ?>>
  <?php endif; ?>

  <?php if ($intro) : ?>
    <p class="section-heading__intro"><?php echo nl2br(esc_html($intro)); ?></p>
  <?php endif; ?>
</header>
