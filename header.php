<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link visually-hidden-focusable" href="#main"><?php esc_html_e('Skip to content', 'gaia'); ?></a>

<?php if (!gaia_get_header_settings()['is_hidden']) : ?>
  <?php get_template_part('template-parts/header/site-header'); ?>
<?php else : ?>
  <?php get_template_part('template-parts/announcement-bar'); ?>
<?php endif; ?>

<main class="site-main" id="main">
  <?php get_template_part('template-parts/breadcrumbs'); ?>
