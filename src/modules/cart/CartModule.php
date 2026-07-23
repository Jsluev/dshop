<?php
/**
 * Cart Module
 *
 * @package DShop\Modules\Cart
 */

namespace DShop\Modules\Cart;

use DShop\Core\BaseModule;

/**
 * Class CartModule
 *
 * Handles shopping cart functionality
 */
class CartModule extends BaseModule
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name = 'cart';

    /**
     * Module version
     *
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Module description
     *
     * @var string
     */
    protected $description = 'Shopping cart management module';

    /**
     * Cart instance
     *
     * @var Cart
     */
    private $cart;

    /**
     * {@inheritdoc}
     */
     public function init(): void
     {
        $this->cart = new Cart();
        $this->active = true;
     }

    /**
     * {@inheritdoc}
     */
    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addAdminMenus']);

        // AJAX handlers
        add_action('wp_ajax_dshop_add_to_cart', [$this, 'ajaxAddToCart']);
        add_action('wp_ajax_nopriv_dshop_add_to_cart', [$this, 'ajaxAddToCart']);
        
        add_action('wp_ajax_dshop_remove_from_cart', [$this, 'ajaxRemoveFromCart']);
        add_action('wp_ajax_nopriv_dshop_remove_from_cart', [$this, 'ajaxRemoveFromCart']);
        
        add_action('wp_ajax_dshop_update_cart', [$this, 'ajaxUpdateCart']);
        add_action('wp_ajax_nopriv_dshop_update_cart', [$this, 'ajaxUpdateCart']);
        
        add_action('wp_ajax_dshop_apply_coupon', [$this, 'ajaxApplyCoupon']);
        add_action('wp_ajax_nopriv_dshop_apply_coupon', [$this, 'ajaxApplyCoupon']);

        add_action('wp_ajax_dshop_remove_coupon', [$this, 'ajaxRemoveCoupon']);
        add_action('wp_ajax_nopriv_dshop_remove_coupon', [$this, 'ajaxRemoveCoupon']);

        // Shortcodes
        add_shortcode('dshop_cart', [$this, 'cartShortcode']);
        add_shortcode('dshop_mini_cart', [$this, 'miniCartShortcode']);

        // Widgets
        add_action('widgets_init', function() {
            register_widget(\DShop\Modules\Cart\Widget::class);
        });
    }

    public function addAdminMenus(): void
    {
        add_submenu_page(
            'dshop',
            'Настройки корзины',
            'Корзина',
            'manage_options',
            'dshop-cart',
            [$this, 'renderCartSettingsPage']
        );
    }

    public function renderCartSettingsPage(): void
    {
        if (isset($_POST['dshop_save_cart']) && check_admin_referer('dshop_cart_save')) {
            $settings = [
                'redirect_after_add' => sanitize_text_field($_POST['redirect_after_add'] ?? ''),
                'cart_page_id' => absint($_POST['cart_page_id'] ?? 0),
                'checkout_page_id' => absint($_POST['checkout_page_id'] ?? 0),
            ];
            update_option('dshop_cart_settings', $settings);
            echo '<div class="notice notice-success"><p>Настройки корзины сохранены.</p></div>';
        }

        $settings = get_option('dshop_cart_settings', [
            'redirect_after_add' => '',
            'cart_page_id' => 0,
            'checkout_page_id' => 0,
        ]);

        include DSHOP_SRC_DIR . 'modules/cart/views/cart-settings.php';
    }

    /**
     * Get cart instance
     *
     * @return Cart
     */
    public function getCart(): Cart
    {
        return $this->cart;
    }

    /**
     * AJAX add to cart
     *
     * @return void
     */
    public function ajaxAddToCart(): void
    {
        check_ajax_referer('dshop_nonce', 'nonce');

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;

        if (!$product_id) {
            wp_send_json_error(['message' => 'Неверный ID товара']);
        }

        $result = $this->cart->add($product_id, $quantity);

        if ($result) {
            wp_send_json_success([
                'message' => 'Товар добавлен в корзину',
                'cart_count' => $this->cart->getCount(),
                'cart_total' => $this->cart->getTotal(),
            ]);
        } else {
            wp_send_json_error(['message' => 'Не удалось добавить товар в корзину']);
        }
    }

    /**
     * AJAX remove from cart
     *
     * @return void
     */
    public function ajaxRemoveFromCart(): void
    {
        check_ajax_referer('dshop_nonce', 'nonce');

        $cart_key = isset($_POST['cart_key']) ? sanitize_text_field($_POST['cart_key']) : '';

        if (!$cart_key) {
            wp_send_json_error(['message' => 'Неверный товар в корзине']);
        }

        $result = $this->cart->remove($cart_key);

        if ($result) {
            wp_send_json_success([
                'message' => 'Товар удалён из корзины',
                'cart_count' => $this->cart->getCount(),
                'totals' => $this->cart->getTotals(),
            ]);
        } else {
            wp_send_json_error(['message' => 'Не удалось удалить товар из корзины']);
        }
    }

    /**
     * AJAX update cart
     *
     * @return void
     */
    public function ajaxUpdateCart(): void
    {
        check_ajax_referer('dshop_nonce', 'nonce');

        $cart_key = isset($_POST['cart_key']) ? sanitize_text_field($_POST['cart_key']) : '';
        $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;

        if (!$cart_key) {
            wp_send_json_error(['message' => 'Неверный товар в корзине']);
        }

        $result = $this->cart->updateQuantity($cart_key, $quantity);

        if ($result) {
            $item = $this->cart->getItem($cart_key);
            wp_send_json_success([
                'message' => 'Корзина обновлена',
                'item_total' => $item ? $this->cart->formatPrice($item['price'] * $item['quantity']) : '0.00',
                'count' => $this->cart->getCount(),
                'totals' => $this->cart->getTotals(),
            ]);
        } else {
            wp_send_json_error(['message' => 'Не удалось обновить корзину']);
        }
    }

    /**
     * AJAX apply coupon
     *
     * @return void
     */
    public function ajaxApplyCoupon(): void
    {
        check_ajax_referer('dshop_nonce', 'nonce');

        $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';

        if (!$coupon_code) {
            wp_send_json_error(['message' => 'Введите код купона']);
        }

        $result = $this->cart->applyCoupon($coupon_code);

        if ($result) {
            wp_send_json_success([
                'message' => 'Купон применён',
                'discount' => $this->cart->getDiscount(),
                'totals' => $this->cart->getTotals(),
            ]);
        } else {
            wp_send_json_error(['message' => 'Неверный код купона']);
        }
    }

    public function ajaxRemoveCoupon(): void
    {
        check_ajax_referer('dshop_nonce', 'nonce');

        $this->cart->removeCoupon();

        wp_send_json_success([
            'message' => 'Купон удалён',
            'totals' => $this->cart->getTotals(),
        ]);
    }

    /**
     * Cart shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function cartShortcode(array $atts): string
    {
        ob_start();
        include DSHOP_TEMPLATE_DIR . 'parts/cart-content.php';
        return ob_get_clean();
    }

    /**
     * Mini cart shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function miniCartShortcode(array $atts): string
    {
        ob_start();
        include DSHOP_SRC_DIR . 'modules/cart/views/mini-cart.php';
        return ob_get_clean();
    }
}
