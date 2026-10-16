<?php
if (!defined('ABSPATH')) {
  exit;
}

$reset_key = wp_unslash($_GET['key'] ?? ($_POST['reset_key'] ?? ''));
$reset_login = wp_unslash($_GET['login'] ?? ($_POST['reset_login'] ?? ''));
$is_valid_reset_key = !is_wp_error(check_password_reset_key($reset_key, $reset_login));
?>

<?php if (!$is_valid_reset_key) : ?>
  <div class="form-notice form-notice--error" role="alert">
    <p><?php esc_html_e('This password reset link is invalid or has expired. Request a new one.', 'gaia'); ?></p>
  </div>
  <p><a href="<?php echo esc_url(gaia_get_login_page_url(['action' => 'lostpassword'])); ?>"><?php esc_html_e('Request a new link', 'gaia'); ?></a></p>
<?php else : ?>
  <form class="form" method="post" action="">
    <?php gaia_form_hidden_fields('resetpassword'); ?>
    <input type="hidden" name="reset_key" value="<?php echo esc_attr($reset_key); ?>">
    <input type="hidden" name="reset_login" value="<?php echo esc_attr($reset_login); ?>">

    <?php get_template_part('template-parts/account/new-password-fields'); ?>

    <div class="form__actions">
      <button class="button" type="submit"><?php esc_html_e('Save password', 'gaia'); ?></button>
    </div>
  </form>
<?php endif; ?>
