<?php
/**
 * Site footer (Ajustes de Gaia > Pie de página). Also printed as a sample on
 * the options pages, once per layout ($args['layout']).
 *
 * @param array $args ['layout' => layout to use instead of the setting]
 */

if (!defined('ABSPATH')) {
  exit;
}

$footer_settings = gaia_get_footer_settings();
$footer_layout = $args['layout'] ?? $footer_settings['layout'];
$footer_tagline = gaia_get_translated_option_text('footer_tagline');
$footer_credits = gaia_get_translated_option_html('footer_credits');
$footer_logo_markup = gaia_get_logo_markup($footer_settings['logo']);

// Samples carry every logo type so they can switch without reloading
if (!empty($args['is_sample'])) {
  $footer_logo_markup = '';

  foreach (array_keys(gaia_get_logo_type_choices()) as $logo_type) {
    $footer_logo_markup .= sprintf(
      '<span data-variant-value="%s"%s>%s</span>',
      esc_attr($logo_type),
      $logo_type === $footer_settings['logo'] ? '' : ' hidden',
      gaia_get_logo_markup($logo_type)
    );
  }
}
?>

<footer class="<?php echo esc_attr(gaia_get_footer_classes($footer_layout)); ?>">
  <?php if ($footer_layout === 'minimal') : ?>
    <div class="site-footer__inner container">
      <?php if ($footer_logo_markup) : ?>
        <a class="site-footer__logo" href="<?php echo esc_url(home_url('/')); ?>"<?php echo empty($args['is_sample']) ? '' : ' data-sample-variants="footer_logo"'; ?>><?php echo $footer_logo_markup; ?></a>
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
          <a class="site-footer__logo" href="<?php echo esc_url(home_url('/')); ?>"<?php echo empty($args['is_sample']) ? '' : ' data-sample-variants="footer_logo"'; ?>><?php echo $footer_logo_markup; ?></a>
        <?php endif; ?>

        <?php if ($footer_tagline) : ?>
          <p class="site-footer__tagline"><?php echo esc_html($footer_tagline); ?></p>
        <?php endif; ?>

        <?php if ($footer_layout === 'columns' && $footer_settings['shows_social']) : ?>
          <?php get_template_part('template-parts/social-links', null, ['class' => 'site-footer__social']); ?>
        <?php endif; ?>
      </div>

      <?php if ($footer_layout === 'brand-contact' && $footer_settings['shows_social']) : ?>
        <?php get_template_part('template-parts/social-links', null, ['class' => 'site-footer__social']); ?>
      <?php endif; ?>

      <?php if ($footer_layout === 'columns') : ?>
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
