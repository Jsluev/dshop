<?php
/**
 * Shipping Method Base Class
 *
 * @package DShop\Modules\Shipping
 */

namespace DShop\Modules\Shipping;

/**
 * Class ShippingMethod
 *
 * Abstract base class for shipping methods
 */
abstract class ShippingMethod
{
    /**
     * Method ID
     *
     * @var string
     */
    protected $id;

    /**
     * Method title
     *
     * @var string
     */
    protected $title;

    /**
     * Method description
     *
     * @var string
     */
    protected $description;

    /**
     * Method settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Get method ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get method title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get method description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Check if method is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $enabled_methods = get_option('dshop_enabled_shipping_methods', []);
        return in_array($this->id, $enabled_methods, true);
    }

    /**
     * Initialize method
     *
     * @return void
     */
    public function init(): void
    {
        // Override in child class
    }

    /**
     * Calculate shipping cost
     *
     * @param array $params Parameters
     * @return float
     */
    abstract public function calculateCost(array $params = []): float;

    /**
     * Get cost text
     *
     * @return string
     */
    public function getCostText(): string
    {
        $cost = $this->calculateCost();
        return number_format($cost, 2, '.', ' ') . ' ' . get_option('dshop_currency', 'RUB');
    }

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
        update_option("dshop_shipping_{$this->id}_settings", $this->settings);
    }

    /**
     * Load settings
     *
     * @return void
     */
    protected function loadSettings(): void
    {
        $this->settings = get_option("dshop_shipping_{$this->id}_settings", []);
    }

    /**
     * Get method form fields
     *
     * @return array
     */
    abstract public function getFormFields(): array;
}
