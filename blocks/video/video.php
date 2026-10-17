<?php
/**
 * Video block: YouTube or Vimeo video behind a cover with a play button
 * (nothing is loaded from those services until it is pressed; video.js
 * then inserts the player), or an uploaded video file.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$heading = gaia_get_block_heading('video', gaia_get_block_field('video_alignment', 'left'));
$video_url = (string) get_field('video_url');
$video_file_id = (int) get_field('video_file');
$poster_id = (int) get_field('video_poster');
$caption = (string) get_field('video_caption');
$width = gaia_get_block_field('video_width', 'normal');
$ratio = gaia_get_block_field('video_ratio', '16-9');
$embed_url = gaia_get_video_embed_url($video_url);

if (!$embed_url && !$video_file_id) {
  if ($is_preview) {
    gaia_print_block_placeholder(__('Video: add a YouTube or Vimeo link or a video file in the block settings.', 'gaia'));
  }

  return;
}

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'video', array_merge(
  gaia_get_block_section_classes('video'),
  ['video--ratio-' . $ratio]
));
$poster_url = $poster_id ? (string) wp_get_attachment_image_url($poster_id, 'large') : '';
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="container<?php echo $width === 'narrow' ? ' container--narrow' : ''; ?>">
    <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>

    <figure class="video__figure">
      <div class="video__frame">
        <?php if ($video_file_id) : ?>
          <video class="video__player" controls preload="metadata" src="<?php echo esc_url(wp_get_attachment_url($video_file_id)); ?>"<?php echo $poster_url ? ' poster="' . esc_url($poster_url) . '"' : ''; ?>></video>
        <?php else : ?>
          <button class="video__cover" type="button" data-video-embed="<?php echo esc_url($embed_url); ?>" data-video-title="<?php echo esc_attr($heading['title'] ?: __('Video', 'gaia')); ?>">
            <?php if ($poster_id) : ?>
              <?php echo wp_get_attachment_image($poster_id, 'large', false, ['class' => 'video__poster', 'alt' => '']); ?>
            <?php endif; ?>
            <span class="video__play"><?php echo gaia_get_icon_svg('ui/play'); ?></span>
            <span class="visually-hidden"><?php esc_html_e('Play the video', 'gaia'); ?></span>
          </button>
        <?php endif; ?>
      </div>

      <?php if ($caption || !$video_file_id) : ?>
        <figcaption class="video__caption">
          <?php echo esc_html($caption); ?>
          <?php if (!$video_file_id) : ?>
            <span class="video__privacy"><?php esc_html_e('The video loads from an external service when you play it.', 'gaia'); ?></span>
          <?php endif; ?>
        </figcaption>
      <?php endif; ?>
    </figure>
  </div>
</section>
