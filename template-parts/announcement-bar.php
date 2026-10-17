<?php
/**
 * Announcement bar (Ajustes de Gaia > Extras), above the header: text, an
 * optional button and the close button at the far right.
 *
 * @param array $args ['is_sample' => bool] (options page sample: always
 *                    shown, with its texts bound to the fields)
 */

if (!defined('ABSPATH')) {
  exit;
}

$is_sample = !empty($args['is_sample']);
$announcement = gaia_get_announcement($is_sample);

if (!$announcement) {
  return;
}

$default_language_slug = gaia_get_default_language_slug();
?>
<?php if ($announcement['is_dismissible'] && !$is_sample) : ?>
  <style>html[data-dismissed-announcement="<?php echo esc_attr($announcement['id']); ?>"] .announcement-bar { display: none; }</style>
<?php endif; ?>
<div
  class="<?php echo esc_attr(implode(' ', $announcement['classes'])); ?>"
  <?php if (!$is_sample) : ?>data-announcement-bar data-announcement-id="<?php echo esc_attr($announcement['id']); ?>"<?php endif; ?>
>
  <div class="announcement-bar__inner container">
    <p class="announcement-bar__text"<?php echo $is_sample ? ' data-sample-text="field_gaia_extras_announcement_text_' . esc_attr($default_language_slug) . '"' : ''; ?>><?php echo esc_html($announcement['text']); ?></p>

    <?php if ($announcement['url'] || $is_sample) : ?>
      <a
        class="announcement-bar__button"
        href="<?php echo esc_url($announcement['url'] ?: '#'); ?>"
        <?php echo $announcement['target'] ? 'target="' . esc_attr($announcement['target']) . '" rel="noopener"' : ''; ?>
        <?php echo $is_sample ? 'data-sample-text="field_gaia_extras_announcement_button_text_' . esc_attr($default_language_slug) . '"' : ''; ?>
      ><?php echo esc_html($announcement['button_text']); ?></a>
    <?php endif; ?>
  </div>

  <?php if ($announcement['is_dismissible'] || $is_sample) : ?>
    <button class="announcement-bar__close" type="button" data-announcement-close>
      <?php echo gaia_get_icon_svg('ui/x'); ?>
      <span class="visually-hidden"><?php esc_html_e('Close announcement', 'gaia'); ?></span>
    </button>
  <?php endif; ?>
</div>
