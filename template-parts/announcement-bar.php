<?php
/**
 * Announcement bar (Ajustes de Gaia > Extras), above the header.
 */

if (!defined('ABSPATH')) {
  exit;
}

$announcement = gaia_get_announcement();

if (!$announcement) {
  return;
}
?>
<?php if ($announcement['is_dismissible']) : ?>
  <style>html[data-dismissed-announcement="<?php echo esc_attr($announcement['id']); ?>"] .announcement-bar { display: none; }</style>
<?php endif; ?>
<div class="announcement-bar announcement-bar--<?php echo esc_attr($announcement['style']); ?>" data-announcement-bar data-announcement-id="<?php echo esc_attr($announcement['id']); ?>">
  <div class="announcement-bar__inner container">
    <?php if ($announcement['url']) : ?>
      <a class="announcement-bar__text" href="<?php echo esc_url($announcement['url']); ?>"<?php echo $announcement['target'] ? ' target="' . esc_attr($announcement['target']) . '" rel="noopener"' : ''; ?>>
        <?php echo esc_html($announcement['text']); ?>
        <?php echo gaia_get_icon_svg('ui/arrow-right'); ?>
      </a>
    <?php else : ?>
      <p class="announcement-bar__text"><?php echo esc_html($announcement['text']); ?></p>
    <?php endif; ?>

    <?php if ($announcement['is_dismissible']) : ?>
      <button class="announcement-bar__close" type="button" data-announcement-close>
        <span aria-hidden="true">×</span>
        <span class="visually-hidden"><?php esc_html_e('Close announcement', 'gaia'); ?></span>
      </button>
    <?php endif; ?>
  </div>
</div>
