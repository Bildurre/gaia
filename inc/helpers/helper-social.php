<?php
/**
 * Social Links Helper Functions
 */

if (!defined('ABSPATH')) exit;

/**
 * Get available social networks
 */
function gaia_get_social_networks() {
  return array(
    'facebook' => __('Facebook', 'gaia'),
    'instagram' => __('Instagram', 'gaia'),
    'x-twitter' => __('X (Twitter)', 'gaia'),
    'linkedin' => __('LinkedIn', 'gaia'),
    'youtube' => __('YouTube', 'gaia'),
    'tiktok' => __('TikTok', 'gaia'),
    'pinterest' => __('Pinterest', 'gaia'),
    'github' => __('GitHub', 'gaia'),
    'behance' => __('Behance', 'gaia'),
  );
}

/**
 * Get social link URL
 */
function gaia_get_social_url($network) {
  return get_option("gaia_social_{$network}", '');
}

/**
 * Check if has any social links
 */
function gaia_has_social_links() {
  $networks = array_keys(gaia_get_social_networks());
  
  foreach ($networks as $network) {
    if (gaia_get_social_url($network)) {
      return true;
    }
  }
  
  return false;
}

/**
 * Get social links shape
 */
function gaia_get_social_shape() {
  return get_option('gaia_social_shape', 'none');
}

/**
 * Check if social links have border
 */
function gaia_social_has_border() {
  return get_option('gaia_social_has_border', '0') === '1';
}

/**
 * Check if social links have background
 */
function gaia_social_has_background() {
  return get_option('gaia_social_has_background', '0') === '1';
}

/**
 * Check if social links use brand colors
 */
function gaia_social_use_brand_colors() {
  return get_option('gaia_social_brand_colors', '0') === '1';
}

/**
 * Get social links CSS classes
 */
function gaia_get_social_classes($base_class = 'social-links') {
  $classes = array($base_class);
  
  $shape = gaia_get_social_shape();
  if ($shape !== 'none') {
    $classes[] = "{$base_class}--{$shape}";
  }
  
  if (gaia_social_has_border()) {
    $classes[] = "{$base_class}--border";
  }
  
  if (gaia_social_has_background()) {
    $classes[] = "{$base_class}--background";
  }
  
  if (gaia_social_use_brand_colors()) {
    $classes[] = "{$base_class}--brand-colors";
  }
  
  return implode(' ', $classes);
}

/**
 * Get brand colors for social networks
 */
function gaia_get_social_brand_color($network) {
  $colors = array(
    'facebook' => '#1877f2',
    'instagram' => '#e4405f',
    'x-twitter' => '#000000',
    'linkedin' => '#0a66c2',
    'youtube' => '#ff0000',
    'tiktok' => '#000000',
    'pinterest' => '#bd081c',
    'github' => '#181717',
    'behance' => '#1769ff',
  );
  
  return isset($colors[$network]) ? $colors[$network] : 'currentColor';
}

/**
 * Display social links
 */
function gaia_social_links($base_class = 'social-links') {
  if (!gaia_has_social_links()) return;

  $networks = array_keys(gaia_get_social_networks());
  $classes = gaia_get_social_classes($base_class);
  $use_brand_colors = gaia_social_use_brand_colors();

  echo '<ul class="' . esc_attr($classes) . '">';
  foreach ($networks as $network) {
    $url = gaia_get_social_url($network);
    if (!empty($url)) {
      $style = $use_brand_colors ? 'style="--social-brand-color: ' . esc_attr(gaia_get_social_brand_color($network)) . ';"' : '';
      
      printf(
        '<li class="%1$s__item"><a href="%2$s" class="%1$s__link %1$s__link--%3$s" target="_blank" rel="noopener noreferrer" %4$s><span class="screen-reader-text">%5$s</span>%6$s</a></li>',
        esc_attr($base_class),
        esc_url($url),
        esc_attr($network),
        $style,
        esc_html(ucfirst(str_replace('-', ' ', $network))),
        gaia_get_social_icon($network)
      );
    }
  }
  echo '</ul>';
}

/**
 * Get social icon SVG
 */
function gaia_get_social_icon($network) {
  $icons = array(
    'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 10 20" fill="currentColor"><path d="M6.82 20V11h2.73l.45-4H6.82V5.05c0-1.03.03-2.05 1.47-2.05h1.46V.14c0-.04-1.25-.14-2.52-.14-2.65 0-4.3 1.66-4.3 4.7V7H0v4h2.92v9h3.9z"/></svg>',
    'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 18C15.3137 18 18 15.3137 18 12C18 8.68629 15.3137 6 12 6C8.68629 6 6 8.68629 6 12C6 15.3137 8.68629 18 12 18ZM12 16C14.2091 16 16 14.2091 16 12C16 9.79086 14.2091 8 12 8C9.79086 8 8 9.79086 8 12C8 14.2091 9.79086 16 12 16Z"/><path d="M18 5C17.4477 5 17 5.44772 17 6C17 6.55228 17.4477 7 18 7C18.5523 7 19 6.55228 19 6C19 5.44772 18.5523 5 18 5Z"/><path fill-rule="evenodd" clip-rule="evenodd" d="M1.65396 4.27606C1 5.55953 1 7.23969 1 10.6V13.4C1 16.7603 1 18.4405 1.65396 19.7239C2.2292 20.8529 3.14708 21.7708 4.27606 22.346C5.55953 23 7.23969 23 10.6 23H13.4C16.7603 23 18.4405 23 19.7239 22.346C20.8529 21.7708 21.7708 20.8529 22.346 19.7239C23 18.4405 23 16.7603 23 13.4V10.6C23 7.23969 23 5.55953 22.346 4.27606C21.7708 3.14708 20.8529 2.2292 19.7239 1.65396C18.4405 1 16.7603 1 13.4 1H10.6C7.23969 1 5.55953 1 4.27606 1.65396C3.14708 2.2292 2.2292 3.14708 1.65396 4.27606ZM13.4 3H10.6C8.88684 3 7.72225 3.00156 6.82208 3.0751C5.94524 3.14674 5.49684 3.27659 5.18404 3.43597C4.43139 3.81947 3.81947 4.43139 3.43597 5.18404C3.27659 5.49684 3.14674 5.94524 3.0751 6.82208C3.00156 7.72225 3 8.88684 3 10.6V13.4C3 15.1132 3.00156 16.2777 3.0751 17.1779C3.14674 18.0548 3.27659 18.5032 3.43597 18.816C3.81947 19.5686 4.43139 20.1805 5.18404 20.564C5.49684 20.7234 5.94524 20.8533 6.82208 20.9249C7.72225 20.9984 8.88684 21 10.6 21H13.4C15.1132 21 16.2777 20.9984 17.1779 20.9249C18.0548 20.8533 18.5032 20.7234 18.816 20.564C19.5686 20.1805 20.1805 19.5686 20.564 18.816C20.7234 18.5032 20.8533 18.0548 20.9249 17.1779C20.9984 16.2777 21 15.1132 21 13.4V10.6C21 8.88684 20.9984 7.72225 20.9249 6.82208C20.8533 5.94524 20.7234 5.49684 20.564 5.18404C20.1805 4.43139 19.5686 3.81947 18.816 3.43597C18.5032 3.27659 18.0548 3.14674 17.1779 3.0751C16.2777 3.00156 15.1132 3 13.4 3Z"/></svg>',
    'x-twitter' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
    'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20" fill="currentColor"><path d="M20,20 L16,20 L16,13.001 C16,11.081 15.153,10.01 13.634,10.01 C11.981,10.01 11,11.126 11,13.001 L11,20 L7,20 L7,7 L11,7 L11,8.462 C11,8.462 12.255,6.26 15.083,6.26 C17.912,6.26 20,7.986 20,11.558 L20,20 Z M2.442,4.921 C1.093,4.921 0,3.819 0,2.46 C0,1.102 1.093,0 2.442,0 C3.79,0 4.883,1.102 4.883,2.46 C4.884,3.819 3.79,4.921 2.442,4.921 Z M0,20 L5,20 L5,7 L0,7 L0,20 Z"/></svg>',
    'youtube' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 22 16" fill="currentColor"><path d="M8.99,10.59 L8.99,4.97 C10.98,5.91 12.52,6.82 14.35,7.79 C12.84,8.63 10.98,9.56 8.99,10.59 M20.09,2.18 C19.75,1.73 19.16,1.38 18.54,1.26 C16.71,0.91 5.27,0.91 3.44,1.26 C2.94,1.36 2.49,1.58 2.11,1.93 C0.5,3.43 1,11.45 1.39,12.75 C1.56,13.31 1.77,13.72 2.03,13.99 C2.38,14.34 2.85,14.58 3.38,14.69 C4.89,15 12.67,15.18 18.51,14.74 C19.04,14.64 19.52,14.39 19.9,14.02 C21.39,12.53 21.28,4.06 20.09,2.18"/></svg>',
    'tiktok' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32" fill="currentColor"><path d="M16.656 1.029c1.637-0.025 3.262-0.012 4.886-0.025 0.054 2.031 0.878 3.859 2.189 5.213l-0.002-0.002c1.411 1.271 3.247 2.095 5.271 2.235l0.028 0.002v5.036c-1.912-0.048-3.71-0.489-5.331-1.247l0.082 0.034c-0.784-0.377-1.447-0.764-2.077-1.196l0.052 0.034c-0.012 3.649 0.012 7.298-0.025 10.934-0.103 1.853-0.719 3.543-1.707 4.954l0.020-0.031c-1.652 2.366-4.328 3.919-7.371 4.011l-0.014 0c-0.123 0.006-0.268 0.009-0.414 0.009-1.73 0-3.347-0.482-4.725-1.319l0.040 0.023c-2.508-1.509-4.238-4.091-4.558-7.094l-0.004-0.041c-0.025-0.625-0.037-1.25-0.012-1.862 0.49-4.779 4.494-8.476 9.361-8.476 0.547 0 1.083 0.047 1.604 0.136l-0.056-0.008c0.025 1.849-0.050 3.699-0.050 5.548-0.423-0.153-0.911-0.242-1.42-0.242-1.868 0-3.457 1.194-4.045 2.861l-0.009 0.030c-0.133 0.427-0.21 0.918-0.21 1.426 0 0.206 0.013 0.41 0.037 0.61l-0.002-0.024c0.332 2.046 2.086 3.59 4.201 3.59 0.061 0 0.121-0.001 0.181-0.004l-0.009 0c1.463-0.044 2.733-0.831 3.451-1.994l0.010-0.018c0.267-0.372 0.45-0.822 0.511-1.311l0.001-0.014c0.125-2.237 0.075-4.461 0.087-6.698 0.012-5.036-0.012-10.060 0.025-15.083z"/></svg>',
    'pinterest' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512" fill="currentColor"><path d="M220.646,338.475C207.223,408.825,190.842,476.269,142.3,511.5c-14.996-106.33,21.994-186.188,39.173-270.971c-29.293-49.292,3.518-148.498,65.285-124.059c76.001,30.066-65.809,183.279,29.38,202.417c99.405,19.974,139.989-172.476,78.359-235.054C265.434-6.539,95.253,81.775,116.175,211.161c5.09,31.626,37.765,41.22,13.062,84.884c-57.001-12.65-74.005-57.6-71.822-117.533c3.53-98.108,88.141-166.787,173.024-176.293c107.34-12.014,208.081,39.398,221.991,140.376c15.67,113.978-48.442,237.412-163.23,228.529C258.085,368.704,245.023,353.283,220.646,338.475z"/></svg>',
    'github' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="-2.5 0 19 19" fill="currentColor"><path d="M9.464 17.178a4.506 4.506 0 0 1-2.013.317 4.29 4.29 0 0 1-2.007-.317.746.746 0 0 1-.277-.587c0-.22-.008-.798-.012-1.567-2.564.557-3.105-1.236-3.105-1.236a2.44 2.44 0 0 0-1.024-1.348c-.836-.572.063-.56.063-.56a1.937 1.937 0 0 1 1.412.95 1.962 1.962 0 0 0 2.682.765 1.971 1.971 0 0 1 .586-1.233c-2.046-.232-4.198-1.023-4.198-4.554a3.566 3.566 0 0 1 .948-2.474 3.313 3.313 0 0 1 .091-2.438s.773-.248 2.534.945a8.727 8.727 0 0 1 4.615 0c1.76-1.193 2.532-.945 2.532-.945a3.31 3.31 0 0 1 .092 2.438 3.562 3.562 0 0 1 .947 2.474c0 3.54-2.155 4.32-4.208 4.548a2.195 2.195 0 0 1 .625 1.706c0 1.232-.011 2.227-.011 2.529a.694.694 0 0 1-.272.587z"/></svg>',
    'behance' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 13" fill="currentColor"><path d="M12.981,2.117 L18.069,2.117 L18.069,0.66 L12.981,0.66 L12.981,2.117 Z M15.489,5.043 C14.354,5.043 13.492,5.747 13.409,7.046 L17.481,7.046 C17.205,5.504 16.52,5.043 15.489,5.043 Z M15.648,11.07 C16.696,11.07 17.465,10.408 17.622,9.85 L19.826,9.85 C19.196,11.867 17.895,13 15.559,13 C12.578,13 10.905,10.88 10.905,8.066 C10.905,1.441 20.465,1.203 19.985,8.685 L13.409,8.685 C13.473,10.204 14.1,11.07 15.648,11.07 Z M5.73,10.778 C6.933,10.778 7.775,10.316 7.775,9.067 C7.775,7.772 7.031,7.21 5.782,7.21 L2.768,7.21 L2.768,10.778 L5.73,10.778 Z M5.571,5.262 C6.572,5.262 7.263,4.796 7.263,3.714 C7.263,2.574 6.459,2.222 5.36,2.222 L2.768,2.222 L2.768,5.262 L5.571,5.262 Z M5.924,0 C8.295,0 9.943,0.784 9.943,3.222 C9.943,4.43 9.458,5.329 8.233,5.936 C9.801,6.401 10.543,7.624 10.543,9.231 C10.543,11.782 8.52,13 6.1,13 L0,13 L0,0 L5.924,0 Z"/></svg>',
  );
  
  return isset($icons[$network]) ? $icons[$network] : '';
}