<?php
/**
 * Checkout Module
 *
 * @package DShop\Modules\Checkout
 */

namespace DShop\Modules\Checkout;

use DShop\Core\BaseModule;

/**
 * Class CheckoutModule
 *
 * Handles checkout process
 */
class CheckoutModule extends BaseModule
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name = 'checkout';

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
    protected $description = 'Checkout process module';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addAdminMenus']);

        // AJAX handlers
        add_action('wp_ajax_dshop_process_checkout', [$this, 'ajaxProcessCheckout']);
        add_action('wp_ajax_nopriv_dshop_process_checkout', [$this, 'ajaxProcessCheckout']);

        // Shortcodes
        add_shortcode('dshop_checkout', [$this, 'checkoutShortcode']);
    }

    public function addAdminMenus(): void
    {
        add_submenu_page(
            'dshop',
            'Настройки оформления заказа',
            'Оформление',
            'manage_options',
            'dshop-checkout',
            [$this, 'renderCheckoutSettingsPage']
        );
    }

    public function renderCheckoutSettingsPage(): void
    {
        if (isset($_POST['dshop_save_checkout']) && check_admin_referer('dshop_checkout_save')) {
            $settings = [
                'require_phone' => isset($_POST['require_phone']) ? 1 : 0,
                'require_company' => isset($_POST['require_company']) ? 1 : 0,
                'require_address' => isset($_POST['require_address']) ? 1 : 0,
                'require_comment' => isset($_POST['require_comment']) ? 1 : 0,
                'enable_guest_checkout' => isset($_POST['enable_guest_checkout']) ? 1 : 0,
                'order_prefix' => sanitize_text_field($_POST['order_prefix'] ?? 'ORD-'),
                'order_status_after' => sanitize_text_field($_POST['order_status_after'] ?? 'pending'),
            ];
            update_option('dshop_checkout_settings', $settings);
            echo '<div class="notice notice-success"><p>Настройки оформления сохранены.</p></div>';
        }

        $settings = get_option('dshop_checkout_settings', [
            'require_phone' => 1,
            'require_company' => 0,
            'require_address' => 1,
            'require_comment' => 0,
            'enable_guest_checkout' => 1,
            'order_prefix' => 'ORD-',
            'order_status_after' => 'pending',
        ]);

        include DSHOP_SRC_DIR . 'modules/checkout/views/checkout-settings.php';
    }

    /**
     * AJAX process checkout
     *
     * @return void
     */
     public function ajaxProcessCheckout(): void
     {
        check_ajax_referer('dshop_nonce', 'nonce');

        $result = $this->processOrder($_POST);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success([
            'message' => 'Заказ успешно оформлен',
            'redirect' => get_permalink(get_option('dshop_checkout_page_id')) . '?order_id=' . $result,
            'order_id' => $result,
        ]);
    }

    public function checkoutShortcode(array $atts): string
    {
        ob_start();
        
        $cart_module = \DShop\Core\DShop::getInstance()->getModule('cart');
        $cart = $cart_module->getCart();
        
        if ($cart->isEmpty()) {
            echo '<div class="dshop-empty-state">';
            echo '<h2>' . 'Ваша корзина пуста' . '</h2>';
            echo '<a href="' . get_post_type_archive_link('dshop_product') . '" class="dshop-empty-state__button">' . 'Продолжить покупки' . '</a>';
            echo '</div>';
        } else {
            include DSHOP_SRC_DIR . 'modules/checkout/views/checkout.php';
        }
        
        return ob_get_clean();
    }

    /**
     * Order confirmation shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function orderConfirmationShortcode(array $atts): string
    {
        ob_start();
        
        $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
        
        if (!$order_id) {
            echo '<div class="dshop-empty-state">';
            echo '<h2>' . 'Заказ не найден' . '</h2>';
            echo '</div>';
        } else {
            include DSHOP_SRC_DIR . 'modules/checkout/views/order-confirmation.php';
        }
        
        return ob_get_clean();
    }

    /**
     * Process order
     *
     * @param array $data Form data
     * @return int|WP_Error Order ID or error
     */
    private function processOrder(array $data)
    {
        global $wpdb;

        // Validate required fields
        $required_fields = [
            'billing_first_name',
            'billing_last_name',
            'billing_email',
            'billing_phone',
        ];

        // Validate shipping/payment
        $valid_shipping = ['pickup', 'city_transport', 'cdek'];
        $valid_payment = ['yookassa', 'cloudpayments', 'free'];

        $shipping_method = sanitize_text_field($data['shipping_method'] ?? '');
        $payment_method = sanitize_text_field($data['payment_method'] ?? '');

        if (!in_array($shipping_method, $valid_shipping, true)) {
            return new \WP_Error('invalid_shipping', 'Неверный способ доставки');
        }

        if (!in_array($payment_method, $valid_payment, true)) {
            return new \WP_Error('invalid_payment', 'Неверный способ оплаты');
        }

        // Address only required for delivery
        if ($shipping_method !== 'pickup') {
            $required_fields[] = 'billing_address_1';
            $required_fields[] = 'billing_city';
            $required_fields[] = 'billing_postcode';
        }

        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return new \WP_Error('missing_field', sprintf('Заполните поле «%s»', str_replace('_', ' ', $field)));
            }
        }

        // Validate email
        if (!is_email($data['billing_email'])) {
            return new \WP_Error('invalid_email', 'Неверный email');
        }

        // Get cart
        $cart_module = \DShop\Core\DShop::getInstance()->getModule('cart');
        $cart = $cart_module->getCart();

        if ($cart->isEmpty()) {
            return new \WP_Error('empty_cart', 'Ваша корзина пуста');
        }

        // Generate order number
        $order_number = $this->generateOrderNumber();

        // Create order
        $wpdb->insert(
            $wpdb->prefix . 'dshop_orders',
            [
                'order_number' => $order_number,
                'status' => get_option('dshop_order_status_after', 'pending'),
                'currency' => get_option('dshop_currency', 'RUB'),
                'subtotal' => $cart->getSubtotal(),
                'discount' => $cart->getDiscount(),
                'shipping_cost' => floatval($data['shipping_cost'] ?? 0),
                'tax' => $cart->getTax(),
                'total' => $cart->getTotal() + floatval($data['shipping_cost'] ?? 0),
                'billing_first_name' => sanitize_text_field($data['billing_first_name']),
                'billing_last_name' => sanitize_text_field($data['billing_last_name']),
                'billing_company' => sanitize_text_field($data['billing_company'] ?? ''),
                'billing_address_1' => sanitize_text_field($data['billing_address_1'] ?? ''),
                'billing_address_2' => sanitize_text_field($data['billing_address_2'] ?? ''),
                'billing_city' => sanitize_text_field($data['billing_city'] ?? ''),
                'billing_state' => sanitize_text_field($data['billing_state'] ?? ''),
                'billing_postcode' => sanitize_text_field($data['billing_postcode'] ?? ''),
                'billing_country' => sanitize_text_field($data['billing_country'] ?? 'RU'),
                'billing_phone' => sanitize_text_field($data['billing_phone']),
                'billing_email' => sanitize_email($data['billing_email']),
                'shipping_first_name' => sanitize_text_field($data['shipping_first_name'] ?? $data['billing_first_name']),
                'shipping_last_name' => sanitize_text_field($data['shipping_last_name'] ?? $data['billing_last_name']),
                'shipping_company' => sanitize_text_field($data['shipping_company'] ?? ''),
                'shipping_address_1' => sanitize_text_field($data['shipping_address_1'] ?? $data['billing_address_1'] ?? ''),
                'shipping_address_2' => sanitize_text_field($data['shipping_address_2'] ?? ''),
                'shipping_city' => sanitize_text_field($data['shipping_city'] ?? $data['billing_city'] ?? ''),
                'shipping_state' => sanitize_text_field($data['shipping_state'] ?? $data['billing_state'] ?? ''),
                'shipping_postcode' => sanitize_text_field($data['shipping_postcode'] ?? $data['billing_postcode'] ?? ''),
                'shipping_country' => sanitize_text_field($data['shipping_country'] ?? $data['billing_country'] ?? 'RU'),
                'payment_method' => sanitize_text_field($data['payment_method'] ?? ''),
                'shipping_method' => sanitize_text_field($data['shipping_method'] ?? ''),
                'customer_note' => sanitize_textarea_field($data['customer_note'] ?? ''),
                'ip_address' => $this->getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ],
            ['%s', '%s', '%s', '%f', '%f', '%f', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        $order_id = $wpdb->insert_id;

        if (!$order_id) {
            return new \WP_Error('order_failed', 'Не удалось создать заказ');
        }

        // Add order items
        $cart_items = $cart->getItems();
        $this->addOrderItems($order_id, $cart_items);

        // Decrement stock
        foreach ($cart_items as $cart_key => $item) {
            $manage_stock = (bool) get_post_meta($item['product_id'], '_dshop_manage_stock', true);
            if ($manage_stock) {
                $current_stock = (int) get_post_meta($item['product_id'], '_dshop_stock_quantity', true);
                $new_stock = max(0, $current_stock - $item['quantity']);
                update_post_meta($item['product_id'], '_dshop_stock_quantity', $new_stock);
            }
        }

        // Log order (before cart clear, while totals are still available)
        \DShop\Core\DShop::getInstance()->getLogger()->info("Order #{$order_number} created", [
            'order_id' => $order_id,
            'total' => $cart->getTotal(),
        ]);

        // Clear cart
        $cart->clear();

        // Send notifications
        $this->sendOrderNotifications($order_id, $data);

        do_action('dshop/order/created', $order_id, $data);

        return $order_id;
    }

    /**
     * Add order items
     *
     * @param int $order_id Order ID
     * @param array $items Cart items
     * @return void
     */
    private function addOrderItems(int $order_id, array $items): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'dshop_order_items';

        foreach ($items as $item) {
            $wpdb->insert(
                $table,
                [
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                    'sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['price'] * $item['quantity'],
                ],
                ['%d', '%d', '%s', '%s', '%d', '%f', '%f']
            );
        }
    }

    /**
     * Generate order number
     *
     * @return string
     */
    private function generateOrderNumber(): string
    {
        global $wpdb;

        $table = $wpdb->prefix . 'dshop_orders';
        $prefix = get_option('dshop_order_prefix', 'ORD');
        $date = date('Ymd');

        $last_order = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT order_number FROM {$table} WHERE order_number LIKE %s ORDER BY id DESC LIMIT 1",
                "{$prefix}-{$date}-%"
            )
        );

        if ($last_order) {
            $last_number = (int) substr($last_order, -4);
            $new_number = $last_number + 1;
        } else {
            $new_number = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $date, $new_number);
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    private function getClientIp(): string
    {
        $ip_keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = explode(',', $_SERVER[$key]);
                $ip = trim($ip[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Send order notifications
     *
     * @param int $order_id Order ID
     * @param array $data Form data
     * @return void
     */
    private function sendOrderNotifications(int $order_id, array $data): void
    {
        global $wpdb;

        // Get order total from DB
        $order_total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT total FROM {$wpdb->prefix}dshop_orders WHERE id = %d",
                $order_id
            )
        );

        // Admin notification
        $admin_email = get_option('dshop_admin_email', get_option('admin_email'));
        $subject = sprintf('Новый заказ #%d', $order_id);
        $message = sprintf(
            "Поступил новый заказ.\n\nID заказа: %d\nСумма: %s\n\nКлиент: %s %s\nEmail: %s\nТелефон: %s",
            $order_id,
            number_format((float) $order_total, 2, '.', ' '),
            $data['billing_first_name'],
            $data['billing_last_name'],
            $data['billing_email'],
            $data['billing_phone']
        );

        wp_mail($admin_email, $subject, $message);

        // Customer notification
        $customer_subject = sprintf('Подтверждение заказа #%d', $order_id);
        $customer_message = sprintf(
            "Спасибо за ваш заказ!\n\nID заказа: %d\n\nМы обработаем ваш заказ в ближайшее время.",
            $order_id
        );

        wp_mail($data['billing_email'], $customer_subject, $customer_message);
    }

    /**
     * Checkout shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function checkoutShortcode(array $atts): string
    {
        ob_start();
        
        $cart_module = \DShop\Core\DShop::getInstance()->getModule('cart');
        $cart = $cart_module->getCart();
        
        if ($cart->isEmpty()) {
            echo '<div class="dshop-empty-state">';
            echo '<h2>' . 'Ваша корзина пуста' . '</h2>';
            echo '<a href="' . get_post_type_archive_link('dshop_product') . '" class="dshop-empty-state__button">' . 'Продолжить покупки' . '</a>';
            echo '</div>';
        } else {
            include DSHOP_SRC_DIR . 'modules/checkout/views/checkout.php';
        }
        
        return ob_get_clean();
    }
}
