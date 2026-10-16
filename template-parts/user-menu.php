<?php
/**
 * Logged out: log in link. Logged in: avatar button that opens a dropdown
 * with profile, personal area, administration (editors only) and log out.
 * "Log in" and "Log out" use the WordPress core text domain on purpose: core
 * already ships their Spanish and Basque front-end translations.
 *
 * @param array $args ['class' => extra CSS class]
 */

if (!defined('ABSPATH')) {
  exit;
}

$extra_class = $args['class'] ?? '';
$current_page_url = gaia_get_current_url();
?>

<?php if (!is_user_logged_in()) : ?>
  <a class="user-menu-login <?php echo esc_attr($extra_class); ?>" href="<?php echo esc_url(wp_login_url($current_page_url)); ?>">
    <?php echo gaia_get_icon_svg('ui/log-in'); ?>
    <span><?php echo esc_html__('Log in'); ?></span>
  </a>
<?php else : ?>
  <?php
  $current_user = wp_get_current_user();
  $user_menu_items = [
    [
      'label' => __('Profile', 'gaia'),
      'url' => gaia_get_user_profile_url(),
      'icon' => 'ui/circle-user',
    ],
    [
      'label' => __('My area', 'gaia'),
      'url' => gaia_get_user_account_url(),
      'icon' => 'ui/layout-dashboard',
    ],
  ];

  if (gaia_user_can_use_admin()) {
    $user_menu_items[] = [
      'label' => __('Administration', 'gaia'),
      'url' => admin_url(),
      'icon' => 'ui/settings',
    ];
  }
  ?>
  <div class="user-menu <?php echo esc_attr($extra_class); ?>" data-user-menu>
    <button
      class="user-menu__toggle"
      type="button"
      aria-expanded="false"
      aria-controls="user-menu-dropdown"
      data-user-menu-toggle
    >
      <?php echo get_avatar($current_user->ID, 48, '', '', ['class' => 'user-menu__avatar']); ?>
      <span class="user-menu__toggle-name"><?php echo esc_html($current_user->display_name); ?></span>
      <span class="visually-hidden"><?php esc_html_e('User menu', 'gaia'); ?></span>
      <?php echo gaia_get_icon_svg('ui/chevron-down'); ?>
    </button>

    <div class="user-menu__dropdown" id="user-menu-dropdown" hidden data-user-menu-dropdown>
      <p class="user-menu__user">
        <span class="user-menu__name"><?php echo esc_html($current_user->display_name); ?></span>
        <span class="user-menu__email"><?php echo esc_html($current_user->user_email); ?></span>
      </p>

      <ul class="user-menu__list" role="list">
        <?php foreach ($user_menu_items as $user_menu_item) : ?>
          <li>
            <a class="user-menu__link" href="<?php echo esc_url($user_menu_item['url']); ?>">
              <?php echo gaia_get_icon_svg($user_menu_item['icon']); ?>
              <span><?php echo esc_html($user_menu_item['label']); ?></span>
            </a>
          </li>
        <?php endforeach; ?>
        <li class="user-menu__separator">
          <a class="user-menu__link user-menu__link--logout" href="<?php echo esc_url(wp_logout_url($current_page_url)); ?>">
            <?php echo gaia_get_icon_svg('ui/log-out'); ?>
            <span><?php echo esc_html__('Log out'); ?></span>
          </a>
        </li>
      </ul>
    </div>
  </div>
<?php endif; ?>
