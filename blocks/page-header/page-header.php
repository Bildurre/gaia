<?php
/**
 * Page header block: eyebrow, page title and intro.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$alignment = gaia_get_block_field('page_header_alignment', 'left');
$heading = gaia_get_block_heading('page_header', $alignment);
$heading['title'] = $heading['title'] ?: get_the_title();
$heading['title_tag'] = get_field('page_header_title_is_h1') === false ? 'h2' : 'h1';

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'page-header', array_merge(
  gaia_get_block_section_classes('page_header'),
  ['page-header--' . $alignment]
));
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="container">
    <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>
  </div>
</section>
