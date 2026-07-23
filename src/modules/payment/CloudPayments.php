<?php
/**
 * CloudPayments Payment Gateway
 *
 * @package DShop\Modules\Payment
 */

namespace DShop\Modules\Payment;

/**
 * Class CloudPayments
 *
 * CloudPayments payment gateway
 */
class CloudPayments extends PaymentGateway
{
    /**
     * Gateway ID
     *
     * @var string
     */
    protected $id = 'cloudpayments';

    /**
     * Gateway title
     *
     * @var string
     */
    protected $title = 'CloudPayments';

    /**
     * Gateway description
     *
     * @var string
     */
    protected $description = 'Оплата через CloudPayments';

    /**
     * {@inheritdoc}
     */
    public function processPayment(int $order_id)
    {
        global $wpdb;

        $order = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}dshop_orders WHERE id = %d", $order_id)
        );

        if (!$order) {
            return new \WP_Error('order_not_found', 'Order not found');
        }

        $public_id = $this->getSetting('public_id');

        if (!$public_id) {
            return new \WP_Error('cloudpayments_error', 'CloudPayments public ID not configured');
        }

        return [
            'public_id' => $public_id,
            'description' => sprintf('Заказ #%s', $order->order_number),
            'amount' => $order->total,
            'currency' => 'RUB',
            'order_id' => $order_id,
            'callback_url' => $this->getCallbackUrl(),
        ];
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

        $success = $data['Success'] ?? false;
        $status = $data['Status'] ?? '';
        $order_id = $data['InvoiceId'] ?? 0;
        $transaction_id = $data['TransactionId'] ?? '';
        $reason = $data['Reason'] ?? '';

        if (!$order_id) {
            wp_die('InvoiceId not found in callback');
        }

        if ($success && $status === 'SUCCESS') {
            $this->updateOrderStatus($order_id, 'processing', (string) $transaction_id);
        } else {
            $this->updateOrderStatus($order_id, 'failed');
            \DShop\Core\DShop::getInstance()->getLogger()->error("CloudPayments payment failed for order #{$order_id}", [
                'reason' => $reason,
            ]);
        }

        http_response_code(200);
        echo json_encode(['code' => 0]);
        exit;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormFields(): array
    {
        return [
            'public_id' => [
                'label' => 'Public ID',
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
