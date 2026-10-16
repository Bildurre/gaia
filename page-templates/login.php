<?php
/**
 * Template Name: Login
 *
 * Log in, lost password and reset password forms (?action=lostpassword|resetpassword).
 */

if (!defined('ABSPATH')) {
  exit;
}

$login_action = sanitize_key($_GET['action'] ?? 'login');
$login_views = [
  'login' => [
    'title' => __('Log in', 'gaia'),
    'template_part' => 'login-form',
  ],
  'lostpassword' => [
    'title' => __('Forgot your password?', 'gaia'),
    'template_part' => 'lost-password-form',
  ],
  'resetpassword' => [
    'title' => __('Choose a new password', 'gaia'),
    'template_part' => 'reset-password-form',
  ],
];
$login_view = $login_views[$login_action] ?? $login_views['login'];

get_header();
?>

<div class="account-page account-page--narrow container">
  <div class="account-card">
    <h1 class="account-card__title"><?php echo esc_html($login_view['title']); ?></h1>

    <?php get_template_part('template-parts/account/form-notices'); ?>
    <?php get_template_part('template-parts/account/' . $login_view['template_part']); ?>
  </div>
</div>

<?php
get_footer();
