<?php
/**
 * Downloads block: files with an icon for their type, format, size and an
 * optional description.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$heading = gaia_get_block_heading('downloads', gaia_get_block_field('downloads_alignment', 'left'));
$files = array_filter(get_field('downloads_items') ?: [], fn($file_row) => !empty($file_row['file']) && get_post((int) $file_row['file']));
$layout = gaia_get_block_field('downloads_layout', 'list');

if (!$files && $is_preview) {
  gaia_print_block_placeholder(__('Downloads: add files in the block settings.', 'gaia'));
  return;
}

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'downloads', array_merge(
  gaia_get_block_section_classes('downloads'),
  ['downloads--' . $layout]
));
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="container<?php echo $layout === 'list' ? ' container--narrow' : ''; ?>">
    <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>

    <ul class="downloads__list" role="list">
      <?php foreach ($files as $file_row) : ?>
        <?php
        $file_id = (int) $file_row['file'];
        $file_path = get_attached_file($file_id);
        $file_extension = strtolower(pathinfo((string) $file_path, PATHINFO_EXTENSION));
        $file_size = $file_path && file_exists($file_path) ? size_format(filesize($file_path), 1) : '';
        $file_title = ($file_row['title'] ?? '') ?: get_the_title($file_id);
        ?>
        <li class="downloads__item">
          <span class="downloads__icon"><?php echo gaia_get_file_type_icon($file_extension); ?></span>
          <div class="downloads__text">
            <a class="downloads__link" href="<?php echo esc_url(wp_get_attachment_url($file_id)); ?>" download>
              <?php echo esc_html($file_title); ?>
            </a>
            <?php if (!empty($file_row['description'])) : ?>
              <p class="downloads__description"><?php echo nl2br(esc_html($file_row['description'])); ?></p>
            <?php endif; ?>
            <p class="downloads__meta"><?php echo esc_html(implode(' · ', array_filter([strtoupper($file_extension), $file_size]))); ?></p>
          </div>
          <span class="downloads__action" aria-hidden="true"><?php echo gaia_get_icon_svg('ui/download'); ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
