<?php
/**
 * Shipping Module
 *
 * @package DShop\Modules\Shipping
 */

namespace DShop\Modules\Shipping;

use DShop\Core\BaseModule;

/**
 * Class ShippingModule
 *
 * Handles shipping methods
 */
class ShippingModule extends BaseModule
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name = 'shipping';

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
    protected $description = 'Shipping methods module';

    /**
     * Shipping methods
     *
     * @var array
     */
    private $methods = [];

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->loadMethods();
    }

    /**
     * {@inheritdoc}
     */
    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addAdminMenus']);
        add_action('admin_init', [$this, 'handleShippingForm']);

        // AJAX handlers
        add_action('wp_ajax_dshop_calculate_shipping', [$this, 'ajaxCalculateShipping']);
        add_action('wp_ajax_nopriv_dshop_calculate_shipping', [$this, 'ajaxCalculateShipping']);

        // Shortcodes
        add_shortcode('dshop_shipping_methods', [$this, 'shippingMethodsShortcode']);
    }

    public function addAdminMenus(): void
    {
        add_submenu_page(
            'dshop',
            'Настройки доставки',
            'Доставка',
            'manage_options',
            'dshop-shipping',
            [$this, 'renderShippingPage']
        );
    }

    /**
     * Handle shipping form submission (admin_init)
     */
    public function handleShippingForm(): void
    {
        if (!isset($_POST['dshop_save_shipping'])) {
            return;
        }
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'dshop_shipping_save')) {
            return;
        }

        $settings = [
            'pickup_enabled' => isset($_POST['pickup_enabled']) ? 1 : 0,
            'pickup_title' => sanitize_text_field($_POST['pickup_title'] ?? 'Самовывоз'),
            'pickup_address' => sanitize_textarea_field($_POST['pickup_address'] ?? ''),
            'pickup_cost' => floatval($_POST['pickup_cost'] ?? 0),
            'city_enabled' => isset($_POST['city_enabled']) ? 1 : 0,
            'city_title' => sanitize_text_field($_POST['city_title'] ?? 'Городская транспортная компания'),
            'city_cost' => floatval($_POST['city_cost'] ?? 0),
            'city_free_from' => floatval($_POST['city_free_from'] ?? 0),
            'cdek_enabled' => isset($_POST['cdek_enabled']) ? 1 : 0,
            'cdek_title' => sanitize_text_field($_POST['cdek_title'] ?? 'СДЭК'),
            'cdek_api_key' => sanitize_text_field($_POST['cdek_api_key'] ?? ''),
            'cdek_api_secret' => sanitize_text_field($_POST['cdek_api_secret'] ?? ''),
            'cdek_cost' => floatval($_POST['cdek_cost'] ?? 0),
            'free_shipping_from' => floatval($_POST['free_shipping_from'] ?? 0),
        ];
        update_option('dshop_shipping_settings', $settings);

        wp_redirect(admin_url('admin.php?page=dshop-shipping&updated=1'));
        exit;
    }

    public function renderShippingPage(): void
    {
        $settings = get_option('dshop_shipping_settings', [
            'pickup_enabled' => 1,
            'pickup_title' => 'Самовывоз',
            'pickup_address' => '',
            'pickup_cost' => 0,
            'city_enabled' => 0,
            'city_title' => 'Городская транспортная компания',
            'city_cost' => 300,
            'city_free_from' => 5000,
            'cdek_enabled' => 0,
            'cdek_title' => 'СДЭК',
            'cdek_api_key' => '',
            'cdek_api_secret' => '',
            'cdek_cost' => 0,
            'free_shipping_from' => 0,
        ]);

        include DSHOP_SRC_DIR . 'modules/shipping/views/shipping-settings.php';
    }

    /**
     * Load shipping methods
     *
     * @return void
     */
    private function loadMethods(): void
    {
        $this->methods = [
            'pickup' => new Pickup(),
            'city' => new CityTransport(),
            'cdek' => new CDEK(),
        ];

        foreach ($this->methods as $method) {
            if ($method->isActive()) {
                $method->init();
            }
        }
    }

    /**
     * Get shipping method by ID
     *
     * @param string $method_id Method ID
     * @return ShippingMethod|null
     */
    public function getMethod(string $method_id): ?ShippingMethod
    {
        return $this->methods[$method_id] ?? null;
    }

    /**
     * Get all active methods
     *
     * @return array
     */
    public function getActiveMethods(): array
    {
        return array_filter($this->methods, function($method) {
            return $method->isActive();
        });
    }

    /**
     * Calculate shipping cost
     *
     * @param string $method_id Method ID
     * @param array $params Parameters
     * @return float|WP_Error
     */
    public function calculateCost(string $method_id, array $params = [])
    {
        $method = $this->getMethod($method_id);

        if (!$method) {
            return new \WP_Error('invalid_method', 'Неверный способ доставки');
        }

        if (!$method->isActive()) {
            return new \WP_Error('inactive_method', 'Способ доставки неактивен');
        }

        return $method->calculateCost($params);
    }

    /**
     * AJAX calculate shipping
     *
     * @return void
     */
    public function ajaxCalculateShipping(): void
    {
        check_ajax_referer('dshop_nonce', 'nonce');

        $method_id = isset($_POST['shipping_method']) ? sanitize_text_field($_POST['shipping_method']) : '';
        $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
        $postcode = isset($_POST['postcode']) ? sanitize_text_field($_POST['postcode']) : '';

        if (!$method_id) {
            wp_send_json_error(['message' => 'Выберите способ доставки']);
        }

        $result = $this->calculateCost($method_id, [
            'city' => $city,
            'postcode' => $postcode,
        ]);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success([
            'cost' => number_format($result, 2, '.', ' '),
            'currency' => get_option('dshop_currency', 'RUB'),
        ]);
    }

    /**
     * Shipping methods shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function shippingMethodsShortcode(array $atts): string
    {
        ob_start();
        
        $methods = $this->getActiveMethods();
        
        if (empty($methods)) {
            echo '<p>' . 'Нет доступных способов доставки' . '</p>';
        } else {
            echo '<div class="dshop-shipping-methods">';
            foreach ($methods as $method) {
                echo '<label class="dshop-shipping-method">';
                echo '<input type="radio" name="shipping_method" value="' . esc_attr($method->getId()) . '">';
                echo '<span class="dshop-shipping-method__label">' . esc_html($method->getTitle()) . '</span>';
                echo '<span class="dshop-shipping-method__description">' . esc_html($method->getDescription()) . '</span>';
                echo '<span class="dshop-shipping-method__cost">' . esc_html($method->getCostText()) . '</span>';
                echo '</label>';
            }
            echo '</div>';
        }
        
        return ob_get_clean();
    }
}
