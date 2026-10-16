<?php
/**
 * Front-end login and account area.
 *
 * Pages are regular WordPress pages using the "Login" and "Account" page
 * templates (one per language with Polylang). Users without the
 * `edit_posts` capability never see wp-admin: they use the account page.
 */

if (!defined('ABSPATH')) {
  exit;
}

const GAIA_LOGIN_TEMPLATE = 'page-templates/login.php';
const GAIA_ACCOUNT_TEMPLATE = 'page-templates/account.php';
const GAIA_MINIMUM_PASSWORD_LENGTH = 8;

/* -------------------------------------------------------------------------
 * Page lookup
 * ---------------------------------------------------------------------- */

function gaia_get_page_id_by_template($template_file) {
  static $page_ids_by_template = [];

  if (!array_key_exists($template_file, $page_ids_by_template)) {
    $page_ids = get_posts([
      'post_type' => 'page',
      'post_status' => 'publish',
      'meta_key' => '_wp_page_template',
      'meta_value' => $template_file,
      'posts_per_page' => 1,
      'fields' => 'ids',
      'no_found_rows' => true,
      'lang' => '', // Polylang: search in every language
    ]);

    $page_ids_by_template[$template_file] = (int) ($page_ids[0] ?? 0);
  }

  $page_id = $page_ids_by_template[$template_file];

  if ($page_id && function_exists('pll_get_post')) {
    $translated_page_id = pll_get_post($page_id);
    $page_id = $translated_page_id ? (int) $translated_page_id : $page_id;
  }

  return $page_id;
}

function gaia_get_login_page_url($query_args = []) {
  $page_id = gaia_get_page_id_by_template(GAIA_LOGIN_TEMPLATE);

  return $page_id ? add_query_arg($query_args, get_permalink($page_id)) : '';
}

function gaia_get_account_page_url($query_args = []) {
  $page_id = gaia_get_page_id_by_template(GAIA_ACCOUNT_TEMPLATE);

  return $page_id ? add_query_arg($query_args, get_permalink($page_id)) : '';
}

/**
 * Front-end personal area for every logged-in user (admins included).
 * Falls back to the wp-admin profile if the account page does not exist.
 */
function gaia_get_user_account_url() {
  return gaia_get_account_page_url() ?: get_edit_profile_url();
}

/**
 * Profile section (details + password) of the account page.
 */
function gaia_get_user_profile_url() {
  return gaia_get_account_page_url(['section' => 'profile']) ?: get_edit_profile_url();
}

function gaia_user_can_use_admin($user_id = null) {
  return user_can($user_id ?? get_current_user_id(), 'edit_posts');
}

/* -------------------------------------------------------------------------
 * Core URLs pointing to the front-end pages
 * ---------------------------------------------------------------------- */

function gaia_filter_login_url($login_url, $redirect_url) {
  $custom_login_url = gaia_get_login_page_url($redirect_url ? ['redirect_to' => rawurlencode($redirect_url)] : []);

  return $custom_login_url ?: $login_url;
}
add_filter('login_url', 'gaia_filter_login_url', 10, 2);

function gaia_filter_lostpassword_url($lostpassword_url) {
  return gaia_get_login_page_url(['action' => 'lostpassword']) ?: $lostpassword_url;
}
add_filter('lostpassword_url', 'gaia_filter_lostpassword_url');

function gaia_filter_edit_profile_url($profile_url, $user_id) {
  if (gaia_user_can_use_admin($user_id)) {
    return $profile_url;
  }

  return gaia_get_account_page_url(['section' => 'profile']) ?: $profile_url;
}
add_filter('edit_profile_url', 'gaia_filter_edit_profile_url', 10, 2);

/**
 * Sends wp-login.php visitors to the front-end pages. Log out, password
 * protected posts, privacy confirmations and the admin re-login modal keep
 * using wp-login.php.
 */
function gaia_redirect_core_login_page() {
  if ($_SERVER['REQUEST_METHOD'] !== 'GET' || isset($_GET['interim-login'])) {
    return;
  }

  $login_action = sanitize_key($_GET['action'] ?? 'login');
  $redirect_to = isset($_GET['redirect_to']) ? wp_unslash($_GET['redirect_to']) : '';
  $target_url = '';

  if ($login_action === 'login') {
    $target_url = gaia_get_login_page_url($redirect_to ? ['redirect_to' => rawurlencode($redirect_to)] : []);
  } elseif (in_array($login_action, ['lostpassword', 'retrievepassword'], true)) {
    $target_url = gaia_get_login_page_url(['action' => 'lostpassword']);
  } elseif (in_array($login_action, ['rp', 'resetpass'], true)) {
    $target_url = gaia_get_login_page_url([
      'action' => 'resetpassword',
      'key' => rawurlencode(wp_unslash($_GET['key'] ?? '')),
      'login' => rawurlencode(wp_unslash($_GET['login'] ?? '')),
    ]);
  }

  if ($target_url) {
    wp_safe_redirect($target_url);
    exit;
  }
}
add_action('login_init', 'gaia_redirect_core_login_page');

/* -------------------------------------------------------------------------
 * Keep regular users out of wp-admin
 * ---------------------------------------------------------------------- */

function gaia_restrict_admin_access() {
  if (wp_doing_ajax() || gaia_user_can_use_admin()) {
    return;
  }

  $account_page_url = gaia_get_account_page_url();

  if ($account_page_url) {
    wp_safe_redirect($account_page_url);
    exit;
  }
}
add_action('admin_init', 'gaia_restrict_admin_access');

function gaia_filter_show_admin_bar($should_show_admin_bar) {
  return $should_show_admin_bar && gaia_user_can_use_admin();
}
add_filter('show_admin_bar', 'gaia_filter_show_admin_bar');

function gaia_get_redirect_after_login($requested_redirect_url, $user) {
  $account_page_url = gaia_get_account_page_url() ?: home_url('/');
  $redirect_url = wp_validate_redirect($requested_redirect_url, $account_page_url);
  $is_admin_url = str_starts_with($redirect_url, admin_url());

  if ($is_admin_url && !gaia_user_can_use_admin($user->ID)) {
    return $account_page_url;
  }

  return $redirect_url;
}

/* -------------------------------------------------------------------------
 * Form handling (runs before the page template renders)
 * ---------------------------------------------------------------------- */

/**
 * Errors of the form submitted in this request, read by the templates.
 */
function gaia_get_form_errors(?WP_Error $new_errors = null) {
  static $form_errors = null;

  if ($new_errors) {
    $form_errors = $new_errors;
  }

  return $form_errors ?? new WP_Error();
}

function gaia_get_submitted_value($field_name) {
  return isset($_POST[$field_name]) ? sanitize_text_field(wp_unslash($_POST[$field_name])) : '';
}

function gaia_handle_account_forms() {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['gaia_form'])) {
    return;
  }

  $form_name = sanitize_key($_POST['gaia_form']);
  $form_handlers = [
    'login' => 'gaia_handle_login_form',
    'lostpassword' => 'gaia_handle_lost_password_form',
    'resetpassword' => 'gaia_handle_reset_password_form',
    'profile' => 'gaia_handle_profile_form',
    'password' => 'gaia_handle_password_form',
  ];

  if (!isset($form_handlers[$form_name])) {
    return;
  }

  if (!wp_verify_nonce(sanitize_key($_POST['_gaia_nonce'] ?? ''), 'gaia_' . $form_name)) {
    gaia_get_form_errors(new WP_Error('invalid_nonce', __('The form has expired. Please try again.', 'gaia')));
    return;
  }

  $form_errors = call_user_func($form_handlers[$form_name]);

  if (is_wp_error($form_errors)) {
    gaia_get_form_errors($form_errors);
  }
}
add_action('template_redirect', 'gaia_handle_account_forms');

function gaia_handle_login_form() {
  $user = wp_signon([
    'user_login' => gaia_get_submitted_value('user_login'),
    'user_password' => $_POST['user_password'] ?? '',
    'remember' => !empty($_POST['remember']),
  ], is_ssl());

  if (is_wp_error($user)) {
    // Generic message: do not reveal whether the user exists.
    return new WP_Error('login_failed', __('The username or password is incorrect.', 'gaia'));
  }

  $requested_redirect_url = isset($_POST['redirect_to']) ? wp_unslash($_POST['redirect_to']) : '';
  wp_safe_redirect(gaia_get_redirect_after_login($requested_redirect_url, $user));
  exit;
}

function gaia_handle_lost_password_form() {
  $user_login = gaia_get_submitted_value('user_login');

  if ($user_login === '') {
    return new WP_Error('empty_login', __('Enter your username or email address.', 'gaia'));
  }

  // Same response whether the account exists or not.
  retrieve_password($user_login);

  wp_safe_redirect(gaia_get_login_page_url(['status' => 'reset-email-sent']));
  exit;
}

function gaia_handle_reset_password_form() {
  $reset_key = wp_unslash($_POST['reset_key'] ?? '');
  $user_login = wp_unslash($_POST['reset_login'] ?? '');
  $user = check_password_reset_key($reset_key, $user_login);

  if (is_wp_error($user)) {
    return new WP_Error('invalid_key', __('This password reset link is invalid or has expired. Request a new one.', 'gaia'));
  }

  $password_errors = gaia_validate_new_password($_POST['new_password'] ?? '', $_POST['confirm_password'] ?? '');

  if ($password_errors->has_errors()) {
    return $password_errors;
  }

  reset_password($user, $_POST['new_password']);

  wp_safe_redirect(gaia_get_login_page_url(['status' => 'password-reset']));
  exit;
}

function gaia_handle_profile_form() {
  $current_user = wp_get_current_user();

  if (!$current_user->exists()) {
    return new WP_Error('not_logged_in', __('You must log in first.', 'gaia'));
  }

  $new_email = sanitize_email(wp_unslash($_POST['user_email'] ?? ''));
  $new_user_login = sanitize_user(wp_unslash($_POST['user_login'] ?? ''), true);
  $is_email_changed = $new_email !== $current_user->user_email;
  $is_user_login_changed = $new_user_login !== $current_user->user_login;
  $form_errors = new WP_Error();

  if (!is_email($new_email)) {
    $form_errors->add('invalid_email', __('Enter a valid email address.', 'gaia'));
  } elseif ($is_email_changed) {
    $existing_user_id = email_exists($new_email);

    if ($existing_user_id && $existing_user_id !== $current_user->ID) {
      $form_errors->add('email_exists', __('That email address is already in use.', 'gaia'));
    }
  }

  if ($is_user_login_changed) {
    $user_login_error = gaia_validate_new_user_login($new_user_login, $current_user->ID);

    if ($user_login_error) {
      $form_errors->add('invalid_user_login', $user_login_error);
    }
  }

  if (($is_email_changed || $is_user_login_changed) && !wp_check_password($_POST['current_password'] ?? '', $current_user->user_pass, $current_user->ID)) {
    $form_errors->add('wrong_password', __('Enter your current password to change your username or email address.', 'gaia'));
  }

  if ($form_errors->has_errors()) {
    return $form_errors;
  }

  $avatar_result = !empty($_POST['remove_avatar'])
    ? gaia_delete_user_avatar($current_user->ID)
    : gaia_save_uploaded_avatar($current_user->ID, 'avatar');

  if (is_wp_error($avatar_result)) {
    return $avatar_result;
  }

  if ($is_user_login_changed) {
    gaia_change_user_login($current_user->ID, $new_user_login);
  }

  $first_name = gaia_get_submitted_value('first_name');
  $last_name = gaia_get_submitted_value('last_name');
  $full_name = trim($first_name . ' ' . $last_name);

  $update_result = wp_update_user([
    'ID' => $current_user->ID,
    'first_name' => $first_name,
    'last_name' => $last_name,
    'display_name' => $full_name !== '' ? $full_name : $new_user_login,
    'user_email' => $new_email,
  ]);

  if (is_wp_error($update_result)) {
    return $update_result;
  }

  wp_safe_redirect(gaia_get_account_page_url(['section' => 'profile', 'status' => 'profile-updated']));
  exit;
}

/**
 * Returns an error message, or an empty string if the username can be used.
 */
function gaia_validate_new_user_login($new_user_login, $user_id) {
  $illegal_user_logins = array_map('strtolower', (array) apply_filters('illegal_user_logins', []));

  if ($new_user_login === '' || !validate_username($new_user_login)) {
    return __('The username can only contain letters, numbers, spaces and the characters . - _ @', 'gaia');
  }

  if (mb_strlen($new_user_login) > 60) {
    return __('The username cannot be longer than 60 characters.', 'gaia');
  }

  $existing_user_id = username_exists($new_user_login);

  if (($existing_user_id && $existing_user_id !== $user_id) || in_array(strtolower($new_user_login), $illegal_user_logins, true)) {
    return __('That username is not available.', 'gaia');
  }

  return '';
}

/**
 * WordPress has no API to change the username: update the users table and
 * renew the auth cookie (it contains the username).
 */
function gaia_change_user_login($user_id, $new_user_login) {
  global $wpdb;

  $new_user_nicename = sanitize_title($new_user_login);
  $nicename_owner_id = (int) $wpdb->get_var($wpdb->prepare(
    "SELECT ID FROM {$wpdb->users} WHERE user_nicename = %s AND ID != %d LIMIT 1",
    $new_user_nicename,
    $user_id
  ));

  $updated_columns = ['user_login' => $new_user_login];

  if (!$nicename_owner_id && $new_user_nicename !== '') {
    $updated_columns['user_nicename'] = $new_user_nicename;
  }

  $wpdb->update($wpdb->users, $updated_columns, ['ID' => $user_id]);
  clean_user_cache($user_id);

  wp_set_current_user($user_id);
  wp_set_auth_cookie($user_id, false, is_ssl());
}

function gaia_handle_password_form() {
  $current_user = wp_get_current_user();

  if (!$current_user->exists()) {
    return new WP_Error('not_logged_in', __('You must log in first.', 'gaia'));
  }

  if (!wp_check_password($_POST['current_password'] ?? '', $current_user->user_pass, $current_user->ID)) {
    return new WP_Error('wrong_password', __('Your current password is incorrect.', 'gaia'));
  }

  $password_errors = gaia_validate_new_password($_POST['new_password'] ?? '', $_POST['confirm_password'] ?? '');

  if ($password_errors->has_errors()) {
    return $password_errors;
  }

  // wp_update_user() keeps the current user logged in after the change.
  $update_result = wp_update_user([
    'ID' => $current_user->ID,
    'user_pass' => $_POST['new_password'],
  ]);

  if (is_wp_error($update_result)) {
    return $update_result;
  }

  wp_safe_redirect(gaia_get_account_page_url(['section' => 'profile', 'status' => 'password-updated']));
  exit;
}

function gaia_validate_new_password($new_password, $confirm_password) {
  $password_errors = new WP_Error();

  if (mb_strlen($new_password) < GAIA_MINIMUM_PASSWORD_LENGTH) {
    $password_errors->add('password_too_short', sprintf(
      /* translators: %d: minimum number of characters */
      __('The password must be at least %d characters long.', 'gaia'),
      GAIA_MINIMUM_PASSWORD_LENGTH
    ));
  }

  if ($new_password !== $confirm_password) {
    $password_errors->add('password_mismatch', __('The passwords do not match.', 'gaia'));
  }

  return $password_errors;
}

/* -------------------------------------------------------------------------
 * Page access
 * ---------------------------------------------------------------------- */

function gaia_protect_account_pages() {
  if (is_page_template(GAIA_ACCOUNT_TEMPLATE) && !is_user_logged_in()) {
    wp_safe_redirect(wp_login_url(get_permalink()));
    exit;
  }

  if (is_page_template(GAIA_LOGIN_TEMPLATE) && is_user_logged_in() && empty($_GET['action'])) {
    wp_safe_redirect(gaia_get_account_page_url() ?: home_url('/'));
    exit;
  }
}
add_action('template_redirect', 'gaia_protect_account_pages', 5);

/* -------------------------------------------------------------------------
 * Password reset email pointing to the front-end page
 * ---------------------------------------------------------------------- */

function gaia_filter_password_reset_email($email, $reset_key, $user_login, $user) {
  $reset_page_url = gaia_get_login_page_url([
    'action' => 'resetpassword',
    'key' => $reset_key,
    'login' => rawurlencode($user_login),
  ]);

  if (!$reset_page_url) {
    return $email;
  }

  $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);

  $email['subject'] = sprintf(
    /* translators: %s: site name */
    __('[%s] Reset your password', 'gaia'),
    $site_name
  );

  $email['message'] = implode("\r\n\r\n", [
    sprintf(
      /* translators: %s: user display name */
      __('Hello %s,', 'gaia'),
      $user->display_name
    ),
    sprintf(
      /* translators: %s: site name */
      __('Someone has requested a password reset for your account on %s. If it was not you, ignore this email and nothing will change.', 'gaia'),
      $site_name
    ),
    __('To choose a new password, open this link:', 'gaia'),
    $reset_page_url,
  ]) . "\r\n";

  return $email;
}
add_filter('retrieve_password_notification_email', 'gaia_filter_password_reset_email', 10, 4);

/* -------------------------------------------------------------------------
 * Template helpers
 * ---------------------------------------------------------------------- */

function gaia_form_hidden_fields($form_name) {
  printf('<input type="hidden" name="gaia_form" value="%s">', esc_attr($form_name));
  wp_nonce_field('gaia_' . $form_name, '_gaia_nonce');
}

function gaia_get_status_message($status_slug) {
  $status_messages = [
    'reset-email-sent' => __('If an account exists with that username or email, you will receive an email with a link to reset your password.', 'gaia'),
    'password-reset' => __('Your password has been changed. You can log in now.', 'gaia'),
    'profile-updated' => __('Your details have been saved.', 'gaia'),
    'password-updated' => __('Your password has been changed.', 'gaia'),
  ];

  return $status_messages[$status_slug] ?? '';
}
