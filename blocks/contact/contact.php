<?php
/**
 * Contact block: text next to the contact details and social networks of
 * Ajustes de Gaia > Contacto, with an optional map link.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$heading = gaia_get_block_heading('contact', gaia_get_block_field('contact_alignment', 'left'));
$content = (string) get_field('contact_content');
$shown_details = get_field('contact_details');
$shown_details = is_array($shown_details) ? $shown_details : ['address', 'phones', 'emails', 'social'];
$map_link = get_field('contact_map_link');

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'contact-block', array_merge(
  gaia_get_block_section_classes('contact'),
  [$content ? 'contact-block--has-text' : '']
));
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="container">
    <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>

    <div class="contact-block__inner">
      <?php if ($content) : ?>
        <div class="contact-block__text block-text"><?php echo wp_kses_post($content); ?></div>
      <?php endif; ?>

      <div class="contact-block__details">
        <?php get_template_part('template-parts/contact-details', null, ['types' => array_intersect($shown_details, ['address', 'phones', 'emails'])]); ?>

        <?php if (in_array('social', $shown_details, true)) : ?>
          <?php get_template_part('template-parts/social-links', null, ['class' => 'contact-block__social']); ?>
        <?php endif; ?>

        <?php if (!empty($map_link['url'])) : ?>
          <?php get_template_part('template-parts/blocks/buttons', null, ['buttons' => [['link' => ['url' => $map_link['url'], 'title' => $map_link['title'] ?: __('View on the map', 'gaia'), 'target' => $map_link['target'] ?? ''], 'style' => 'secondary']]]); ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
