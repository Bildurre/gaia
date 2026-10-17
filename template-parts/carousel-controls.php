<?php
/**
 * Controls of a carousel (assets/js/carousel.js): previous / next, dots
 * (built by the script) and pause for autoplay.
 *
 * @param array $args ['has_autoplay' => bool]
 */

if (!defined('ABSPATH')) {
  exit;
}
?>
<div class="carousel__controls">
  <button class="carousel__button" type="button" data-carousel-previous>
    <?php echo gaia_get_icon_svg('ui/chevron-left'); ?>
    <span class="visually-hidden"><?php esc_html_e('Previous', 'gaia'); ?></span>
  </button>

  <div class="carousel__dots" data-carousel-dots></div>

  <?php if (!empty($args['has_autoplay'])) : ?>
    <button class="carousel__button carousel__pause" type="button" aria-pressed="false" data-carousel-pause>
      <span class="carousel__pause-icon"><?php echo gaia_get_icon_svg('ui/pause'); ?></span>
      <span class="carousel__play-icon"><?php echo gaia_get_icon_svg('ui/play'); ?></span>
      <span class="visually-hidden"><?php esc_html_e('Pause the slides', 'gaia'); ?></span>
    </button>
  <?php endif; ?>

  <button class="carousel__button" type="button" data-carousel-next>
    <?php echo gaia_get_icon_svg('ui/chevron-right'); ?>
    <span class="visually-hidden"><?php esc_html_e('Next', 'gaia'); ?></span>
  </button>
</div>
