<?php
/**
 * Figures block: highlighted numbers with a label. With "animate", the
 * numbers count up when they come into view (figures.js).
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$heading = gaia_get_block_heading('figures', gaia_get_block_field('figures_alignment', 'center'));
$figures = array_filter(get_field('figures_items') ?: [], fn($figure) => is_numeric($figure['value'] ?? null));
$is_animated = get_field('figures_animate') !== false && !$is_preview;

if (!$figures && $is_preview) {
  gaia_print_block_placeholder(__('Figures: add figures in the block settings.', 'gaia'));
  return;
}

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'figures', gaia_get_block_section_classes('figures'));
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="container">
    <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>

    <dl class="figures__list">
      <?php foreach ($figures as $figure) : ?>
        <?php
        $figure_value = (float) $figure['value'];
        $decimal_places = str_contains((string) $figure['value'], '.') ? strlen(substr(strrchr((string) $figure['value'], '.'), 1)) : 0;
        ?>
        <div class="figures__item">
          <dt class="figures__label"><?php echo esc_html($figure['label']); ?></dt>
          <dd class="figures__value">
            <?php echo esc_html($figure['prefix'] ?? ''); ?><span
              <?php if ($is_animated) : ?>data-count-to="<?php echo esc_attr((string) $figure_value); ?>" data-decimals="<?php echo esc_attr((string) $decimal_places); ?>"<?php endif; ?>
            ><?php echo esc_html(number_format_i18n($figure_value, $decimal_places)); ?></span><?php echo esc_html($figure['suffix'] ?? ''); ?>
          </dd>
        </div>
      <?php endforeach; ?>
    </dl>
  </div>
</section>
