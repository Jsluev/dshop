<?php
/**
 * CDEK Shipping Method
 *
 * @package DShop\Modules\Shipping
 */

namespace DShop\Modules\Shipping;

/**
 * Class CDEK
 *
 * CDEK shipping method
 */
class CDEK extends ShippingMethod
{
    /**
     * Method ID
     *
     * @var string
     */
    protected $id = 'cdek';

    /**
     * Method title
     *
     * @var string
     */
    protected $title = 'СДЭК';

    /**
     * Method description
     *
     * @var string
     */
    protected $description = 'Доставка службой СДЭК';

    /**
     * {@inheritdoc}
     */
    public function calculateCost(array $params = []): float
    {
        $base_cost = (float) $this->getSetting('base_cost', 350);
        $per_kg = (float) $this->getSetting('per_kg', 50);

        // TODO: Implement actual CDEK API calculation
        // For now, return base cost
        return $base_cost;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormFields(): array
    {
        return [
            'api_key' => [
                'label' => 'API Key',
                'type' => 'password',
                'required' => true,
            ],
            'base_cost' => [
                'label' => 'Base Cost',
                'type' => 'number',
                'default' => 350,
                'required' => true,
            ],
            'per_kg' => [
                'label' => 'Cost per KG',
                'type' => 'number',
                'default' => 50,
            ],
        ];
    }
}
