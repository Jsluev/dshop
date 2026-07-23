<?php
/**
 * Cart Class
 *
 * @package DShop\Modules\Cart
 */

namespace DShop\Modules\Cart;

/**
 * Class Cart
 *
 * Handles cart operations
 */
class Cart
{
    /**
     * Option key for cart storage
     *
     * @var string
     */
    const OPTION_KEY = 'dshop_cart_data';

    /**
     * Cart token cookie name
     *
     * @var string
     */
    const COOKIE_NAME = 'dshop_cart_token';

    /**
     * Cart expiration in seconds (7 days)
     *
     * @var int
     */
    const EXPIRY = 604800;

    /**
     * Cart items
     *
     * @var array
     */
    private $items = [];

    /**
     * Applied coupon
     *
     * @var string|null
     */
    private $coupon = null;

    /**
     * Cart token
     *
     * @var string
     */
    private $token = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initToken();
        $this->load();
    }

    /**
     * Initialize cart token from cookie or create new
     *
     * @return void
     */
    private function initToken(): void
    {
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            $this->token = sanitize_text_field($_COOKIE[self::COOKIE_NAME]);
        }

        if (empty($this->token)) {
            $this->token = wp_generate_password(32, false);
            setcookie(self::COOKIE_NAME, $this->token, time() + self::EXPIRY, '/');
        }
    }

    /**
     * Get all cart data from options
     *
     * @return array
     */
    private function getAllCarts(): array
    {
        $all = get_option(self::OPTION_KEY, []);
        $now = time();

        // Garbage collection: remove expired carts
        foreach ($all as $key => $cart) {
            if (isset($cart['expiry']) && $cart['expiry'] < $now) {
                unset($all[$key]);
            }
        }

        return $all;
    }

    /**
     * Save all cart data to options
     *
     * @param array $all All carts
     * @return void
     */
    private function saveAllCarts(array $all): void
    {
        update_option(self::OPTION_KEY, $all, false);
    }

    /**
     * Load cart from storage
     *
     * @return void
     */
    private function load(): void
    {
        $all = $this->getAllCarts();

        if (isset($all[$this->token])) {
            $this->items = $all[$this->token]['items'] ?? [];
            $this->coupon = $all[$this->token]['coupon'] ?? null;
        }
    }

    /**
     * Save cart to storage
     *
     * @return void
     */
    private function save(): void
    {
        $all = $this->getAllCarts();
        $all[$this->token] = [
            'items' => $this->items,
            'coupon' => $this->coupon,
            'expiry' => time() + self::EXPIRY,
        ];
        $this->saveAllCarts($all);
    }

    /**
     * Add product to cart
     *
     * @param int $product_id Product ID
     * @param int $quantity Quantity
     * @param array $variation_data Variation data
     * @return bool
     */
    public function add(int $product_id, int $quantity = 1, array $variation_data = []): bool
    {
        $product = get_post($product_id);
        
        if (!$product || $product->post_type !== 'dshop_product') {
            return false;
        }

        $price = (float) get_post_meta($product_id, '_dshop_price', true);
        $sale_price = get_post_meta($product_id, '_dshop_sale_price', true);
        $sku = get_post_meta($product_id, '_dshop_sku', true);
        $stock_quantity = (int) get_post_meta($product_id, '_dshop_stock_quantity', true);
        $manage_stock = (bool) get_post_meta($product_id, '_dshop_manage_stock', true);

        // Check stock
        if ($manage_stock && $stock_quantity < $quantity) {
            return false;
        }

        $cart_key = $this->generateCartKey($product_id, $variation_data);

        if (isset($this->items[$cart_key])) {
            $this->items[$cart_key]['quantity'] += $quantity;
        } else {
            $this->items[$cart_key] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'price' => $sale_price ?: $price,
                'regular_price' => $price,
                'sale_price' => $sale_price,
                'sku' => $sku,
                'name' => $product->post_title,
                'variation_data' => $variation_data,
            ];
        }

        $this->save();

        do_action('dshop/cart/after_add', $cart_key, $this->items[$cart_key]);

        return true;
    }

    /**
     * Remove item from cart
     *
     * @param string $cart_key Cart key
     * @return bool
     */
    public function remove(string $cart_key): bool
    {
        if (!isset($this->items[$cart_key])) {
            return false;
        }

        $item = $this->items[$cart_key];
        unset($this->items[$cart_key]);

        $this->save();

        do_action('dshop/cart/after_remove', $cart_key, $item);

        return true;
    }

    /**
     * Update item quantity
     *
     * @param string $cart_key Cart key
     * @param int $quantity New quantity
     * @return bool
     */
    public function updateQuantity(string $cart_key, int $quantity): bool
    {
        if (!isset($this->items[$cart_key])) {
            return false;
        }

        if ($quantity <= 0) {
            return $this->remove($cart_key);
        }

        $product_id = $this->items[$cart_key]['product_id'];
        $stock_quantity = (int) get_post_meta($product_id, '_dshop_stock_quantity', true);
        $manage_stock = (bool) get_post_meta($product_id, '_dshop_manage_stock', true);

        // Check stock
        if ($manage_stock && $stock_quantity < $quantity) {
            return false;
        }

        $this->items[$cart_key]['quantity'] = $quantity;
        $this->save();

        do_action('dshop/cart/after_update', $cart_key, $this->items[$cart_key]);

        return true;
    }

    /**
     * Clear cart
     *
     * @return void
     */
    public function clear(): void
    {
        $this->items = [];
        $this->coupon = null;
        $this->save();

        do_action('dshop/cart/after_clear');
    }

    /**
     * Apply coupon
     *
     * @param string $coupon_code Coupon code
     * @return bool
     */
    public function applyCoupon(string $coupon_code): bool
    {
        global $wpdb;

        $table = $wpdb->prefix . 'dshop_coupons';
        $coupon = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE code = %s AND status = 'active'", $coupon_code)
        );

        if (!$coupon) {
            return false;
        }

        // Check expiration
        if ($coupon->expires_at && strtotime($coupon->expires_at) < time()) {
            return false;
        }

        // Check usage limit
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return false;
        }

        // Check minimum spend
        if ($coupon->minimum_spend > 0 && $this->getSubtotal() < $coupon->minimum_spend) {
            return false;
        }

        $this->coupon = $coupon;
        $this->save();

        do_action('dshop/cart/after_apply_coupon', $coupon);

        return true;
    }

    /**
     * Remove coupon
     *
     * @return void
     */
    public function removeCoupon(): void
    {
        $this->coupon = null;
        $this->save();

        do_action('dshop/cart/after_remove_coupon');
    }

    /**
     * Get cart items
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get item by key
     *
     * @param string $cart_key Cart key
     * @return array|null
     */
    public function getItem(string $cart_key): ?array
    {
        return $this->items[$cart_key] ?? null;
    }

    /**
     * Get cart count
     *
     * @return int
     */
    public function getCount(): int
    {
        $count = 0;
        foreach ($this->items as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    /**
     * Get cart subtotal
     *
     * @return float
     */
    public function getSubtotal(): float
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return $subtotal;
    }

    /**
     * Get discount amount
     *
     * @return float
     */
    public function getDiscount(): float
    {
        if (!$this->coupon) {
            return 0;
        }

        $subtotal = $this->getSubtotal();

        switch ($this->coupon->type) {
            case 'percent':
                return $subtotal * ($this->coupon->amount / 100);
            case 'fixed':
                return min($this->coupon->amount, $subtotal);
            default:
                return 0;
        }
    }

    /**
     * Get tax amount
     *
     * @return float
     */
    public function getTax(): float
    {
        $tax_rate = (float) get_option('dshop_tax_rate', 0);
        $subtotal = $this->getSubtotal() - $this->getDiscount();
        return $subtotal * ($tax_rate / 100);
    }

    /**
     * Get cart total
     *
     * @return float
     */
    public function getTotal(): float
    {
        return $this->getSubtotal() - $this->getDiscount() + $this->getTax();
    }

    /**
     * Get all totals
     *
     * @return array
     */
    public function getTotals(): array
    {
        return [
            'subtotal' => $this->formatPrice($this->getSubtotal()),
            'discount' => $this->formatPrice($this->getDiscount()),
            'tax' => $this->formatPrice($this->getTax()),
            'total' => $this->formatPrice($this->getTotal()),
        ];
    }

    /**
     * Check if cart is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Get applied coupon
     *
     * @return object|null
     */
    public function getCoupon(): ?object
    {
        return $this->coupon;
    }

    /**
     * Generate cart key
     *
     * @param int $product_id Product ID
     * @param array $variation_data Variation data
     * @return string
     */
    private function generateCartKey(int $product_id, array $variation_data): string
    {
        $key = $product_id;
        if (!empty($variation_data)) {
            $key .= '_' . md5(serialize($variation_data));
        }
        return (string) $key;
    }

    /**
     * Format price
     *
     * @param float $price Price
     * @return string
     */
    public function formatPrice(float $price): string
    {
        return number_format($price, 2, '.', ' ');
    }

    /**
     * Get cart data for display
     *
     * @return array
     */
    public function toArray(): array
    {
        $items = [];
        foreach ($this->items as $key => $item) {
            $items[$key] = [
                'key' => $key,
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $this->formatPrice($item['price']),
                'total' => $this->formatPrice($item['price'] * $item['quantity']),
                'image' => get_the_post_thumbnail_url($item['product_id'], 'thumbnail') ?: dshop_get_placeholder($item['product_id'], 100, 100),
                'url' => get_permalink($item['product_id']),
            ];
        }

        return [
            'items' => $items,
            'count' => $this->getCount(),
            'totals' => $this->getTotals(),
            'coupon' => $this->coupon ? $this->coupon->code : null,
        ];
    }
}
