<?php
/**
 * Custom Search Form
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
  <label class="screen-reader-text" for="search-field-<?php echo esc_attr(wp_unique_id()); ?>">
    <?php _e('Search for:', 'gaia'); ?>
  </label>
  <input 
    type="search" 
    id="search-field-<?php echo esc_attr(wp_unique_id()); ?>"
    class="search-field" 
    placeholder="<?php esc_attr_e('Search...', 'gaia'); ?>" 
    value="<?php echo get_search_query(); ?>" 
    name="s" 
  />
  <button type="submit" class="search-submit" aria-label="<?php esc_attr_e('Search', 'gaia'); ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
      <circle cx="11" cy="11" r="8"></circle>
      <path d="m21 21-4.3-4.3"></path>
    </svg>
  </button>
</form>