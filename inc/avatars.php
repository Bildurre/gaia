<?php
/**
 * Local user avatars uploaded from the profile page. They replace Gravatar
 * everywhere (get_avatar()) and are hidden from the admin media library.
 */

if (!defined('ABSPATH')) {
  exit;
}

const GAIA_AVATAR_META_KEY = 'gaia_avatar_id';
const GAIA_AVATAR_ATTACHMENT_FLAG = '_gaia_user_avatar';
const GAIA_AVATAR_MAX_BYTES = 2 * MB_IN_BYTES;
const GAIA_AVATAR_MIME_TYPES = [
  'jpg|jpeg|jpe' => 'image/jpeg',
  'png' => 'image/png',
  'webp' => 'image/webp',
  'gif' => 'image/gif',
];

function gaia_get_user_avatar_id($user_id) {
  $avatar_id = (int) get_user_meta($user_id, GAIA_AVATAR_META_KEY, true);

  return $avatar_id && wp_attachment_is_image($avatar_id) ? $avatar_id : 0;
}

function gaia_filter_avatar_data($avatar_data, $id_or_email) {
  $user = false;

  if (is_numeric($id_or_email)) {
    $user = get_user_by('id', (int) $id_or_email);
  } elseif ($id_or_email instanceof WP_User) {
    $user = $id_or_email;
  } elseif ($id_or_email instanceof WP_Post) {
    $user = get_user_by('id', (int) $id_or_email->post_author);
  } elseif ($id_or_email instanceof WP_Comment) {
    $user = $id_or_email->user_id ? get_user_by('id', (int) $id_or_email->user_id) : false;
  } elseif (is_string($id_or_email) && is_email($id_or_email)) {
    $user = get_user_by('email', $id_or_email);
  }

  $avatar_id = $user ? gaia_get_user_avatar_id($user->ID) : 0;

  if (!$avatar_id) {
    return $avatar_data;
  }

  $requested_size = (int) ($avatar_data['size'] ?? 96);
  $image_size = $requested_size <= 150 ? 'thumbnail' : 'medium';
  $avatar_url = wp_get_attachment_image_url($avatar_id, $image_size);

  if ($avatar_url) {
    $avatar_data['url'] = $avatar_url;
    $avatar_data['found_avatar'] = true;
  }

  return $avatar_data;
}
add_filter('pre_get_avatar_data', 'gaia_filter_avatar_data', 10, 2);

/**
 * Saves the uploaded file of the "avatar" field as the user avatar.
 * Returns true, or a WP_Error with a user-facing message.
 */
function gaia_save_uploaded_avatar($user_id, $file_field_name) {
  $uploaded_file = $_FILES[$file_field_name] ?? null;

  if (!$uploaded_file || ($uploaded_file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    return true;
  }

  if ($uploaded_file['error'] !== UPLOAD_ERR_OK || $uploaded_file['size'] > GAIA_AVATAR_MAX_BYTES) {
    return new WP_Error('avatar_too_big', __('The image could not be uploaded. It must be a JPG, PNG, WebP or GIF file of up to 2 MB.', 'gaia'));
  }

  $file_type = wp_check_filetype_and_ext($uploaded_file['tmp_name'], $uploaded_file['name'], GAIA_AVATAR_MIME_TYPES);

  if (empty($file_type['type'])) {
    return new WP_Error('avatar_invalid_type', __('The image could not be uploaded. It must be a JPG, PNG, WebP or GIF file of up to 2 MB.', 'gaia'));
  }

  require_once ABSPATH . 'wp-admin/includes/file.php';
  require_once ABSPATH . 'wp-admin/includes/image.php';
  require_once ABSPATH . 'wp-admin/includes/media.php';

  $attachment_id = media_handle_upload($file_field_name, 0, [], [
    'test_form' => false,
    'mimes' => GAIA_AVATAR_MIME_TYPES,
  ]);

  if (is_wp_error($attachment_id)) {
    return new WP_Error('avatar_upload_failed', __('The image could not be uploaded. It must be a JPG, PNG, WebP or GIF file of up to 2 MB.', 'gaia'));
  }

  wp_update_post([
    'ID' => $attachment_id,
    'post_author' => $user_id,
  ]);
  update_post_meta($attachment_id, GAIA_AVATAR_ATTACHMENT_FLAG, 1);

  gaia_delete_user_avatar($user_id);
  update_user_meta($user_id, GAIA_AVATAR_META_KEY, $attachment_id);

  return true;
}

function gaia_delete_user_avatar($user_id) {
  $previous_avatar_id = (int) get_user_meta($user_id, GAIA_AVATAR_META_KEY, true);

  if ($previous_avatar_id && get_post_meta($previous_avatar_id, GAIA_AVATAR_ATTACHMENT_FLAG, true)) {
    wp_delete_attachment($previous_avatar_id, true);
  }

  delete_user_meta($user_id, GAIA_AVATAR_META_KEY);
}

// Avatars are not regular media: keep them out of the media library.
function gaia_hide_avatars_from_media_library($query_args) {
  $query_args['meta_query'] = array_merge($query_args['meta_query'] ?? [], [[
    'key' => GAIA_AVATAR_ATTACHMENT_FLAG,
    'compare' => 'NOT EXISTS',
  ]]);

  return $query_args;
}
add_filter('ajax_query_attachments_args', 'gaia_hide_avatars_from_media_library');

function gaia_hide_avatars_from_media_list($query) {
  if (is_admin() && $query->is_main_query() && ($GLOBALS['pagenow'] ?? '') === 'upload.php') {
    $query->set('meta_query', array_merge((array) $query->get('meta_query'), [[
      'key' => GAIA_AVATAR_ATTACHMENT_FLAG,
      'compare' => 'NOT EXISTS',
    ]]));
  }
}
add_action('pre_get_posts', 'gaia_hide_avatars_from_media_list');

// Delete the avatar file when the user is deleted.
add_action('delete_user', 'gaia_delete_user_avatar');
