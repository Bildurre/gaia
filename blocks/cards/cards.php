<?php
/**
 * Cards block: grid of cards with image, title, text and link.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$heading = gaia_get_block_heading('cards', gaia_get_block_field('cards_alignment', 'left'));
$cards = get_field('cards_items') ?: [];
$more_link = get_field('cards_more_link');
$columns = gaia_get_block_field('cards_columns', '3');
$card_style = gaia_get_block_field('cards_card_style', 'surface');
$image_ratio = gaia_get_block_field('cards_image_ratio', 'landscape');

if (!$cards && $is_preview) {
  gaia_print_block_placeholder(__('Cards: add cards in the block settings.', 'gaia'));
  return;
}

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'cards', array_merge(
  gaia_get_block_section_classes('cards'),
  ['cards--columns-' . $columns, 'cards--' . $card_style, 'cards--ratio-' . $image_ratio]
));
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="container">
    <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>

    <ul class="cards__list" role="list">
      <?php foreach ($cards as $card) : ?>
        <?php
        $card_link = !empty($card['link']['url']) ? $card['link'] : null;
        $card_image_id = (int) ($card['image'] ?? 0);
        ?>
        <li class="cards__item<?php echo $card_link ? ' has-link' : ''; ?>">
          <?php if ($card_image_id) : ?>
            <div class="cards__media">
              <?php echo wp_get_attachment_image($card_image_id, 'medium_large', false, ['class' => 'cards__image', 'alt' => '']); ?>
            </div>
          <?php endif; ?>

          <div class="cards__body">
            <h3 class="cards__title">
              <?php if ($card_link) : ?>
                <a
                  class="cards__link"
                  href="<?php echo esc_url($card_link['url']); ?>"
                  <?php if (!empty($card_link['target'])) : ?>target="<?php echo esc_attr($card_link['target']); ?>" rel="noopener"<?php endif; ?>
                ><?php echo esc_html($card['title']); ?></a>
              <?php else : ?>
                <?php echo esc_html($card['title']); ?>
              <?php endif; ?>
            </h3>

            <?php if (!empty($card['text'])) : ?>
              <p class="cards__text"><?php echo nl2br(esc_html($card['text'])); ?></p>
            <?php endif; ?>

            <?php if ($card_link && !empty($card_link['title'])) : ?>
              <span class="cards__more" aria-hidden="true">
                <?php echo esc_html($card_link['title']); ?>
                <?php echo gaia_get_icon_svg('ui/arrow-right'); ?>
              </span>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>

    <?php if (!empty($more_link['url']) && !empty($more_link['title'])) : ?>
      <?php get_template_part('template-parts/blocks/buttons', null, ['buttons' => [['link' => $more_link, 'style' => 'secondary']], 'class' => 'cards__more-link']); ?>
    <?php endif; ?>
  </div>
</section>
