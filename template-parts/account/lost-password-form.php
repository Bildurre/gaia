<?php
if (!defined('ABSPATH')) {
  exit;
}
?>
<p class="account-card__intro"><?php esc_html_e('Enter your username or email address and we will send you a link to choose a new password.', 'gaia'); ?></p>

<form class="form" method="post" action="">
  <?php gaia_form_hidden_fields('lostpassword'); ?>

  <div class="form__field">
    <label class="form__label" for="user_login"><?php esc_html_e('Username or email', 'gaia'); ?></label>
    <input class="form__input" type="text" id="user_login" name="user_login" autocomplete="username" required value="<?php echo esc_attr(gaia_get_submitted_value('user_login')); ?>">
  </div>

  <div class="form__actions">
    <button class="button" type="submit"><?php esc_html_e('Send link', 'gaia'); ?></button>
    <a href="<?php echo esc_url(gaia_get_login_page_url()); ?>"><?php esc_html_e('Back to log in', 'gaia'); ?></a>
  </div>
</form>
