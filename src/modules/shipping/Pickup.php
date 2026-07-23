<?php
/**
 * Pickup Shipping Method
 *
 * @package DShop\Modules\Shipping
 */

namespace DShop\Modules\Shipping;

/**
 * Class Pickup
 *
 * Pickup shipping method
 */
class Pickup extends ShippingMethod
{
    /**
     * Method ID
     *
     * @var string
     */
    protected $id = 'pickup';

    /**
     * Method title
     *
     * @var string
     */
    protected $title = 'Самовывоз';

    /**
     * Method description
     *
     * @var string
     */
    protected $description = 'Забрать заказ самостоятельно из магазина';

    /**
     * {@inheritdoc}
     */
    public function calculateCost(array $params = []): float
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormFields(): array
    {
        return [
            'address' => [
                'label' => 'Address',
                'type' => 'text',
                'required' => true,
            ],
            'working_hours' => [
                'label' => 'Working Hours',
                'type' => 'text',
                'default' => 'Пн-Пт: 9:00-18:00',
            ],
            'instructions' => [
                'label' => 'Instructions',
                'type' => 'textarea',
            ],
        ];
    }
}
