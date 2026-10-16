<?php
if (!defined('ABSPATH')) {
  exit;
}
?>
<form class="form" method="post" action="">
  <?php gaia_form_hidden_fields('password'); ?>

  <div class="form__field">
    <label class="form__label" for="password_current_password"><?php esc_html_e('Current password', 'gaia'); ?></label>
    <input class="form__input" type="password" id="password_current_password" name="current_password" autocomplete="off" required data-no-autofill>
  </div>

  <?php get_template_part('template-parts/account/new-password-fields'); ?>

  <div class="form__actions">
    <button class="button" type="submit"><?php esc_html_e('Change password', 'gaia'); ?></button>
  </div>
</form>
