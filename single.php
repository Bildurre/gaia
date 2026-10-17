<?php
/**
 * Single post (news). Custom post types can provide their own single-{type}.php.
 */

get_header();

while (have_posts()) :
  the_post();
  ?>
  <article <?php post_class('entry container container--narrow'); ?>>
    <header class="entry__header">
      <h1 class="entry__title"><?php the_title(); ?></h1>
      <p class="entry__meta"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date()); ?></time></p>
    </header>

    <?php if (has_post_thumbnail()) : ?>
      <figure class="entry__image"><?php the_post_thumbnail('large'); ?></figure>
    <?php endif; ?>

    <div class="entry__content entry-content">
      <?php the_content(); ?>
    </div>
  </article>
  <?php
endwhile;

get_footer();
