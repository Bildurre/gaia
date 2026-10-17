<?php
/**
 * Breadcrumbs (Ajustes de Gaia > Extras > Navegación).
 */

if (!defined('ABSPATH')) {
  exit;
}

if (!gaia_should_show_breadcrumbs()) {
  return;
}

$breadcrumb_items = gaia_get_breadcrumb_items();
?>
<nav class="breadcrumbs container" aria-label="<?php esc_attr_e('Breadcrumbs', 'gaia'); ?>">
  <ol class="breadcrumbs__list">
    <?php foreach ($breadcrumb_items as $breadcrumb_item) : ?>
      <li class="breadcrumbs__item">
        <?php if ($breadcrumb_item['url']) : ?>
          <a href="<?php echo esc_url($breadcrumb_item['url']); ?>"><?php echo esc_html($breadcrumb_item['label']); ?></a>
          <?php echo gaia_get_icon_svg('ui/chevron-right'); ?>
        <?php else : ?>
          <span aria-current="page"><?php echo esc_html($breadcrumb_item['label']); ?></span>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</nav>
