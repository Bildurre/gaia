<?php
/**
 * Hero block: full-height section with a background image, title, text and
 * up to two buttons.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$image_id = (int) get_field('hero_image');
$image_position = get_field('hero_image_position') ?: 'center';
$overlay_strength = get_field('hero_overlay') ?: 'medium';
$content_alignment = get_field('hero_alignment') ?: 'left';
$eyebrow = (string) get_field('hero_eyebrow');
$title = (string) get_field('hero_title');
$title_tag = get_field('hero_title_is_h1') === false ? 'h2' : 'h1';
$text = (string) get_field('hero_text');
$buttons = get_field('hero_buttons') ?: [];

if (!$title && $is_preview) {
  echo '<p class="block-placeholder">' . esc_html__('Hero: fill in the title in the block settings.', 'gaia') . '</p>';
  return;
}

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'hero', [
  'hero--overlay-' . $overlay_strength,
  'hero--align-' . $content_alignment,
  $image_id ? 'hero--has-image' : '',
  $is_preview ? 'hero--preview' : '',
]);
$hero_end_id = 'hero-end-' . ($block['id'] ?? wp_unique_id());
?>
<section <?php echo $wrapper_attributes; ?>>
  <?php if ($image_id) : ?>
    <div class="hero__media">
      <?php
      echo wp_get_attachment_image($image_id, 'full', false, [
        'class' => 'hero__image',
        'style' => 'object-position: ' . esc_attr($image_position),
        'loading' => 'eager',
        'fetchpriority' => 'high',
        'sizes' => '100vw',
        'alt' => '',
      ]);
      ?>
    </div>
  <?php endif; ?>

  <div class="hero__content container">
    <div class="hero__text-block">
      <?php if ($eyebrow) : ?>
        <p class="hero__eyebrow"><?php echo esc_html($eyebrow); ?></p>
      <?php endif; ?>

      <<?php echo $title_tag; ?> class="hero__title"><?php echo esc_html($title); ?></<?php echo $title_tag; ?>>

      <?php if ($text) : ?>
        <p class="hero__text"><?php echo nl2br(esc_html($text)); ?></p>
      <?php endif; ?>

      <?php if ($buttons) : ?>
        <div class="hero__buttons">
          <?php foreach ($buttons as $button) : ?>
            <?php
            $button_link = $button['link'] ?? null;

            if (empty($button_link['url']) || empty($button_link['title'])) {
              continue;
            }

            // Old values: "accent" (now primary) and "outline" (now secondary)
            $button_style = str_replace(['accent', 'outline'], ['primary', 'secondary'], $button['style'] ?? 'primary');
            ?>
            <a
              class="button button--<?php echo esc_attr($button_style); ?>"
              href="<?php echo esc_url($button_link['url']); ?>"
              <?php if (!empty($button_link['target'])) : ?>target="<?php echo esc_attr($button_link['target']); ?>" rel="noopener"<?php endif; ?>
            ><?php echo esc_html($button_link['title']); ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <a class="hero__scroll" href="#<?php echo esc_attr($hero_end_id); ?>">
    <?php echo gaia_get_icon_svg('ui/chevron-down'); ?>
    <span class="visually-hidden"><?php esc_html_e('Go to content', 'gaia'); ?></span>
  </a>
</section>
<span class="hero__end" id="<?php echo esc_attr($hero_end_id); ?>"></span>
