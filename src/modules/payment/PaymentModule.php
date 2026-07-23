<?php
/**
 * Payment Module
 *
 * @package DShop\Modules\Payment
 */

namespace DShop\Modules\Payment;

use DShop\Core\BaseModule;

/**
 * Class PaymentModule
 *
 * Handles payment processing
 */
class PaymentModule extends BaseModule
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name = 'payment';

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
    protected $description = 'Payment processing module';

    /**
     * Payment gateways
     *
     * @var array
     */
    private $gateways = [];

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->loadGateways();
    }

    /**
     * {@inheritdoc}
     */
    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addAdminMenus']);

        // AJAX handlers
        add_action('wp_ajax_dshop_process_payment', [$this, 'ajaxProcessPayment']);
        add_action('wp_ajax_nopriv_dshop_process_payment', [$this, 'ajaxProcessPayment']);

        add_action('wp_ajax_dshop_payment_callback', [$this, 'handleCallback']);
        add_action('wp_ajax_nopriv_dshop_payment_callback', [$this, 'handleCallback']);

        // Shortcodes
        add_shortcode('dshop_payment_form', [$this, 'paymentFormShortcode']);
    }

    /**
     * Add admin menu pages
     *
     * @return void
     */
    public function addAdminMenus(): void
    {
        add_submenu_page(
            'dshop',
            'Способы оплаты',
            'Оплата',
            'manage_options',
            'dshop-payment',
            [$this, 'renderPaymentPage']
        );
    }

    /**
     * Render payment settings page
     *
     * @return void
     */
    public function renderPaymentPage(): void
    {
        if (isset($_POST['dshop_save_payment']) && check_admin_referer('dshop_payment_save')) {
            $settings = [
                'active_gateway' => sanitize_text_field($_POST['active_gateway'] ?? ''),
                'yookassa_shop_id' => sanitize_text_field($_POST['yookassa_shop_id'] ?? ''),
                'yookassa_secret_key' => sanitize_text_field($_POST['yookassa_secret_key'] ?? ''),
                'yookassa_enabled' => isset($_POST['yookassa_enabled']) ? 1 : 0,
                'cloudpayments_public_id' => sanitize_text_field($_POST['cloudpayments_public_id'] ?? ''),
                'cloudpayments_api_key' => sanitize_text_field($_POST['cloudpayments_api_key'] ?? ''),
                'cloudpayments_enabled' => isset($_POST['cloudpayments_enabled']) ? 1 : 0,
                'free_enabled' => isset($_POST['free_enabled']) ? 1 : 0,
                'free_title' => sanitize_text_field($_POST['free_title'] ?? 'Оплата при получении'),
            ];
            update_option('dshop_payment_settings', $settings);
            echo '<div class="notice notice-success"><p>Настройки оплаты сохранены.</p></div>';
        }

        $settings = get_option('dshop_payment_settings', [
            'active_gateway' => 'free',
            'yookassa_shop_id' => '',
            'yookassa_secret_key' => '',
            'yookassa_enabled' => 0,
            'cloudpayments_public_id' => '',
            'cloudpayments_api_key' => '',
            'cloudpayments_enabled' => 0,
            'free_enabled' => 1,
            'free_title' => 'Оплата при получении',
        ]);

        include DSHOP_SRC_DIR . 'modules/payment/views/payment-settings.php';
    }

    /**
     * Load payment gateways
     *
     * @return void
     */
    private function loadGateways(): void
    {
        $this->gateways = [
            'yookassa' => new YooKassa(),
            'cloudpayments' => new CloudPayments(),
            'free' => new FreePay(),
        ];

        foreach ($this->gateways as $gateway) {
            if ($gateway->isActive()) {
                $gateway->init();
            }
        }
    }

    /**
     * Get gateway by ID
     *
     * @param string $gateway_id Gateway ID
     * @return PaymentGateway|null
     */
    public function getGateway(string $gateway_id): ?PaymentGateway
    {
        return $this->gateways[$gateway_id] ?? null;
    }

    /**
     * Get all active gateways
     *
     * @return array
     */
    public function getActiveGateways(): array
    {
        return array_filter($this->gateways, function($gateway) {
            return $gateway->isActive();
        });
    }

    /**
     * Process payment
     *
     * @param int $order_id Order ID
     * @param string $gateway_id Gateway ID
     * @return array|WP_Error
     */
    public function processPayment(int $order_id, string $gateway_id)
    {
        $gateway = $this->getGateway($gateway_id);

        if (!$gateway) {
            return new \WP_Error('invalid_gateway', 'Неверный платёжный шлюз');
        }

        if (!$gateway->isActive()) {
            return new \WP_Error('inactive_gateway', 'Платёжный шлюз неактивен');
        }

        $result = $gateway->processPayment($order_id);

        if (is_wp_error($result)) {
            \DShop\Core\DShop::getInstance()->getLogger()->error("Payment failed for order #{$order_id}", [
                'gateway' => $gateway_id,
                'error' => $result->get_error_message(),
            ]);
        } else {
            \DShop\Core\DShop::getInstance()->getLogger()->info("Payment processed for order #{$order_id}", [
                'gateway' => $gateway_id,
                'transaction_id' => $result['transaction_id'] ?? '',
            ]);
        }

        return $result;
    }

    /**
     * Handle payment callback
     *
     * @return void
     */
    public function handleCallback(): void
    {
        $gateway_id = isset($_GET['gateway']) ? sanitize_text_field($_GET['gateway']) : '';
        $gateway = $this->getGateway($gateway_id);

        if (!$gateway) {
            wp_die('Invalid gateway');
        }

        $gateway->handleCallback();
    }

    /**
     * AJAX process payment
     *
     * @return void
     */
    public function ajaxProcessPayment(): void
    {
        check_ajax_referer('dshop_nonce', 'nonce');

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        $gateway_id = isset($_POST['gateway_id']) ? sanitize_text_field($_POST['gateway_id']) : '';

        if (!$order_id || !$gateway_id) {
            wp_send_json_error(['message' => 'Неверные параметры']);
        }

        $result = $this->processPayment($order_id, $gateway_id);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success($result);
    }

    /**
     * Payment form shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function paymentFormShortcode(array $atts): string
    {
        $atts = shortcode_atts([
            'order_id' => 0,
        ], $atts, 'dshop_payment_form');

        ob_start();
        include DSHOP_SRC_DIR . 'modules/payment/views/payment-form.php';
        return ob_get_clean();
    }
}
