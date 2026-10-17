<?php
/**
 * Extras (Ajustes de Gaia > Extras): announcement bar, maintenance mode,
 * breadcrumbs and back to top button.
 */

if (!defined('ABSPATH')) {
  exit;
}

/* -------------------------------------------------------------------------
 * Announcement bar
 * ---------------------------------------------------------------------- */

/**
 * Announcement bar data, or null when it should not be shown.
 */
function gaia_get_announcement() {
  if (!gaia_get_option_field('extras_announcement_enabled')) {
    return null;
  }

  $announcement_text = gaia_get_translated_option_text('extras_announcement_text');
  $expiry_date = (string) gaia_get_option_field('extras_announcement_expiry');

  if ($announcement_text === '' || ($expiry_date !== '' && current_time('Y-m-d H:i:s') >= $expiry_date)) {
    return null;
  }

  $announcement_style = gaia_get_option_value('extras_announcement_style', 'primary');
  $announcement_link = gaia_get_option_field('extras_announcement_link');

  return [
    'id' => substr(md5($announcement_text), 0, 10),
    'text' => $announcement_text,
    'url' => is_array($announcement_link) ? ($announcement_link['url'] ?? '') : '',
    'target' => is_array($announcement_link) ? ($announcement_link['target'] ?? '') : '',
    'style' => in_array($announcement_style, ['primary', 'dark', 'highlight'], true) ? $announcement_style : 'primary',
    'is_dismissible' => (bool) gaia_get_option_value('extras_announcement_dismissible', true),
  ];
}

/**
 * Hides a dismissed announcement before the page is painted.
 */
function gaia_print_announcement_script() {
  $announcement = gaia_get_announcement();

  // The options preview always shows the bar, even if it was dismissed
  if (!$announcement || !$announcement['is_dismissible'] || gaia_is_options_preview_request()) {
    return;
  }

  echo "<script>(function(){try{var dismissedAnnouncement=localStorage.getItem('gaia-dismissed-announcement');if(dismissedAnnouncement){document.documentElement.setAttribute('data-dismissed-announcement',dismissedAnnouncement);}}catch(error){}})();</script>\n";
}
add_action('wp_head', 'gaia_print_announcement_script', 0);

/* -------------------------------------------------------------------------
 * Maintenance mode
 * ---------------------------------------------------------------------- */

/**
 * Visitors without editing rights get a 503 maintenance page. The login
 * page stays available so members and editors can log in.
 */
function gaia_show_maintenance_page() {
  if (!gaia_get_option_field('extras_maintenance_enabled') || current_user_can('edit_posts')) {
    return;
  }

  if (wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST) || is_page_template(GAIA_LOGIN_TEMPLATE)) {
    return;
  }

  status_header(503);
  header('Retry-After: 3600');
  nocache_headers();
  get_template_part('template-parts/maintenance');
  exit;
}
add_action('template_redirect', 'gaia_show_maintenance_page', 1);

/* -------------------------------------------------------------------------
 * Breadcrumbs
 * ---------------------------------------------------------------------- */

function gaia_should_show_breadcrumbs() {
  if (!gaia_get_option_field('extras_breadcrumbs') || is_front_page() || is_404()) {
    return false;
  }

  // Pages that start with a hero do not need them (and the hero should be
  // the first thing below the header).
  return !gaia_page_starts_with_hero();
}

/**
 * Breadcrumb trail as a list of ['label' => string, 'url' => string]; the
 * last item (current page) has no URL.
 */
function gaia_get_breadcrumb_items() {
  $breadcrumb_items = [[
    'label' => __('Home', 'gaia'),
    'url' => home_url('/'),
  ]];

  if (is_singular()) {
    $current_post = get_queried_object();

    if ($current_post->post_type === 'post') {
      $posts_page_id = (int) get_option('page_for_posts');
      $posts_page_id = $posts_page_id && function_exists('pll_get_post') ? ((int) pll_get_post($posts_page_id) ?: $posts_page_id) : $posts_page_id;

      if ($posts_page_id) {
        $breadcrumb_items[] = ['label' => get_the_title($posts_page_id), 'url' => get_permalink($posts_page_id)];
      }
    } elseif ($current_post->post_type !== 'page') {
      $post_type_object = get_post_type_object($current_post->post_type);
      $archive_url = get_post_type_archive_link($current_post->post_type);

      if ($post_type_object && $archive_url) {
        $breadcrumb_items[] = ['label' => $post_type_object->labels->name, 'url' => $archive_url];
      }
    }

    foreach (array_reverse(get_post_ancestors($current_post)) as $ancestor_id) {
      $breadcrumb_items[] = ['label' => get_the_title($ancestor_id), 'url' => get_permalink($ancestor_id)];
    }

    $breadcrumb_items[] = ['label' => get_the_title($current_post), 'url' => ''];
  } elseif (is_search()) {
    $breadcrumb_items[] = [
      'label' => sprintf(
        /* translators: %s: search query */
        __('Search results for “%s”', 'gaia'),
        get_search_query()
      ),
      'url' => '',
    ];
  } elseif (is_home()) {
    $breadcrumb_items[] = ['label' => single_post_title('', false), 'url' => ''];
  } elseif (is_archive()) {
    $breadcrumb_items[] = ['label' => wp_strip_all_tags(get_the_archive_title()), 'url' => ''];
  }

  return $breadcrumb_items;
}

/* -------------------------------------------------------------------------
 * Back to top
 * ---------------------------------------------------------------------- */

function gaia_shows_back_to_top_button() {
  return (bool) gaia_get_option_value('extras_back_to_top', true);
}

/**
 * Text of the configurable 404 page, with defaults.
 */
function gaia_get_not_found_texts() {
  return [
    'title' => gaia_get_translated_option_text('extras_404_title') ?: __('Page not found', 'gaia'),
    'text' => gaia_get_translated_option_text('extras_404_text') ?: __('The page you are looking for does not exist or has been moved.', 'gaia'),
  ];
}
