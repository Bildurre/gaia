<?php
/**
 * Success message (from ?status=) and errors of the submitted form.
 */

if (!defined('ABSPATH')) {
  exit;
}

$status_message = gaia_get_status_message(sanitize_key($_GET['status'] ?? ''));
$form_errors = gaia_get_form_errors();
?>

<?php if ($status_message && !$form_errors->has_errors()) : ?>
  <div class="form-notice form-notice--success" role="status">
    <p><?php echo esc_html($status_message); ?></p>
  </div>
<?php endif; ?>

<?php if ($form_errors->has_errors()) : ?>
  <div class="form-notice form-notice--error" role="alert">
    <?php foreach ($form_errors->get_error_messages() as $error_message) : ?>
      <p><?php echo esc_html($error_message); ?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
