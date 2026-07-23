<?php
/**
 * Free Pay Payment Gateway
 *
 * @package DShop\Modules\Payment
 */

namespace DShop\Modules\Payment;

/**
 * Class FreePay
 *
 * Payment on delivery gateway
 */
class FreePay extends PaymentGateway
{
    /**
     * Gateway ID
     *
     * @var string
     */
    protected $id = 'free';

    /**
     * Gateway title
     *
     * @var string
     */
    protected $title = 'Оплата при получении';

    /**
     * Gateway description
     *
     * @var string
     */
    protected $description = 'Оплата наличными или картой при получении заказа';

    /**
     * {@inheritdoc}
     */
    public function processPayment(int $order_id)
    {
        $this->updateOrderStatus($order_id, 'processing');

        return [
            'success' => true,
            'message' => 'Заказ оформлен. Оплата при получении.',
            'redirect' => $this->getReturnUrl($order_id),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handleCallback(): void
    {
        // No callback needed for free payment
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
            'instructions' => [
                'label' => 'Payment Instructions',
                'type' => 'textarea',
                'default' => 'Оплата наличными или картой курьеру при доставке заказа.',
            ],
        ];
    }
}
