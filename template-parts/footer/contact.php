<?php
/**
 * Footer contact details (address, phones, emails) from the Contacto options.
 */

if (!defined('ABSPATH')) {
  exit;
}

$contact_details = gaia_get_contact_details();

if (!$contact_details['address'] && !$contact_details['phones'] && !$contact_details['emails']) {
  return;
}
?>
<address class="site-footer__contact">
  <?php if ($contact_details['address']) : ?>
    <div class="site-footer__contact-group">
      <p class="site-footer__contact-item">
        <?php echo gaia_get_icon_svg('ui/map-pin'); ?>
        <span><?php echo nl2br(esc_html($contact_details['address'])); ?></span>
      </p>
    </div>
  <?php endif; ?>

  <?php foreach (['phones' => ['ui/phone', 'number'], 'emails' => ['ui/mail', 'address']] as $contact_type => [$icon_path, $value_key]) : ?>
    <?php if ($contact_details[$contact_type]) : ?>
      <ul class="site-footer__contact-group" role="list">
        <?php foreach ($contact_details[$contact_type] as $contact_item) : ?>
          <li class="site-footer__contact-item">
            <?php echo gaia_get_icon_svg($icon_path); ?>
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
