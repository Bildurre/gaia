<?php
/**
 * Pages are built with blocks: each block handles its own width and spacing.
 */

get_header();

while (have_posts()) {
  the_post();
  the_content();
}

get_footer();
