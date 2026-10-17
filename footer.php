</main>

<?php
$footer_settings = gaia_get_footer_settings();
$footer_tagline = gaia_get_translated_option_text('footer_tagline');
$footer_credits = gaia_get_translated_option_html('footer_credits');
$footer_logo_markup = gaia_get_logo_markup($footer_settings['logo']);
?>

<?php if (!$footer_settings['is_hidden']) : ?>
<footer class="<?php echo esc_attr(gaia_get_footer_classes()); ?>">
  <?php if ($footer_settings['layout'] === 'minimal') : ?>
    <div class="site-footer__inner container">
      <?php if ($footer_logo_markup) : ?>
        <a class="site-footer__logo" href="<?php echo esc_url(home_url('/')); ?>"><?php echo $footer_logo_markup; ?></a>
      <?php endif; ?>

      <?php gaia_footer_menu('legal', __('Legal', 'gaia'), 'site-footer__legal'); ?>

      <?php if ($footer_settings['shows_social']) : ?>
        <?php get_template_part('template-parts/social-links', null, ['class' => 'site-footer__social']); ?>
      <?php endif; ?>

      <?php if ($footer_credits) : ?>
        <div class="site-footer__credits"><?php echo $footer_credits; ?></div>
      <?php endif; ?>
    </div>
  <?php else : ?>
    <div class="site-footer__inner container">
      <div class="site-footer__brand">
        <?php if ($footer_logo_markup) : ?>
          <a class="site-footer__logo" href="<?php echo esc_url(home_url('/')); ?>"><?php echo $footer_logo_markup; ?></a>
        <?php endif; ?>

        <?php if ($footer_tagline) : ?>
          <p class="site-footer__tagline"><?php echo esc_html($footer_tagline); ?></p>
        <?php endif; ?>

        <?php if ($footer_settings['layout'] === 'columns' && $footer_settings['shows_social']) : ?>
          <?php get_template_part('template-parts/social-links', null, ['class' => 'site-footer__social']); ?>
        <?php endif; ?>
      </div>

      <?php if ($footer_settings['layout'] === 'brand-contact' && $footer_settings['shows_social']) : ?>
        <?php get_template_part('template-parts/social-links', null, ['class' => 'site-footer__social']); ?>
      <?php endif; ?>

      <?php if ($footer_settings['layout'] === 'columns') : ?>
        <?php gaia_footer_menu('footer', __('Footer menu', 'gaia'), 'site-footer__menu'); ?>
      <?php endif; ?>

      <?php if ($footer_settings['shows_contact']) : ?>
        <?php get_template_part('template-parts/footer/contact'); ?>
      <?php endif; ?>
    </div>

    <?php if ($footer_credits || has_nav_menu('legal')) : ?>
      <div class="site-footer__bottom container">
        <div class="site-footer__bottom-inner">
          <?php gaia_footer_menu('legal', __('Legal', 'gaia'), 'site-footer__legal'); ?>

          <?php if ($footer_credits) : ?>
            <div class="site-footer__credits"><?php echo $footer_credits; ?></div>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</footer>
<?php endif; ?>

<?php if (gaia_shows_back_to_top_button()) : ?>
  <a class="back-to-top" href="#" data-back-to-top>
    <?php echo gaia_get_icon_svg('ui/arrow-up'); ?>
    <span class="visually-hidden"><?php esc_html_e('Back to top', 'gaia'); ?></span>
  </a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
