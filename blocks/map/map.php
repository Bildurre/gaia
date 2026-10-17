<?php
/**
 * Map block: OpenStreetMap embed with a marker (no API key, no cookies),
 * optionally next to the address of the Contacto options.
 *
 * @var array $block      Block settings and attributes.
 * @var bool  $is_preview True inside the block editor.
 */

if (!defined('ABSPATH')) {
  exit;
}

$heading = gaia_get_block_heading('map', gaia_get_block_field('map_alignment', 'left'));
$latitude = get_field('map_latitude');
$longitude = get_field('map_longitude');
$zoom = min(19, max(5, (int) gaia_get_block_field('map_zoom', 15)));
$height = gaia_get_block_field('map_height', 'medium');
$contact_address = get_field('map_show_address') !== false ? gaia_get_contact_details()['address'] : '';

if (!is_numeric($latitude) || !is_numeric($longitude)) {
  if ($is_preview) {
    gaia_print_block_placeholder(__('Map: fill in the latitude and longitude in the block settings.', 'gaia'));
  }

  return;
}

// Bounding box around the point for the zoom level (OpenStreetMap embed)
$latitude = (float) $latitude;
$longitude = (float) $longitude;
$longitude_span = 360 / (2 ** $zoom) * 1.5;
$latitude_span = $longitude_span * cos(deg2rad($latitude)) * 0.6;
$embed_url = add_query_arg([
  'bbox' => implode(',', [$longitude - $longitude_span / 2, $latitude - $latitude_span / 2, $longitude + $longitude_span / 2, $latitude + $latitude_span / 2]),
  'layer' => 'mapnik',
  'marker' => $latitude . ',' . $longitude,
], 'https://www.openstreetmap.org/export/embed.html');
$larger_map_url = sprintf('https://www.openstreetmap.org/?mlat=%1$s&mlon=%2$s#map=%3$d/%1$s/%2$s', $latitude, $longitude, $zoom);

$wrapper_attributes = gaia_get_block_wrapper_attributes($block, 'map-block', array_merge(
  gaia_get_block_section_classes('map'),
  ['map-block--height-' . $height, $contact_address ? 'map-block--has-address' : '']
));
?>
<section <?php echo $wrapper_attributes; ?>>
  <div class="container">
    <?php get_template_part('template-parts/blocks/section-heading', null, $heading); ?>

    <div class="map-block__inner">
      <?php if ($contact_address) : ?>
        <div class="map-block__details">
          <?php get_template_part('template-parts/contact-details', null, ['types' => ['address']]); ?>
        </div>
      <?php endif; ?>

      <div class="map-block__map">
        <iframe
          class="map-block__frame"
          src="<?php echo esc_url($embed_url); ?>"
          title="<?php esc_attr_e('Map', 'gaia'); ?>"
          loading="lazy"
          referrerpolicy="no-referrer"
        ></iframe>
        <a class="map-block__larger" href="<?php echo esc_url($larger_map_url); ?>" target="_blank" rel="noopener">
          <?php esc_html_e('View larger map', 'gaia'); ?>
          <?php echo gaia_get_icon_svg('ui/external-link'); ?>
        </a>
      </div>
    </div>
  </div>
</section>
