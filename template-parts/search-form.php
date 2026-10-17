<?php
/**
 * Search form. @param array $args ['id' => input id]
 */

if (!defined('ABSPATH')) {
  exit;
}

$input_id = $args['id'] ?? 'search-input-' . wp_unique_id();
?>
<form class="search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
  <label class="visually-hidden" for="<?php echo esc_attr($input_id); ?>"><?php esc_html_e('Search', 'gaia'); ?></label>
  <input class="search-form__input" type="search" id="<?php echo esc_attr($input_id); ?>" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Search…', 'gaia'); ?>">
  <button class="button" type="submit"><?php esc_html_e('Search', 'gaia'); ?></button>
</form>
