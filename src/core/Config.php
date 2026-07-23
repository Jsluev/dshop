<?php
/**
 * Configuration Manager
 *
 * @package DShop\Core
 */

namespace DShop\Core;

/**
 * Class Config
 *
 * Manages plugin configuration
 */
class Config
{
    /**
     * Configuration values
     *
     * @var array
     */
    private $config = [];

    /**
     * Default configuration
     *
     * @var array
     */
    private $defaults = [
        'general' => [
            'currency' => 'RUB',
            'tax_rate' => 0,
            'weight_unit' => 'kg',
            'distance_unit' => 'm',
            'timezone' => '',
        ],
        'catalog' => [
            'products_per_page' => 12,
            'enable_reviews' => true,
            'enable_wishlist' => false,
            'enable_compare' => false,
            'enable_ajax' => true,
        ],
        'inventory' => [
            'manage_stock' => true,
            'low_stock_threshold' => 5,
            'out_of_stock_threshold' => 0,
            'allow_backorders' => false,
        ],
        'cart' => [
            'redirect_to_checkout' => false,
            'enable_coupon' => true,
            'minimum_order_amount' => 0,
            'maximum_order_amount' => 0,
        ],
        'checkout' => [
            'enable_guest_checkout' => true,
            'enable_registration' => true,
            'force_ssl' => false,
        ],
        'payment' => [
            'enabled_methods' => [],
            'test_mode' => true,
        ],
        'shipping' => [
            'enabled_methods' => [],
            'free_shipping_threshold' => 0,
        ],
        'email' => [
            'from_name' => '',
            'from_email' => '',
            'admin_email' => '',
        ],
        'seo' => [
            'enable_schema' => true,
            'enable_breadcrumbs' => true,
            'product_title_format' => '{name} - купить в интернет-магазине',
        ],
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->load();
    }

    /**
     * Load configuration from database
     *
     * @return void
     */
    public function load(): void
    {
        $saved = get_option('dshop_config', []);
        $this->config = array_replace_recursive($this->defaults, $saved);
    }

    /**
     * Save configuration to database
     *
     * @return void
     */
    public function save(): void
    {
        update_option('dshop_config', $this->config);
    }

    /**
     * Get configuration value
     *
     * @param string $key Dot-notated key (e.g., 'catalog.products_per_page')
     * @param mixed $default Default value
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set configuration value
     *
     * @param string $key Dot-notated key
     * @param mixed $value Value to set
     * @return void
     */
    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    /**
     * Get all configuration
     *
     * @return array
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Get section configuration
     *
     * @param string $section Section name
     * @return array
     */
    public function getSection(string $section): array
    {
        return $this->config[$section] ?? [];
    }

    /**
     * Set section configuration
     *
     * @param string $section Section name
     * @param array $values Configuration values
     * @return void
     */
    public function setSection(string $section, array $values): void
    {
        $this->config[$section] = array_merge(
            $this->config[$section] ?? [],
            $values
        );
    }

    /**
     * Check if configuration key exists
     *
     * @param string $key Dot-notated key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Get default value
     *
     * @param string $key Dot-notated key
     * @return mixed
     */
    public function getDefault(string $key)
    {
        $keys = explode('.', $key);
        $value = $this->defaults;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return null;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Reset configuration to defaults
     *
     * @return void
     */
    public function reset(): void
    {
        $this->config = $this->defaults;
        $this->save();
    }
}
