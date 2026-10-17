</main>

<?php if (!gaia_get_footer_settings()['is_hidden']) : ?>
  <?php get_template_part('template-parts/footer/site-footer'); ?>
<?php endif; ?>

<?php if (gaia_shows_back_to_top_button()) : ?>
  <a class="back-to-top" href="#" data-back-to-top>
    <?php echo gaia_get_icon_svg('ui/arrow-up'); ?>
    <span class="visually-hidden"><?php esc_html_e('Back to top', 'gaia'); ?></span>
  </a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
