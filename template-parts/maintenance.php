<?php
/**
 * Maintenance page (503) for visitors without editing rights.
 */

if (!defined('ABSPATH')) {
  exit;
}

$maintenance_title = gaia_get_translated_option_text('extras_maintenance_title') ?: __('We will be back soon', 'gaia');
$maintenance_text = gaia_get_translated_option_text('extras_maintenance_text') ?: __('We are working on the website. Please come back in a little while.', 'gaia');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <?php wp_head(); ?>
</head>
<body <?php body_class('is-maintenance'); ?>>
  <main class="maintenance-page" id="main">
    <div class="maintenance-page__content container">
      <?php echo gaia_get_logo_markup('main'); ?>
      <h1><?php echo esc_html($maintenance_title); ?></h1>
      <p><?php echo nl2br(esc_html($maintenance_text)); ?></p>
      <a class="maintenance-page__login" href="<?php echo esc_url(wp_login_url()); ?>"><?php echo esc_html__('Log in'); ?></a>
    </div>
  </main>
  <?php wp_footer(); ?>
</body>
</html>
