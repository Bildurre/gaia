<?php
/**
 * 404 page: title and text from Ajustes de Gaia > Extras.
 */

$not_found_texts = gaia_get_not_found_texts();

get_header();
?>

<div class="not-found container">
  <p class="not-found__code" aria-hidden="true">404</p>
  <h1 class="not-found__title"><?php echo esc_html($not_found_texts['title']); ?></h1>
  <p class="not-found__text"><?php echo nl2br(esc_html($not_found_texts['text'])); ?></p>
  <?php get_template_part('template-parts/search-form', null, ['id' => 'not-found-search']); ?>
  <p><a class="button" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Go to the home page', 'gaia'); ?></a></p>
</div>

<?php
get_footer();
