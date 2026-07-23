<?php
/**
 * YooKassa Payment Gateway
 *
 * @package DShop\Modules\Payment
 */

namespace DShop\Modules\Payment;

use YooKassa\Client;

/**
 * Class YooKassa
 *
 * YooKassa payment gateway
 */
class YooKassa extends PaymentGateway
{
    /**
     * Gateway ID
     *
     * @var string
     */
    protected $id = 'yookassa';

    /**
     * Gateway title
     *
     * @var string
     */
    protected $title = 'ЮKassa';

    /**
     * Gateway description
     *
     * @var string
     */
    protected $description = 'Оплата через ЮKassa';

    /**
     * YooKassa client
     *
     * @var Client|null
     */
    private $client = null;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->initClient();
    }

    /**
     * Initialize YooKassa client
     *
     * @return void
     */
    private function initClient(): void
    {
        $shop_id = $this->getSetting('shop_id');
        $secret_key = $this->getSetting('secret_key');

        if ($shop_id && $secret_key) {
            $this->client = new Client();
            $this->client->setAuth($shop_id, $secret_key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processPayment(int $order_id)
    {
        if (!$this->client) {
            return new \WP_Error('yookassa_error', 'YooKassa client not configured');
        }

        global $wpdb;

        $order = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}dshop_orders WHERE id = %d", $order_id)
        );

        if (!$order) {
            return new \WP_Error('order_not_found', 'Order not found');
        }

        try {
            $idempotence_key = uniqid('dshop_', true);

            $payment = $this->client->createPayment([
                'amount' => [
                    'value' => number_format($order->total, 2, '.', ''),
                    'currency' => 'RUB',
                ],
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => $this->getReturnUrl($order_id),
                ],
                'capture' => true,
                'description' => sprintf('Заказ #%s', $order->order_number),
                'metadata' => [
                    'order_id' => $order_id,
                ],
            ], $idempotence_key);

            return [
                'redirect' => $payment->getConfirmation()->getRedirectUrl(),
                'payment_id' => $payment->getId(),
            ];
        } catch (\Exception $e) {
            return new \WP_Error('yookassa_error', $e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleCallback(): void
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            wp_die('Invalid callback data');
        }

        $event = $data['event'] ?? '';
        $payment = $data['object'] ?? [];

        $order_id = $payment['metadata']['order_id'] ?? 0;
        $payment_id = $payment['id'] ?? '';
        $status = $payment['status'] ?? '';

        if (!$order_id) {
            wp_die('Order ID not found in callback');
        }

        switch ($event) {
            case 'payment.succeeded':
                $this->updateOrderStatus($order_id, 'processing', $payment_id);
                break;

            case 'payment.canceled':
                $this->updateOrderStatus($order_id, 'cancelled', $payment_id);
                break;

            case 'payment.waiting_for_capture':
                $this->updateOrderStatus($order_id, 'pending', $payment_id);
                break;
        }

        http_response_code(200);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormFields(): array
    {
        return [
            'shop_id' => [
                'label' => 'Shop ID',
                'type' => 'text',
                'required' => true,
            ],
            'secret_key' => [
                'label' => 'Secret Key',
                'type' => 'password',
                'required' => true,
            ],
            'test_mode' => [
                'label' => 'Test Mode',
                'type' => 'checkbox',
                'default' => true,
            ],
        ];
    }
}
