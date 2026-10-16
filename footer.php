</main>

<?php
$footer_logo_id = (int) gaia_get_option_field('footer_logo');
$footer_tagline = gaia_get_translated_option_text('footer_tagline');
$footer_credits = gaia_get_translated_option_html('footer_credits');
$contact_details = gaia_get_contact_details();
$has_contact_details = $contact_details['address'] || $contact_details['phones'] || $contact_details['emails'];
?>

<footer class="site-footer">
  <div class="site-footer__inner container">
    <div class="site-footer__top">
      <div class="site-footer__brand">
        <a class="site-footer__logo" href="<?php echo esc_url(home_url('/')); ?>">
          <?php if ($footer_logo_id) : ?>
            <?php echo wp_get_attachment_image($footer_logo_id, 'medium', false, ['alt' => get_bloginfo('name')]); ?>
          <?php else : ?>
            <?php bloginfo('name'); ?>
          <?php endif; ?>
        </a>

        <?php if ($footer_tagline) : ?>
          <p class="site-footer__tagline"><?php echo esc_html($footer_tagline); ?></p>
        <?php endif; ?>
      </div>

      <?php get_template_part('template-parts/social-links', null, ['class' => 'site-footer__social']); ?>
    </div>

    <?php if ($has_contact_details) : ?>
      <address class="site-footer__contact">
        <?php if ($contact_details['address']) : ?>
          <div class="site-footer__contact-group">
            <p class="site-footer__contact-item">
              <?php echo gaia_get_icon_svg('ui/map-pin'); ?>
              <span><?php echo nl2br(esc_html($contact_details['address'])); ?></span>
            </p>
          </div>
        <?php endif; ?>

        <?php if ($contact_details['phones']) : ?>
          <ul class="site-footer__contact-group" role="list">
            <?php foreach ($contact_details['phones'] as $phone) : ?>
              <li class="site-footer__contact-item">
                <?php echo gaia_get_icon_svg('ui/phone'); ?>
                <span class="site-footer__contact-text">
                  <a href="<?php echo esc_attr($phone['href']); ?>"><?php echo esc_html($phone['number']); ?></a>
                  <?php if ($phone['label']) : ?>
                    <span class="site-footer__contact-label"><?php echo esc_html($phone['label']); ?></span>
                  <?php endif; ?>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <?php if ($contact_details['emails']) : ?>
          <ul class="site-footer__contact-group" role="list">
            <?php foreach ($contact_details['emails'] as $email) : ?>
              <li class="site-footer__contact-item">
                <?php echo gaia_get_icon_svg('ui/mail'); ?>
                <span class="site-footer__contact-text">
                  <a href="<?php echo esc_attr($email['href']); ?>"><?php echo esc_html($email['address']); ?></a>
                  <?php if ($email['label']) : ?>
                    <span class="site-footer__contact-label"><?php echo esc_html($email['label']); ?></span>
                  <?php endif; ?>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </address>
    <?php endif; ?>
  </div>

  <?php if ($footer_credits) : ?>
    <div class="site-footer__credits container">
      <div class="site-footer__credits-inner"><?php echo $footer_credits; ?></div>
    </div>
  <?php endif; ?>
</footer>

<?php wp_footer(); ?>
</body>
</html>
