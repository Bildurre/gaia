<?php
/**
 * Polylang language switcher (language codes, current one marked).
 *
 * @param array $args ['class' => extra CSS class]
 */

if (!defined('ABSPATH')) {
  exit;
}

if (!function_exists('pll_the_languages')) {
  return;
}

$languages = pll_the_languages([
  'raw' => 1,
  'hide_if_empty' => 0,
  'hide_if_no_translation' => 0,
]);

if (!is_array($languages) || count($languages) < 2) {
  return;
}

$extra_class = $args['class'] ?? '';
?>
<nav class="language-switcher <?php echo esc_attr($extra_class); ?>" aria-label="<?php esc_attr_e('Language', 'gaia'); ?>">
  <ul role="list">
    <?php foreach ($languages as $language) : ?>
      <li>
        <a
          href="<?php echo esc_url($language['url']); ?>"
          lang="<?php echo esc_attr($language['locale'] ?? $language['slug']); ?>"
          hreflang="<?php echo esc_attr($language['slug']); ?>"
          <?php if (!empty($language['current_lang'])) : ?>aria-current="true"<?php endif; ?>
        >
          <span aria-hidden="true"><?php echo esc_html(strtoupper($language['slug'])); ?></span>
          <span class="visually-hidden"><?php echo esc_html($language['name']); ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>
