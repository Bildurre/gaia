<?php
/**
 * Light / dark / browser scheme switcher ("auto" color mode). The choice is
 * stored in localStorage by assets/js/site-header.js; the icon follows
 * data-color-preference on <html>.
 */

if (!defined('ABSPATH')) {
  exit;
}

$scheme_choices = [
  'light' => ['icon' => 'ui/sun', 'label' => __('Light', 'gaia')],
  'dark' => ['icon' => 'ui/moon', 'label' => __('Dark', 'gaia')],
  'system' => ['icon' => 'ui/monitor', 'label' => __('Browser', 'gaia')],
];
?>
<div class="scheme-switcher" data-scheme-switcher>
  <button
    class="site-header__tool scheme-switcher__toggle"
    type="button"
    aria-expanded="false"
    aria-controls="scheme-switcher-menu"
    data-scheme-switcher-toggle
  >
    <?php foreach ($scheme_choices as $scheme_value => $scheme_choice) : ?>
      <span class="scheme-switcher__icon scheme-switcher__icon--<?php echo esc_attr($scheme_value); ?>"><?php echo gaia_get_icon_svg($scheme_choice['icon']); ?></span>
    <?php endforeach; ?>
    <span class="visually-hidden"><?php esc_html_e('Color scheme', 'gaia'); ?></span>
  </button>

  <ul class="scheme-switcher__menu" id="scheme-switcher-menu" role="list" hidden data-scheme-switcher-menu>
    <?php foreach ($scheme_choices as $scheme_value => $scheme_choice) : ?>
      <li>
        <button class="scheme-switcher__choice" type="button" aria-pressed="false" data-scheme-choice="<?php echo esc_attr($scheme_value); ?>">
          <?php echo gaia_get_icon_svg($scheme_choice['icon']); ?>
          <span><?php echo esc_html($scheme_choice['label']); ?></span>
        </button>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
