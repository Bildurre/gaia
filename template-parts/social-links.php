<?php
/**
 * Social network links from the Contacto options page.
 *
 * @param array $args ['class' => extra CSS class]
 */

if (!defined('ABSPATH')) {
  exit;
}

$social_networks = gaia_get_contact_details()['social_networks'];

if (!$social_networks) {
  return;
}

$extra_class = $args['class'] ?? '';
?>
<ul class="social-links <?php echo esc_attr($extra_class); ?>" role="list">
  <?php foreach ($social_networks as $social_network) : ?>
    <li>
      <a href="<?php echo esc_url($social_network['url']); ?>" target="_blank" rel="noopener">
        <?php echo gaia_get_social_icon_svg($social_network['network']); ?>
        <span class="visually-hidden"><?php echo esc_html($social_network['label']); ?></span>
      </a>
    </li>
  <?php endforeach; ?>
</ul>
