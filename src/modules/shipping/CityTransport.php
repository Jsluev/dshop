<?php
/**
 * City Transport Shipping Method
 *
 * @package DShop\Modules\Shipping
 */

namespace DShop\Modules\Shipping;

/**
 * Class CityTransport
 *
 * City transport shipping method
 */
class CityTransport extends ShippingMethod
{
    /**
     * Method ID
     *
     * @var string
     */
    protected $id = 'city';

    /**
     * Method title
     *
     * @var string
     */
    protected $title = 'Городская доставка';

    /**
     * Method description
     *
     * @var string
     */
    protected $description = 'Доставка курьером по городу';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->loadSettings();
    }

    /**
     * {@inheritdoc}
     */
    public function calculateCost(array $params = []): float
    {
        $base_cost = (float) $this->getSetting('base_cost', 300);
        $free_threshold = (float) $this->getSetting('free_threshold', 0);

        if ($free_threshold > 0) {
            $cart_module = \DShop\Core\DShop::getInstance()->getModule('cart');
            if ($cart_module) {
                $cart = $cart_module->getCart();
                if ($cart->getSubtotal() >= $free_threshold) {
                    return 0;
                }
            }
        }

        return $base_cost;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormFields(): array
    {
        return [
            'base_cost' => [
                'label' => 'Base Cost',
                'type' => 'number',
                'default' => 300,
                'required' => true,
            ],
            'free_threshold' => [
                'label' => 'Free Shipping Threshold',
                'type' => 'number',
                'default' => 0,
                'description' => 'Free shipping for orders above this amount',
            ],
            'working_hours' => [
                'label' => 'Working Hours',
                'type' => 'text',
                'default' => 'Пн-Пт: 9:00-18:00',
            ],
        ];
    }
}
