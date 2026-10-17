<?php
/**
 * Listings: posts page, post type archives, taxonomy archives and search.
 * Card and archive settings come from Ajustes de Gaia > Contenidos.
 */

get_header();

$archive_post_type = gaia_get_archive_post_type();
$archive_settings = gaia_get_archive_settings($archive_post_type);
$main_taxonomy = gaia_get_main_taxonomy($archive_post_type);
$current_page = max(1, (int) get_query_var('paged'));
$archive_description = '';

if (is_search()) {
  $archive_title = sprintf(
    /* translators: %s: search query */
    __('Search results for “%s”', 'gaia'),
    get_search_query()
  );
  $archive_settings['layout'] = 'list';
  $archive_settings['has_featured'] = false;
  $archive_settings['shows_filters'] = false;
} elseif (is_home()) {
  $archive_title = single_post_title('', false) ?: __('News', 'gaia');
} elseif (is_post_type_archive()) {
  $archive_title = post_type_archive_title('', false);
  $archive_description = get_the_post_type_description();
} else {
  $archive_title = wp_strip_all_tags(get_the_archive_title());
  $archive_description = get_the_archive_description();
}

// Featured: first item of every page with page numbers, of the first page
// only when items are added below (load more, infinite scroll)
$shows_featured = $archive_settings['has_featured'] && ($archive_settings['pagination'] === 'numbers' || $current_page === 1);

// Filters: the terms of the main taxonomy, plus "All"
$filter_terms = [];
if ($archive_settings['shows_filters'] && $main_taxonomy && !is_search()) {
  $filter_terms = get_terms(['taxonomy' => $main_taxonomy, 'hide_empty' => true, 'parent' => 0]);
  $filter_terms = is_array($filter_terms) ? $filter_terms : [];
}

$all_items_url = $archive_post_type === 'post'
  ? (get_option('page_for_posts') ? get_permalink((int) get_option('page_for_posts')) : home_url('/'))
  : get_post_type_archive_link($archive_post_type);
$current_term_id = (is_category() || is_tag() || is_tax()) ? (int) get_queried_object_id() : 0;

$grid_classes = [
  'post-grid',
  $archive_settings['layout'] === 'list' ? 'post-grid--list' : 'post-grid--columns-' . $archive_settings['columns'],
];
?>

<div class="archive container">
  <header class="archive__header">
    <h1 class="archive__title"><?php echo esc_html($archive_title); ?></h1>

    <?php if ($archive_description) : ?>
      <div class="archive__description"><?php echo wp_kses_post(wpautop($archive_description)); ?></div>
    <?php endif; ?>

    <?php if (is_search()) : ?>
      <?php get_template_part('template-parts/search-form', null, ['id' => 'archive-search']); ?>
    <?php endif; ?>

    <?php if (count($filter_terms) > 1 && $all_items_url) : ?>
      <nav class="archive__filters" aria-label="<?php esc_attr_e('Filter by category', 'gaia'); ?>">
        <ul class="archive__filter-list" role="list">
          <li><a class="archive__filter<?php echo $current_term_id ? '' : ' is-current'; ?>" href="<?php echo esc_url($all_items_url); ?>"<?php echo $current_term_id ? '' : ' aria-current="page"'; ?>><?php esc_html_e('All', 'gaia'); ?></a></li>
          <?php foreach ($filter_terms as $filter_term) : ?>
            <?php $is_current_term = $filter_term->term_id === $current_term_id; ?>
            <li><a class="archive__filter<?php echo $is_current_term ? ' is-current' : ''; ?>" href="<?php echo esc_url(get_term_link($filter_term)); ?>"<?php echo $is_current_term ? ' aria-current="page"' : ''; ?>><?php echo esc_html($filter_term->name); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </header>

  <?php if (have_posts()) : ?>
    <div class="<?php echo esc_attr(implode(' ', $grid_classes)); ?>" data-archive-items>
      <?php $item_index = 0; ?>
      <?php while (have_posts()) : the_post(); ?>
        <?php
        get_template_part('template-parts/post-card', null, [
          'post' => get_post(),
          'heading_tag' => 'h2',
          'featured_layout' => $shows_featured && $item_index === 0 ? $archive_settings['featured_layout'] : '',
        ]);
        $item_index++;
        ?>
      <?php endwhile; ?>
    </div>

    <?php if ($archive_settings['pagination'] === 'numbers') : ?>
      <?php the_posts_pagination(['mid_size' => 1]); ?>
    <?php elseif (get_next_posts_link()) : ?>
      <div class="archive__more" data-archive-more="<?php echo esc_attr($archive_settings['pagination']); ?>">
        <a class="button button--secondary" href="<?php echo esc_url(get_next_posts_page_link()); ?>" data-archive-next><?php esc_html_e('Load more', 'gaia'); ?></a>
      </div>
    <?php endif; ?>
  <?php else : ?>
    <p class="archive__empty"><?php esc_html_e('No content found.', 'gaia'); ?></p>
  <?php endif; ?>
</div>

<?php
get_footer();
