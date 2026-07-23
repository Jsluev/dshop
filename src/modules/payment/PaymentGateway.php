<?php
/**
 * Payment Gateway Base Class
 *
 * @package DShop\Modules\Payment
 */

namespace DShop\Modules\Payment;

/**
 * Class PaymentGateway
 *
 * Abstract base class for payment gateways
 */
abstract class PaymentGateway
{
    /**
     * Gateway ID
     *
     * @var string
     */
    protected $id;

    /**
     * Gateway title
     *
     * @var string
     */
    protected $title;

    /**
     * Gateway description
     *
     * @var string
     */
    protected $description;

    /**
     * Gateway settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->loadSettings();
    }

    /**
     * Get gateway ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get gateway title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get gateway description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Check if gateway is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $enabled_gateways = get_option('dshop_enabled_payment_methods', []);
        return in_array($this->id, $enabled_gateways, true);
    }

    /**
     * Initialize gateway
     *
     * @return void
     */
    public function init(): void
    {
        // Override in child class
    }

    /**
     * Process payment
     *
     * @param int $order_id Order ID
     * @return array|WP_Error
     */
    abstract public function processPayment(int $order_id);

    /**
     * Handle payment callback
     *
     * @return void
     */
    abstract public function handleCallback(): void;

    /**
     * Get payment form fields
     *
     * @return array
     */
    abstract public function getFormFields(): array;

    /**
     * Get settings
     *
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Update setting
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return void
     */
    public function updateSetting(string $key, $value): void
    {
        $this->settings[$key] = $value;
        update_option("dshop_payment_{$this->id}_settings", $this->settings);
    }

    /**
     * Load settings
     *
     * @return void
     */
    protected function loadSettings(): void
    {
        $this->settings = get_option("dshop_payment_{$this->id}_settings", []);
    }

    /**
     * Get callback URL
     *
     * @return string
     */
    public function getCallbackUrl(): string
    {
        return add_query_arg([
            'action' => 'dshop_payment_callback',
            'gateway' => $this->id,
        ], admin_url('admin-ajax.php'));
    }

    /**
     * Get return URL
     *
     * @param int $order_id Order ID
     * @return string
     */
    public function getReturnUrl(int $order_id): string
    {
        return add_query_arg('order_id', $order_id, get_permalink(get_option('dshop_checkout_page_id')));
    }

    /**
     * Get cancel URL
     *
     * @param int $order_id Order ID
     * @return string
     */
    public function getCancelUrl(int $order_id): string
    {
        return get_permalink(get_option('dshop_checkout_page_id'));
    }

    /**
     * Update order status
     *
     * @param int $order_id Order ID
     * @param string $status New status
     * @param string $transaction_id Transaction ID
     * @return void
     */
    protected function updateOrderStatus(int $order_id, string $status, string $transaction_id = ''): void
    {
        global $wpdb;

        $wpdb->update(
            $wpdb->prefix . 'dshop_orders',
            [
                'status' => $status,
                'payment_status' => $status,
            ],
            ['id' => $order_id],
            ['%s', '%s'],
            ['%d']
        );

        if ($transaction_id) {
            update_post_meta($order_id, '_dshop_transaction_id', $transaction_id);
        }

        do_action('dshop/order/status_changed', $order_id, $status, $transaction_id);
    }
}
