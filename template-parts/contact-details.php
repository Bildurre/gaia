<?php
/**
 * Contact details (address, phones, emails) from the Contacto options, in the
 * footer and in the contact block. Icon style from Contacto > Iconos (classes
 * from inc/option-styles.php).
 *
 * @param array $args ['class' => extra CSS class, 'types' => address|phones|emails to show, 'is_sample' => bool]
 */

if (!defined('ABSPATH')) {
  exit;
}

$contact_types = $args['types'] ?? ['address', 'phones', 'emails'];
$contact_details = array_intersect_key(
  gaia_get_contact_details(),
  array_flip($contact_types)
) + ['address' => '', 'phones' => [], 'emails' => []];
$is_sample = !empty($args['is_sample']);
$contact_classes = array_merge(
  ['contact-details', $args['class'] ?? ''],
  gaia_get_option_classes(['contact_icons_show', 'contact_icons_shape', 'contact_icons_fill'])
);

if (!$contact_details['address'] && !$contact_details['phones'] && !$contact_details['emails']) {
  return;
}
?>
<address class="<?php echo esc_attr(implode(' ', array_filter($contact_classes))); ?>">
  <?php if ($contact_details['address']) : ?>
    <div class="contact-details__group">
      <p class="contact-details__item">
        <?php echo gaia_get_contact_icon_markup('address', $is_sample); ?>
        <span><?php echo nl2br(esc_html($contact_details['address'])); ?></span>
      </p>
    </div>
  <?php endif; ?>

  <?php foreach (['phones' => ['phone', 'number'], 'emails' => ['email', 'address']] as $contact_type => [$icon_type, $value_key]) : ?>
    <?php if ($contact_details[$contact_type]) : ?>
      <ul class="contact-details__group" role="list">
        <?php foreach ($contact_details[$contact_type] as $contact_item) : ?>
          <li class="contact-details__item">
            <?php echo gaia_get_contact_icon_markup($icon_type, $is_sample); ?>
            <span class="contact-details__text">
              <a href="<?php echo esc_attr($contact_item['href']); ?>"><?php echo esc_html($contact_item[$value_key]); ?></a>
              <?php if ($contact_item['label']) : ?>
                <span class="contact-details__label"><?php echo esc_html($contact_item['label']); ?></span>
              <?php endif; ?>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  <?php endforeach; ?>
</address>
