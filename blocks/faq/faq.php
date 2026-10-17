<?php
/**
 * FAQ block: questions with answers that open and close (native details
 * element, no JavaScript), plus optional FAQPage structured data.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$heading = gaia_get_block_heading('faq', gaia_get_block_field('faq_alignment', 'left'));
$questions = array_filter(get_field('faq_items') ?: [], fn($question) => !empty($question['question']));
$is_first_open = (bool) get_field('faq_first_open');
$has_structured_data = get_field('faq_structured_data') !== false;

if (!$questions && $is_preview) {
  gaia_print_block_placeholder(__('FAQ: add questions in the block settings.', 'gaia'));
  return;
}

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'faq', gaia_get_block_section_classes('faq'));
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="container container--narrow">
    <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>

    <div class="faq__list">
      <?php foreach (array_values($questions) as $question_index => $question) : ?>
        <details class="faq__item"<?php echo $is_first_open && $question_index === 0 ? ' open' : ''; ?>>
          <summary class="faq__question">
            <span><?php echo esc_html($question['question']); ?></span>
            <?php echo gaia_get_icon_svg('ui/chevron-down'); ?>
          </summary>
          <div class="faq__answer block-text"><?php echo wp_kses_post($question['answer'] ?? ''); ?></div>
        </details>
      <?php endforeach; ?>
    </div>
  </div>

  <?php if ($has_structured_data && !$is_preview) : ?>
    <script type="application/ld+json">
      <?php
      echo wp_json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn($question) => [
          '@type' => 'Question',
          'name' => wp_strip_all_tags($question['question']),
          'acceptedAnswer' => ['@type' => 'Answer', 'text' => wp_kses_post($question['answer'] ?? '')],
        ], array_values($questions)),
      ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
      ?>
    </script>
  <?php endif; ?>
</section>
