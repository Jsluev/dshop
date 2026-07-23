<?php
/**
 * DShop Helpers — placeholders, formatting
 */

defined('ABSPATH') || exit;

/**
 * Get SVG placeholder for product image
 * Each product gets a consistent placeholder based on its ID
 */
function dshop_get_placeholder(int $product_id, int $width = 600, int $height = 600): string
{
    $gradients = [
        // [bg1, bg2, icon_color, angle]
        ['#e0e7ff', '#c7d2fe', '#818cf8', 135],   // indigo
        ['#fce7f3', '#fbcfe8', '#f472b6', 135],   // pink
        ['#d1fae5', '#a7f3d0', '#34d399', 135],   // emerald
        ['#fef3c7', '#fde68a', '#fbbf24', 135],   // amber
        ['#dbeafe', '#bfdbfe', '#60a5fa', 135],   // blue
        ['#ede9fe', '#ddd6fe', '#a78bfa', 135],   // violet
        ['#ccfbf1', '#99f6e4', '#2dd4bf', 135],   // teal
        ['#ffe4e6', '#fecdd3', '#fb7185', 135],   // rose
        ['#f0fdf4', '#dcfce7', '#4ade80', 135],   // green
        ['#fff7ed', '#ffedd5', '#fb923c', 135],   // orange
    ];

    $index = $product_id % count($gradients);
    [$bg1, $bg2, $icon, $angle] = $gradients[$index];

    $half_w = (int)($width / 2);
    $half_h = (int)($height / 2);

    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}">
  <defs>
    <linearGradient id="g{$product_id}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$bg1}"/>
      <stop offset="100%" stop-color="{$bg2}"/>
    </linearGradient>
  </defs>
  <rect fill="url(#g{$product_id})" width="{$width}" height="{$height}"/>
  <g transform="translate($half_w, $half_h)">
    <rect x="-36" y="-40" width="72" height="80" rx="6" fill="{$icon}" opacity="0.18"/>
    <rect x="-28" y="-32" width="56" height="64" rx="4" fill="none" stroke="{$icon}" stroke-width="2" opacity="0.4"/>
    <circle cx="0" cy="-6" r="12" fill="{$icon}" opacity="0.3"/>
    <path d="M-18 16 Q0 2 18 16" fill="none" stroke="{$icon}" stroke-width="2" opacity="0.3"/>
    <line x1="-12" y1="24" x2="12" y2="24" stroke="{$icon}" stroke-width="2" opacity="0.2"/>
    <line x1="-8" y1="30" x2="8" y2="30" stroke="{$icon}" stroke-width="2" opacity="0.15"/>
  </g>
</svg>
SVG;

    return 'data:image/svg+xml,' . rawurlencode($svg);
}

/**
 * Get cart page URL
 */
function dshop_cart_url(): string
{
    $page_id = get_option('dshop_cart_page_id');
    return $page_id ? get_permalink($page_id) : home_url('/cart/');
}

/**
 * Get shop/catalog page URL
 */
function dshop_shop_url(): string
{
    $page_id = get_option('dshop_shop_page_id');
    if ($page_id) {
        return get_permalink($page_id);
    }
    // fallback: CPT archive
    $cpt = get_post_type_object('dshop_product');
    return $cpt && $cpt->has_archive ? get_post_type_archive_link('dshop_product') : home_url('/shop/');
}

/**
 * Get checkout page URL
 */
function dshop_checkout_url(): string
{
    $page_id = get_option('dshop_checkout_page_id');
    return $page_id ? get_permalink($page_id) : home_url('/checkout/');
}
