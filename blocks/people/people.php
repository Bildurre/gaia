<?php
/**
 * People block: team, board or partners with photo, name, role and text.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$heading = gaia_get_block_heading('people', gaia_get_block_field('people_alignment', 'left'));
$people = array_filter(get_field('people_items') ?: [], fn($person) => !empty($person['name']));
$columns = gaia_get_block_field('people_columns', '4');
$photo_shape = gaia_get_block_field('people_photo_shape', 'round');

if (!$people && $is_preview) {
  gaia_print_block_placeholder(__('People: add people in the block settings.', 'gaia'));
  return;
}

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'people', array_merge(
  gaia_get_block_section_classes('people'),
  ['people--columns-' . $columns, 'people--photo-' . $photo_shape]
));
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="container">
    <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>

    <ul class="people__list" role="list">
      <?php foreach ($people as $person) : ?>
        <li class="people__item">
          <div class="people__photo">
            <?php if (!empty($person['photo'])) : ?>
              <?php echo wp_get_attachment_image((int) $person['photo'], 'medium', false, ['class' => 'people__image', 'alt' => '']); ?>
            <?php else : ?>
              <span class="people__initials" aria-hidden="true"><?php echo esc_html(mb_strtoupper(mb_substr($person['name'], 0, 1))); ?></span>
            <?php endif; ?>
          </div>
          <h3 class="people__name"><?php echo esc_html($person['name']); ?></h3>
          <?php if (!empty($person['role'])) : ?>
            <p class="people__role"><?php echo esc_html($person['role']); ?></p>
          <?php endif; ?>
          <?php if (!empty($person['text'])) : ?>
            <p class="people__text"><?php echo nl2br(esc_html($person['text'])); ?></p>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
