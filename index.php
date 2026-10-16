<?php get_header(); ?>

<?php if (have_posts()) : ?>
  <?php while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
      <?php the_content(); ?>
    </article>
  <?php endwhile; ?>

  <?php the_posts_pagination(); ?>
<?php else : ?>
  <p><?php esc_html_e('No content found.', 'gaia'); ?></p>
<?php endif; ?>

<?php get_footer(); ?>
