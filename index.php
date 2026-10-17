<?php
/**
 * Fallback template: posts page, archives and search results.
 */

get_header();

if (is_search()) {
  $listing_title = sprintf(
    /* translators: %s: search query */
    __('Search results for “%s”', 'gaia'),
    get_search_query()
  );
} elseif (is_home()) {
  $listing_title = single_post_title('', false) ?: __('News', 'gaia');
} else {
  $listing_title = wp_strip_all_tags(get_the_archive_title());
}
?>

<div class="listing container">
  <header class="listing__header">
    <h1 class="listing__title"><?php echo esc_html($listing_title); ?></h1>
    <?php if (is_search()) : ?>
      <?php get_template_part('template-parts/search-form', null, ['id' => 'listing-search']); ?>
    <?php endif; ?>
  </header>

  <?php if (have_posts()) : ?>
    <div class="listing__items">
      <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('listing-item'); ?>>
          <h2 class="listing-item__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <?php if (get_post_type() === 'post') : ?>
            <p class="listing-item__meta"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time></p>
          <?php endif; ?>
          <p class="listing-item__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 30)); ?></p>
        </article>
      <?php endwhile; ?>
    </div>

    <?php the_posts_pagination(['mid_size' => 1]); ?>
  <?php else : ?>
    <p class="listing__empty"><?php esc_html_e('No content found.', 'gaia'); ?></p>
  <?php endif; ?>
</div>

<?php
get_footer();
