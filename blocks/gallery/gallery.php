<?php
/**
 * Gallery block: image grid; with "lightbox", images open in a dialog with
 * their caption and previous / next buttons (gallery.js).
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$heading = gaia_get_block_heading('gallery', gaia_get_block_field('gallery_alignment', 'left'));
$image_ids = array_map('intval', get_field('gallery_images') ?: []);
$columns = gaia_get_block_field('gallery_columns', '3');
$image_ratio = gaia_get_block_field('gallery_image_ratio', 'square');
$has_lightbox = get_field('gallery_lightbox') !== false;

if (!$image_ids && $is_preview) {
  gaia_print_block_placeholder(__('Gallery: add images in the block settings.', 'gaia'));
  return;
}

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'gallery', array_merge(
  gaia_get_block_section_classes('gallery'),
  ['gallery--columns-' . $columns, 'gallery--ratio-' . $image_ratio]
));
?>
<section <?php echo $wrapper_attributes; ?><?php echo $has_lightbox ? ' data-gallery' : ''; ?>>
  <div class="container">
    <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>

    <ul class="gallery__list" role="list">
      <?php foreach ($image_ids as $image_id) : ?>
        <?php
        $image_caption = wp_get_attachment_caption($image_id);
        $image_markup = wp_get_attachment_image($image_id, 'medium_large', false, ['class' => 'gallery__image']);
        ?>
        <li class="gallery__item">
          <?php if ($has_lightbox) : ?>
            <a
              class="gallery__link"
              href="<?php echo esc_url(wp_get_attachment_image_url($image_id, 'full')); ?>"
              data-gallery-item
              data-caption="<?php echo esc_attr($image_caption); ?>"
            ><?php echo $image_markup; ?></a>
          <?php else : ?>
            <?php echo $image_markup; ?>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <?php if ($has_lightbox && !$is_preview) : ?>
    <dialog class="gallery__dialog" data-gallery-dialog aria-label="<?php esc_attr_e('Image viewer', 'gaia'); ?>">
      <figure class="gallery__figure">
        <img class="gallery__full-image" src="" alt="" data-gallery-image>
        <figcaption class="gallery__caption" data-gallery-caption></figcaption>
      </figure>
      <button class="gallery__control gallery__control--previous" type="button" data-gallery-previous>
        <?php echo gaia_get_icon_svg('ui/chevron-left'); ?>
        <span class="visually-hidden"><?php esc_html_e('Previous image', 'gaia'); ?></span>
      </button>
      <button class="gallery__control gallery__control--next" type="button" data-gallery-next>
        <?php echo gaia_get_icon_svg('ui/chevron-right'); ?>
        <span class="visually-hidden"><?php esc_html_e('Next image', 'gaia'); ?></span>
      </button>
      <button class="gallery__control gallery__control--close" type="button" data-gallery-close>
        <?php echo gaia_get_icon_svg('ui/x'); ?>
        <span class="visually-hidden"><?php esc_html_e('Close', 'gaia'); ?></span>
      </button>
    </dialog>
  <?php endif; ?>
</section>
