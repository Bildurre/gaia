<?php
/**
 * Footer contact details (address, phones, emails) from the Contacto options.
 * Icon style from Contacto > Iconos (classes from inc/option-styles.php).
 *
 * @param array $args ['is_sample' => bool]
 */

if (!defined('ABSPATH')) {
  exit;
}

$contact_details = gaia_get_contact_details();
$is_sample = !empty($args['is_sample']);
$contact_classes = array_merge(['site-footer__contact', 'contact-details'], gaia_get_option_classes(['contact_icons_show', 'contact_icons_shape', 'contact_icons_fill']));

if (!$contact_details['address'] && !$contact_details['phones'] && !$contact_details['emails']) {
  return;
}
?>
<address class="<?php echo esc_attr(implode(' ', $contact_classes)); ?>">
  <?php if ($contact_details['address']) : ?>
    <div class="site-footer__contact-group">
      <p class="site-footer__contact-item">
        <?php echo gaia_get_contact_icon_markup('address', $is_sample); ?>
        <span><?php echo nl2br(esc_html($contact_details['address'])); ?></span>
      </p>
    </div>
  <?php endif; ?>

  <?php foreach (['phones' => ['phone', 'number'], 'emails' => ['email', 'address']] as $contact_type => [$icon_type, $value_key]) : ?>
    <?php if ($contact_details[$contact_type]) : ?>
      <ul class="site-footer__contact-group" role="list">
        <?php foreach ($contact_details[$contact_type] as $contact_item) : ?>
          <li class="site-footer__contact-item">
            <?php echo gaia_get_contact_icon_markup($icon_type, $is_sample); ?>
            <span class="site-footer__contact-text">
              <a href="<?php echo esc_attr($contact_item['href']); ?>"><?php echo esc_html($contact_item[$value_key]); ?></a>
              <?php if ($contact_item['label']) : ?>
                <span class="site-footer__contact-label"><?php echo esc_html($contact_item['label']); ?></span>
              <?php endif; ?>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  <?php endforeach; ?>
</address>
