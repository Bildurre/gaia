<?php
if (!defined('ABSPATH')) {
  exit;
}

$current_user = wp_get_current_user();
$is_profile_form_submitted = ($_POST['gaia_form'] ?? '') === 'profile';
$field_values = [
  'user_login' => $is_profile_form_submitted ? gaia_get_submitted_value('user_login') : $current_user->user_login,
  'first_name' => $is_profile_form_submitted ? gaia_get_submitted_value('first_name') : $current_user->first_name,
  'last_name' => $is_profile_form_submitted ? gaia_get_submitted_value('last_name') : $current_user->last_name,
  'user_email' => $is_profile_form_submitted ? gaia_get_submitted_value('user_email') : $current_user->user_email,
];
$has_custom_avatar = (bool) gaia_get_user_avatar_id($current_user->ID);
?>
<form class="form" method="post" action="" enctype="multipart/form-data">
  <?php gaia_form_hidden_fields('profile'); ?>

  <div
    class="avatar-field"
    data-avatar-field
    data-error-type="<?php esc_attr_e('The file must be a JPG, PNG, WebP or GIF image.', 'gaia'); ?>"
    data-error-size="<?php esc_attr_e('The image is larger than 2 MB.', 'gaia'); ?>"
  >
    <?php echo get_avatar($current_user->ID, 160, '', '', ['class' => 'avatar-field__image', 'extra_attr' => 'data-avatar-preview']); ?>

    <div class="avatar-field__controls">
      <span class="form__label" aria-hidden="true"><?php esc_html_e('Profile picture', 'gaia'); ?></span>

      <?php // The native file input is visually hidden: its button texts cannot be translated. ?>
      <label class="avatar-dropzone" data-avatar-dropzone>
        <input class="visually-hidden" type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" aria-describedby="avatar_hint avatar_status" data-avatar-input>
        <?php echo gaia_get_icon_svg('ui/image-up'); ?>
        <span class="avatar-dropzone__text">
          <?php
          printf(
            /* translators: %s: "choose an image" link-like text */
            esc_html__('Drag an image here or %s', 'gaia'),
            '<span class="avatar-dropzone__action">' . esc_html__('choose a file', 'gaia') . '</span>'
          );
          ?>
        </span>
      </label>

      <p class="avatar-field__status" id="avatar_status" aria-live="polite" data-avatar-status></p>
      <p class="form__hint" id="avatar_hint"><?php esc_html_e('JPG, PNG, WebP or GIF, up to 2 MB. A square image looks best.', 'gaia'); ?></p>

      <?php if ($has_custom_avatar) : ?>
        <label class="form__checkbox">
          <input type="checkbox" name="remove_avatar" value="1">
          <span><?php esc_html_e('Remove profile picture', 'gaia'); ?></span>
        </label>
      <?php endif; ?>
    </div>
  </div>

  <div class="form__field">
    <label class="form__label" for="profile_user_login"><?php esc_html_e('Username', 'gaia'); ?></label>
    <input class="form__input" type="text" id="profile_user_login" name="user_login" autocomplete="username" required maxlength="60" value="<?php echo esc_attr($field_values['user_login']); ?>" aria-describedby="profile_user_login_hint">
    <p class="form__hint" id="profile_user_login_hint"><?php esc_html_e('You use it to log in, together with your email address.', 'gaia'); ?></p>
  </div>

  <div class="form__row">
    <div class="form__field">
      <label class="form__label" for="first_name"><?php esc_html_e('First name', 'gaia'); ?></label>
      <input class="form__input" type="text" id="first_name" name="first_name" autocomplete="given-name" value="<?php echo esc_attr($field_values['first_name']); ?>">
    </div>

    <div class="form__field">
      <label class="form__label" for="last_name"><?php esc_html_e('Last name', 'gaia'); ?></label>
      <input class="form__input" type="text" id="last_name" name="last_name" autocomplete="family-name" value="<?php echo esc_attr($field_values['last_name']); ?>">
    </div>
  </div>

  <div class="form__field">
    <label class="form__label" for="user_email"><?php esc_html_e('Email', 'gaia'); ?></label>
    <input class="form__input" type="email" id="user_email" name="user_email" autocomplete="email" required value="<?php echo esc_attr($field_values['user_email']); ?>">
  </div>

  <div class="form__field">
    <label class="form__label" for="profile_current_password"><?php esc_html_e('Current password', 'gaia'); ?></label>
    <input class="form__input" type="password" id="profile_current_password" name="current_password" autocomplete="off" aria-describedby="profile_current_password_hint" data-no-autofill>
    <p class="form__hint" id="profile_current_password_hint"><?php esc_html_e('Only needed if you change your username or email address.', 'gaia'); ?></p>
  </div>

  <div class="form__actions">
    <button class="button" type="submit"><?php esc_html_e('Save changes', 'gaia'); ?></button>
  </div>
</form>
