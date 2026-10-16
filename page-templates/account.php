<?php
/**
 * Template Name: Account
 *
 * Personal area of logged-in users ("My area") and, with ?section=profile,
 * their profile (details + password). Visitors are redirected to log in.
 */

if (!defined('ABSPATH')) {
  exit;
}

$current_user = wp_get_current_user();
$is_profile_section = sanitize_key($_GET['section'] ?? '') === 'profile';

get_header();
?>

<div class="account-page container">
  <?php if ($is_profile_section) : ?>
    <header class="account-page__header">
      <h1><?php esc_html_e('Profile', 'gaia'); ?></h1>
    </header>

    <?php get_template_part('template-parts/account/form-notices'); ?>

    <div class="account-page__sections">
      <section class="account-card" aria-labelledby="account-details-title">
        <h2 class="account-card__title" id="account-details-title"><?php esc_html_e('Your details', 'gaia'); ?></h2>
        <?php get_template_part('template-parts/account/profile-form'); ?>
      </section>

      <section class="account-card" aria-labelledby="account-password-title">
        <h2 class="account-card__title" id="account-password-title"><?php esc_html_e('Password', 'gaia'); ?></h2>
        <?php get_template_part('template-parts/account/password-form'); ?>
      </section>
    </div>
  <?php else : ?>
    <header class="account-page__header">
      <h1>
        <?php
        printf(
          /* translators: %s: user first name or display name */
          esc_html__('Hello, %s', 'gaia'),
          esc_html($current_user->first_name ?: $current_user->display_name)
        );
        ?>
      </h1>
    </header>

    <?php get_template_part('template-parts/account/form-notices'); ?>

    <div class="account-card">
      <p><?php esc_html_e('This is your personal area. More things will appear here soon.', 'gaia'); ?></p>
      <p class="account-card__actions">
        <a class="button" href="<?php echo esc_url(gaia_get_user_profile_url()); ?>"><?php esc_html_e('Edit your profile', 'gaia'); ?></a>
      </p>
    </div>
  <?php endif; ?>
</div>

<?php
get_footer();
