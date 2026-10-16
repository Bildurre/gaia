<?php
if (!defined('ABSPATH')) {
  exit;
}
?>
<div class="form__field">
  <label class="form__label" for="new_password"><?php esc_html_e('New password', 'gaia'); ?></label>
  <input class="form__input" type="password" id="new_password" name="new_password" autocomplete="new-password" minlength="<?php echo esc_attr(GAIA_MINIMUM_PASSWORD_LENGTH); ?>" required aria-describedby="new_password_hint">
  <p class="form__hint" id="new_password_hint">
    <?php
    printf(
      /* translators: %d: minimum number of characters */
      esc_html__('At least %d characters.', 'gaia'),
      (int) GAIA_MINIMUM_PASSWORD_LENGTH
    );
    ?>
  </p>
</div>

<div class="form__field">
  <label class="form__label" for="confirm_password"><?php esc_html_e('Repeat the new password', 'gaia'); ?></label>
  <input class="form__input" type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
</div>
