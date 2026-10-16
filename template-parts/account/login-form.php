<?php
if (!defined('ABSPATH')) {
  exit;
}

$redirect_to = isset($_GET['redirect_to']) ? wp_unslash($_GET['redirect_to']) : '';
?>
<form class="form" method="post" action="">
  <?php gaia_form_hidden_fields('login'); ?>
  <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>">

  <div class="form__field">
    <label class="form__label" for="user_login"><?php esc_html_e('Username or email', 'gaia'); ?></label>
    <input class="form__input" type="text" id="user_login" name="user_login" autocomplete="username" required value="<?php echo esc_attr(gaia_get_submitted_value('user_login')); ?>">
  </div>

  <div class="form__field">
    <label class="form__label" for="user_password"><?php esc_html_e('Password', 'gaia'); ?></label>
    <input class="form__input" type="password" id="user_password" name="user_password" autocomplete="current-password" required>
  </div>

  <label class="form__checkbox">
    <input type="checkbox" name="remember" value="1">
    <span><?php esc_html_e('Remember me', 'gaia'); ?></span>
  </label>

  <div class="form__actions">
    <button class="button" type="submit"><?php esc_html_e('Log in', 'gaia'); ?></button>
    <a href="<?php echo esc_url(gaia_get_login_page_url(['action' => 'lostpassword'])); ?>"><?php esc_html_e('Forgot your password?', 'gaia'); ?></a>
  </div>
</form>
